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
require_once(__ROOT__.'/model/client/clientModel.php');
require_once(__ROOT__.'/model/distributor/distributorModel.php');


//Connection With Database
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
//error connecting to DB
$returnArr["errCode"] = 1;
$returnArr["errMsg"] = getErrMsg(1).$conn["errMsg"];
} else {
$conn = $conn["errMsg"];

/*  echo $sql_co_mapping = "select client_username ,   CASE WHEN JSON_VALID(client_type_details) THEN JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutubeAudio')) ELSE null END as rev_share  from crep_cms_clients  ";

$channel_co_maping_result = runQuery($sql_co_mapping, $conn);

print_r($channel_co_maping_result);

while ($row3 = mysqli_fetch_assoc($channel_co_maping_result["dbResource"])) {
     print_r($row3);
    
}  */

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
        $pageTitle = "Revenue Report Video-id";
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
                                    <!-- <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="<?php //echo $rootUrl."views/export/"; ?>">
                                                <i class="fa fa-arrows">&nbsp;</i>Export Revenue
                                            </a>
                                        </li> 
                                    </ol>-->
                                </div> <!-- end card header -->
                                <div class="card-content1">
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
                                        <input type="hidden" name="mode" value="assignContentOwner">
                                        <!-- Assign Asset-id Report Youtube -->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="col-md-4" id="step1">
                                                    <div class="form-group">
                                                        <div class="input-group ">
                                                            <select class="form-control" name="cmstype" id="cmstype">
                                                                <option value="">Select Nirvana Disgital type</option>
                                                                <option value="nd1">ND1</option>
                                                                <option value="nd2">ND2</option>
                                                                <option value="ndkids">ND Kids</option>
                                                                <option value="redmusic">Youtube Music</option>
                                                               <!--  <option value="applemusic">Apple Music</option>
                                                                <option value="itune">Itune</option>
                                                                <option value="gaana">Gaana</option>
                                                                <option value="saavn">Saavan</option>
                                                                <option value="spotify">Spotify</option> -->
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4" id="step2">
                                                    <div class="form-group">
                                                        <div class="input-group ">
                                                            <select class="form-control" name="type_cate"
                                                                id="type_cate">
                                                                <option value="">Select Report</option>
                                                                <option value="youtube_video_claim_report_nd">Youtube Claim  Report</option>
                                                                <option value="youtuberedmusic_video_report">Youtube Youtube  Music</option>
                                                                <option value="youtube_red_music_video_finance_report">
                                                                    Youtube Red Finance
                                                                </option>
                                                                <option value="youtube_ecommerce_paid_features_report">
                                                                    Youtube ecommerce paid features</option>
                                                                <option value="youtube_labelengine_report">Youtube US  Report
                                                                </option>
                                                               <!--  <option value="report_audio">Apple Music Report
                                                                </option>
                                                                <option value="report_audio">Itune Report
                                                                </option>
                                                                <option value="report_audio">Gaana Report
                                                                </option>
                                                                <option value="report_audio">Saavan Report
                                                                </option>
                                                                <option value="report_audio">Spotify Report
                                                                </option> -->
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4" id="step3">
                                                    <div class="form-group">
                                                        <p style="font-size:16px;"> Select Date:</p>

                                                        <i class="fa fa-calendar" style="color: #3688ca;"></i>
                                                        <input type="month" placeholder="Select Date"
                                                            class="form-control nd" id="selected_date"
                                                            name="selected_date" autocomplete="off" size="28">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="col-md-3" id="step4">
                                                <div class="form-group mx-sm-3 mb-2">

                                                    <input type="checkbox" class="form-control" id="onlyunassigned2"
                                                        name="onlyunassigned" value="2"> Only Assigned Content Owner
                                                    </br>
                                                    <!-- <input type="radio" class="form-control" id="onlyunassigned1"
                                                        checked name="onlyunassigned" value="1"> Only Un-assigned
                                                    Content Owner -->
                                                </div>
                                            </div>

                                            <div class="col-md-4" id="step5">
                                                <div class="form-group">
                                                    <?php
                                                $clientsSearchArr = array("status"=>1);
                                                $fieldsStr = "email, client_username, client_firstname";
                                                $allClients = getClientsInfo($clientsSearchArr, $fieldsStr, null, $conn);
                                                if (!noError($allClients)) {
                                                    printArr("Error fetching all clients");
                                                    exit;
                                                }
                                                $allClients = $allClients["errMsg"];
                                                ?>

                                                    <select name="contentOwner" id="contentOwner" class="form-control">
                                                        <option value="">Select Client</option>
                                                        <?php
                                                    foreach ($allClients as $clientEmail => $clientDetails) {
                                                        $selected = "";
                                                        if (isset($clientSearchArr["client_username"]) && ($clientDetails['client_username']==$clientSearchArr["client_username"])) {
                                                            $selected = "selected='selected'";
                                                        }
                                                    ?>
                                                        <option <?php echo $selected; ?>
                                                            value="<?php echo $clientDetails['client_username']; ?>">
                                                            <?php echo $clientDetails['client_username']."-".$clientDetails['client_firstname']; ?>
                                                        </option>
                                                        <?php
                                                    }
                                                    ?>
                                                    </select>

                                                </div>
                                            </div>






                                            <!-- new row start-->


                                        </div>
                                        <div class="col-md-12 form-group is-empty">
                                            <button class="btn" type="button" id="btnSumit">Export
                                            </button>
                                        </div>
                                        <div class="col-md-12 form-group is-empty">
                                            <!--  Path for file download : /var/lib/mysql-files/forDJ/</br> -->
                                             <!-- for Pending:pendingAssetCoMAPRevenue_nd1_2021-07_1631606509.csv</br> -->
                                            <!--  For contentOwner: Revenue_Video_ND1_2021_07_mobilemedia_1631606467.csv -->  
                                            <?php
                                            
                                             

                                            if ($handle = opendir('../../excelreports/arevenuereports/')) {

                                                while (false !== ($entry = readdir($handle))) {
                                            
                                                    if ($entry != "." && $entry != "..") {
                                            
                                                        $zip_file_tmp = $entry;
                                                        ?>
                                                         <a href='../../excelreports/arevenuereports/<?=$zip_file_tmp?>' target='_blank'><button type="button" name="btnDownload" id="btnDownload"
                class="btn btn-info"> <?php echo $zip_file_tmp?></button></a></br>
                                                        <?php
                                                    }
                                                }
                                            
                                                closedir($handle);
                                            }

                                            ?>
                                        </div>

                                </div>

                                </form>

                            </div>

                        </div>
                    </div>
                </div>
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
    <!--    <script src="<?php echo $rootUrl; ?>assets/js/material-dashboard.js"></script> -->
    <script type="text/javascript">
    function submitForm() {
        $("#submitBtn").value = 'Submitting';
        return true;
    }
    $('#step2').hide();
    $('#step3').hide();
    $('#step4').hide();
    $('#step5').hide();

    //event handler to handle the change event of the datepicker
    $('#cmstype').change(function() {
        var cmstype = $('#cmstype').val();
        if (cmstype != "") {
            $('#step2').show();
        } else {
            $('#step2').hide();
            $('#step3').hide();
            $('#step4').hide();
            $('#step5').hide();


        }
    });
    $('#type_cate').change(function() {
        var type_cate = $('#type_cate').val();
        if (type_cate != "") {
            $('#step3').show();
        } else {

            $('#step3').hide();
            $('#step4').hide();
            $('#step5').hide();


        }
    });
    $('#selected_date').change(function() {
        var selected_date = $('#selected_date').val();
        if (selected_date != "") {
            $('#step4').show();
        } else {


            $('#step4').hide();
            $('#step5').hide();


        }
    });



    $('#onlyunassigned2').click(function() {

        
        if ($(this).is(':checked')) {
            $('#step5').show();
    } else {
         //$(this).prop('checked',true);
         $('#step5').hide();
    }

    });
    $('#onlyunassigned1').click(function() {

        $('#step5').hide();
    });

    //event handler to handle the change event of the datepicker
    $('#btnSumit').click(function() {

        var cmstype = $("#cmstype").val();

        var type_cate = $("#type_cate").val();
        var selected_date = $("#selected_date").val();
        var contentOwner = $("#contentOwner").val();

        if (cmstype == "") {
            alert("Please select Nirvana Disgital type");
            return false;
        }

        if (type_cate == "") {
            alert("Please select report");
            return false;
        }
        if (selected_date == "") {
            alert("Please select date");
            return false;
        }
        /* if ($("#onlyunassigned2").is(":checked") && contentOwner == "") {
            alert("Please select content Owner");
            return false;
        } */


        $("#confirmClientModal").modal();
    });

    function submitClient(buttonElement) {

        var cmstype = $("#cmstype").val();

        var type_cate = $("#type_cate").val();
        var selected_date = $("#selected_date").val();;
        var contentOwner = $("#contentOwner").val();;

        if (cmstype == "") {
            alert("Please select Nirvana Disgital type");
            return false;
        }

        if (type_cate == "") {
            alert("Please select report");
            return false;
        }
        if (selected_date == "") {
            alert("Please select date");
            return false;
        }
        /* 
        if(contentOwner==""){
            alert("Please select Content owner/Client");
            return false;
        } */


        //resetting the error message
        $("#confirmClientModal .alert").
        removeClass("alert-success").
        removeClass("alert-danger").
        fadeOut().
        find("span").html("");

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/export/exportrevenuedetailv2.php",
            data: {
                mode: $("#mode").val(),
                cmstype: $("#cmstype").val(),
                type_cate: $("#type_cate").val(),
                selected_date: $("#selected_date").val(),
                contentOwner: $("#contentOwner").val(),
                onlyunassigned: $("#onlyunassigned1").is(":checked") ? 1 : 2
            },
            success: function(client) {
                console.log("return data ", client);
                if (client["errCode"]) {
                    if (client["errCode"] != "-1") { //there is some error
                        $("#confirmClientModal .alert").
                        removeClass("alert-success").
                        addClass("alert-danger").
                        fadeIn().
                        find("span").
                        html(client["errMsg"]);
                    } else {
                        $("#confirmClientModal .alert").
                        removeClass("alert-danger").
                        addClass("alert-success").
                        fadeIn().
                        find("span").
                        html(client["errMsg"]);
                        setTimeout(function() {
                            window.location.reload();
                        }, 3000);
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

                $("#confirmClientModal .alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html("500 internal server error " + msg);
            }
        });
    }


    $(function() {

    });
    </script>

    <!-- delete Client modal -->
    <div class="modal fade" id="confirmClientModal">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Export Revenue Detail!</h4>
            </div>
            <div class="modal-body">
                <div class="alert" style="display: none">
                    <span></span>
                </div>
                <p>
                    Are you sure you want to export Revenue for this Content Owner?
                <p id="dataSelected"></p>
                </p>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="deleteClientBtn" data-client-code=""
                        onclick="submitClient(this);">Continue</button>
                </div>
            </div>
        </div>
    </div>
    <!-- end delete client modal -->
</body>

</html>