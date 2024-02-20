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
            
                $Keyword = (isset($_GET['Keyword'])) ? $_GET['Keyword'] :'';
                if($Keyword!=""){
                    $clientSearchArr = array("email"=>$Keyword);  
                } else {
                   
                    $clientSearchArr = array("1"=>1);  
                }

                 if (isset($_GET["userName"]) && !empty($_GET["userName"])) {
                     $clientSearchArr["client_username"] = cleanQueryParameter($conn, cleanXSS($_GET["userName"]));
                 }
                if (isset($_GET["source"]) && !empty($_GET["source"])) {
                    $clientSearchArr["source"] = cleanQueryParameter($conn, cleanXSS($_GET["source"]));
                }
                
                if (isset($_GET["status"]) && is_numeric($_GET["status"])) {
                    $clientSearchArr["status"] = cleanQueryParameter($conn, cleanXSS($_GET["status"]));
                }
                $dateField = null;
                if (isset($_GET["inception_start_date"]) && !empty($_GET["inception_start_date"])) {
                    $inceptionFromDate = cleanQueryParameter($conn, cleanXSS($_GET["inception_start_date"]));
                    $inceptionFromDate = date("Y-m-d", strtotime($inceptionFromDate));
                    if ( !(isset($_GET["inception_end_date"])) || empty($_GET["inception_end_date"]) ) {
                        //need to set default inception to date
                        $inceptionToDate = date("Y-m-d");
                    } else {
                        $inceptionToDate = cleanQueryParameter($conn, cleanXSS($_GET["inception_end_date"]));
                        $inceptionToDate = date("Y-m-d", strtotime($inceptionToDate));
                    }
                    $dateField = array(
                        "fromDate" => $inceptionFromDate,
                        "toDate" => $inceptionToDate
                    );   
                }  
                if (isset($_GET["inception_end_date"]) && !empty($_GET["inception_end_date"])) {
                    $inceptionToDate = cleanQueryParameter($conn, cleanXSS($_GET["inception_end_date"]));
                    $inceptionToDate = date("Y-m-d", strtotime($inceptionToDate));
                    if ( !(isset($_GET["inception_start_date"])) || empty($_GET["inception_start_date"]) ) {
                        //need to et default inception from date
                        $inceptionFromDate = date("Y-m-d");
                    } else {
                        $inceptionFromDate = cleanQueryParameter($conn, cleanXSS($_GET["inception_start_date"]));
                        $inceptionFromDate = date("Y-m-d", strtotime($inceptionFromDate));
                    }
                    $dateField = array(
                        "fromDate" => $inceptionFromDate,
                        "toDate" => $inceptionToDate
                    );
                }
                $fieldsStr = "COUNT(*) as noOfClients";
                $allClientsCount = getClientsInfo_org($clientSearchArr, $fieldsStr, $dateField, $conn);
            
                if (!noError($allClientsCount)) {
                    //error fetching all clients Count
                    $logMsg = "Couldn't fetch all clients Count: {$allClientsCount["errMsg"]}.".
                                "Search params: ".json_encode($clientSearchArr);
                    $logData["step4.1"]["data"] = "4.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                    
                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Error fetching client details.";
                } else {
                    $allClientsCount = $allClientsCount["errMsg"]["anonymous"]["noOfClients"]; //why anonymous? see function definition
                    // printArr($allClientsCount);
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
                    
                    $fieldsStr = "client_username, client_firstname, client_lastname, address, email, mobile_number, pan,source, status, client_type_details";
                    //set different getter arguments if it is in export mode
                    $export = false;
                    if (isset($_GET["export"])) {
                        $export = true;
                        $offset = 0;
                        $resultsPerPage = 9999;
                        $fieldsStr = "*";
                    }
                
                    $allClientsInfo = getClientsInfo(
                        $clientSearchArr,
                        $fieldsStr,
                        $dateField,
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
                                $client[14] = isset($clientStatusMap[$client[14]]) ? $clientStatusMap[$client[14]] : '';
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
            $pageTitle = "Manage Clients";
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
                                            <a href="<?php echo $rootUrl; ?>views/clients/index.php">
                                                <i class="fa fa-users">&nbsp;</i>Manage Clients
                                            </a>
                                        </li>
                                    </ol>
                                </div> <!-- end card header -->
                                <div class="card-content">
                                    <!-- success/error messages -->
                                    <?php
                                    $alertMsg = "";
                                    $alertClass = "alert-success";
                                    if (!noError($returnArr)) {
                                        $alertClass = "alert-danger";
                                        $alertMsg = $returnArr["errMsg"];
                                    ?>
                                        <div class="alert <?php echo $alertClass; ?>">
                                            <span>
                                                <?php echo $alertMsg; ?>
                                            </span>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <!-- end success/error messages -->

                                    <!-- Search row -->
                                    <div class="col-md-12">
                                        <form enctype="multipart/form-data" class="form-inline searchForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
                                            <!-- client search drop down -->
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

                                                 <select name="userName" id="userName" class="form-control"> 
                                                    <option value="">Select Client</option>
                                                    <?php
                                                    foreach ($allClients as $clientEmail => $clientDetails) {
                                                        $selected = "";
                                                        if (isset($clientSearchArr["client_username"]) && ($clientDetails['client_username']==$clientSearchArr["client_username"])) {
                                                            $selected = "selected='selected'";
                                                        }
                                                    ?>
                                                        <option <?php echo $selected; ?> value="<?php echo $clientDetails['client_username']; ?>">
                                                            <?php echo $clientDetails['client_username']."-".$clientDetails['client_firstname']; ?>
                                                        </option> 
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <!-- end client search drop down -->
                                            <!-- distributor search drop down -->
                                            <div class="form-group">
                                                <?php
                                                $distributorSearchArr = array("status"=>1);
                                                $fieldsStr = "distributor_id, distributor_name, email";
                                                $allDistributors = getDistributorsInfo($distributorSearchArr, $fieldsStr, null, $conn);
                                                if (!noError($allDistributors)) {
                                                    printArr("Error fetching all distributors");
                                                    exit;
                                                }
                                                $allDistributors = $allDistributors["errMsg"];
                                                ?>
                                                <select name="source" id="source" class="form-control">
                                                    <option value="">Select Source</option>
                                                    <option value="self" 
                                                    <?php
                                                    if (
                                                        isset($clientSearchArr["source"]) && 
                                                        strtolower(trim($clientSearchArr["source"]))=="self"
                                                    ) {
                                                        echo "selected='selected'";
                                                    }
                                                    ?>
                                                    >
                                                        Self
                                                    </option>
                                                    <?php
                                                    foreach ($allDistributors as $distributorId => $distributorDetails) {
                                                        $selected = "";
                                                        if (
                                                            isset($clientSearchArr["source"]) && 
                                                            ($distributorDetails['distributor_id']==$clientSearchArr["source"])
                                                        ) {
                                                            $selected = "selected='selected'";
                                                        }
                                                    ?>
                                                        <option <?php echo $selected; ?> value="<?php echo $distributorDetails['distributor_id']; ?>">
                                                            <?php echo $distributorDetails['distributor_name']; ?>
                                                        </option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <!-- Status -->
                                            <div class="form-group">
                                                <select name="status" id="status" class="form-control">
                                                    <option value="" >Select Status</option>
                                                <?php
                                                foreach ($clientStatusMap as $statusCode=>$statusName) {
                                                    $selected = "";
                                                    if (
                                                        isset($clientSearchArr["status"]) && 
                                                        ($statusCode==$clientSearchArr["status"])
                                                    ) {
                                                        $selected = "selected='selected'";
                                                    }
                                                ?>
                                                    <option <?php echo $selected; ?> value="<?php echo $statusCode; ?>">
                                                        <?php echo $statusName; ?>
                                                    </option>
                                                <?php
                                                }
                                                ?>
                                                </select>
                                            </div>
                                            <!-- end Status -->
                                            <!-- search button -->
                                            <div class="form-group">
                                                <button type="submit" 
                                                class="btn btn-success fa fa-search">
                                                </button>
                                            </div>
                                            <!-- end search button -->
                                            <!-- export button -->
                                            <?php
                                            //if user has write access, show export button
                                            if ($userHighestPermOnPage == 2) {
                                            ?>
                                                <div class="form-group">
                                                    <button type="submit" name="export" 
                                                    class="btn btn-warning fa fa-file-excel-o">
                                                    </button>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                            <!-- end export button -->
                                        </form>
                                    </div>
                                    <!-- Search row -->

                                    <!-- Add Clients button -->
                                    <?php
                                    //if user has write access, show add client button
                                    if ($userHighestPermOnPage == 2) {
                                    ?>
                                     <div class="col-md-8">
                                                <form class="form-inline" method="get">
                                                     
                                                    <div class="form-group mx-sm-3 mb-2">
                                                        <label for="Keyword" class="sr-only">Keyword</label>
                                                        <input type="text" class="form-control" id="Keyword" name="Keyword"
                                                            placeholder="Email">
                                                    </div>
                                                    <button type="submit" class="btn mb-2">Search</button>
                                                </form>
                                            </div>
                                        <div class="col-md-4">
                                            <a href="javascript:;" 
                                                class="ls-modal btn btn-xs btn-warning pull-right" onclick="showAddClientForm('', 'Add');"
                                            >
                                                <span class="fa fa-plus">Add Client</span>
                                            </a>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <!-- end Add Clients button -->
                                    <!-- Clients table -->
                                    <table class="table table-bordered table-condensed">
                                        <thead>
                                            <tr>
                                                <th>Client Username</th>
                                                <th>Client Name</th>
                                                <th>Contact Details</th>
                                                <th>Fee Structure</th>
                                                <th>Status</th>
                                                <?php
                                                //if user has write access, show Actions col
                                                if ($userHighestPermOnPage == 2) {
                                                ?>
                                                    <th>Actions</th>
                                                <?php
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach($allClientsInfo as $clientEmail=>$clientDetails){ 
                                            ?>
                                            <tr>
                                                <td><?php echo $clientDetails["client_username"]; ?></td>
                                                <td><?php echo $clientDetails["client_firstname"]; ?></td>
                                                <td><?php echo $clientDetails["address"]."<br>".$clientDetails["email"]."<br>".$clientDetails["mobile_number"]; ?></td>
                                                <td><?php $clientDataDetails=json_decode($clientDetails["client_type_details"]); 
                                                          $revenueShareYoutube = $clientDataDetails->revenueShareYoutube;
                                                          $revenueShareYoutubeRed = $clientDataDetails->revenueShareYoutubeRed;
                                                          $revenueShareYoutubeAudioRed = $clientDataDetails->revenueShareYoutubeAudioRed;
                                                          $revenueShareYoutubeAudio = $clientDataDetails->revenueShareYoutubeAudio;
                                                          $revenueItunes = $clientDataDetails->revenueItunes;
                                                          $revenueAppleMusic = $clientDataDetails->revenueAppleMusic;
                                                          $revenueAmazon = $clientDataDetails->revenueAmazon;
                                                          print("Revenue Share Youtube : "."<b>$revenueShareYoutube % </b>"."<br>"
                                                          ."Revenue Share Youtube Red : "."<b>$revenueShareYoutubeRed % </b>"."<br>"
                                                          ."Revenue Share Youtube Audio Red : "."<b>$revenueShareYoutubeAudioRed % </b>"."<br>"
                                                          ."Revenue Share Youtube Audio : "."<b>$revenueShareYoutubeAudio % </b>"."<br>"
                                                          ."Revenue Itunes : "."<b>$revenueItunes % </b>"."<br>"
                                                          ."Revenue Apple Music : "."<b>$revenueAppleMusic % </b>"."<br>"
                                                          ."Revenue Amazon : "."<b>$revenueAmazon % </b>");                                                     
                                                ?></td>
                                                <td><?php echo $clientStatusMap[$clientDetails["status"]]; ?></td> 

                                                <?php
                                                //if user has write access, show Actions col
                                                if ($userHighestPermOnPage == 2) {
                                                ?>
                                                    <td>
                                                        <a href="javascript:;"
                                                            class="ls-modal btn btn-xs btn-success" onclick="showAddClientForm('<?php echo htmlentities($clientDetails['client_username']); ?>', 'Edit');"
                                                        >
                                                            <span class="fa fa-edit"></span>
                                                        </a>
                                                        <a class="btn btn-xs btn-danger" onclick="confirmDeleteClient('<?php echo htmlentities($clientDetails['client_username']); ?>',  '<?php echo htmlentities($clientDetails['client_firstname']); ?>');"> 
                                                            <span class="fa fa-close"></span>
                                                        </a>
                                                        <?php
                                                        if (strpos($userInfo[$email]["groups"], "superadmin") === false) {
                                                           ?>
                                                       <a id="clientDashboard" class="hidden btn-xs btn ls-modal btn-success" >
                                                           <span class="fa fa-dashboard"></span>
                                                       </a>
                                                       <?php
                                                        }
                                                          else {
                                                              ?>
                                                       <a id="clientDashboard" class="btn-xs btn ls-modal btn-success" onclick="showClientDashboard('<?php echo $clientDetails['email']; ?>');" >
                                                           <span class="fa fa-dashboard"></span>
                                                       </a>
                                                       <?php
                                                       }
                                                       ?>
                                                    </td>
                                                <?php
                                                }
                                                ?>
                                            </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                    <!-- end Clients table -->
                                    <!-- pagination -->
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination">
                                            <?php
                                            
                                            if($page>1){
                                            ?>
                                                <li class="page-item"><a class="page-link" href="?page=1">&laquo;</a></li>
                                                <li class="page-item"><a class="page-link" href="?page=<?php echo ($page-1); ?>">Prev</a></li>
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
                                                <li class="page-item <?php echo $activeClass; ?>"><a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                                            <?php
                                            }
                                            ?>
                                            <?php
                                            if($page<$lastPage){	
                                            ?>
                                                <li class="page-item"><a class="page-link" href="?page=<?php echo ($page+1); ?>">Next</a></li>
                                                <li class="page-item"><a class="page-link" href="?page=<?php echo ($lastPage); ?>">&raquo;</a></li>
                                            <?php
                                            }
                                            ?>
                                        </ul>
                                    </nav>
                                    <!-- end pagination -->
                                </div> <!-- end card content -->
                            </div> <!-- end card -->
                        </div> <!-- end col md 12 -->
                    </div> <!-- end row -->
                </div> <!-- end container fluid -->
            </div> <!-- end content -->
        </div> <!-- end main panel -->
    </div> <!-- end wrapper -->
    <?php
    //if user has write access, keep add+delete modal and related scripts
    if ($userHighestPermOnPage == 2) {
    ?>
        <!-- delete Client modal -->
        <div class="modal fade" id="deleteClientModal">
            <!-- Modal content-->
            <div class="modal-content">        
                <div class="modal-header">
                    <h4 class="modal-title">Delete Client!</h4>
                </div>
                <div class="modal-body">
                    <div class="alert" style="display: none">
                        <span></span>
                    </div>
                    <p>
                        Are you sure you want to delete this Client?
                        <p id="clientCodeToDelete"></p>
                    </p>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-danger" id="deleteClientBtn" data-client-code=""
                            onclick="deleteClient(this);"
                        >Continue</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- end delete client modal -->
    
        <!-- add client modal -->
        <div class="modal fade modal-lg" id="addClientModal">
            <!-- Modal content-->
            <div class="modal-content">        
                <div class="modal-header">
                    <h4 class="modal-title"><span></span> Client</h4>
                </div>
                <div class="modal-body">
                    <div class="alert" style="display: none">
                        <span></span>
                    </div>
                    <div class="modal-body-content"></div>                
                </div>
            </div>
        </div>
        <!-- end add client modal -->
        <script>
            function confirmDeleteClient(userName,firstName) 
            {
                $("#deleteClientModal .modal-body #clientCodeToDelete").html(userName+"-"+firstName);
                $("#deleteClientModal .modal-footer #deleteClientBtn").data("user-name", userName);
                $("#deleteClientModal").modal();
            }

            function deleteClient(buttonElement)
            {
                let userName = $(buttonElement).data("user-name");
                //resetting the error message
                $("#deleteClientModal .alert").
                    removeClass("alert-success").
                    removeClass("alert-danger").
                    fadeOut().
                    find("span").html("");

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "<?php echo $rootUrl; ?>controller/client/delete/",
                    data: {"userName":encodeURIComponent(userName)},
                    success: function (client) {
                        if (client["errCode"]) {
                            if (client["errCode"] != "-1") { //there is some error
                                $("#deleteClientModal .alert").
                                    removeClass("alert-success").
                                    addClass("alert-danger").
                                    fadeIn().
                                    find("span").
                                    html(client["errMsg"]);                                
                            } else {
                                $("#deleteClientModal .alert").
                                    removeClass("alert-danger").
                                    addClass("alert-success").
                                    fadeIn().
                                    find("span").
                                    html(client["errMsg"]);
                                    setTimeout(function(){
                                        window.location.reload();
                                    }, 3000);
                            }
                        }
                    },
                    error: function () {
                        $("#deleteClientModal .alert").
                            removeClass("alert-success").
                            addClass("alert-danger").
                            fadeIn().
                            find("span").
                            html("500 internal server error");
                    }
                });
            }

            function showAddClientForm(userName, actionType)
            {
                $("#addClientModal .modal-title span").html(actionType);
                $("#addClientModal .modal-body-content").load(
                    "<?php echo $rootUrl; ?>views/clients/manage/?userName="+encodeURIComponent(userName)
                );
                $("#addClientModal").modal();
            }
            function showClientDashboard(userName)
           {
               location.href="<?php echo $rootUrl;?>views/dashboard/client/?userName="+encodeURIComponent(userName)
           }
        </script>    
    <?php
    }
    //include the loader
    require_once(__ROOT__."/views/common/loader.php");
    ?>
    <!--   Core JS Files   -->
    <script src="<?php echo $rootUrl; ?>assets/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/material.min.js" type="text/javascript"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/perfect-scrollbar.jquery.min.js"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/parsley.js"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/material-dashboard.js"></script>
</body>

</html>