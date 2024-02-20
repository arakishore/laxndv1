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
    $fieldsStr = "email, status, image, rights, groups, firstname, lastname";
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
                        $allClientsCount = getClientsInfo($clientSearchArr, $fieldsStr, $dateField, $conn);
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

                // printArr()
                // printArr($user); exit;
                //user is found and is active. Now validate the request parameters
                // $reportDate = date("Y-m-d");
                // $misType = 1; //holding client
                // if(isset($_GET["portfolio_date"]) && !empty($_GET["portfolio_date"])){
                //     $latestMISDate = date("Y-m-d", strtotime($_GET["portfolio_date"]));
                // } else {
                //     $logMsg = "Fetching latest date of holding client";
                //     $logData["step4"]["data"] = "4. {$logMsg}";
                    
                //     $latestMISDate = getLatestMISDate($conn, $misType);
                //     if (!noError($latestMISDate)) {
                //         //error fetching latest MIS date
                //         $logMsg = "Error Fetching latest date of holding client: ".$latestMISDate["errMsg"];
                //         $logData["step4.1"]["data"] = "4.1. {$logMsg}";
                //         $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                //         $returnArr["errCode"] = 5;
                //         $returnArr["errMsg"] = getErrMsg(5)." Could not get latest MIS date.";
                //     } else {
                //         $logMsg = "Latest date of holding client fetched successfully. Fetching client info for {$email}";
                //         $logData["step5"]["data"] = "5. {$logMsg}";
                        
                //         //fetch holding client for this date
                //         $latestMISDate = $latestMISDate["errMsg"]["latestDate"];
                //     } //close error of getting latest mis date
                // } //close checking $_GET for portfolio date

                // need to get the client info in order to get client code
                // $clientSearchArr = array('email'=>$user);
                // $fieldsStr = "client_code, email";
                // $clientInfo = getClientsInfo($clientSearchArr, $fieldsStr, null, $conn);
                // if (!noError($clientInfo)) {
                //     //error fetching latest client info
                //     $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                //     $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                //     $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                //     $returnArr["errCode"] = 5;
                //     $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                // } else {
                //     $clientInfo = $clientInfo["errMsg"];
                //     $logMsg = "Client data fetched successfully. Fetch holding client info for {$latestMISDate}";
                //     $logData["step6"]["data"] = "6. {$logMsg}";

                //     $fieldsStr = "*";
                //     $clientCode = (isset($clientInfo[$user]))?$clientInfo[$user]["client_code"]:"";
                //     if (empty($clientCode)) {
                //         //error fetching latest Holding Client Data
                //         $logMsg = "Empty client code: ".json_encode($clientInfo);
                //         $logData["step6.1"]["data"] = "6.1. {$logMsg}";
                //         $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                //         $returnArr["errCode"] = 5;
                //         $returnArr["errMsg"] = getErrMsg(5)." Invalid client code.";
                //     } else {
                //         $holdingClientData = getMISData($conn, $latestMISDate, $misType, $fieldsStr, $clientCode);
                //         if (!noError($holdingClientData)) {
                //             //error fetching latest Holding Client Data
                //             $logMsg = "Error Fetching Holding Client data: ".$holdingClientData["errMsg"];
                //             $logData["step6.1"]["data"] = "6.1. {$logMsg}";
                //             $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                //             $returnArr["errCode"] = 5;
                //             $returnArr["errMsg"] = getErrMsg(5)." Could not get Holding Client Data for {$latestMISDate}.";
                //         } else {
                //             $logMsg = "Holding Client data fetched successfully.";
                //             $logData["step7"]["data"] = "7. {$logMsg}";
                            
                //             //now use this data down
                //             $holdingClientData = $holdingClientData["errMsg"];
                //             if (empty($holdingClientData)) {
                //                 $logMsg = "Holding Client data empty.";
                //                 $logData["step7.1"]["data"] = "7.1. {$logMsg}";

                //                 $returnArr["errCode"] = 5;
                //                 $returnArr["errMsg"] = "No portfolio data for this date";
                //             } else {
                //                 $logMsg = "Holding global data not empty.";
                //                 $logData["step7.1"]["data"] = "7.1. {$logMsg}";

                //                 $returnArr["errCode"] = -1;
                //                 $header = json_decode($holdingClientData["header"], true);
                //                 if (isset($header[12]) || empty($header[12])) {
                //                     unset($header[12]);
                //                 }
                //                 if (isset($header["L"]) || empty($header["L"])) {
                //                     unset($header["L"]);
                //                 }
                //                 if (isset($header[8]) || empty($header[8])) {
                //                     unset($header[8]);
                //                 }
                //                 if (isset($header["H"]) || empty($header["H"])) {
                //                     unset($header["H"]);
                //                 }
                //                 $securities = json_decode($holdingClientData["securities"], true);
                //                 $subtotals = json_decode($holdingClientData["subtotals"], true);
                //                 if (isset($subtotals[12]) || empty($subtotals[12])) {
                //                     unset($subtotals[12]);
                //                 }
                //                 if (isset($subtotals["L"]) || empty($subtotals["L"])) {
                //                     unset($subtotals["L"]);
                //                 }
                //                 if (isset($subtotals[8]) || empty($subtotals[8])) {
                //                     unset($subtotals[8]);
                //                 }
                //                 if (isset($subtotals["H"]) || empty($subtotals["H"])) {
                //                     unset($subtotals["H"]);
                //                 }
                //                 $totals = json_decode($holdingClientData["totals"], true);
                //                 if (isset($totals[12]) || empty($totals[12])) {
                //                     unset($totals[12]);
                //                 }
                //                 if (isset($totals["L"]) || empty($totals["L"])) {
                //                     unset($totals["L"]);
                //                 }
                //                 if (isset($totals[8]) || empty($totals[8])) {
                //                     unset($totals[8]);
                //                 }
                //                 if (isset($totals["H"]) || empty($totals["H"])) {
                //                     unset($totals["H"]);
                //                 }
                //                 $cashReceivables = json_decode($holdingClientData["cash_receivables"], true);
                //                 if (isset($cashReceivables[12]) || empty($cashReceivables[12])) {
                //                     unset($cashReceivables[12]);
                //                 }
                //                 if (isset($cashReceivables["L"]) || empty($cashReceivables["L"])) {
                //                     unset($cashReceivables["L"]);
                //                 }
                //                 if (isset($cashReceivables[8]) || empty($cashReceivables[8])) {
                //                     unset($cashReceivables[8]);
                //                 }
                //                 if (isset($cashReceivables["H"]) || empty($cashReceivables["H"])) {
                //                     unset($cashReceivables["H"]);
                //                 }
                //                 $netAssets = json_decode($holdingClientData["net_assets"], true);
                //                 if (isset($netAssets[12]) || empty($netAssets[12])) {
                //                     unset($netAssets[12]);
                //                 }
                //                 if (isset($netAssets["L"]) || empty($netAssets["L"])) {
                //                     unset($netAssets["L"]);
                //                 }
                //                 if (isset($netAssets[8]) || empty($netAssets[8])) {
                //                     unset($netAssets[8]);
                //                 }
                //                 if (isset($netAssets["H"]) || empty($netAssets["H"])) {
                //                     unset($netAssets["H"]);
                //                 }

                //                 if( isset($_POST["export"]) && $_POST["export"]=="export") {
                //                     $logMsg = "Request is to export client dashboard data to excel.";
                //                     $logData["step6"]["data"] = "6. {$logMsg}";
                                    
                //                     $spreadsheet = new Spreadsheet(); 
                //                     $spreadsheet->setActiveSheetIndex(0);
                //                     $activeSheet = $spreadsheet->getActiveSheet();
                                    
                //                     //add header to spreadsheet
                //                     $header = array_values($header);
                //                     $activeSheet->fromArray([$header], NULL, 'A1');
                                    
                //                     //add each security to the spreadsheet
                //                     $security = array();
                //                     $startCell = 2; //starting from A2
                //                     foreach($securities as $key=>$securityDetails) {
                //                         if (isset($securityDetails[12]) || empty($securityDetails[12])) {
                //                             unset($securityDetails[12]);
                //                         }
                //                         if (isset($securityDetails["L"]) || empty($securityDetails["L"])) {
                //                             unset($securityDetails["L"]);
                //                         }
                //                         if (isset($securityDetails[8]) || empty($securityDetails[8])) {
                //                             unset($securityDetails[8]);
                //                         }
                //                         if (isset($securityDetails["H"]) || empty($securityDetails["H"])) {
                //                             unset($securityDetails["H"]);
                //                         }
                //                         $security = array_values($securityDetails);    
                //                         $activeSheet->fromArray([$security], NULL, 'A'.$startCell);
                //                         $startCell++;
                //                     }

                //                     //add the totals rows to the spreadsheet
                //                     $subTotalRow = $startCell++;
                //                     $subtotals = array_values($subtotals);
                //                     $activeSheet->fromArray([$subtotals], NULL, 'A'.$subTotalRow);
                //                     $totalsRow = $startCell++;
                //                     $totals = array_values($totals);
                //                     $activeSheet->fromArray([$totals], NULL, 'A'.$totalsRow);
                //                     $netAssetsRow = $startCell++;
                //                     $netAssets = array_values($netAssets);
                //                     $activeSheet->fromArray([$netAssets], NULL, 'A'.$netAssetsRow);
                                    
                //                     //auto width on each column
                //                     $highestColumn = $spreadsheet->getActiveSheet()->getHighestDataColumn();
                //                     foreach (range('A', $highestColumn) as $col) {
                //                         $spreadsheet->getActiveSheet()
                //                                 ->getColumnDimension($col)
                //                                 ->setAutoSize(true);
                //                     }

                //                     //style the header and totals rows
                //                     $styleArray = [
                //                         'font' => [
                //                             'bold' => true,
                //                             'color'=>array('argb' => 'FFC5392A'),
                //                         ],
                //                         'alignment' => [
                //                             'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                //                         ],
                //                         'borders' => [
                //                             'top' => [
                //                                 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                //                             ]
                //                         ]
                //                     ];                            
                //                     $spreadsheet->getActiveSheet()->getStyle('A1:'.$highestColumn.'1')->applyFromArray($styleArray);
                //                     $spreadsheet->getActiveSheet()->getStyle('A'.$subTotalRow.':'.$highestColumn.$subTotalRow)->applyFromArray($styleArray);
                //                     $spreadsheet->getActiveSheet()->getStyle('A'.$totalsRow.':'.$highestColumn.$totalsRow)->applyFromArray($styleArray);
                //                     $spreadsheet->getActiveSheet()->getStyle('A'.$netAssetsRow.':'.$highestColumn.$netAssetsRow)->applyFromArray($styleArray);

                //                     //download the file
                //                     $filename = "holding_client_".$latestMISDate;
                //                     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                //                     header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
                //                     header('Cache-Control: max-age=0');

                //                     $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                //                     $writer->save('php://output');

                //                     $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                //                     exit;
                //                 }
                //             }
                //             $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);                         
                //         }
                //     } //close else to check if client code is emoty                    
                // } //close error checking for client info

            } //close checking if user is active
        } //close checking if user not found
    } //close no error userinfo else
} //close check db conn
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="icon" type="image/png" href="<?php echo $rootUrl; ?>assets/img/nirvana_favicon.png"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title><?php echo APPNAME; ?></title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />
    <link href="<?php echo $rootUrl; ?>assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo $rootUrl; ?>assets/css/material-dashboard.css?v=1.2.0" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Work+Sans:400,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php 
            $pageTitle = "Adjustment";
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
                                            <a href="<?php echo $rootUrl."views/dashboard/client"; ?>"><i class="fa fa-dashboard">&nbsp;</i>Adjustment</a>
                                        </li>
                                    </ol>
                                </div>
                                <div class="card-content">
                                    <!-- success/error messages -->
                                    <?php

                                    print(" Reports of ".$user." will be displayed");

                                    ?>
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
                                    <div class="col-md-12">
                                        <form enctype="multipart/form-data" id="getPortfolioForm" name="getPortfolioForm"
                                            action="index.php" required method="GET">
                                            <div class="col-md-4 col-sm-12">

                                            <?php 
                $clientSearchArr = array('email'=>$email);
                $fieldsStr = "client_username, email";
                $clientInfo = getClientsInfo($clientSearchArr, $fieldsStr, null, $conn);
                if (!noError($clientInfo)) {
                    //error fetching latest client info
                    $logMsg = "Error Fetching client info: ".$clientInfo["errMsg"];
                    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Could not get client Info for {$email}.";
                } else {
                   $clientname = $clientInfo['errMsg'][$email]['client_username'];
                   $allfantable = getAvilableActivateReports('youtube_activation_finance', $clientname ,$conn);
                   
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
                                                <label class="control-label">Youtube finance report </label>

                                                <select name="finance" class="form-control" > 
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
                                    <div class="card-content">
                                    <table class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            
                            <th>Content owner</th>
                            <th>total amt recvd</th>
                            <th>shares</th>
                            <th>amt payable</th>
                            <th>Status</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        $selectedDate = $_GET["finance"]; 
                        $year     = date("Y", strtotime($selectedDate));
                        $month    = date("m", strtotime($selectedDate));
                        $clientSearchArr = array("1"=>1);
                        $fieldsStr = "*";
                        $export = true;
                        $offset = 0;
                        $resultsPerPage = 10;
 
$activatetableName = "youtube_activation_finance_report_" . $year . "_" . $month;
                       $allClientsInfo = getActivationReport(
                        $activatetableName,
                         $clientSearchArr,
                         $fieldsStr,
                         $dateField,
                         $conn,
                         $offset,
                         $resultsPerPage
                     );
                  
                        foreach($allClientsInfo['errMsg'] as $clientEmail=>$clientDetails){ 
                        ?>
                        <tr>
                           
                            <td><?php echo $clientDetails["content_owner"]; ?></td>
                            <td><?php echo $clientDetails["total_amt_recd"]; ?></td>
                            <td><?php echo $clientDetails["shares"]; ?></td>
                            <td><?php echo $clientDetails["amt_payable"]; ?></td>
                            <td><?php echo $clientDetails["status"]; ?></td>
                             
                            </td>
                            
                           ?>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                                    </div>    
                                    <?php
                                    if (noError($returnArr)) {
                                        //process the data to print the table
                                    ?>
                                        <!-- <table class="table table-bordered table-condensed" style="width: 150%;">
                                            <thead>
                                                <tr>
                                                <?php
                                                foreach ($header as $headerTitle) {
                                                ?>
                                                    <th><?php echo $headerTitle; ?></th>
                                                <?php
                                                }
                                                ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($securities as $securityDetails) {
                                                    if (!empty($securityDetails)) {
                                                        if (isset($securityDetails[12]) || empty($securityDetails[12])) {
                                                            unset($securityDetails[12]);
                                                        }
                                                        if (isset($securityDetails["L"]) || empty($securityDetails["L"])) {
                                                            unset($securityDetails["L"]);
                                                        }
                                                        if (isset($securityDetails[8]) || empty($securityDetails[8])) {
                                                            unset($securityDetails[8]);
                                                        }
                                                        if (isset($securityDetails["H"]) || empty($securityDetails["H"])) {
                                                            unset($securityDetails["H"]);
                                                        }
                                                ?>
                                                        <tr>
                                                            <?php
                                                            foreach($securityDetails as $colName=>$securityDetail) {
                                                                if (is_numeric($securityDetail)) {
                                                                    $securityDetail = moneyFormatIndia($securityDetail);
                                                                }
                                                            ?>
                                                                    <td><?php echo $securityDetail; ?></td>
                                                            <?php
                                                            }
                                                            ?>
                                                        </tr>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                <?php
                                                    foreach($subtotals as $colName=>$subTotalDetail) {
                                                        if (is_numeric($subTotalDetail)) {
                                                            $subTotalDetail = moneyFormatIndia($subTotalDetail);
                                                        }
                                                    ?>
                                                        <th><?php echo $subTotalDetail; ?></th>
                                                    <?php
                                                    }
                                                    ?>
                                                </tr>
                                                <tr>
                                                <?php
                                                    foreach($totals as $colName=>$totalDetail) {
                                                        if (is_numeric($totalDetail)) {
                                                            $totalDetail = moneyFormatIndia($totalDetail);
                                                        }
                                                    ?>
                                                        <th><?php echo $totalDetail; ?></th>
                                                    <?php
                                                    }
                                                    ?>
                                                </tr>
                                                <tr>
                                                <?php
                                                    foreach($cashReceivables as $colName=>$cashReceivableDetail) {
                                                        if (is_numeric($cashReceivableDetail)) {
                                                            $cashReceivableDetail = moneyFormatIndia($cashReceivableDetail);
                                                        }
                                                    ?>
                                                        <th><?php echo $cashReceivableDetail; ?></th>
                                                    <?php
                                                    }
                                                    ?>
                                                </tr>
                                                <tr>
                                                <?php
                                                    foreach($netAssets as $colName=>$netAssetDetail) {
                                                        if (is_numeric($netAssetDetail)) {
                                                            $netAssetDetail = moneyFormatIndia($netAssetDetail);
                                                        }
                                                    ?>
                                                        <th><?php echo $netAssetDetail; ?></th>
                                                    <?php
                                                    }
                                                    ?>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        export button -->
                                        <!-- <?php
                                        //if user has write access, show export button
                                        if ($userHighestPermOnPage == 2) {
                                        ?>
                                            <div class="col-md-12">
                                                <form class="" method="POST"
                                                action="index.php?portfolio_date=<?php //echo $latestMISDate; ?>" 
                                                >
                                                    <button type="submit" name="export" value="export" class="btn btn-danger btn-outline-info waves-effect">
                                                        <i class="fa fa-external-link"></i> Export
                                                    </button>
                                                </form>
                                            </div>
                                        <?php
                                        }
                                        ?> -->
                                            <!-- end export button -->                                        
                                    <?php
                                    }
                                    ?>
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
    $(window).on("load", function(e){
        //managing the floating labels behaviour
        $("form#getPortfolioForm :input").each(function () {
            var input = $(this).val();
            if ($.trim(input) != "") {
                $(this).parent().removeClass("is-empty");
            }
            $(this).on("focus", function(){
                $(this).parent().removeClass("is-empty");
            })
            $(this).on("blur", function(){
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