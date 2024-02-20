<?php
//Manage Clients view page
session_start();

//prepare for request
//include necessary helpers
require_once '../../config/config.php';

//check if session is active
$sessionCheck = checkSession();

//include some more necessary helpers
require_once __ROOT__ . '/config/dbUtils.php';
require_once __ROOT__ . '/config/errorMap.php';
require_once __ROOT__ . '/config/logs/logsProcessor.php';
require_once __ROOT__ . '/config/logs/logsCoreFunctions.php';
require_once __ROOT__ . '/vendor/league/csv/src/ByteSequence.php';
require_once __ROOT__ . '/vendor/league/csv/src/AbstractCsv.php';
require_once __ROOT__ . '/vendor/league/csv/src/Stream.php';
require_once __ROOT__ . '/vendor/league/csv/src/Reader.php';
require_once __ROOT__ . '/vendor/league/csv/src/Statement.php';
require_once __ROOT__ . '/vendor/league/csv/src/MapIterator.php';
require_once __ROOT__ . '/vendor/league/csv/src/ResultSet.php';

use League\Csv\Reader;
use League\Csv\Statement;

require_once __ROOT__ . '/vendor/league/csv/src/functions.php';

//include necessary models
require_once __ROOT__ . '/model/user/userModel.php';
require_once __ROOT__ . '/model/client/clientModel.php';
require_once __ROOT__ . '/model/distributor/distributorModel.php';
function getExtension($str)
{

    $i = strrpos($str, ".");

    if (!$i) {return "";}

    $l = strlen($str) - $i;

    $ext = substr($str, $i + 1, $l);

    return $ext;

}

//Connection With Database
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1) . $conn["errMsg"];
} else {
    $conn = $conn["errMsg"];
    $returnArr = array();
    //get the user info
    $email = $_SESSION['userEmail'];

    //initialize logs
    $logsProcessor = new logsProcessor();
    $initLogs = initializeJsonLogs($email);
    $logFilePath = $logStorePaths["clients"];
    $logFileName = "viewClients.json";

    $logMsg = "View Clients process start.";
    $logData['step1']["data"] = "1. {$logMsg}";

    $logMsg = "Database connection successful.";
    $logData["step2"]["data"] = "2. {$logMsg}";

    $logMsg = "Attempting to get user info.";
    $logData["step3"]["data"] = "3. {$logMsg}";

    $clientStatusMap = array(
        "1" => "Active",
        "0" => "Inactive",
        "2" => "Deleted",
    );

    $userSearchArr = array('email' => $email);
    $fieldsStr = "email, status, image, `groups`, rights, firstname, lastname";
    $userInfo = getUserInfo($userSearchArr, $fieldsStr, $conn);
    if (!noError($userInfo)) {
        //error fetching user info
        $logMsg = "Couldn't fetch user info: {$userInfo["errMsg"]}";
        $logData["step3.1"]["data"] = "3.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5) . ": Error fetching user details.";
    } else {
        //check if user not found
        $userInfo = $userInfo["errMsg"];
        if (empty($userInfo)) {
            //user not found
            $logMsg = "User not found: {$token}";
            $logData["step3.1"]["data"] = "3.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5) . ": This URL is invalid or expired.";
        } else {
            //check if user is active
            //first get the user email
            $email = array_keys($userInfo);
            $email = $email[0];
            if ($userInfo[$email]["status"] != 1) {
                //user not active
                $logMsg = "User not active: {$token}";
                $logData["step3.1"]["data"] = "3.1 {$logMsg}";
                $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                $returnArr["errCode"] = 5;
                $returnArr["errMsg"] = getErrMsg(5) . ": This URL is invalid or expired.";
            } else {

                $res_all_cms = array();
                $sql = "select * from cms_master";
                $getClientInfoQueryResult = runQuery($sql, $conn);
                while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {

                    $res_all_cms[] = $row['CMS'];
                }

                if (isset($_FILES['import_co_mapping']['tmp_name'])) {

                    //uploads
                    $filename = $_FILES['import_co_mapping']['name'];
                    $extension = getExtension($filename);
                    $extension = strtolower($extension);
                    $image_name = time() . '.' . $extension;
                    $newname = __ROOT__ . '/uploads/' . $image_name;
                    $copied = copy($_FILES['import_co_mapping']['tmp_name'], $newname);
                    $logsProcessor = new logsProcessor();
                    $initLogs = initializeJsonLogs($email);

                    $reader = Reader::createFromPath($_FILES['import_co_mapping']['tmp_name']);
                    $reader->setHeaderOffset(0);

                    $header = $reader->getHeader(); //returns the CSV header record

                    $reader->setHeaderOffset(0);

                    $records = (new Statement())->process($reader);

                    $truncateTableQuery = "TRUNCATE TABLE channel_co_map_import";
                    $truncateTableQueryResult = runQuery($truncateTableQuery, $conn);

                    foreach ($records->getRecords() as $key => $record) {
                        $error_falg = 0;
                        $values = array();
                        $keys = array();
                        foreach ($record as $key1 => $val) {
                            $value = mysqli_real_escape_string($conn, $val);
                            $keys[] = "`" . $key1 . "`";

                            $values[] = "'{$val}'";
                        }
                        $partner_provided = $record['Partner_Provided'];

                        //check if client already exists with this client code
                        //check if client already exists with this client code
                        $clientSearchArr = array("client_username" => $partner_provided);
                        $fieldsStr = "client_username";
                        $clientInfo = getClientsInfo($clientSearchArr, $fieldsStr, null, $conn);

                        if (!noError($clientInfo)) {
                            $error_falg = 1;
                            //error fetching client info
                            $logMsg = "Error fetching client info to check for partner_provided exist or not: {$clientInfo["errMsg"]}";
                            $query = "INSERT INTO channel_co_map_import (" . implode(",", $keys) . ",reason) VALUES (" . implode(",", $values) . ",'{$logMsg}')";
                            $queryresult = runQuery($query, $conn);
                        }

                        if (empty($clientInfo["errMsg"])) {
                            //client with this client code already exists
                            $error_falg = 1;
                            $logMsg = "partner_provided doesnot exist exists: {$partner_provided}";
                            $query = "INSERT INTO channel_co_map_import (" . implode(",", $keys) . ",reason) VALUES (" . implode(",", $values) . ",'{$logMsg}')";
                            $queryresult = runQuery($query, $conn);
                        }

                        $clientSearchArr = array("Channel" => $record['Channel'], "partner_provided" => $record['Partner_Provided'], "ugc" => $record['UGC'], "assetChannelID" => $record['AssetChannelID'], "CMS" => $record['CMS'],"Label" => $record['Label'],"Label2" => $record['Label2']);
                        $fieldsStr = "assetChannelID";
                        $clientInfo = getClientsInfoYoutube($clientSearchArr, $fieldsStr, null, $conn);
                        if (!noError($clientInfo)) {
                            $error_falg = 1;
                            $logMsg = "Error fetching client info to check for duplicate: {$clientInfo["errMsg"]}";
                            $query = "INSERT INTO channel_co_map_import (" . implode(",", $keys) . ",reason) VALUES (" . implode(",", $values) . ",'{$logMsg}')";
                            $queryresult = runQuery($query, $conn);
                        }

                        if (!empty($clientInfo["errMsg"])) {
                            $error_falg = 1;
                            $logMsg = "Client code already exists: {$partner_provided}";
                            $query = "INSERT INTO channel_co_map_import (" . implode(",", $keys) . ",reason) VALUES (" . implode(",", $values) . ",'{$logMsg}')";
                            $queryresult = runQuery($query, $conn);
                        }

                        if(!in_array($record['CMS'],$res_all_cms)){
                            $error_falg = 1;
                            $logMsg = "CMS does not exist: {$record['CMS']}";
                            $query = "INSERT INTO channel_co_map_import (" . implode(",", $keys) . ",reason) VALUES (" . implode(",", $values) . ",'{$logMsg}')";
                            $queryresult = runQuery($query, $conn);
                        }

                        if ($error_falg == 0) {
                            $query = "INSERT INTO channel_co_map_import (" . implode(",", $keys) . ",reason,status) VALUES (" . implode(",", $values) . ",'ok',1)";
                            $queryresult = runQuery($query, $conn);

                            //create a new client
                            $arrToCreate = array(
                                'userName' => array(
                                    'partner_provided' => "'" . strtoupper($record['Partner_Provided']) . "'",
                                    'Channel' => "'" . $record['Channel'] . "'",
                                    'ugc' => "'" . strtoupper($record['UGC']) . "'",
                                    'Channel_id' => "'" . $record['Channel_id'] . "'",
                                    'Label' => "'" . strtoupper($record['Label']) . "'",
                                    'Label2' => "'" . $record['Label2'] . "'",
                                    'CMS' => "'" . $record['CMS'] . "'",
                                    'client_youtube_shares' => "'" . $record['client_youtube_shares'] . "'",
                                    'assetChannelID' => "'" . $record['AssetChannelID'] . "'",
                                    'added_by_file' => "1",

                                ),
                            );

                            $fieldsStr = array_keys($arrToCreate['userName']);
                            $fieldsStr = implode(",", $fieldsStr);
                            
                            $createClientMapping = createClientMappingYoutube($arrToCreate, $fieldsStr, $conn);
                            if (!noError($createClientMapping)) {
                                //error creating client
                                $logMsg = "Client could not be created: {$createClientMapping["errMsg"]}";
                                $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                                $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                                $rollback = rollbackTransaction($conn);

                                $returnArr["errMsg"] = getErrMsg(5) . " Error creating client. Please try again after some time.";

                            }
                        }

                    }

                    $sql = "update channel_co_map_import ci , cms_master cm  set ci.status=1,ci.reason='partner_provided doesnot exist exists: {$partner_provided}' where ci.CMS=cm.CMS and ci.reason='' ";
                    $queryresult = runQuery($sql, $conn);

                }

                $fieldsStr = " id ,Channel , Partner_Provided,UGC,Channel_id,Label,AssetChannelID,Label2,CMS,client_youtube_shares,status";
                //set different getter arguments if it is in export mode
                $export = false;
                // $export = true;
                $offset = 0;
                $resultsPerPage = 9999;
                $fieldsStr = "*";
                $clientSearchArr = array("1" => 1);
                $dateField = null;
                $check = "select * from channel_co_map_import";
                $checkresult = runQuery($check, $conn);

                $res = array();
                while ($row = mysqli_fetch_assoc($checkresult["dbResource"])) {
                    $res[] = $row;
                }
                //print_r($res);
            } //close checking if user is active
        } // close checking if user is found
    } // close user info
} //close db conn

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="icon" type="image/png" href="<?php echo $rootUrl; ?>assets/img/nirvana_favicon.png" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>
        <?php echo APPNAME; ?>
    </title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />
    <link href="<?php echo $rootUrl; ?>assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo $rootUrl; ?>assets/css/material-dashboard.css?v=1.2.0" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Work+Sans:400,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/style.css">
    <script src="<?php echo $rootUrl; ?>assets/js/jquery.min.js" type="text/javascript"></script>
    <style>
    fieldset.scheduler-border {
        border: 1px groove #ddd !important;
        padding: 0 1.4em 1.4em 1.4em !important;
        margin: 0 0 1.5em 0 !important;
        -webkit-box-shadow: 0px 0px 0px 0px #000;
        box-shadow: 0px 0px 0px 0px #000;
    }

    legend.scheduler-border {
        font-size: 1.2em !important;
        font-weight: bold !important;
        text-align: left !important;
        width: auto;
        padding: 0 10px;
        border-bottom: none;
    }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php
$pageTitle = "Co-Mapping";
require_once __ROOT__ . '/controller/access-control/checkUserAccess.php';
require_once __ROOT__ . "/views/common/sidebar.php";
?>
        <div class="main-panel">
            <?php
require_once __ROOT__ . "/views/common/header.php";
?>
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <!-- card header and breadcrumbs -->
                                <div class="card-header">
                                    <h4 class="title">
                                        <?php echo cleanXSS($pageTitle); ?>
                                    </h4>
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="<?php echo $rootUrl; ?>views/clients/clients_mapping_youtube.php">
                                                <i class="fa fa-users">&nbsp;</i>Import Co-Mapping
                                            </a>
                                        </li>
                                    </ol>
                                </div> <!-- end card header -->
                                <div class="card-content">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form method="post" name="frmassigncontentowner" id="frmassigncontentowner"
                                                action="clients_mapping_youtube_import.php"
                                                enctype="multipart/form-data">
                                                <div class="">



                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <input type="file" required="" id="import_co_mapping"
                                                                name="import_co_mapping">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <button type="submit" class="chkND" name="btnUpload"
                                                                id="btnUpload">
                                                                <i class="fa fa-upload"></i> Upload for Add New
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        * <a href="<?php echo $rootUrl; ?>sample_files/co-mapping.csv"
                                                            target="_Blank">Sample Dowload</a> <br>

                                                    </div>
                                                </div>
                                                <div>

                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="responsive">
                                        <table class="table table-bordered table-condensed">
                                            <thead>
                                                <tr>

                                                    <th>SR</th>

                                                    <th>Partner_Provided</th>
                                                    <th>UGC</th>
                                                    <th>Channel_id</th>
                                                    <th>Channel</th>
                                                    <th>Label</th>
                                                    <th>AssetChannelID</th>
                                                    <th>Label2</th>
                                                    <th>CMS</th>
                                                    <th>client_youtube_shares</th>
                                                    <th>Status</th>
                                                    <th>Reason</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
foreach ($res as $key => $val) {
    ?>
                                                <tr class="<?php echo ($val["status"] != "1") ? 'danger' : ''; ?>">
                                                <td><?php echo $val["id"]; ?></td>
                                                    <td><?php echo $val["Partner_Provided"]; ?></td>
                                                    <td><?php echo $val["UGC"]; ?></td>
                                                    <td><?php echo $val["Channel_id"]; ?></td>
                                                    <td><?php echo $val["Channel"]; ?></td>
                                                    <td><?php echo $val["Label"]; ?></td>
                                                    <td><?php echo $val["AssetChannelID"]; ?></td>
                                                    <td><?php echo $val["Label2"]; ?></td>
                                                    <td><?php echo $val["CMS"]; ?></td>
                                                    <td><?php echo $val["client_youtube_shares"]; ?></td>

                                                    <td>

                                                        <?php
echo ($val["status"] == "1") ? 'OK' : 'Fail';
    ?>
                                                    </td>
                                                    <td><?php echo $val["reason"]; ?></td>
                                                </tr>
                                                <?php
}
?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div> <!-- end card content -->
                            </div> <!-- end card -->
                        </div> <!-- end col md 12 -->
                    </div> <!-- end row -->
                </div> <!-- end container fluid -->
            </div> <!-- end content -->
        </div> <!-- end main panel -->
    </div> <!-- end wrapper -->
    <?php

require_once __ROOT__ . "/views/common/loader.php";
?>
    <!--   Core JS Files   -->
    <script src="<?php echo $rootUrl; ?>assets/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/material.min.js" type="text/javascript"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/perfect-scrollbar.jquery.min.js"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/parsley.js"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/material-dashboard.js"></script>
    <script>
    $(document).ready(function() {
        $("form#frmassigncontentowner").submit(function(e) {
            /*
                       // e.preventDefault();
                        var formData = new FormData(this);

                        var filename = $('#filename').val();


                        var revenueShareYoutubeUpload = $('input[type=file]').val().split('\\').pop();

                        if (revenueShareYoutubeUpload.indexOf(' ') >= 0) {
                            $("#alert2").
                            removeClass("alert-success").
                            addClass("alert-danger").
                            fadeIn().
                            find("span").html("File name should not have any spaces in it");
                            return false;
                        }

                        //validate if filename is .csv
                        if (revenueShareYoutubeUpload.indexOf('.csv') < 0) {
                            $("#alert2").
                            removeClass("alert-success").
                            addClass("alert-danger").
                            fadeIn().
                            find("span").html("File should be .csv only");
                            return false;
                        }


                        if (1) {
                            $("#alert2").
                            removeClass("alert-danger").
                            addClass("alert-success").
                            fadeIn().
                            find("span").html("Assigning in Process.");
                            return true;
                        }




                     */
        });
    });
    </script>
</body>

</html>