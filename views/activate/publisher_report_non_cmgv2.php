<?php
   //Manage Clients view page
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
   require_once(__ROOT__.'/model/validate/validateModel.php');
   require_once(__ROOT__.'/model/activate/activateModel.php'); 
   require_once(__ROOT__.'/model/reports/youtubeClaimReportsModel.php');


   $currenturl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
   $actual_link = explode('&&',$currenturl)[0];   
   //Connection With Database
   $conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
   if (!noError($conn)) {
       //error connecting to DB
       $returnArr["errCode"] = 1;
       $returnArr["errMsg"] = getErrMsg(1).$conn["errMsg"];
   } else {
       $conn = $conn["errMsg"];
       $returnArr = array();

       //check weather table exist or not
       $nd = 'non_cmg';

       $selectedDate = $_GET["reportMonthYear"];
       $year     = date("Y", strtotime($selectedDate));
       $month    = date("m", strtotime($selectedDate));
  
       $haveactivationreport = false;
       $activatetableName = 'report_publishing_main_non_cmg_'.$year.'_'.$month ;
       
       $tableArr = checkTableExist($activatetableName, $conn); 
       if ($tableArr['errMsg'] == '1') {
        $haveactivationreport = true;
             
       }else{
        $haveactivationreport = false;
       }

      // $contentowner = getContentOwner($conn);
       //get the user info
       $email = $_SESSION['userEmail'];
   
       //initialize logs
       $logsProcessor = new logsProcessor();
       $initLogs = initializeJsonLogs($email);
       $logFilePath = $logStorePaths["clients"];
       $logFileName="viewClients.json";
   
       $logMsg = "View Clients process start.";
       $logData['step1']["data"] = "1. {$logMsg}";
   
       $logMsg = "Database connection successful.";
       $logData["step2"]["data"] = "2. {$logMsg}";
   
       $logMsg = "Attempting to get user info.";
       $logData["step3"]["data"] = "3. {$logMsg}";
   
   
       
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
               $logMsg = "User not found: {$token}";
               $logData["step3.1"]["data"] = "3.1. {$logMsg}";
               $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
               $returnArr["errCode"] = 5;
               $returnArr["errMsg"] = getErrMsg(5).": This URL is invalid or expired.";
           } else {
               //check if user is active
             $table_typename="report_publishing_main_non_cmg_";
             $selectedDate_frm = $year.'_'.$month;
               $allClientsInfo = getPublisherReportv2(
                 $selectedDate_frm,$conn
             );
         
               if (!noError($allClientsInfo)) {

                 //error fetching all clients info
                 $logMsg = "Couldn't fetch all clients info: {$allClientsInfo["errMsg"]}";
                 $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                 $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                 
                 $returnArr["errCode"] = 5;
                 $returnArr["errMsg"] = getErrMsg(5)." Error fetching clients details.";
             } else {
                 $logMsg = "Got all clients data for page: {$page}";
                 $logData["step6"]["data"] = "6. {$logMsg}";
                 $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                 $allClientsInfo = $allClientsInfo["errMsg"];
             
                
             
                 $returnArr["errCode"] = -1;
             } //close getting all clients info

           } // close checking if user is found
       } // close user info
   }
  // print_r($allClientsInfo);
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
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" type="text/css"
        href="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.7/css/dataTables.checkboxes.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs/dt-1.10.21/datatables.min.css" />
    <script src="<?php echo $rootUrl; ?>assets/js/jquery.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap.min.js" type="text/javascript"></script>
    <script src="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.7/js/dataTables.checkboxes.min.js"
        type="text/javascript"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css"
        rel="stylesheet" />
</head>
<style>
</style>

<body>
    <?php /*
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
         } */
         ?>
    <!--Loading new page-->
    <div class="header" id="youtube1">
        <div class="row">
            <div class="col-lg-1">
                <div class="form-group" style="margin:10px; ">
                    <button type="button" data-dismiss="modal" style="float:left; padding:5px; font-size:15px;">
                        <a style="color:white;" href="../activate/publisher_report.php"><i style="font-size:20px;"
                                class="fa fa-arrow-left"></i>
                        </a></button>
                </div>
            </div>
            <div class="col-lg-6">
                <h4 class="modal2-title">Publisher Reports - <?php echo $_GET["reportMonthYear"];?></h4>
            </div>
        </div>
    </div>
    <!-- choose field drowpdoun-->


    <!--main table page-->

    <div class="col-md-12">

        <div id="alert" class="alert alert-default" style="display: none;">

        </div>
        <div class="card">
            <?php if(!$haveactivationreport || empty($allClientsInfo)){ ?>



            <div class="card-content">
                <div class="alert alert-danger">There is no activation data, please select other date 
                </div>
            </div>
            <?php }else{  ?>
            <!-- <div class="card-content">
                <a class="btn btn-success" id="btnExportunAssigned" data-toggle="collapse" href="#"
                    style="margin-left:0.5vw;">Export</a> <?php
          //  $fileis=  'youtube_video_claim_activation_report_'.$nd.'_'.$year.'_'.$month.'.zip';
        
            if(file_exists('../../excelreports/'.$fileis)){?>
                <a href='../../excelreports/<?=$fileis?>'>Download zip</a>
                <?php }
        ?>
            </div> -->
            <div class="card-content">

                <table class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th>SR#</th>
                            <th>Type</th>
                            <th>Content Owner</th>
                            <th>Asset Label</th>
                            
                            <th>Partner Revenue</th>
                           
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                      $i=1;
        $grandtotal = 0;
                        foreach($allClientsInfo as $clientEmail=>$clientDetails){ 
                         $grandtotal = $grandtotal+$clientDetails["partnerRevenue"];
                        ?>
                        <tr>
                            <td><?php echo $i++?></td>
                            <td><?php echo $clientDetails["typename"]; ?></td>
                            <td><?php echo $clientDetails["content_owner"]; ?></td>
                            <td><?php echo $clientDetails["Asset_Label"]; ?></td>
                            <td><?php echo $clientDetails["partnerRevenue"]; ?></td>
                           
                        </tr>
                        <?php
                        }
                        ?>
                         <tr>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td><strong>Grand</strong></td>
                            <td><?php echo $grandtotal; ?></td>
                           
                        </tr>
                    </tbody>
                </table>

                <!-- pagination -->

                <!-- end pagination -->
            </div>
            <?php } ?>
        </div>
    </div>
    <!-- end Clients table -->

</body>
<script>
$(document).ready(function() {



});
</script>
<script src="<?php echo $rootUrl; ?>assets/js/bootstrap.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>

</html>