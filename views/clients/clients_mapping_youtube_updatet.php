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
$error = [];
$success = [];

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

                    $datetime=date("ymdhis");
                    $insertTableQuery = " SELECT *  FROM channel_co_maping    INTO OUTFILE '/var/lib/mysql-files/mysql-backup/channel_co_maping.{$datetime}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";
    
                    $insertTableResult = runQuery($insertTableQuery, $conn);
    
                   
                    foreach ($records->getRecords() as $key => $record) {
                         
                       // print_r($record);

                            //create a new client
                            $arrToCreate =array(
                                'partner_provided' => $record['partner_provided'] ,
                                'Channel' =>  $record['Channel'] ,
                                'ugc' =>  $record['ugc'] ,
                                'Channel_id' =>  $record['Channel_id'] ,
                                'Label' =>  $record['Label'] ,
                                'Label2' =>  $record['Label2'] ,
                                'CMS' =>  $record['CMS'] ,
                                'client_youtube_shares' =>  $record['client_youtube_shares'] ,
                                'assetChannelID' =>  $record['assetChannelID'] ,
                                'added_by_file' => "2",
                            );

                        
                      
                     
                        $updates = array();
                    
                        foreach ($arrToCreate as $key=>$val) {
                            $value = mysqli_real_escape_string($conn, $val);
                            $updates[] = "$key = '{$value}'";
                        }
                      
                        $implodeArray = implode(', ', $updates);

                      $client_youtube_sharesQuery = "UPDATE  channel_co_maping  set  {$implodeArray}  where  id ='{$record['id']}' ";

                        $updateQueryResult1 = runQuery($client_youtube_sharesQuery, $conn);
                         
                        if (noError($updateQueryResult1)) {
                            //message 
                             $success[] = json_encode($record);
                        } else {
                              //message 
                              $error[] = json_encode($record);
                        }   
                    }

                   

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
$pageTitle = "Update Co-Mapping";
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
                                                <i class="fa fa-users">&nbsp;</i>Co-Mapping
                                            </a>
                                        </li>
                                    </ol>
                                </div> <!-- end card header -->
                                <div class="card-content">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form method="post" name="frmassigncontentowner" id="frmassigncontentowner"
                                                action="clients_mapping_youtube_updatet.php"
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
                                                                <i class="fa fa-upload"></i> Upload for Update Comapping
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        * <a href="<?php echo $rootUrl; ?>sample_files/co-mapping-update.csv"
                                                            target="_Blank">Sample Dowload</a> <br>

                                                    </div>
                                                </div>
                                                <div>

                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            Total record updated <?php echo count($success)?>
                                        </div>
                                        <div class="col-md-12">
                                            Total record error <?php echo count($error)?>
                                        </div>
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
             
        });
    });
    </script>
</body>

</html>