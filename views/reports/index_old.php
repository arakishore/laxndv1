<?php
/*
    File - views/upload-mis-files.php
    view file that shows the form to upload MIS and Trial Balance excel files. Form submits to controller/mis/upload/index.php
    The error/success messages are also displayed here after form submission
*/

//Manage distributors view page
session_start();

//prepare for request
//include necessary helpers
require_once('../../config/config.php');

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
require_once(__ROOT__.'/model/distributor/distributorModel.php');

//Connection With Database
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);



if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1).$conn["errMsg"];
} else {
    $conn = $conn["errMsg"];

/*  
    $paramlog['table_name'] = "crep_cms_clients";
    $paramlog['file_name'] = '';
    $paramlog['status_name'] = "Insert-update";
    $paramlog['status_flag'] = "Start";
    $paramlog['date_added'] = date("Y-m-d H:i:s");
    $paramlog['ip_address'] = get_client_ip();
    $paramlog['login_user'] = $_SESSION["userEmail"];
    $paramlog['log_file'] = $logStorePaths["clients"];
    $paramlog['raw_data'] = json_encode($_POST);
    $username = activitylogs($paramlog, $conn);

 */
    $returnArr = array();

    //get the user info
    $email = $_SESSION['userEmail'];

    //initialize logs
    $logsProcessor = new logsProcessor();
    $initLogs = initializeJsonLogs($email);
    // $logFilePath = $logStorePaths["mis"];
    // $logFileName="uploadMISView.json";

    // $logMsg = "View MIS process start.";
    // $logData['step1']["data"] = "1. {$logMsg}";

    // $logMsg = "Database connection successful.";
    // $logData["step2"]["data"] = "2. {$logMsg}";

    // $logMsg = "Attempting to get user info.";
    // $logData["step3"]["data"] = "3. {$logMsg}";

    $userSearchArr = array('email'=>$email);
    $fieldsStr = "email, status, image, `groups`, rights, firstname, lastname";
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
        if (empty($userInfo)) {
            //user not found
            $logMsg = "User not found: {$email}";
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
                $logMsg = "User not active: {$email}";
                $logData["step3.1"]["data"] = "3.1 {$logMsg}";
                $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                $returnArr["errCode"] = 5;
                $returnArr["errMsg"] = getErrMsg(5).": This URL is invalid or expired.";
            } else {

                $selectedMisDate = isset($_POST["selected_date"])?cleanXSS($_POST["selected_date"]):"";
                if (isset($_GET["selected_date"]) && !empty($_GET["selected_date"])) {
                    $selectedMisDate = cleanQueryParameter($conn, cleanXSS($_GET["selected_date"]));
                }

                //Store date, month, year separatly to make the path.
                $time  = strtotime($selectedMisDate);
                $day   = date('d',$time);
                $month = date('m',$time);
                $year  = date('Y',$time);
            
                //user is found and is active. Do nothing
                $logMsg = "user is found and is active. Do nothing: {$email}";
                $logData["step3.1"]["data"] = "3.1 {$logMsg}";
                // $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                $returnArr["errCode"] = 1;
                $returnArr["errMsg"] = "";
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
            $pageTitle = "Import Reports";
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
                                    <h4 class="title">
                                        <?php echo cleanXSS($pageTitle); ?>
                                    </h4>
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="<?php echo $rootUrl."views/reports/"; ?>">
                                                <i class="fa fa-arrows">&nbsp;</i>Import Reports
                                            </a>
                                        </li>
                                    </ol>
                                </div> <!-- end card header -->
                                <div class="card-content">
                                    <!-- success/error messages -->
                                    <?php
                                    $alertMsg = "";
                                    $alertClass = "";
                                    if (!noError($returnArr)) {
                                        $alertClass = "alert-danger";
                                        $alertMsg = $returnArr["errMsg"];
                                    ?>
                                    <div class="alert <?php echo $alertClass; ?>" style="display: none">
                                        <span>
                                            <?php echo $alertMsg; ?>
                                        </span>
                                    </div>
                                    <?php
                                    }
                                    ?>
                                    <!-- end success/error messages -->

                                    <!-- select date form -->
                                    <form class="form-inline" enctype="multipart/form-data" id="uploadReportForm"
                                        name="uploadReportForm" action="" method="POST">
                                        <!-- <form enctype="multipart/form-data" class="form-inline searchForm" action="<?php //echo $_SERVER['PHP_SELF']; ?>" method="GET"> -->

                                        <div class="col-md-12">
                                            <div class="col-md-12">
                                                <h4>Please select date for Import Reports</h4>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <div class="input-group ">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-calendar" style="color: #3688ca;"></i>
                                                        </div>
                                                        <input type="date" placeholder="Select Date"
                                                            class="form-control" id="selected_date" name="selected_date"
                                                            autocomplete="off" size="28">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <div class="input-group ">
                                                        <select class="form-control" id="nd">
                                                            <option value="">Select Nirvana Disgital type</option>
                                                            <option value="nd1">ND1</option>
                                                            <option value="nd2">ND2</option>
                                                            <option value="ndkids">ND Kids</option>
                                                            <option value="redmusic">Youtube Music</option>
                                                            <option value="Other">Other</option>
                                                        </select>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12" id="uploadReportFilesContainer"
                                                style="display: none">
                                                 <!-- Input Youtube Claim v2.0 File 1 -->
                                                <fieldset class="scheduler-border">
                                                    <legend class="scheduler-border"> Youtube Claim v2.0 </legend>
                                                   
                                                    <div>
                                                        <div class="row">
                                                            <label for="youtube_video_claim_report_checkbox">
                                                                Youtube Claim Raw v1-1 File 1:
                                                            </label>
                                                            <input type="checkbox"
                                                                id="youtube_video_claim_report_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_video_claim_report', this)"
                                                                value="1">
                                                                <a href="<?php echo $rootUrl?>sample_files/youtube_video_claim_report/YouTube_nirvanadigital_M_SAMPLE_claim_raw.csv" target="_Blank">Sample file (nd1,nd2,ndkids)</a> | <a href="<?php echo $rootUrl?>sample_files/youtube_video_claim_report/YouTube_NirvanaMusic_M_20210301_claim_raw_sample.csv" target="_Blank">Sample file (Youtube music)</a>
                                                        </div> 

                                                        <div class="row"
                                                            id="youtube_video_claim_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Input Youtube Claim report
                                                                    filename
                                                                    YouTube_nirvanadigital_M_20201201_claim_raw_v1-1.csv</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="youtube_video_claim_report_upload"
                                                                    name="youtube_video_claim_report_upload" value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button" class="chkND"
                                                                    id='youtube_video_claim_report_file_upload'
                                                                    onclick="saveReport('youtube_video_claim_report_file_upload','youtube_video_claim_report_upload');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- End   Input Youtube Claim v2.0 File 1 -->

                                                    <!-- Input Youtube Claim v2.0 File 2 -->
                                                    <div>
                                                        <div class="row">
                                                            <label for="youtube_video_claim_report_2_checkbox">
                                                                Input Youtube Claim Report  File 2:
                                                            </label>
                                                            <input type="checkbox"
                                                                id="youtube_video_claim_report_2_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_video_claim_report_2', this)"
                                                                value="1">
                                                                <a href="<?php echo $rootUrl?>sample_files/youtube_video_claim_report/claim_report_nirvanadigital_C_sample.csv" target="_Blank">Sample file (nd1,nd2,ndkids)</a>  |   <a href="<?php echo $rootUrl?>sample_files/youtube_video_claim_report/claim_report_NirvanaMusic_C_sample.csv" target="_Blank">Sample file (Youtube music)</a> 
                                                        </div>

                                                        <div class="row"
                                                            id="youtube_video_claim_report_2_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Input Youtube Claim report
                                                                    filename claim_report_nirvanadigital_C.csv</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="youtube_video_claim_report_2_upload"
                                                                    name="youtube_video_claim_report_2_upload" value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button" class="chkND"
                                                                    id='youtube_video_claim_report_2_file_upload'
                                                                    onclick="saveReport('youtube_video_claim_report_2_file_upload','youtube_video_claim_report_2_upload');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- End   Youtube -->
                                                    <!-- Input Youtube Claim Report 1 -->
                                                    <div>
                                                        <div class="row">
                                                            <label for="youtube_video_claim_report_3_checkbox">
                                                                Input Youtube Asset Label  File 3:
                                                            </label>
                                                            <input type="checkbox"
                                                                id="youtube_video_claim_report_3_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_video_claim_report_3', this)"
                                                                value="1">

                                                                <a href="<?php echo $rootUrl?>sample_files/youtube_video_claim_report/asset_full_report_nirvanadigital_L_v_sample.csv" target="_Blank">Sample file (nd1,nd2,ndkids)</a> |  <a href="<?php echo $rootUrl?>sample_files/youtube_video_claim_report/asset_full_report_NirvanaMusic_L_v_sample.csv" target="_Blank">Sample file (Youtube music)</a>
                                                        </div>

                                                        <div class="row"
                                                            id="youtube_video_claim_report_3_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Input Youtube Claim report
                                                                    Asset_Label_Data_NDX.csv </label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="youtube_video_claim_report_3_upload"
                                                                    name="youtube_video_claim_report_3_upload" value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button" class="chkND"
                                                                    id='youtube_video_claim_report_3_file_upload'
                                                                    onclick="saveReport('youtube_video_claim_report_3_file_upload','youtube_video_claim_report_3_upload');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- End   Youtube -->

                                                </fieldset>
                                                <!-- Youtube Red Finance v2.0 -->
                                                <fieldset class="scheduler-border">
                                                    <legend class="scheduler-border"> Youtube Red Finance v2.0 </legend>
                                                   
                                                    <div>
                                                        <div class="row">
                                                            <label for="youtube_red_music_video_finance_report_checkbox">
                                                            Youtube Red Finance:
                                                            </label>
                                                            <input type="checkbox"
                                                                id="youtube_red_music_video_finance_report_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_red_music_video_finance_report', this)"
                                                                value="1">

                                                                <a href="<?php echo $rootUrl?>sample_files/youtube_red_finance_report/YouTube_nirvanadigital_M_20210301_20210331_red_music_rawdata_video_samepl.csv" target="_Blank">Sample file (nd1,nd2,ndkids)</a> |  <a href="<?php echo $rootUrl?>sample_files/youtube_red_finance_report/YouTube_NirvanaMusic_M_20210201_20210228_red_label_rawdata_video_sample.csv" target="_Blank">Sample file (Youtube music)</a>
                                                        </div>

                                                        <div class="row"
                                                            id="youtube_red_music_video_finance_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Youtube Red Finance
                                                                    filename
                                                                    YouTube_Nirvanadigital2_M_20201201_20201231_red_music_rawdata_video_v1-1.csv</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="youtube_red_music_video_finance_report"
                                                                    name="youtube_red_music_video_finance_report" value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button" class="chkND"
                                                                    id='youtube_red_music_video_finance_report_file_upload'
                                                                    onclick="saveReport('youtube_red_music_video_finance_report_file_upload','youtube_red_music_video_finance_report');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                  
                                                </fieldset>
                                                <!-- End   Youtube Red Finance v2.0  -->
                                                <!-- YouTube Ecommerce paid features v2.0 youtube_ecommerce_paid_features -->
                                                <fieldset class="scheduler-border">
                                                    <legend class="scheduler-border"> YouTube Ecommerce paid features v2.0 </legend>
                                                   
                                                    <div>
                                                        <div class="row">
                                                            <label for="youtube_ecommerce_paid_features_report_checkbox">
                                                            YouTube Ecommerce paid features:
                                                            </label>
                                                            <input type="checkbox"
                                                                id="youtube_ecommerce_paid_features_report_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_ecommerce_paid_features_report', this)"
                                                                value="1">
                                                                <a href="<?php echo $rootUrl?>sample_files/youtube_ecommerce_paid_features/YouTube_nirvanadigital_Ecommerce_paid_features_M_20210301_20210331_v10.csv" target="_Blank">Sample file (nd1)</a> |  <a href="<?php echo $rootUrl?>sample_files/youtube_ecommerce_paid_features/YouTube_NirvanaMusic_Ecommerce_paid_features_M_20210301_20210331_v1-1.csv" target="_Blank">Sample file (nd2,ndkids,Youtube music)</a>
                                                        </div>

                                                        <div class="row"
                                                            id="youtube_ecommerce_paid_features_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">YouTube Ecommerce Paid Features  filename  YouTube_Nirvanadigital2_Ecommerce_paid_features_M_20210101_20210131_v1-1.csv</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="youtube_ecommerce_paid_features_report"
                                                                    name="youtube_ecommerce_paid_features_report" value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button" class="chkND"
                                                                    id='youtube_ecommerce_paid_features_report_file_upload'
                                                                    onclick="saveReport('youtube_ecommerce_paid_features_report_file_upload','youtube_ecommerce_paid_features_report');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                  
                                                </fieldset>
                                                 <!-- End   Youtube Red Finance v2.0  -->

                                                 <!-- YouTube Label Engine -->
                                                <fieldset class="scheduler-border">
                                                    <legend class="scheduler-border"> YouTube US Report </legend>
                                                   
                                                    <div>
                                                        <div class="row">
                                                            <label for="youtube_labelengine_report_checkbox">
                                                            YouTube US Report:
                                                            </label>
                                                            <input type="checkbox"
                                                                id="youtube_labelengine_report_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_labelengine_report', this)"
                                                                value="1">
                                                                <a href="<?php echo $rootUrl?>sample_files/youtube_video_claim_report/NDMusic_March_LE_reports.csv" target="_Blank">Sample file (nd1)</a> 
                                                        </div>

                                                        <div class="row"
                                                            id="youtube_labelengine_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">YouTube US Report  youtube_labelengine_report_file_upload-1.csv</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="youtube_labelengine_report"
                                                                    name="youtube_labelengine_report" value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button" class="chkND"
                                                                    id='youtube_labelengine_report_file_upload'
                                                                    onclick="saveReport('youtube_labelengine_report_file_upload','youtube_labelengine_report');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                  
                                                </fieldset>
                                                 <!-- End  YouTube Label Engine  -->
                                                <!-- Amazon Video report  -->
                                              <!--  <fieldset class="scheduler-border">
                                                    <legend class="scheduler-border"> Amazon Video report </legend>
                                                     -->
                                                 <!--    
                                                    <div>
                                                        <div class="row">
                                                            <label for="amazon_video_report_checkbox2">
                                                                Amazon Video report:
                                                            </label>
                                                            <input type="checkbox" id="amazon_video_report_checkbox2"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('amazon_video_report', this)"
                                                                value="1">
                                                        </div>
                                                        <div class="row" id="amazon_video_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Input Amazon Video report
                                                                    filename</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="amazon_video_report_upload"
                                                                    name="amazon_video_report_upload" value="">
                                                                <button type="button"
                                                                    id='amazon_video_report_file_upload'
                                                                    onclick="saveReport('amazon_video_report_file_upload','amazon_video_report_upload');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="row"
                                                            id="amazon_video_payments_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Input Amazon Payments
                                                                    report
                                                                    filename (file recived after 3 months)</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="amazon_video_payments_report_upload"
                                                                    name="amazon_video_payments_report_upload" value="">
                                                                <button type="button"
                                                                    id='amazon_video_payments_report_file_upload'
                                                                    onclick="saveReport('amazon_video_payments_report_file_upload','amazon_video_payments_report_upload');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </fieldset> -->
                                                  <!-- End   Amazon Video report  -->
                                                <!-- <fieldset class="scheduler-border">
                                                    <legend class="scheduler-border"> Old Code </legend>
                                                    <div>
                                                        <div class="row">
                                                            <label for="revenue_share_youtube">
                                                                Input Youtube Claim v1.1:
                                                            </label>
                                                            <input type="checkbox" id="revenue_share_youtube_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('revenue_share_youtube', this)"
                                                                value="1">
                                                        </div>

                                                        <div class="row"
                                                            id="revenue_share_youtube_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Youtube Finance report
                                                                    filename</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="revenue_share_youtube_upload"
                                                                    name="revenue_share_youtube_upload" value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button"
                                                                    id='revenue_share_youtube_file_upload'
                                                                    onclick="saveReport('revenue_share_youtube_file_upload','revenue_share_youtube_upload');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                            </div>
                                                        </div>
                                                    </div> -->
                                                    <!-- End Revenue Share Youtube -->
                                                    <!--Input Report Youtube Red-->
                                                    <!-- <div>
                                                        <div class="row">
                                                            <label for="youtube_video_report">
                                                                Input Youtube Claim report:
                                                            </label>
                                                            <input type="checkbox" id="youtube_video_report_checkbox2"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_video_report', this)"
                                                                value="1">
                                                        </div>
                                                        <div class="row" id="youtube_video_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Youtube Video report
                                                                    filename</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="youtube_video_report_upload"
                                                                    name="youtube_video_report_upload" value="">
                                                                <button type="button"
                                                                    id='youtube_video_report_file_upload'
                                                                    onclick="saveReport('youtube_video_report_file_upload','youtube_video_report_upload');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                    </div> -->

                                                    <!--Input Youtube Red Finance report start-->
                                                    <!-- <div>
                                                        <div class="row">
                                                            <label for="youtube_red_finance_report">
                                                                Input Youtube Red Finance report:
                                                            </label>
                                                            <input type="checkbox"
                                                                id="youtube_red_finance_report_checkbox2"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_red_finance_report', this)"
                                                                value="1">
                                                        </div>
                                                        <div class="row"
                                                            id="youtube_red_finance_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Input Youtube Red Finance
                                                                    report
                                                                    filename</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="youtube_red_finance_report_upload"
                                                                    name="youtube_red_finance_report_upload" value="">
                                                                <button type="button"
                                                                    id='youtube_red_finance_report_file_upload'
                                                                    onclick="saveReport('youtube_red_finance_report_file_upload','youtube_red_finance_report_upload');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div> -->
                                                    <!--Input Youtube Red Finance report end-->
                                                    <!--Input Youtube Red  report start-->
                                                    <!-- <div>
                                                        <div class="row">
                                                            <label for="youtube_red_video_report">
                                                                Input Youtube Red Video report:
                                                            </label>
                                                            <input type="checkbox"
                                                                id="youtube_red_video_report_checkbox2"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_red_video_report', this)"
                                                                value="1">
                                                        </div>
                                                        <div class="row"
                                                            id="youtube_red_video_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Input Youtube Red Video
                                                                    report
                                                                    filename</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="youtube_red_video_report_upload"
                                                                    name="youtube_red_video_report_upload" value="">
                                                                <button type="button"
                                                                    id='youtube_red_video_report_file_upload'
                                                                    onclick="saveReport('youtube_red_video_report_file_upload','youtube_red_video_report_upload');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div> -->
                                                    <!--Input Youtube Red  report end-->
                                                    <!-- Input Youtube Audio Claim v1.1 -->
                                                    <!-- <div>
                                                        <div class="row">
                                                            <label for="youtube_audio_finance_report">
                                                                Input Youtube Audio Claim v1.1:
                                                            </label>
                                                            <input type="checkbox" id="youtube_audio_finance_report"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_audio_finance_report', this)"
                                                                value="1">
                                                        </div>

                                                        <div class="row"
                                                            id="youtube_audio_finance_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Youtube Audio Claim v1.1
                                                                    filename</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="youtube_audio_finance_report_upload"
                                                                    name="youtube_audio_finance_report_upload" value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button"
                                                                    id='youtube_audio_finance_report_file_upload'
                                                                    onclick="saveReport('youtube_audio_finance_report_file_upload','youtube_audio_finance_report_upload');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                            </div>
                                                        </div>
                                                    </div> -->

                                                    <!-- Youtube Audio Claim report -->
                                                    <!-- <div>
                                                        <div class="row">
                                                            <label for="youtube_audio_report">
                                                                Input Youtube Audio Claim report:
                                                            </label>
                                                            <input type="checkbox" id="youtube_audio_report_checkbox2"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_audio_report', this)"
                                                                value="1">
                                                        </div>
                                                        <div class="row" id="youtube_audio_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Youtube audio report
                                                                    filename</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="youtube_audio_report_upload"
                                                                    name="youtube_audio_report_upload" value="">
                                                                <button type="button"
                                                                    id='youtube_audio_report_file_upload'
                                                                    onclick="saveReport('youtube_audio_report_file_upload','youtube_audio_report_upload');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                    </div> -->
                                                    <!-- Start Import Youtube audio red report  -->
                                                    <!-- <div>
                                                        <div class="row">
                                                            <label for="youtube_audio_report">
                                                                Input Youtube Audio Red report:
                                                            </label>
                                                            <input type="checkbox"
                                                                id="youtube_audio_red_report_checkbox2"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_audio_red_report', this)"
                                                                value="1">
                                                        </div>
                                                        <div class="row"
                                                            id="youtube_audio_red_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Youtube audio red report
                                                                    filename</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="youtube_audio_red_report_upload"
                                                                    name="youtube_audio_red_report_upload" value="">
                                                                <button type="button"
                                                                    id='youtube_audio_red_report_file_upload'
                                                                    onclick="saveReport('youtube_audio_red_report_file_upload','youtube_audio_red_report_upload');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                    </div> -->
                                               <!--  </fieldset> -->
                                              
                                                <!--Input Amazon  report end-->
                                            </div>
                                             

                                        </div>
                                        <!----------------------End of Client Details----------------------->

                                </div><!-- close uploadReportFilesContainer -->

                            </div><!-- closing col-md-12 -->
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <!-- delete file modal -->
    <div class="modal fade" id="deleteReportFileModal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Delete <span></span></h4>
                </div>
                <div class="modal-body">
                    <p>Do you want to delete <span></span>?.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-md btn-default" data-dismiss="modal">Close</button>
                    <button type="button" id="submit_btn" onclick="deleteMisFile(this);" data-misType=""
                        class="btn btn-md btn-danger" data-dismiss="modal">Continue</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Delete File Model -->

    <?php
    //include the loader
    require_once(__ROOT__."/views/common/loader.php");
    ?>
    <!--   Core JS Files   -->
    <script src="<?php echo $rootUrl; ?>assets/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/material.min.js" type="text/javascript"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/perfect-scrollbar.jquery.min.js"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/parsley.js"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/material-dashboard.js"></script>
    <script type="text/javascript">
    function submitForm() {
        $("#submitBtn").value = 'Submitting';
        return true;
    }

    //event handler to handle the change event of the datepicker
    $('#selected_date').change(function() {
        var selectedDate = $('#selected_date').val();
        var revenueShareYoutubeUpload = $('#revenue_share_youtube_upload').val();
        $("#uploadReportFilesContainer").show();
    });

    $('.chkND').click(function() {
            var selectedDate = $('#selected_date').val();
        
            var nd = $('#nd').val();
        
            if(nd==""){
               // alert("Please select Channel  ND");
                $(".alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").html("Please select Channel  ND");
                return;
            }
        });
    function saveReport(reportType, textvalue) {
        $(".alert").
        removeClass("alert-success").
        removeClass("alert-danger").
        fadeOut().
        find("span").html("");
        var selectedDate = $('#selected_date').val();
        var nd = $('#nd').val();
        var revenueShareYoutubeUpload = $('#' + textvalue).val();

            //event handler to handle the change event of the datepicker
            //chkND
        

        //validate if filename has a space
        if (revenueShareYoutubeUpload.indexOf(' ') >= 0) {
            $(".alert").
            removeClass("alert-success").
            addClass("alert-danger").
            fadeIn().
            find("span").html("File name should not have any spaces in it");
            return;
        }

        //validate if filename is .csv
        if (revenueShareYoutubeUpload.indexOf('.csv') < 0) {
            $(".alert").
            removeClass("alert-success").
            addClass("alert-danger").
            fadeIn().
            find("span").html("File should be .csv only");
            return;
        }
        if (nd == "") {
            $(".alert").
            removeClass("alert-success").
            addClass("alert-danger").
            fadeIn().
            find("span").html("Please select Nirvana Disgital type");
            return;
        }
    console.log("url : ",'<?php echo $rootUrl; ?>controller/reports/upload/');
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/reports/upload/",
            data: {
                selected_date: selectedDate,
                csv_files: revenueShareYoutubeUpload,
                nd: nd,
                type: reportType
            },
            success: function(response) {
                console.log(response);
                //handle error in response
                if (response["errCode"]) {
                    if (response["errCode"] != "-1") {
                        console.log("hiee");
                        $(".alert").css("display", "block");
                        //there was an error, alert the error and hide the form.
                        $(".alert").
                        removeClass("alert-success").
                        addClass("alert-danger").
                        fadeIn().
                        find("span").
                        html(response["errMsg"]);
                        // $("#uploadMISFilesContainer").hide();
                    } else {
                        $(".alert").css("display", "block");
                        $(".alert").
                        removeClass("alert-danger").
                        addClass("alert-success").
                        fadeIn().
                        find("span").
                        html(response["errMsg"]);
                        // setTimeout(function(){
                        //     window.location.reload();
                        // }, 3000);

                    }
                }
            },
            error: function(jqXHR, exception) {
                var msg = '';
        if (jqXHR.status === 0) {
            msg = 'Not connect.\n Verify Network.';
        } else if (jqXHR.status == 404) {
            msg = 'Requested page not found. [404]';
        } else if (jqXHR.status == 500) {
            msg = 'Internal Server Error [500].';
        } else if (exception === 'parsererror') {
            msg = 'Requested JSON parse failed.';
        } else if (exception === 'timeout') {
            msg = 'Time out error.';
        } else if (exception === 'abort') {
            msg = 'Ajax request aborted.';
        } else {
            msg = 'Uncaught Error.\n' + jqXHR.responseText;
        }

                $(".alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html(""+msg);
            }
        });
    }

    //function to show the modal to confirm deletion of mis file
    function confirmDeleteReport(type) {
        $("#deleteReportFileModal .modal-title span").html(type);
        $("#deleteReportFileModal .modal-body span").html(type);
        $("#deleteReportFileModal .modal-footer #submit_btn").data("misType", type);
        $("#deleteReportFileModal").modal();
    }

    //function to actually carry out the deletion of a particular mis file
    // function deleteMisFile(buttonElement){
    //     var misType = $(buttonElement).data("misType");
    //     $("#"+misType+"_files_container").hide(); //show the file details container
    //     $("#"+misType+"_file_download").attr('href',""); //add the file URL to the anchor tag's href attribute
    //     //reset the filename in the relevant container
    //     $("#"+misType+"_doc_name").text("");
    //     //reset the filename in the hidden input field as well so that data persists in case user does not change anything
    //     $("#"+misType+"_filename").val("");
    // }

    //function to show/hide the container to upload a particular MIS file
    function toggleReportFileUpload(type, checkboxElement) {
        if ($(checkboxElement).prop("checked") == true) {
            $("#" + type + "_file_upload_container").show();
        } else if ($(checkboxElement).prop("checked") == false) {
            $("#" + type + "_file_upload_container").hide();
        }


        if ($("#amazon_video_report_checkbox2").prop("checked") == true) {
            $("#amazon_video_payments_report_file_upload_container").show();
        } else if ($("#amazon_video_report_checkbox2").prop("checked") == false) {
            $("#amazon_video_payments_report_file_upload_container").hide();
        }
    }
    </script>
</body>

</html>