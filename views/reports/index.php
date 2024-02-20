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
                                            <div class="col-md-3 ">
                                                <div class="form-group">
                                                    <div class="input-group ">
                                                        <select class="form-control " name="nd" id="nd"
                                                            style="display: none">
                                                            <option value="">Select Nirvana Disgital type</option>
                                                            <option value="nd1">ND1</option>
                                                            <option value="nd2">ND2</option>
                                                            <option value="ndkids">ND Kids</option>
                                                            <option value="redmusic">Youtube Music</option>
                                                            <option value="applemusic">Apple Music</option>
                                                            <option value="itune">Itune</option>
                                                            <option value="gaana">Ganna</option>
                                                            <option value="saavan">Saavan</option>
                                                            <option value="spotify">Spotify</option>
                                                            <option value="non_cmg">Publishing</option>
                                                            <!--  <option value="other">Other</option> -->
                                                        </select>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12" id="uploadReportFilesContainer"
                                                style="display: none1">
                                                <!-- Input Youtube Claim v2.0 File 1 -->
                                                <fieldset
                                                    class="scheduler-border nd1 nd2 ndkids redmusic hidealldefault">
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
                                                            <a href="<?php echo $rootUrl?>sample_files/youtube_video_claim_report/YouTube_nirvanadigital_M_SAMPLE_claim_raw.csv"
                                                                target="_Blank">Sample file (nd1,nd2,ndkids)</a> | <a
                                                                href="<?php echo $rootUrl?>sample_files/youtube_video_claim_report/YouTube_NirvanaMusic_M_20210301_claim_raw_sample.csv"
                                                                target="_Blank">Sample file (Youtube music)</a>
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
                                                            Path for file upload :
                                                            /var/lib/mysql-files/youtube_video_claim_report/
                                                        </div>
                                                    </div>
                                                    <!-- End   Input Youtube Claim v2.0 File 1 -->

                                                    <!-- Input Youtube Claim v2.0 File 2 -->
                                                    <div>
                                                        <div class="row">
                                                            <label for="youtube_video_claim_report_2_checkbox">
                                                                Input Youtube Claim Report File 2:
                                                            </label>
                                                            <input type="checkbox"
                                                                id="youtube_video_claim_report_2_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_video_claim_report_2', this)"
                                                                value="1">
                                                            <a href="<?php echo $rootUrl?>sample_files/youtube_video_claim_report/claim_report_nirvanadigital_C_sample.csv"
                                                                target="_Blank">Sample file (nd1,nd2,ndkids)</a> | <a
                                                                href="<?php echo $rootUrl?>sample_files/youtube_video_claim_report/claim_report_NirvanaMusic_C_sample.csv"
                                                                target="_Blank">Sample file (Youtube music)</a>
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
                                                            Path for file upload :
                                                            /var/lib/mysql-files/youtube_video_claim_report/
                                                        </div>
                                                    </div>
                                                    <!-- End   Youtube -->
                                                    <!-- Input Youtube Claim Report 1 -->
                                                    <div>
                                                        <div class="row">
                                                            <label for="youtube_video_claim_report_3_checkbox">
                                                                Input Youtube Asset Label File 3:
                                                            </label>
                                                            <input type="checkbox"
                                                                id="youtube_video_claim_report_3_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_video_claim_report_3', this)"
                                                                value="1">

                                                            <a href="<?php echo $rootUrl?>sample_files/youtube_video_claim_report/asset_full_report_nirvanadigital_L_v_sample.csv"
                                                                target="_Blank">Sample file (nd1,nd2,ndkids)</a> | <a
                                                                href="<?php echo $rootUrl?>sample_files/youtube_video_claim_report/asset_full_report_NirvanaMusic_L_v_sample.csv"
                                                                target="_Blank">Sample file (Youtube music)</a>
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
                                                            Path for file upload :
                                                            /var/lib/mysql-files/youtube_video_claim_report/
                                                        </div>
                                                    </div>
                                                    <!-- End   Youtube -->

                                                </fieldset>
                                                <!-- Youtube Red Finance v2.0 -->
                                                <fieldset
                                                    class="scheduler-border nd1 nd2 ndkids redmusic hidealldefault">
                                                    <legend class="scheduler-border"> Youtube Red Finance v2.0 </legend>

                                                    <div>
                                                        <div class="row">
                                                            <label
                                                                for="youtube_red_music_video_finance_report_checkbox">
                                                                Youtube Red Finance:
                                                            </label>
                                                            <input type="checkbox"
                                                                id="youtube_red_music_video_finance_report_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_red_music_video_finance_report', this)"
                                                                value="1">

                                                            <a href="<?php echo $rootUrl?>sample_files/youtube_red_finance_report/YouTube_nirvanadigital_M_20210301_20210331_red_music_rawdata_video_samepl.csv"
                                                                target="_Blank">Sample file (nd1,nd2,ndkids)</a> | <a
                                                                href="<?php echo $rootUrl?>sample_files/youtube_red_finance_report/YouTube_NirvanaMusic_M_20210201_20210228_red_label_rawdata_video_sample.csv"
                                                                target="_Blank">Sample file (Youtube music)</a>
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
                                                                    required=""
                                                                    id="youtube_red_music_video_finance_report"
                                                                    name="youtube_red_music_video_finance_report"
                                                                    value="">
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
                                                            Path for file upload :
                                                            /var/lib/mysql-files/youtube_video_claim_report/
                                                        </div>
                                                    </div>

                                                </fieldset>
                                                <!-- End   Youtube Red Finance v2.0  -->
                                                <!-- YouTube Ecommerce paid features v2.0 youtube_ecommerce_paid_features -->
                                                <fieldset
                                                    class="scheduler-border nd1 nd2 ndkids redmusic hidealldefault">
                                                    <legend class="scheduler-border"> YouTube Ecommerce paid features
                                                        v2.0 </legend>

                                                    <div>
                                                        <div class="row">
                                                            <label
                                                                for="youtube_ecommerce_paid_features_report_checkbox">
                                                                YouTube Ecommerce paid features:
                                                            </label>
                                                            <input type="checkbox"
                                                                id="youtube_ecommerce_paid_features_report_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youtube_ecommerce_paid_features_report', this)"
                                                                value="1">
                                                            <a href="<?php echo $rootUrl?>sample_files/youtube_ecommerce_paid_features/YouTube_nirvanadigital_Ecommerce_paid_features_M_20210301_20210331_v10.csv"
                                                                target="_Blank">Sample file (nd1)</a> | <a
                                                                href="<?php echo $rootUrl?>sample_files/youtube_ecommerce_paid_features/YouTube_NirvanaMusic_Ecommerce_paid_features_M_20210301_20210331_v1-1.csv"
                                                                target="_Blank">Sample file (nd2,ndkids,Youtube
                                                                music)</a>
                                                        </div>

                                                        <div class="row"
                                                            id="youtube_ecommerce_paid_features_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">YouTube Ecommerce Paid
                                                                    Features filename
                                                                    YouTube_Nirvanadigital2_Ecommerce_paid_features_M_20210101_20210131_v1-1.csv</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required=""
                                                                    id="youtube_ecommerce_paid_features_report"
                                                                    name="youtube_ecommerce_paid_features_report"
                                                                    value="">
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
                                                            Path for file upload :
                                                            /var/lib/mysql-files/youtube_video_claim_report/
                                                        </div>
                                                    </div>

                                                </fieldset>
                                                <!-- End   Youtube Red Finance v2.0  -->

                                                <!-- YouTube US Report -->
                                                <fieldset
                                                    class="scheduler-border nd1 nd2 ndkids redmusic hidealldefault">
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
                                                            <a href="<?php echo $rootUrl?>sample_files/youtube_video_claim_report/NDMusic_March_LE_reports.csv"
                                                                target="_Blank">Sample file (nd1)</a>
                                                        </div>

                                                        <div class="row"
                                                            id="youtube_labelengine_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">YouTube US Report
                                                                    youtube_labelengine_report_file_upload-1.csv</label>
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
                                                            Path for file upload :
                                                            /var/lib/mysql-files/youtube_video_claim_report/
                                                        </div>

                                                    </div>

                                                </fieldset>
                                                <!-- End  YouTube US Report  -->
                                                <!-- applemusic audio report  -->
                                                <fieldset class="scheduler-border applemusic  hidealldefault">
                                                    <legend class="scheduler-border"> Apple Music </legend>

                                                    <div>
                                                        <div class="row">
                                                            <label for="applemusic_report_checkbox">
                                                                Apple Music Report:
                                                            </label>
                                                            <input type="checkbox" id="applemusic_report_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('applemusic_report', this)"
                                                                value="1">
                                                            <a href="<?php echo $rootUrl?>sample_files/audio/sample_xxxxxxAppleMusic.csv"
                                                                target="_Blank">Sample file (Apple Music)</a>
                                                        </div>

                                                        <div class="row" id="applemusic_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Apple Music Report
                                                                    applemusic_report_file_upload-1.csv</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="applemusic_report"
                                                                    name="applemusic_report" value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button" class="chkND"
                                                                    id='applemusic_report_file_upload'
                                                                    onclick="saveReport('applemusic_report_file_upload','applemusic_report');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                                Path for file upload :
                                                                /var/lib/mysql-files/music_reports/

                                                            </div>
                                                        </div>
                                                    </div>

                                                </fieldset>
                                                <!-- End applemusic Video report  -->
                                                <!-- applemusic audio report  -->
                                                <fieldset class="scheduler-border itune  hidealldefault">
                                                    <legend class="scheduler-border"> Itune Music </legend>


                                                    <div>
                                                        <div class="row">
                                                            <label for="itunemusic_report_checkbox">
                                                                Itune Music Report:
                                                            </label>
                                                            <input type="checkbox" id="itunemusic_report_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('itunemusic_report', this)"
                                                                value="1">
                                                            <a href="<?php echo $rootUrl?>sample_files/audio/sample_xxxxxxxItune.csv"
                                                                target="_Blank">Sample file (Itune Music)</a>
                                                        </div>

                                                        <div class="row" id="itunemusic_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Itune Music Report
                                                                    itunemusic_report_file_upload-1.csv</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="itunemusic_report"
                                                                    name="itunemusic_report" value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button" class="chkND"
                                                                    id='itunemusic_report_file_upload'
                                                                    onclick="saveReport('itunemusic_report_file_upload','itunemusic_report');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                            </div>
                                                            Path for file upload : /var/lib/mysql-files/music_reports/
                                                        </div>

                                                    </div>
                                                </fieldset>
                                                <!-- End applemusic Video report  -->
                                                <!-- gaana audio report  -->
                                                <fieldset class="scheduler-border gaana  hidealldefault">
                                                    <legend class="scheduler-border"> Gaana </legend>

                                                    <div>
                                                        <div class="row">
                                                            <label for="gaana_report_checkbox">
                                                                Gaana Report:
                                                            </label>
                                                            <input type="checkbox" id="gaana_report_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('gaana_report', this)"
                                                                value="1">
                                                            <a href="<?php echo $rootUrl?>sample_files/audio/Sample_GaanaRawReport.csv"
                                                                target="_Blank">Sample file (Gaana)</a>
                                                        </div>

                                                        <div class="row" id="gaana_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <input type="text"
                                                                            class="form-control numbersOnly"
                                                                            name="free_playout_revenue"
                                                                            id="free_playout_revenue" required=""
                                                                            value="" placeholder="Free Playout Revenue">
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <input type="text"
                                                                            class="form-control numbersOnly"
                                                                            name="paid_playout_revenue"
                                                                            id="paid_playout_revenue" required=""
                                                                            value="" placeholder="Paid Playout Revenue">
                                                                    </div>

                                                                </div>
                                                            </div>

                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Gaana Report
                                                                    gaana_report_file_upload-1.csv</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="gaana_report" name="gaana_report"
                                                                    value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button" class="chkND"
                                                                    id='gaana_report_file_upload'
                                                                    onclick="saveReport('gaana_report_file_upload','gaana_report');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                            </div>
                                                            Path for file upload : /var/lib/mysql-files/music_reports/
                                                        </div>
                                                    </div>

                                                </fieldset>
                                                <!-- End gaana Video report  -->
                                                <!-- saavan audio report  -->
                                                <fieldset class="scheduler-border saavan  hidealldefault">
                                                    <legend class="scheduler-border"> Saavan </legend>

                                                    <div>
                                                        <div class="row">
                                                            <label for="saavan_report_checkbox">
                                                                Saavan Report:
                                                            </label>
                                                            <input type="checkbox" id="saavan_report_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('saavan_report', this)"
                                                                value="1">
                                                            <a href="<?php echo $rootUrl?>sample_files/audio/sample_Saavn_NirvanaDigitalStudios_Log_xxx_Raw_Calc.csv"
                                                                target="_Blank">Sample file (saavan)</a>
                                                        </div>

                                                        <div class="row" id="saavan_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <input type="text"
                                                                            class="form-control numbersOnly"
                                                                            name="Ad_Supported_Revenue"
                                                                            id="Ad_Supported_Revenue" required=""
                                                                            value="" placeholder="Ad Supported Revenue">
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <input type="text"
                                                                            class="form-control numbersOnly"
                                                                            name="Subscription_Revenue"
                                                                            id="Subscription_Revenue" required=""
                                                                            value="" placeholder="Subscription Revenue">
                                                                    </div>

                                                                </div>
                                                            </div>
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Saavan Report
                                                                    saavan_report_file_upload-1.csv</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="saavan_report" name="saavan_report"
                                                                    value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button" class="chkND"
                                                                    id='saavan_report_file_upload'
                                                                    onclick="saveReport('saavan_report_file_upload','saavan_report');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                            </div>
                                                            Path for file upload : /var/lib/mysql-files/music_reports/
                                                        </div>

                                                    </div>

                                                </fieldset>
                                                <!-- End saavan Video report  -->


                                                <!-- spotify audio report  -->
                                                <fieldset class="scheduler-border spotify  hidealldefault">
                                                    <legend class="scheduler-border"> Spotify </legend>

                                                    <div>
                                                        <div class="row">
                                                            <label for="spotify_report_checkbox">
                                                                Spotify Report:
                                                            </label>
                                                            <input type="checkbox" id="spotify_report_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('spotify_report', this)"
                                                                value="1">
                                                            <a href="<?php echo $rootUrl?>sample_files/audio/spotify_report_file_upload-1.csv"
                                                                target="_Blank">Sample file (spotify)</a>
                                                        </div>

                                                        <div class="row" id="spotify_report_file_upload_container"
                                                            style="display: none;">

                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Spotify Report
                                                                    spotify_report_file_upload-1.csv</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="spotify_report"
                                                                    name="spotify_report" value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button" class="chkND"
                                                                    id='spotify_report_file_upload'
                                                                    onclick="saveReport('spotify_report_file_upload','spotify_report');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                            </div>
                                                            Path for file upload : /var/lib/mysql-files/music_reports/
                                                        </div>

                                                    </div>

                                                </fieldset>
                                                <!-- End spotify Video report  -->


                                                <!-- non_cmg  report  -->
                                                <fieldset class="scheduler-border non_cmg  hidealldefault">
                                                    <legend class="scheduler-border"> Publishing </legend>

                                                    <div>
                                                        <div class="row">
                                                            <label for="non_cmg_main_report_checkbox">
                                                            Publishing (Main)):
                                                            </label>
                                                            <input type="checkbox" id="non_cmg_main_report_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('non_cmg_main_report', this)"
                                                                value="1">
                                                            <a href="<?php echo $rootUrl?>sample_files/cmg/nirvana_publishing_yyyymmdd_nirvana_publishing_claim_raw_non_cmgyyyymmdd.csv"
                                                                target="_Blank">Sample file (Publishing Main)</a>
                                                        </div>

                                                        <div class="row" id="non_cmg_main_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12">

                                                                <input type="checkbox" id="truncate_data1"
                                                                    name="truncate_data1" value="1">
                                                                <label for="truncate_data1">
                                                                    Truncate the table (Delete al previous Data and
                                                                    Re-calculate)
                                                                </label>
                                                            </div>    
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Publishing Report
                                                                    nirvana_publishing_yyyymmdd_nirvana_publishing_claim_raw_non_cmgyyyymmdd.csv</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="non_cmg_main_report"
                                                                    name="non_cmg_main_report" value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button" class="chkND"
                                                                    id='non_cmg_main_report_file_upload'
                                                                    onclick="saveReport('non_cmg_main_report_file_upload','non_cmg_main_report');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                            </div>
                                                            <div>
                                                                For multiple Comma seperated file
                                                            </div>

                                                            <div>
                                                                Path for file upload :
                                                                /var/lib/mysql-files/youtube_video_claim_report/
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div style="padding-top:20px;">
                                                        <div class="row">
                                                            <label for="non_cmg_red_report_checkbox">
                                                            Publishing (Red/Subscription)):
                                                            </label>
                                                            <input type="checkbox" id="non_cmg_red_report_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('non_cmg_red_report', this)"
                                                                value="1">
                                                            <a href="<?php echo $rootUrl?>sample_files/cmg/nirvana_publishing_yyyymmdd_nirvana_publishing_red_pubs_rawdata_video_non_cmgyyyymmdd.csv"
                                                                target="_Blank">Sample file (Publishing
                                                                Red/Subscription)</a>
                                                        </div>

                                                        <div class="row" id="non_cmg_red_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12">

                                                                <input type="checkbox" id="truncate_data2"
                                                                    name="truncate_data2" value="1">
                                                                <label for="truncate_data2">
                                                                    Truncate the table (Delete al previous Data and
                                                                    Re-calculate)
                                                                </label>
                                                            </div>
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">Publishing  Report
                                                                    nirvana_publishing_yyyymmdd_nirvana_publishing_red_pubs_rawdata_video_non_cmgyyyymmdd.csv</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="non_cmg_red_report"
                                                                    name="non_cmg_red_report" value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button" class="chkND"
                                                                    id='non_cmg_red_report_file_upload'
                                                                    onclick="saveReport('non_cmg_red_report_file_upload','non_cmg_red_report');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                            </div>
                                                            <div>
                                                                For multiple Comma seperated file
                                                            </div>

                                                            <div>
                                                                Path for file upload :
                                                                /var/lib/mysql-files/youtube_video_claim_report/
                                                            </div>
                                                        </div>

                                                    </div>

                                                </fieldset>
                                                <!-- End non_cmg Video report  -->

                                                <!-- YouTube WithHolding Percentage -->
                                                <fieldset
                                                    class="scheduler-border nd1 nd2 ndkids redmusic hidealldefault">
                                                    <legend class="scheduler-border"> YouTube WithHolding Percentage Report </legend>
                                                    
                                                    <div>
                                                        <div class="row">
                                                            <label for="youTube_withholding_percentage_report_checkbox">
                                                            * file which comes after 3 month:
                                                            </label>
                                                            <input type="checkbox"
                                                                id="youTube_withholding_percentage_report_checkbox"
                                                                name="holding_check[]"
                                                                onclick="toggleReportFileUpload('youTube_withholding_percentage_report', this)"
                                                                value="1">
                                                            <a href="<?php echo $rootUrl?>sample_files/youtube_video_claim_report/YouTube_Nirvanadigital2_M_202211_affiliate_payment_summary_v1-0.csv"
                                                                target="_Blank">Sample file (nd1)</a>
                                                        </div>

                                                        <div class="row"
                                                            id="youTube_withholding_percentage_report_file_upload_container"
                                                            style="display: none;">
                                                            <div class="col-md-12 form-group label-floating is-focused">
                                                                <label class="control-label">
                                                                YouTube_Nirvanadigital2_M_202211_affiliate_payment_summary_v1-0.csv</label>
                                                                <input type="text" class="form-control"
                                                                    style="width:90%" data-parsley-trigger="keyup"
                                                                    required="" id="youTube_withholding_percentage_report"
                                                                    name="youTube_withholding_percentage_report" value="">
                                                                <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                                <button type="button" class="chkND"
                                                                    id='youTube_withholding_percentage_report_file_upload'
                                                                    onclick="saveReport('youTube_withholding_percentage_report_file_upload','youTube_withholding_percentage_report');">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                                <?php 
                                                            }
                                                            ?>
                                                            </div>
                                                            <div>
                                                                Path for file upload :
                                                                /var/lib/mysql-files/youtube_video_claim_report/</br>
                                                                <b>* Will compare with selected Date <span id="selecteddate"></span>.</b>
                                                            </div>
                                                        </div>

                                                    </div>

                                                </fieldset>
                                                <!-- End  YouTube US Report  -->
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
    /*  jQuery('.numbersOnly').keyup(function() {
       // this.value = this.value.replace(/[^0-9\.]/g, '');
    }); */
    function submitForm() {
        $("#submitBtn").value = 'Submitting';
        return true;
    }
    $(".hidealldefault").hide();
    //event handler to handle the change event of the datepicker
    $('#selected_date').change(function() {
        var selectedDate = $('#selected_date').val();
        var revenueShareYoutubeUpload = $('#revenue_share_youtube_upload').val();
        //$("#uploadReportFilesContainer").show();
        //$(".hidealldefault").show();
        $("#nd").show();
        $("#selecteddate").html("<font color='red'>"+selectedDate+"</font>");
        getRates();
    });
    $('#nd').change(function() {
        $(".hidealldefault").hide();
        var nd = $('#nd').val();
        //alert(nd);
        $('.' + nd).show();

        getRates()
    });
    $('.chkND').click(function() {
        var selectedDate = $('#selected_date').val();

        var nd = $('#nd').val();

        if (nd == "") {
            // alert("Please select Channel  ND");
            $(".alert").
            removeClass("alert-success").
            addClass("alert-danger").
            fadeIn().
            find("span").html("Please select Channel  ND");
            return;
        }
    });

    function getRates() {
        var selected_date = $('#selected_date').val();
        var nd = $('#nd').val();
        // ajax show old rate of saavan 
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/reports/upload/savan_rate.php",
            data: {
                selected_date: selected_date,
                nd: nd,
            },
            success: function(response) {
                // console.log(response);
                //handle error in response
                if (response["errCode"]) {
                    if (response["errCode"] == "-1") {
                        console.log(response['errMsg']);
                        $('#Ad_Supported_Revenue').val(response['errMsg']["Ad_Supported_Revenue"]);
                        $('#Subscription_Revenue').val(response['errMsg']["Subscription_Revenue"]);
                        $('#paid_playout_revenue').val(response['errMsg']["paid_playout_revenue"]);
                        $('#free_playout_revenue').val(response['errMsg']["free_playout_revenue"]);
                        //paid_playout_revenue
                        //free_playout_revenue
                    } else {
                        $('#Ad_Supported_Revenue').val("");
                        $('#Subscription_Revenue').val("");

                    }
                } else {
                    $('#Ad_Supported_Revenue').val("");
                    $('#Subscription_Revenue').val("");

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


            }
        });
        //end 
    }

    function saveReport(reportType, textvalue) {
        $(".alert").
        removeClass("alert-success").
        removeClass("alert-danger").
        fadeOut().
        find("span").html("");
        var selectedDate = $('#selected_date').val();
        var nd = $('#nd').val();
        var revenueShareYoutubeUpload = $('#' + textvalue).val();
        var Ad_Supported_Revenue = $("#Ad_Supported_Revenue").val();
        var Subscription_Revenue = $("#Subscription_Revenue").val();

        var paid_playout_revenue = $("#paid_playout_revenue").val();
        var free_playout_revenue = $("#free_playout_revenue").val();
        
        var truncate_data1 = 0;
        var truncate_data2 = 0;
        //event handler to handle the change event of the datepicker
        //chkND
        if ($('#truncate_data1').is(":checked"))
            {
            // it is checked
            truncate_data1 = 1
                
            }
            if ($('#truncate_data2').is(":checked"))
            {
            // it is checked
            truncate_data2 = 1
            }

        if (reportType == "saavan_report_file_upload") {
            if (Ad_Supported_Revenue == "") {
                $(".alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").html("Please enter Ad Supported Revenue");
                return;
            }
            if (Subscription_Revenue == "") {
                $(".alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").html("Please enter Subscription Revenue");
                return;
            }
        }
        if (reportType == "gaana_report_file_upload") {
            if (paid_playout_revenue == "") {
                $(".alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").html("Please Enter Paid Playout Revenue");
                return;
            }
            if (free_playout_revenue == "") {
                $(".alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").html("Please Enter Free Playout Revenue");
                return;
            }
        }


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
        console.log("url : ", '<?php echo $rootUrl; ?>controller/reports/upload/');
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/reports/upload/",
            data: {
                selected_date: selectedDate,
                csv_files: revenueShareYoutubeUpload,
                nd: nd,
                type: reportType,
                Ad_Supported_Revenue: Ad_Supported_Revenue,
                Subscription_Revenue: Subscription_Revenue,
                paid_playout_revenue: paid_playout_revenue,
                truncate_data1: truncate_data1,
                truncate_data2: truncate_data2
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
                html("" + msg);
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



    }
    </script>
</body>

</html>