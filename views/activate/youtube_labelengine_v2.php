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
       $nd = $_GET["nd"];

       $nd_label = ($nd=="redmusic") ? 'Youtube Music':$nd;
       $selectedDate = $_GET["reportMonthYear"];
       $year     = date("Y", strtotime($selectedDate));
       $month    = date("m", strtotime($selectedDate));
  
       $haveactivationreport = false;
       $activatetableName = 'youtube_labelengine_activation_report_'.$nd.'_'.$year.'_'.$month; ;
       
       $tableArr = checkTableExist($activatetableName, $conn); 
       if ($tableArr['errMsg'] == '1') {
        $haveactivationreport = true;
             
       }else{
        $haveactivationreport = false;
       }

       $contentowner = getContentOwner($conn);
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
   
       $clientStatusMap = array(
           "1" => "Active",
           "0" => "Inactive",
           "2" => "Deleted"
       );
       
       $userSearchArr = array('email'=>$email);
       $fieldsStr = "`email`, `status`, `image`, `groups`, `rights`, `firstname`, `lastname`";
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
               //first get the user email
               $email = array_keys($userInfo);
               $email = $email[0];
               if ($userInfo[$email]["status"]!=1) {
                   //user not active
                   $logMsg = "User not active: {$token}";
                   $logData["step3.1"]["data"] = "3.1 {$logMsg}";
                   $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
   
                   $returnArr["errCode"] = 5;
                   $returnArr["errMsg"] = getErrMsg(5).": This URL is invalid or expired.";
               } else {
                   //user is found and is active. Now validate the request parameters
                   //pagination parameters
                   $page = 1;
                   if (isset($_GET['page']) && !empty($_GET["page"])) {
                       $page = preg_replace('#[^0-9]#i', '', $_GET['page']);
                   }
                   $resultsPerPage = RESULTSPERPAGE;
                   $offset = ($page - 1) * $resultsPerPage;
                   
                   $logMsg = "Attempting to get count of all clients.";
                   $logData["step4"]["data"] = "4. {$logMsg}";
                   
                   //set the search array based on get parameters
                   $clientSearchArr = array("1"=>1);
               
                    if (isset($_GET["userName"]) && !empty($_GET["userName"])) {
                        $clientSearchArr["client_username"] = cleanQueryParameter($conn, cleanXSS($_GET["userName"]));
                    }
                   if (isset($_GET["source"]) && !empty($_GET["source"])) {
                       $clientSearchArr["source"] = cleanQueryParameter($conn, cleanXSS($_GET["source"]));
                   }
                   
                   if (isset($_GET["status"]) && is_numeric($_GET["status"])) {
                       $clientSearchArr["status"] = cleanQueryParameter($conn, cleanXSS($_GET["status"]));
                   }
                   if (isset($_GET["contentowner"]) && !empty($_GET["contentowner"])) {
                    $clientSearchArr["content_owner"] = cleanQueryParameter($conn, cleanXSS($_GET["contentowner"]));
                   }
                  
                    
                   $fieldsStr = "COUNT(*) as noOfClients";
                   $allClientsCount = getActivationReport($activatetableName,$clientSearchArr, $fieldsStr, null, $conn);
               
                   if (!noError($allClientsCount)) {
                       //error fetching all clients Count
                       $logMsg = "Couldn't fetch all clients Count: {$allClientsCount["errMsg"]}.".
                                   "Search params: ".json_encode($clientSearchArr);
                       $logData["step4.1"]["data"] = "4.1. {$logMsg}";
                       $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                       
                       $returnArr["errCode"] = 5;
                       $returnArr["errMsg"] = getErrMsg(5)." Error fetching client details.";
                   } else {
                       $allClientsCount = $allClientsCount["errMsg"][0]["noOfClients"]; //why anonymous? see function definition
                  
                       //set the last page num
                       $lastPage = ceil($allClientsCount / $resultsPerPage);
                       // printArr($lastPage);
   
                       if ($page <= 1) {
                           $page = 1;
                       } else if ($page > $lastPage) {
                           $page = $lastPage;
                       }
   
                       $logMsg = "Got all clients count for page: {$page}. Now getting all clients info";
                       $logData["step5"]["data"] = "5. {$logMsg}";
                       
                       $fieldsStr = "*";
                       //set different getter arguments if it is in export mode
                       $export = false;
                       if (isset($_GET["export"])) {
                           $export = true;
                           $offset = 0;
                           $resultsPerPage = 9999;
                           $fieldsStr = "*";
                       }
                
                       $allClientsInfo = getActivationReportv2(
                          $activatetableName,
                           $clientSearchArr,
                           $fieldsStr,
                           null,
                           $conn,
                           $offset,
                           $resultsPerPage
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
                       
                           if ($export) {
   
                               $logMsg = "Request is to export all clients data to excel.";
                               $logData["step7"]["data"] = "7. {$logMsg}";
                               
                               $spreadsheet = new Spreadsheet();
                               $spreadsheet->setActiveSheetIndex(0);
                               $activeSheet = $spreadsheet->getActiveSheet();
                               
                               //add header to spreadsheet
                               $header = array_keys($allClientsInfo);
                               
                               $header = $header[0];
                               $header = array_keys($allClientsInfo[$header]);
                               $header = array_values($header);
                               $activeSheet->fromArray([$header], NULL, 'A1');
    
                               //add each client to the spreadsheet
                               $clients = array();
                               $startCell = 2; //starting from A2
                               foreach($allClientsInfo as $clientEmail=>$clientDetails) {
                                   $client = array_values($clientDetails);
   
                                   //replace status code with name
                                   $client[14] = $clientStatusMap[$client[14]];
                                   //replace client type details json with string
                                   $client[15] = json_decode($client[15], true);
                                   $clientTypeDetails = "";
                                   if(is_array($client[15])) {
                                       foreach ($client[15] as $detailName=>$detailValue) {
                                           $clientTypeDetails .= "{$detailName}={$detailValue}, ";
                                       }
                                   }
                                   $client[15] = $clientTypeDetails;
   
                                   $client[16] = json_decode($client[16], true);
                                   $companyTypeDetails = "";
                                   if(is_array($client[16])) {
                                       foreach ($client[16] as $detailName=>$detailValue) {
                                           $companyTypeDetails .= "{$detailName}={$detailValue}, ";
                                       }
                                   }
                                   $client[16] = $companyTypeDetails;
                                   $activeSheet->fromArray([$client], NULL, 'A'.$startCell);
                                   $startCell++;
                               }
   
                               //auto width on each column
                               $highestColumn = $spreadsheet->getActiveSheet()->getHighestDataColumn();
   
                               foreach (range('A', $highestColumn) as $col) {
                                   $spreadsheet->getActiveSheet()
                                           ->getColumnDimension($col)
                                           ->setAutoSize(true);
                               }
   
                               //style the header and totals rows
                               $styleArray = [
                                   'font' => [
                                       'bold' => true,
                                       'color'=>array('argb' => 'FFC5392A'),
                                   ],
                                   'alignment' => [
                                       'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                                   ],
                                   'borders' => [
                                       'top' => [
                                           'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                                       ]
                                   ]
                               ];
                               $spreadsheet->getActiveSheet()->getStyle('A1:'.$highestColumn.'1')->applyFromArray($styleArray);
                               
                               // //download the file
                               $filename = "clientsMaster";
                               header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                               header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
                               header('Cache-Control: max-age=0');
   
                               $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                               ob_clean();
                               $writer->save('php://output');
   
                               $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                               exit;
                           }
                       
                           $returnArr["errCode"] = -1;
                       } //close getting all clients info
                   } //close getting all clients count
               } //close checking if user is active
           } // close checking if user is found
       } // close user info
   }
   
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
                        <a style="color:white;" href="../activate/"><i style="font-size:20px;"
                                class="fa fa-arrow-left"></i>
                        </a></button>
                </div>
            </div>
            <div class="col-lg-11">

                <h4 class="modal2-title">Activate Report US Report  <?php echo $nd_label?> -<?php echo $selectedDate?></h4>
            </div>
        </div>
    </div>
    <!-- choose field drowpdoun-->
    <div class="col-md-12">
        <!-- <div class="col-md-2" style="margin-left:1vw; ">
            <div class="head2" style="margin-left:1vw; margin-top:4vh;">
                <select name="userName" id="searchby" class="form-control">
                    <option value="">Choose a field</option>
                    <option value="Video_id">Video ID</option>
                    <option value="video_title">Video Title</option>
                    <option value="asset_channel">Channel</option>
                    <option value="uploader2">Uploader</option>
                    <option value="uploader">Content Owner</option>
                    <option value="content_type">Content Type</option>
                    <option value="asset_id">Asset ID</option>
                </select>
            </div>
            </div> -->
        <!-- search button -->
        <div class="" style="margin-left:1vw;">
            <!-- <button type="submit" id="search" class="btn btn-success fa fa-search">
                    </button> -->
            <form enctype="multipart/form-data" class="form-inline searchForm"
                action="<?=$currenturl?>" method="GET">
                <input type="hidden" name="reportMonthYear" value="<?=$_GET['reportMonthYear']?>" />
                <input type="hidden" name="nd" value="<?=$nd?>" />
                <select name="contentowner" id="contentowner" class="form-control1 selectpicker" data-live-search="true">
                    <option value="">--Search by Content Owner--</option>
                    <?php 
                            $currentco=$_GET['contentowner'];
                            foreach($contentowner['errMsg'] as $c){
                                if($currentco==$c){
                                    echo '<option value="'.$c.'" selected>'.$c.'</option>';
                                }else{
                                    echo '<option value="'.$c.'">'.$c.'</option>';
                                }
                              
                            }
                      ?>
                </select>

                <button type="submit" class="btn btn-success fa fa-search">
                    <div class="ripple-container"></div>
                </button>
            </form>
        </div>
        <!-- end Status -->
        <!-- search button -->
        <div class="" style="margin-left:1vw;">
            <!-- <button type="submit" id="search" class="btn btn-success fa fa-search">
               </button> -->
            <td colspan="7" align="center" style="padding-top:3px; ">

                <a class="btn btn-success" id="savebulk" data-toggle="collapse" href="#collapseExample5" role="button"
                    aria-expanded="false" aria-controls="collapseExample" style="margin-left:0.5vw;">Bulk Change
                    status</a>


                <button class="btn btn-success" id="genrateReport">Genrate Report</button>
            </td>
        </div>
        <!-- end search button -->
        <!-- Table Header + Save Button -->
    </div>
    </div>
    <!--main table page-->
        
    <div class="col-md-12">
  
    <div id="alert" class="alert alert-default" style="display: none;">
  
    </div>
        <div class="card">
            <?php if(!$haveactivationreport || empty($allClientsInfo)){ ?>
              
           

            <div class="card-content">
                <div class="alert alert-danger">There is no activation data, please click the Generate Report button
                </div>
            </div>
            <?php }else{  ?>
                <div class="card-content">
                <a class="btn btn-success" id="btnExportunAssigned" data-toggle="collapse" href="#"   style="margin-left:0.5vw;">Export</a>   <?php
         $fileis=  'youtube_labelengine_activation_report_'.$nd.'_'.$year.'_'.$month.'.zip';
        
            if(file_exists('../../excelreports/'.$fileis)){?>
        <a href='../../excelreports/<?=$fileis?>'>Download zip</a>
        <?php }
        ?>
            </div>
            <div class="card-content">

                <table class="table table-bordered table-condensed">
                <thead>
                        <tr>
                            <th><input type="checkbox" value="0" id="selectAll" /></th>
                            <th>Content Owner</th>
                            <th>Total Amount Recvd</th>
                            <th>Shares (%)</th>
                            <th>Amt Payable</th>
                            <th>US Payout</th>
                            <th>Holding Perc</th>
                            <th>Witholding</th>
                            <th>Final Payable</th>
                            <th>GST Perc</th>
                            <th>Final Payable With GST</th>
                            <th>Status</th>
                             
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                      
                        foreach($allClientsInfo as $clientEmail=>$clientDetails){ 
                          //  $holding_percentage = $clientDetails["holding_percentage"];
                           // $final_payable = $clientDetails["final_payable"];
                           // $gst_percentage = $clientDetails["gst_percentage"];
                           // $final_payable_with_gst = number_format($final_payable + ($final_payable * $gst_percentage /100),2,'.','');
                        ?>
                        <tr>
                            <td><input type="checkbox" name="act_id[]" class="delete_act"
                                    value="<?php echo $clientDetails["id"]; ?>" /></td>
                            <td><?php echo $clientDetails["content_owner"]; ?></td>
                            <td><?php echo $clientDetails["total_amt_recd"]; ?></td>
                            <td><?php echo $clientDetails["shares"]; ?></td>
                            <td><?php echo $clientDetails["amt_payable"]; ?></td>
                            <td><?php echo $clientDetails["us_payout"]; ?></td>
                            <td><?php echo $clientDetails["holding_percentage"]; ?>%</td>
                            <td><?php echo $clientDetails["witholding"]; ?></td>
                            <td><?php echo $clientDetails["final_payable"]; ?></td>
                            <td><?php echo $clientDetails["gst_percentage"]; ?>%</td>
                            <td><?php echo $clientDetails["final_payable_with_gst"]; ?></td>
                            <td><?php echo $clientDetails["status"]; ?></td>
                            
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>

                <!-- pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php

                       
                        if($page>1){
                        ?>
                        <li class="page-item"><a class="page-link" href="<?=$actual_link.'&&page=1'?>">&laquo;</a></li>
                        <li class="page-item"><a class="page-link"
                                href="<?=$actual_link?>&&page=<?php echo ($page-1); ?>">Prev</a></li>
                        <?php
                        }
                        //loop through the pagination range after setting it to display page numbers
                        if ($page == 1) {
                            $startLoop = 1;
                            $endLoop = ($lastPage < PAGINATIONRANGE) ? $lastPage : PAGINATIONRANGE;
                        } else if ($page == $lastPage) {
                                $startLoop = (($lastPage - PAGINATIONRANGE) < 1) ? 1 : ($lastPage - PAGINATIONRANGE);
                                $endLoop = $lastPage;
                        } else {
                                $startLoop = (($page - PAGINATIONRANGE) < 1) ? 1 : ($page - PAGINATIONRANGE);
                                $endLoop = (($page + PAGINATIONRANGE) > $lastPage) ? $lastPage : ($page + PAGINATIONRANGE);
                        }
                        
                        for ($i = $startLoop; $i <= $endLoop; $i++) {
                            $activeClass = ($i==$page)?"active":"";                                                        
                        ?>
                        <li class="page-item <?php echo $activeClass; ?>"><a class="page-link"
                                href="<?=$actual_link?>&&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                        <?php
                        }
                        ?>
                        <?php
                        if($page<$lastPage){	
                        ?>
                        <li class="page-item"><a class="page-link"
                                href="<?=$actual_link?>&&page=<?php echo ($page+1); ?>">Next</a></li>
                        <li class="page-item"><a class="page-link"
                                href="<?=$actual_link?>&&page=<?php echo ($lastPage); ?>">&raquo;</a></li>
                        <?php
                        }
                        ?>
                    </ul>
                </nav>
                <!-- end pagination -->
            </div>
            <?php } ?>
        </div>
    </div>
    <!-- end Clients table -->
    <div class="modal fade" id="deleteDistributorModal">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Change status</h4>
            </div>
            <div class="modal-body">
                <p id="alertmsg">Please select action to changes status of selected records</p>
                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" id="activateRecords">Active</button>
                    <button type="button" class="btn btn-info" id="inactivateRecords">Inactive</button>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
$(document).ready(function() {

    $('#btnExportunAssigned').on('click', function(e) {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/activate/export/",
            data: {
                selected_date: '<?php echo $_GET["reportMonthYear"];?>',
                type: 'labelengine_activation_report',
                nd: '<?php echo $nd?>'
            },
            success: function(response) {
                console.log(response);
                //handle error in response
                if (response["errCode"]) {
                    if (response["errCode"] != "-1") {

                        $("#alert").css("display", "block");
                        //there was an error, alert the error and hide the form.
                        $("#alert").
                        removeClass("alert-success").
                        addClass("alert-danger").
                        fadeIn().
                        html(response["errMsg"]);
                        // setTimeout(function(){
                        //     window.location.reload();
                        // }, 3000);
                        // $("#uploadMISFilesContainer").hide();
                    } else {
                        $("#alert").css("display", "block");
                        $("#alert").
                        removeClass("alert-danger").
                        addClass("alert-success").
                        fadeIn().
                        html(response["errMsg"]);
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
        console.log("msg",msg);
                $(".alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html("500 Internal Server Error");
            }
        });
    });


    // Handle click on "Select all" control
    $('#selectAll').click(function(e) {
        var table = $(e.target).closest('table');
        $('td input:checkbox', table).prop('checked', this.checked);
    });


    // Handle click on checkbox to set state of "Select all" control
    $('#example tbody').on('change', 'input[type="checkbox"]', function() {
        // If checkbox is not checked
        if (!this.checked) {
            var el = $('#example-select-all').get(0);
        }
    });


    $('#genrateReport').on('click', function(e) {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/activate/generate/",
            data: {
                selected_date: '<?php echo $_GET["reportMonthYear"];?>',
                nd: '<?php echo $_GET["nd"];?>',
                type: 'labelengine_activation_report'
            },
            success: function(response) {
                //alert(response);
                console.log("response : ",response);
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
                        html(response["errMsg"]);
                        // $("#uploadMISFilesContainer").hide();
                    } else {
                        $(".alert").css("display", "block");
                        $(".alert").
                        removeClass("alert-danger").
                        addClass("alert-success").
                        fadeIn().
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
    });
    var id = [];

    // Handle form submission event
    $('#savebulk').on('click', function(e) {id = []
        $(':checkbox:checked').each(function(i) { console.log(i);
          //  if (i != 0) {
                id.push($(this).val());
           // }

        });
        if (id.length > 0) {
            confirmbox();
        }
    });

    $('#activateRecords').on('click', function(e) {
        bulkassign('active');
        $("#deleteDistributorModal").modal('toggle');
    });
    $('#inactivateRecords').on('click', function(e) {
        bulkassign('inactive');
        $("#deleteDistributorModal").modal('toggle');
    });


    function bulkassign(status) {
 
        saveContentowner(id, status);
    }

    function confirmbox() {
        //     $("#alertmsg").html('Are you sure you want to bulk assign for selected records?');
        $("#deleteDistributorModal").modal();
    }

    //   $(document).on('change', '.cselect', function() {
    //       var id = $(this).data("id");
    //       saveContentowner(id, this.value);
    //   });

    function saveContentowner(ids, status) {
        console.log(ids);
        if (!ids) {
            alert('Please select records..');
            return false;
        }
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/activate/generate/changeStatus.php",
            data: {
                id: ids.join(','),
                status: status,
                nd: '<?php echo $_GET["nd"];?>',
                reportMonthYear: '<?php echo $_GET["reportMonthYear"]?>',
                report: 'labelengine_activation_report'
            },
            success: function(response) {

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

                    } else {
                        $(".alert").css("display", "block");
                        $(".alert").
                        removeClass("alert-danger").
                        addClass("alert-success").
                        fadeIn().
                        find("span").
                        html(response["errMsg"]);
                        window.location.reload();
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


});
</script>
<script src="<?php echo $rootUrl; ?>assets/js/bootstrap.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>

</html>