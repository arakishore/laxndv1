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
                                                            <option value="nd3">ND3</option>
                                                            <option value="Other">Other</option>
                                                        </select>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12" id="uploadReportFilesContainer"
                                                style="display: none">
                                                <!-- Input Youtube Claim Report 1 -->
                                                <div>
                                                    <div class="row">
                                                        <label for="youtube_video_claim_report">
                                                            Input Youtube Claim v2.0 File 1:
                                                        </label>
                                                        <input type="checkbox" id="youtube_video_claim_report_checkbox"
                                                            name="holding_check[]"
                                                            onclick="toggleReportFileUpload('youtube_video_claim_report', this)"
                                                            value="1">
                                                    </div>

                                                    <div class="row"
                                                        id="youtube_video_claim_report_file_upload_container"
                                                        style="display: none;">
                                                        <div class="col-md-12 form-group label-floating is-focused">
                                                            <label class="control-label">Input Youtube Claim report
                                                                filename YouTube_yyyymmdd_claim_raw_v1-1</label>
                                                            <input type="text" class="form-control" style="width:90%"
                                                                data-parsley-trigger="keyup" required=""
                                                                id="youtube_video_claim_report_upload"
                                                                name="youtube_video_claim_report_upload" value="">
                                                            <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                            <button type="button"
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
                                                <!-- End   Youtube -->

                                                <!-- Input Youtube Claim Report 1 -->
                                                <div>
                                                    <div class="row">
                                                        <label for="youtube_video_claim_report_2">
                                                            Input Youtube Claim v2.0 File 2:
                                                        </label>
                                                        <input type="checkbox" id="youtube_video_claim_report_2_checkbox"
                                                            name="holding_check[]"
                                                            onclick="toggleReportFileUpload('youtube_video_claim_report_2', this)"
                                                            value="1">
                                                    </div>

                                                    <div class="row"
                                                        id="youtube_video_claim_report_2_file_upload_container"
                                                        style="display: none;">
                                                        <div class="col-md-12 form-group label-floating is-focused">
                                                            <label class="control-label">Input Youtube Claim report
                                                                filename claim_report_nirvanadigital_mm_yyyy</label>
                                                            <input type="text" class="form-control" style="width:90%"
                                                                data-parsley-trigger="keyup" required=""
                                                                id="youtube_video_claim_report_2_upload"
                                                                name="youtube_video_claim_report_2_upload" value="">
                                                            <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                            <button type="button"
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
                                                        <label for="youtube_video_claim_report_3">
                                                            Input Youtube Claim v2.0 File 3:
                                                        </label>
                                                        <input type="checkbox" id="youtube_video_claim_report_3_checkbox"
                                                            name="holding_check[]"
                                                            onclick="toggleReportFileUpload('youtube_video_claim_report_3', this)"
                                                            value="1">
                                                    </div>

                                                    <div class="row"
                                                        id="youtube_video_claim_report_3_file_upload_container"
                                                        style="display: none;">
                                                        <div class="col-md-12 form-group label-floating is-focused">
                                                            <label class="control-label">Input Youtube Claim report
                                                            Asset Label Data </label>
                                                            <input type="text" class="form-control" style="width:90%"
                                                                data-parsley-trigger="keyup" required=""
                                                                id="youtube_video_claim_report_3_upload"
                                                                name="youtube_video_claim_report_3_upload" value="">
                                                            <?php 
                                                            if ($userHighestPermOnPage == 2) {
                                                            ?>
                                                            <button type="button"
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
        var revenueShareYoutubeUpload = $('#youtube_video_claim_report_upload').val();
        $("#uploadReportFilesContainer").show();
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
            error: function() {
                $(".alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html("500 Internal Server Error");
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