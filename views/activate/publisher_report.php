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
        $pageTitle = "Publisher Reports";
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
                                        <!-- Activate Reports Youtube -->

                                 <!--        <div class="col-md-12">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <div class="input-group ">
                                                        <select class="form-control" name="nd" id="nd">
                                                            <option value="">Select Nirvana Disgital type</option>
                                                            <option value="nd1">ND1</option>
                                                            <option value="nd2">ND2</option>
                                                            <option value="ndkids">ND Kids</option>
                                                            <option value="redmusic">Youtube Music</option>

                                                            <option value="applemusic">Apple Music</option>
                                                            <option value="itune">Itune</option>
                                                            <option value="gaana">Gaana</option>
                                                            <option value="saavan">Saavan</option>
                                                            <option value="spotify">Spotify</option>
                                                        </select>

                                                    </div>
                                                </div>
                                            </div>
                                        </div> -->
                                         
                                         
                                         <div class="col-md-4   non_cmg hidealldefault1">

                                            <div class="col-md-4 " id="non_cmg">
                                                <div class="form-group">
                                                    <p style="font-size:16px;">Publisher Report v2.0:</p>

                                                    <i class="fa fa-calendar" style="color: #3688ca;"></i>
                                                    <input type="month" placeholder="Select Date"
                                                        class="form-control nd" id="selected_datev2_non_cmg"
                                                        name="selected_date" autocomplete="off" size="28">


                                                    <div class="col-md-6 ndbtnYoutubenddiv"
                                                        id="uploadReportFilesContainerv2_non_cmg" style="display: none">
                                                        <a href="../activate/publisher_report_non_cmgv2.php"
                                                            class="btn btn-success ndbtnYoutubend">Go</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

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
    <script src="<?php echo $rootUrl; ?>assets/js/material-dashboard.js"></script>
    <script type="text/javascript">
    function submitForm() {
        $("#submitBtn").value = 'Submitting';
        return true;
    }
   // $(".hidealldefault").hide();
    $('#nd').change(function() {
        $(".hidealldefault").hide();
        var nd = $('#nd').val();
        //alert(nd);
        $('.' + nd).show();

    });

 
   

 
    //event handler to handle the change event of the datepicker

    $('#selected_datev2_non_cmg').change(function() {
        var selectedDate = $('#selected_datev2_non_cmg').val();

        
        var k = $('#uploadReportFilesContainerv2_non_cmg a').attr('href');
        $('#uploadReportFilesContainerv2_non_cmg a').attr('href', k.split("?")[0] + '?reportMonthYear=' +
            selectedDate );
        $("#uploadReportFilesContainerv2_non_cmg").show();
    });

     
    //event handler to handle the change event of the datepicker
    $('#selected_date').change(function() {
        var selectedDate = $('#selected_date').val();
        var revenueShareYoutubeUpload = $('#revenue_share_youtube_upload').val();
        var k = $('#uploadReportFilesContainer a').attr('href');
        $('#uploadReportFilesContainer a').attr('href', k.split("?")[0] + '?reportMonthYear=' + selectedDate);
        $("#uploadReportFilesContainer").show();
    });

  
     
   
     
    $(function() {
        var dtToday = new Date();

        var month = dtToday.getMonth() + 1;
        var day = dtToday.getDate();
        var year = dtToday.getFullYear();
        if (month < 10)
            month = '0' + month.toString();
        if (day < 10)
            day = '0' + day.toString();

        var maxDate = year + '-' + month + '-' + day;
        var maxmonth = year + '-' + month;
        $('input[type=date]').attr('max', maxDate);
        $('input[type=month]').attr('max', maxmonth);
    });
    </script>
</body>

</html>