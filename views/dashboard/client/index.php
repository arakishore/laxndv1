<?php
//Client Dashboard view page
/*
0. Prepare for request
1. Validate session and current user details. Get necessary preresuisites for header, sidebar, access control
2. if current user belongs to Client/Distributor group, redirect to relevant dashboard
3. Validate portfolio date parameter. If not set, get latest MIS date from Distributor
4. get the MIS data from DB for client code BUOYANTCAP
5. remove columns 12/L, 8/H from header, subtotals, totals, cashreceivables, netassets and securities array
6. check if request is to export, then create excel, else print on screen
*/

session_start();
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
//prepare for request
//include necessary helpers
require_once('../../../config/config.php');

//check if session is active
$sessionCheck = checkSession();

//include some more necessary helpers
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/logs/logsProcessor.php');
require_once(__ROOT__.'/config/logs/logsCoreFunctions.php');
require_once(__ROOT__.'/vendor/phpoffice/phpspreadsheet/src/Bootstrap.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

//include necessary models
require_once(__ROOT__.'/model/user/userModel.php');
require_once(__ROOT__.'/model/client/clientModel.php');
require_once(__ROOT__.'/model/distributor/distributorModel.php');
require_once(__ROOT__.'/model/activate/activateModel.php'); 

//Connection With Database
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1).$conn["errMsg"];
} else {
    $conn = $conn["errMsg"];
    $returnArr = array();

    //get the user info
    $email = $_SESSION['userEmail'];
    if(isset($_GET["userName"]) && !empty($_GET["userName"])) {
         $email = $_GET["userName"];
    } else {
        $email = $_SESSION['userEmail'];
    }

    //initialize logs
    $logsProcessor = new logsProcessor();
    $initLogs = initializeJsonLogs($email);
    $logFilePath = $logStorePaths["dashboard"];
    $logFileName="clientDashboard.json";

    $logMsg = "Client Dashboard process start.";
    $logData['step1']["data"] = "1. {$logMsg}";

    $logMsg = "Database connection successful.";
    $logData["step2"]["data"] = "2. {$logMsg}";

    $logMsg = "Attempting to get client info.";
    $logData["step3"]["data"] = "3. {$logMsg}";

    $userSearchArr = array('email'=>$email);
    $fieldsStr = "email, status, image, rights, `groups`, firstname, lastname";
    $userInfo = getUserInfo($userSearchArr, $fieldsStr, $conn);
    if (!noError($userInfo)) {
        //error fetching user info
        $logMsg = "Couldn't fetch user info: {$userInfo["errMsg"]}";
        $logData["step3.1"]["data"] = "3.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5).": Error fetching user details.";
    } else {
        //check if user not found
        $userInfo = $userInfo["errMsg"];
        // printArr($userInfo); exit;
        if (empty($userInfo)) {
            //user not found
            $logMsg = "User not found: {$token}";
            $logData["step3.1"]["data"] = "3.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5).": This URL is invalid or expired.";
        } else {
            //check if user is active
            //first get the user email
            $email = array_keys($userInfo);
            $email = $email[0];
            if ($userInfo[$email]["status"]!=1) {
                //user not active
                $logMsg = "User not active: {$token}";
                $logData["step3.1"]["data"] = "3.1 {$logMsg}";
                $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                $returnArr["errCode"] = 5;
                $returnArr["errMsg"] = getErrMsg(5)." This URL is invalid or expired.";
            } else {
                $flag = 0;
                $user = "";
                if(isset($_GET["userName"]) && !empty($_GET["userName"])) {
                    if (strpos($userInfo[$email]["groups"], "superadmin") !== false) {
                        $user = $_GET["userName"];
                    } else if (strpos($userInfo[$email]["groups"], "Distributors") !== false) {
                        $user = $_GET["userName"];
                        // printArr($user); exit;
                        //Search Distributor ID
                        $distributorSearchArr = array("email"=>$email);
                        $fieldsStr = "distributor_id, email";
                        $allDistributors = getDistributorsInfo($distributorSearchArr, $fieldsStr, null, $conn);
                        $userId = $allDistributors["errMsg"][$email];

                        //Search Distribuotr Id in Client Group
                        $clientSearchArr = array();
                        $clientSearchArr["source"] = $userId["distributor_id"];
                        $fieldsStr = "email";
                        $dateField = null;
                        $allClientsCount = getClientsInfo_email($clientSearchArr, $fieldsStr, $dateField, $conn);
                        $allClientsCount = $allClientsCount["errMsg"];
                        $returnArr["errCode"] = -1;
                        //Check If Client mail not matches with AllclientCount
                        foreach ($allClientsCount as $clientEmail => $clientDetails) {
                            if ($user == $clientDetails["email"]) {
                                $flag = 1;
                            }
                        }
                        if (!$flag) {
                            $user = "";
                        }
                    }
                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Not Authorized.";
                } else {
                    $returnArr["errCode"] = -1;
                    $user = $_SESSION['userEmail'];
                }

                 
            } //close checking if user is active
        } //close checking if user not found
    } //close no error userinfo else
} //close check db conn
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="icon" type="image/png" href="<?php echo $rootUrl; ?>assets/img/nirvana_favicon.png" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title><?php echo APPNAME; ?></title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />
    <link href="<?php echo $rootUrl; ?>assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo $rootUrl; ?>assets/css/material-dashboard.css?v=1.2.0" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Work+Sans:400,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/style.css">
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

    legend {
        margin-bottom: 20px;
        font-size: 21px;
        width: auto;
        padding: 0 10px;
    }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php 
            $pageTitle = "Content Owner Dashboard ";
            //include access control
            require_once(__ROOT__.'/controller/access-control/checkUserAccess.php');
            require_once(__ROOT__."/views/common/sidebar.php");
        ?>
        <div class="main-panel">
            <?php 
                require_once(__ROOT__."/views/common/header.php");
            ?>
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <!-- card header and breadcrumbs -->
                                <div class="card-header">
                                    <h4 class="title"><?php echo cleanXSS($pageTitle); ?></h4>
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="<?php echo $rootUrl."views/dashboard/client"; ?>"><i
                                                    class="fa fa-dashboard">&nbsp;</i>Dashboard</a>
                                        </li>
                                    </ol>
                                </div>
                                <div class="card-content">

                                    <!-- <div class="alert <?php //echo $alertClass; ?>">
                                            <span>
                                                <?php echo $alertMsg; ?>
                                            </span>
                                         </div> -->
                                    <?php
                                    // }
                                    ?>
                                    <!-- end success/error messages -->
                                    <!-- Search by date form -->

                                    <!-- client dashboard table -->
                                    <fieldset class="scheduler-border">
                                        <legend>Youtube Claim report</legend>

                                        <div class="col-md-12">
                                            <form enctype="multipart/form-data" id="getPortfolioForm"
                                                name="getPortfolioForm" action="youtubev2.php" required method="GET">
                                                <input type="hidden" name="userName" value="<?php echo $email?>">
                                                <input type="hidden" name="type_table" value="nd">
                                                <div class="col-md-4 col-sm-12">

                                                    <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo_email_v5($clientSearchArr, $fieldsStr, null, $conn);
            
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                
                    //print_r($clientInfo['errMsg']);
                   $clientname = $clientInfo['errMsg'][strtolower($email)]['client_username'];
                  
                   $table_type_name = 'youtube_video_claim_activation_report_%';

                   $allfantable = getAvilableActivateReportsYoutubev2($table_type_name, $clientname ,$conn);
                  // print_r($allfantable);
                   if (!noError($allfantable)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$allfantable["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                   }else{
                    $allfantable = $allfantable['errMsg']; 
                   }
                }
                                            ?>
                                                    <label class="control-label">Youtube Claim report v2</label>

                                                    <select name="reportMonthYear" class="form-control">
                                                        <option value="">--Select report--</option>
                                                        <?php 
                                                     foreach($allfantable as $k=>$v){
                                                         $t=date('F-Y',strtotime($v));
                                                         echo '<option value="'.$v.'">'.$t.'</option>';   

                                                     }                   
                                                ?>
                                                    </select>
                                                    <!-- <input type="date" class="form-control"
                                                    data-parsley-trigger="keyup" required="" id="portfolio_date" name="portfolio_date" 
                                                    value="<?php //echo $latestMISDate; ?>"
                                                > -->
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <button type="submit" class="btn btn-success">Go</button>
                                                </div>
                                            </form>
                                        </div>
                                        <!-- client dashboard table -->


                                        <div class="col-md-12">
                                            <form enctype="multipart/form-data" id="getPortfolioForm"
                                                name="getPortfolioForm" action="youtuberedmusicvideofinancev2.php"
                                                required method="GET">
                                                <input type="hidden" name="userName" value="<?php echo $email?>">
                                                <input type="hidden" name="type_table" value="nd">
                                                <div class="col-md-4 col-sm-12">

                                                    <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo_email($clientSearchArr, $fieldsStr, null, $conn);
             
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                   $clientname = $clientInfo['errMsg'][$email]['client_username'];
                   $table_type_name = 'youtube_red_music_finance_activation_report_nd%';
                   $allfantable = getAvilableActivateReportsYoutubev2($table_type_name, $clientname ,$conn);
                  // print_r($allfantable);
                   if (!noError($allfantable)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$allfantable["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                   }else{
                    $allfantable = $allfantable['errMsg']; 
                   }
                }
                                            ?>
                                                    <label class="control-label">Youtube red music video finance report
                                                        v2</label>

                                                    <select name="reportMonthYear" class="form-control">
                                                        <option value="">--Select report--</option>
                                                        <?php 
                                                     foreach($allfantable as $k=>$v){
                                                         $t=date('F-Y',strtotime($v));
                                                         echo '<option value="'.$v.'">'.$t.'</option>';   

                                                     }                   
                                                ?>
                                                    </select>
                                                    <!-- <input type="date" class="form-control"
                                                    data-parsley-trigger="keyup" required="" id="portfolio_date" name="portfolio_date" 
                                                    value="<?php //echo $latestMISDate; ?>"
                                                > -->
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <button type="submit" class="btn btn-success">Go</button>
                                                </div>
                                            </form>
                                        </div>
                                        <!-- client dashboard table -->
                                        <!-- client dashboard table -->


                                        <div class="col-md-12">
                                            <form enctype="multipart/form-data" id="getPortfolioForm"
                                                name="getPortfolioForm" action="youtubeecommercepaidfeaturesv2.php"
                                                required method="GET">
                                                <input type="hidden" name="userName" value="<?php echo $email?>">
                                                <input type="hidden" name="type_table" value="nd">
                                                <div class="col-md-4 col-sm-12">

                                                    <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo_email($clientSearchArr, $fieldsStr, null, $conn);
             
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                   $clientname = $clientInfo['errMsg'][$email]['client_username'];
                   $table_type_name = 'youtube_ecom_paid_features_activation_report_nd%';
                   $allfantable = getAvilableActivateReportsYoutubev2($table_type_name, $clientname ,$conn);
                   
                   if (!noError($allfantable)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$allfantable["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                   }else{
                    $allfantable = $allfantable['errMsg']; 
                   }
                }
                                            ?>
                                                    <label class="control-label">youtube ecommerce paid features report
                                                        v2</label>

                                                    <select name="reportMonthYear" class="form-control">
                                                        <option value="">--Select report--</option>
                                                        <?php 
                                                     foreach($allfantable as $k=>$v){
                                                         $t=date('F-Y',strtotime($v));
                                                         echo '<option value="'.$v.'">'.$t.'</option>';   

                                                     }                   
                                                ?>
                                                    </select>
                                                    <!-- <input type="date" class="form-control"
                                                    data-parsley-trigger="keyup" required="" id="portfolio_date" name="portfolio_date" 
                                                    value="<?php //echo $latestMISDate; ?>"
                                                > -->
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <button type="submit" class="btn btn-success">Go</button>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- client dashboard table -->


                                        <div class="col-md-12">
                                            <form enctype="multipart/form-data" id="getPortfolioForm"
                                                name="getPortfolioForm" action="youtubeusreportv2.php" required
                                                method="GET">
                                                <input type="hidden" name="userName" value="<?php echo $email?>">
                                                <input type="hidden" name="type_table" value="nd">
                                                <div class="col-md-4 col-sm-12">

                                                    <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo_email($clientSearchArr, $fieldsStr, null, $conn);
             
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                   $clientname = $clientInfo['errMsg'][$email]['client_username'];
                   $table_type_name = 'youtube_labelengine_activation_report_nd%';
                   $allfantable = getAvilableActivateReportsYoutubev2($table_type_name, $clientname ,$conn);
                   
                   if (!noError($allfantable)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$allfantable["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                   }else{
                    $allfantable = $allfantable['errMsg']; 
                   }
                }
                                            ?>
                                                    <label class="control-label">Youtube US Report
                                                        v2</label>

                                                    <select name="reportMonthYear" class="form-control">
                                                        <option value="">--Select report--</option>
                                                        <?php 
                                                     foreach($allfantable as $k=>$v){
                                                         $t=date('F-Y',strtotime($v));
                                                         echo '<option value="'.$v.'">'.$t.'</option>';   

                                                     }                   
                                                ?>
                                                    </select>
                                                    <!-- <input type="date" class="form-control"
                                                    data-parsley-trigger="keyup" required="" id="portfolio_date" name="portfolio_date" 
                                                    value="<?php //echo $latestMISDate; ?>"
                                                > -->
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <button type="submit" class="btn btn-success">Go</button>
                                                </div>
                                            </form>
                                        </div>

                                    </fieldset>
                                    <!-- client dashboard table -->
                                    <fieldset class="scheduler-border">
                                        <legend>Youtube Music</legend>

                                        <div class="col-md-12">
                                            <form enctype="multipart/form-data" id="getPortfolioForm"
                                                name="getPortfolioForm" action="youtuberedmusic_v2.php" required
                                                method="GET">
                                                <input type="hidden" name="userName" value="<?php echo $email?>">
                                                <input type="hidden" name="type_table" value="redmusic">
                                                <div class="col-md-4 col-sm-12">

                                                    <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo_email($clientSearchArr, $fieldsStr, null, $conn);
             
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                   $clientname = $clientInfo['errMsg'][$email]['client_username'];
                   $table_type_name = 'youtube_redmusic_activation_report_redmusic_%';
                   $allfantable = getAvilableActivateReportsYoutubev2($table_type_name, $clientname ,$conn);
                   
                   if (!noError($allfantable)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$allfantable["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                   }else{
                    $allfantable = $allfantable['errMsg']; 
                   }
                }
                                            ?>
                                                    <label class="control-label">Youtube Red-Music report v2</label>

                                                    <select name="reportMonthYear" class="form-control">
                                                        <option value="">--Select report--</option>
                                                        <?php 
                                                     foreach($allfantable as $k=>$v){
                                                         $t=date('F-Y',strtotime($v));
                                                         echo '<option value="'.$v.'">'.$t.'</option>';   

                                                     }                   
                                                ?>
                                                    </select>
                                                    <!-- <input type="date" class="form-control"
                                                    data-parsley-trigger="keyup" required="" id="portfolio_date" name="portfolio_date" 
                                                    value="<?php //echo $latestMISDate; ?>"
                                                > -->
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <button type="submit" class="btn btn-success">Go</button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="col-md-12">
                                            <form enctype="multipart/form-data" id="getPortfolioForm"
                                                name="getPortfolioForm" action="youtuberedmusicvideofinancev2.php"
                                                required method="GET">
                                                <input type="hidden" name="userName" value="<?php echo $email?>">
                                                <input type="hidden" name="type_table" value="redmusic">
                                                <div class="col-md-4 col-sm-12">

                                                    <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo_email($clientSearchArr, $fieldsStr, null, $conn);
             
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                   $clientname = $clientInfo['errMsg'][$email]['client_username'];
                   $table_type_name = 'youtube_red_music_finance_activation_report_redmusic%';
                   $allfantable = getAvilableActivateReportsYoutubev2($table_type_name, $clientname ,$conn);
                   
                  // print_r($allfantable);

                   if (!noError($allfantable)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$allfantable["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                   }else{
                    $allfantable = $allfantable['errMsg']; 
                   }
                }
                                            ?>
                                                    <label class="control-label">Youtube red music video finance report v2</label>

                                                    <select name="reportMonthYear" class="form-control">
                                                        <option value="">--Select report--</option>
                                                        <?php 
                                                     foreach($allfantable as $k=>$v){
                                                         $t=date('F-Y',strtotime($v));
                                                         if($v!="08-bk"){
                                                         echo '<option value="'.$v.'">'.$t.'</option>';   
                                                         }

                                                     }                   
                                                ?>
                                                    </select>
                                                    <!-- <input type="date" class="form-control"
                                                    data-parsley-trigger="keyup" required="" id="portfolio_date" name="portfolio_date" 
                                                    value="<?php //echo $latestMISDate; ?>"
                                                > -->
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <button type="submit" class="btn btn-success">Go</button>
                                                </div>
                                            </form>
                                        </div>
                                        <!-- client dashboard table -->
                                        <!-- client dashboard table -->


                                        <div class="col-md-12">
                                            <form enctype="multipart/form-data" id="getPortfolioForm"
                                                name="getPortfolioForm" action="youtubeecommercepaidfeaturesv2.php"
                                                required method="GET">
                                                <input type="hidden" name="userName" value="<?php echo $email?>">
                                                <input type="hidden" name="type_table" value="redmusic">
                                                <div class="col-md-4 col-sm-12">

                                                    <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo_email($clientSearchArr, $fieldsStr, null, $conn);
             
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                   $clientname = $clientInfo['errMsg'][$email]['client_username'];
                   $table_type_name = 'youtube_ecom_paid_features_activation_report_redmusic%';
                   $allfantable = getAvilableActivateReportsYoutubev2($table_type_name, $clientname ,$conn);
                   
                   if (!noError($allfantable)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$allfantable["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                   }else{
                    $allfantable = $allfantable['errMsg']; 
                   }
                }
                                            ?>
                                                    <label class="control-label">youtube ecommerce paid features report
                                                        v2</label>

                                                    <select name="reportMonthYear" class="form-control">
                                                        <option value="">--Select report--</option>
                                                        <?php 
                                                     foreach($allfantable as $k=>$v){
                                                         $t=date('F-Y',strtotime($v));
                                                         echo '<option value="'.$v.'">'.$t.'</option>';   

                                                     }                   
                                                ?>
                                                    </select>
                                                    <!-- <input type="date" class="form-control"
                                                    data-parsley-trigger="keyup" required="" id="portfolio_date" name="portfolio_date" 
                                                    value="<?php //echo $latestMISDate; ?>"
                                                > -->
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <button type="submit" class="btn btn-success">Go</button>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- client dashboard table -->


                                        <div class="col-md-12">
                                            <form enctype="multipart/form-data" id="getPortfolioForm"
                                                name="getPortfolioForm" action="youtubeusreportv2.php" required
                                                method="GET">
                                                <input type="hidden" name="userName" value="<?php echo $email?>">
                                                <input type="hidden" name="type_table" value="redmusic">
                                                <div class="col-md-4 col-sm-12">

                                                    <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo_email($clientSearchArr, $fieldsStr, null, $conn);
             
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                   $clientname = $clientInfo['errMsg'][$email]['client_username'];
                   $table_type_name = 'youtube_labelengine_activation_report_redmusic_%';
                   $allfantable = getAvilableActivateReportsYoutubev2($table_type_name, $clientname ,$conn);
                   
                   if (!noError($allfantable)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$allfantable["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                   }else{
                    $allfantable = $allfantable['errMsg']; 
                   }
                }
                                            ?>
                                                    <label class="control-label">Youtube US Report
                                                        v2</label>

                                                    <select name="reportMonthYear" class="form-control">
                                                        <option value="">--Select report--</option>
                                                        <?php 
                                                     foreach($allfantable as $k=>$v){
                                                         $t=date('F-Y',strtotime($v));
                                                         echo '<option value="'.$v.'">'.$t.'</option>';   

                                                     }                   
                                                ?>
                                                    </select>
                                                    <!-- <input type="date" class="form-control"
                                                    data-parsley-trigger="keyup" required="" id="portfolio_date" name="portfolio_date" 
                                                    value="<?php //echo $latestMISDate; ?>"
                                                > -->
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <button type="submit" class="btn btn-success">Go</button>
                                                </div>
                                            </form>
                                        </div>

                                    </fieldset>
                                    <fieldset class="scheduler-border">
                                        <legend>Apple Music</legend>

                                        <div class="col-md-12">
                                            <form enctype="multipart/form-data" id="getPortfolioForm"
                                                name="getPortfolioForm" action="applemusic_v2.php" required
                                                method="GET">
                                                <input type="hidden" name="userName" value="<?php echo $email?>">
                                                <input type="hidden" name="type_table" value="applemusic">
                                                <div class="col-md-4 col-sm-12">

                                                    <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo_email($clientSearchArr, $fieldsStr, null, $conn);
             
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                   $clientname = $clientInfo['errMsg'][$email]['client_username'];
                   $table_type_name = 'report_audio_activation_applemusic%';
                   $allfantable = getAvilableActivateReportsYoutubev2($table_type_name, $clientname ,$conn);
                   
                   if (!noError($allfantable)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$allfantable["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                   }else{
                    $allfantable = $allfantable['errMsg']; 
                   }
                }
                                            ?>
                                                    <label class="control-label">Apple Music Report
                                                        v2</label>

                                                    <select name="reportMonthYear" class="form-control">
                                                        <option value="">--Select report--</option>
                                                        <?php 
                                                     foreach($allfantable as $k=>$v){
                                                         $t=date('F-Y',strtotime($v));
                                                         echo '<option value="'.$v.'">'.$t.'</option>';   

                                                     }                   
                                                ?>
                                                    </select>
                                                    <!-- <input type="date" class="form-control"
                                                    data-parsley-trigger="keyup" required="" id="portfolio_date" name="portfolio_date" 
                                                    value="<?php //echo $latestMISDate; ?>"
                                                > -->
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <button type="submit" class="btn btn-success">Go</button>
                                                </div>
                                            </form>
                                        </div>
                                    </fieldset>
                                    <fieldset class="scheduler-border">
                                        <legend>Itune Music</legend>

                                        <div class="col-md-12">
                                            <form enctype="multipart/form-data" id="getPortfolioForm"
                                                name="getPortfolioForm" action="itunemusic_v2.php" required
                                                method="GET">
                                                <input type="hidden" name="userName" value="<?php echo $email?>">
                                                <input type="hidden" name="type_table" value="itune">
                                                <div class="col-md-4 col-sm-12">

                                                    <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo_email($clientSearchArr, $fieldsStr, null, $conn);
             
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                   $clientname = $clientInfo['errMsg'][$email]['client_username'];
                   $table_type_name = 'report_audio_activation_itune%';
                   $allfantable = getAvilableActivateReportsYoutubev2($table_type_name, $clientname ,$conn);
                   
                   if (!noError($allfantable)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$allfantable["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                   }else{
                    $allfantable = $allfantable['errMsg']; 
                   }
                }
                                            ?>
                                                    <label class="control-label">Itune Music Report
                                                        v2</label>

                                                    <select name="reportMonthYear" class="form-control">
                                                        <option value="">--Select report--</option>
                                                        <?php 
                                                     foreach($allfantable as $k=>$v){
                                                         $t=date('F-Y',strtotime($v));
                                                         echo '<option value="'.$v.'">'.$t.'</option>';   

                                                     }                   
                                                ?>
                                                    </select>
                                                    <!-- <input type="date" class="form-control"
                                                    data-parsley-trigger="keyup" required="" id="portfolio_date" name="portfolio_date" 
                                                    value="<?php //echo $latestMISDate; ?>"
                                                > -->
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <button type="submit" class="btn btn-success">Go</button>
                                                </div>
                                            </form>
                                        </div>
                                    </fieldset>
                                    <fieldset class="scheduler-border">
                                        <legend>Gaana Music</legend>

                                        <div class="col-md-12">
                                            <form enctype="multipart/form-data" id="getPortfolioForm"
                                                name="getPortfolioForm" action="gaanamusgc_v2.php" required
                                                method="GET">
                                                <input type="hidden" name="userName" value="<?php echo $email?>">
                                                <input type="hidden" name="type_table" value="gaana">
                                                <div class="col-md-4 col-sm-12">

                                                    <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo_email($clientSearchArr, $fieldsStr, null, $conn);
             
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                   $clientname = $clientInfo['errMsg'][$email]['client_username'];
                   $table_type_name = 'report_audio_activation_gaana%';
                   $allfantable = getAvilableActivateReportsYoutubev2($table_type_name, $clientname ,$conn);
                   
                   if (!noError($allfantable)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$allfantable["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                   }else{
                    $allfantable = $allfantable['errMsg']; 
                   }
                }
                                            ?>
                                                    <label class="control-label">Gaana Music Report v2</label>

                                                    <select name="reportMonthYear" class="form-control">
                                                        <option value="">--Select report--</option>
                                                        <?php 
                                                     foreach($allfantable as $k=>$v){
                                                         $t=date('F-Y',strtotime($v));
                                                         echo '<option value="'.$v.'">'.$t.'</option>';   

                                                     }                   
                                                ?>
                                                    </select>
                                                    <!-- <input type="date" class="form-control"
                                                    data-parsley-trigger="keyup" required="" id="portfolio_date" name="portfolio_date" 
                                                    value="<?php //echo $latestMISDate; ?>"
                                                > -->
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <button type="submit" class="btn btn-success">Go</button>
                                                </div>
                                            </form>
                                        </div>
                                    </fieldset>
                                    <fieldset class="scheduler-border">
                                        <legend>Saavan Music</legend>

                                        <div class="col-md-12">
                                            <form enctype="multipart/form-data" id="getPortfolioForm"
                                                name="getPortfolioForm" action="saavanmusic_v2.php" required
                                                method="GET">
                                                <input type="hidden" name="userName" value="<?php echo $email?>">
                                                <input type="hidden" name="type_table" value="saavan">
                                                <div class="col-md-4 col-sm-12">

                                                    <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo_email($clientSearchArr, $fieldsStr, null, $conn);
             
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                   $clientname = $clientInfo['errMsg'][$email]['client_username'];
                   $table_type_name = 'report_audio_activation_saavan%';
                   $allfantable = getAvilableActivateReportsYoutubev2($table_type_name, $clientname ,$conn);
                   
                   if (!noError($allfantable)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$allfantable["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                   }else{
                    $allfantable = $allfantable['errMsg']; 
                   }
                }
                                            ?>
                                                    <label class="control-label">Saavan Music Report v2</label>

                                                    <select name="reportMonthYear" class="form-control">
                                                        <option value="">--Select report--</option>
                                                        <?php 
                                                     foreach($allfantable as $k=>$v){
                                                         $t=date('F-Y',strtotime($v));
                                                         echo '<option value="'.$v.'">'.$t.'</option>';   

                                                     }                   
                                                ?>
                                                    </select>
                                                    <!-- <input type="date" class="form-control"
                                                    data-parsley-trigger="keyup" required="" id="portfolio_date" name="portfolio_date" 
                                                    value="<?php //echo $latestMISDate; ?>"
                                                > -->
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <button type="submit" class="btn btn-success">Go</button>
                                                </div>
                                            </form>
                                        </div>
                                    </fieldset>


                                    <fieldset class="scheduler-border">
                                        <legend>Spotify Music</legend>

                                        <div class="col-md-12">
                                            <form enctype="multipart/form-data" id="getPortfolioForm"
                                                name="getPortfolioForm" action="spotifymusic_v2.php" required
                                                method="GET">
                                                <input type="hidden" name="userName" value="<?php echo $email?>">
                                                <input type="hidden" name="type_table" value="spotify">
                                                <div class="col-md-4 col-sm-12">

                                                    <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo_email($clientSearchArr, $fieldsStr, null, $conn);
             
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                   $clientname = $clientInfo['errMsg'][$email]['client_username'];
                   $table_type_name = 'report_audio_activation_spotify%';
                   $allfantable = getAvilableActivateReportsYoutubev2($table_type_name, $clientname ,$conn);
                   
                   if (!noError($allfantable)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$allfantable["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                   }else{
                    $allfantable = $allfantable['errMsg']; 
                   }
                }
                                            ?>
                                                    <label class="control-label">Spotify Music Report v2</label>

                                                    <select name="reportMonthYear" class="form-control">
                                                        <option value="">--Select report--</option>
                                                        <?php 
                                                     foreach($allfantable as $k=>$v){
                                                         $t=date('F-Y',strtotime($v));
                                                         echo '<option value="'.$v.'">'.$t.'</option>';   

                                                     }                   
                                                ?>
                                                    </select>
                                                    <!-- <input type="date" class="form-control"
                                                    data-parsley-trigger="keyup" required="" id="portfolio_date" name="portfolio_date" 
                                                    value="<?php //echo $latestMISDate; ?>"
                                                > -->
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <button type="submit" class="btn btn-success">Go</button>
                                                </div>
                                            </form>
                                        </div>
                                    </fieldset>


                                    <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo_email($clientSearchArr, $fieldsStr, null, $conn);
             
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                   $clientname = $clientInfo['errMsg'][$email]['client_username'];
                   $table_type_name = 'report_publishing_main_non_cmg%';
                   $allfantable = getAvilableActivateReportsMain_non_cmgv2($table_type_name, $clientname ,$conn);
                   
                   if (!noError($allfantable)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$allfantable["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                   }else{
                    $allfantable = $allfantable['errMsg']; 
                   }
                }

                        if(isset($allfantable) && count($allfantable)>0){

                        
                                            ?>
                                    <fieldset class="scheduler-border">
                                        <legend>Publisher Non CMG (Main)</legend>

                                        <div class="col-md-12">
                                            <form enctype="multipart/form-data" id="getPortfolioForm"
                                                name="getPortfolioForm" action="cmg_publishing_main_non_cmg.php" required
                                                method="GET">
                                                <input type="hidden" name="userName" value="<?php echo $email?>">
                                                <input type="hidden" name="type_table" value="main_non_cmg">
                                                <div class="col-md-4 col-sm-12">

                                                
                                                    <label class="control-label">Publisher Non CMG (Main) v2</label>

                                                    <select name="reportMonthYear" class="form-control">
                                                        <option value="">--Select report--</option>
                                                        <?php 
                                                     foreach($allfantable as $k=>$v){
                                                         $t=date('F-Y',strtotime($v));
                                                         echo '<option value="'.$v.'">'.$t.'</option>';   

                                                     }                   
                                                ?>
                                                    </select>
                                                    <!-- <input type="date" class="form-control"
                                                    data-parsley-trigger="keyup" required="" id="portfolio_date" name="portfolio_date" 
                                                    value="<?php //echo $latestMISDate; ?>"
                                                > -->
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <button type="submit" class="btn btn-success">Go</button>
                                                </div>
                                            </form>
                                        </div>
                                    </fieldset>
                                <?php }?>        
                                
                                
                                <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo_email($clientSearchArr, $fieldsStr, null, $conn);
             
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                   $clientname = $clientInfo['errMsg'][$email]['client_username'];
                   $table_type_name = 'report_publishing_redsubscription_non_cmg%';
                   $allfantable = getAvilableActivateReportsMain_non_cmgv2($table_type_name, $clientname ,$conn);
                   
                   if (!noError($allfantable)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$allfantable["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                   }else{
                    $allfantable = $allfantable['errMsg']; 
                   }
                }

                        if(isset($allfantable) && count($allfantable)>0){

                        
                                            ?>
                                    <fieldset class="scheduler-border">
                                        <legend>Publisher Non CMG (Red/Subscription)</legend>

                                        <div class="col-md-12">
                                            <form enctype="multipart/form-data" id="getPortfolioForm"
                                                name="getPortfolioForm" action="cmg_publishing_red_subsciption_non_cmg.php" required
                                                method="GET">
                                                <input type="hidden" name="userName" value="<?php echo $email?>">
                                                <input type="hidden" name="type_table" value="redsubscription_non_cmg">
                                                <div class="col-md-4 col-sm-12">

                                                
                                                    <label class="control-label">Publisher Non CMG (Red/Subscription) v2</label>

                                                    <select name="reportMonthYear" class="form-control">
                                                        <option value="">--Select report--</option>
                                                        <?php 
                                                     foreach($allfantable as $k=>$v){
                                                         $t=date('F-Y',strtotime($v));
                                                         echo '<option value="'.$v.'">'.$t.'</option>';   

                                                     }                   
                                                ?>
                                                    </select>
                                                    <!-- <input type="date" class="form-control"
                                                    data-parsley-trigger="keyup" required="" id="portfolio_date" name="portfolio_date" 
                                                    value="<?php //echo $latestMISDate; ?>"
                                                > -->
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <button type="submit" class="btn btn-success">Go</button>
                                                </div>
                                            </form>
                                        </div>
                                    </fieldset>
                                <?php }?>        

                                    <!-- client dashboard table -->


                                    <div class="card-content">


                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<!--   Core JS Files   -->
<script src="<?php echo $rootUrl; ?>assets/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $rootUrl; ?>assets/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo $rootUrl; ?>assets/js/material.min.js" type="text/javascript"></script>
<script src="<?php echo $rootUrl; ?>assets/js/perfect-scrollbar.jquery.min.js"></script>
<script src="<?php echo $rootUrl; ?>assets/js/material-dashboard.js"></script>
<script>
$(window).on("load", function(e) {
    //managing the floating labels behaviour
    $("form#getPortfolioForm :input").each(function() {
        var input = $(this).val();
        if ($.trim(input) != "") {
            $(this).parent().removeClass("is-empty");
        }
        $(this).on("focus", function() {
            $(this).parent().removeClass("is-empty");
        })
        $(this).on("blur", function() {
            var input = $(this).val();
            if (input && $.trim(input) != "") {
                $(this).parent().removeClass("is-empty");
            } else {
                $(this).parent().addClass("is-empty");
            }
        })
    });
});
</script>

</html>