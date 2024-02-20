<?php
//prepare for request
session_start();

//include necessary helpers
require_once('../../config/config.php');
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/auth.php');
require_once(__ROOT__.'/config/logs/logsProcessor.php');
require_once(__ROOT__.'/config/logs/logsCoreFunctions.php');
require_once(__ROOT__.'/libphp-phpmailer/autoload.php');

//include necessary models
require_once(__ROOT__.'/model/user/userModel.php');
require_once(__ROOT__.'/model/client-transaction/clientTransactionModel.php');

//Connection With Database
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1).$conn["errMsg"];
    echo(json_encode($returnArr));
    exit;
}

$conn = $conn["errMsg"];

//accept, sanitize and validate inputs 
//need email first to create logs
$username = "";
if (isset($_SESSION["userEmail"])) {
    $username = cleanQueryParameter($conn, cleanXSS($_SESSION["userEmail"]));
}

if (empty($username)) {
    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4).": There seems to be noone logged in or the session has timed out. Please login again.";
    echo(json_encode($returnArr));
    exit;
}

//initialize logs
$logsProcessor = new logsProcessor();
$initLogs = initializeJsonLogs($username);
$logFilePath = $logStorePaths["distributors"];
$logFileName="addEditDistributor.json";

$logMsg = "Add/edit Distributor controller process start.";
$logData['step1']["data"] = "1. {$logMsg}";

$logMsg = "Database connection successful. Lets validate inputs.";
$logData["step2"]["data"] = "2. {$logMsg}";

//need rate id field
$rateId = "";
if (isset($_POST["rateId"])) {
    $rateId = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["rateId"])));
}

//need client resident field
$oldMonthlyRate = "";
if (isset($_POST["oldMonthlyRate"])) {
    $oldMonthlyRate = cleanQueryParameter($conn, cleanXSS($_POST["oldMonthlyRate"]));
}

//get client type and type details
$clientTypeDetails = "";
$clientTypeDetails = (isset($_POST["clientTypeDetails"])) ? $_POST["clientTypeDetails"] : "";
foreach ($clientTypeDetails as $detailName => $detailValue) {
    $clientTypeDetails[$detailName] = cleanQueryParameter($conn, cleanXSS($detailValue));
}
$clientTypeDetails = json_encode($clientTypeDetails);

$date = ($_POST['date']);
$month = date('M-Y', strtotime($date));

$editMode = true;
if (empty($rateId)) {
    $editMode = false;
    $logMsg = "Request is to add a new distributor: ".json_encode($_POST);;
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
} else {
    $logMsg = "Request is to edit an existing distributor: ".json_encode($_POST);
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
}

$logMsg = "Attempting to start a transaction";
$logData["step4"]["data"] = "4. {$logMsg}";

$startTransaction = startTransaction($conn);
if (!noError($startTransaction)) {
    //error starting transaction
    $logMsg = "Couldn't start transaction: ".$startTransaction["errMsg"];
    $logData["step4.1"]["data"] = "4.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 3;
    $returnArr["errMsg"] = getErrMsg(3)." Couldn't start transaction: {$startTransaction["errMsg"]}";

    echo(json_encode($returnArr));
    exit;
}

$logMsg = "Transaction started. Attempting to save distributor info";
$logData["step5"]["data"] = "5. {$logMsg}";

if ($editMode===true) {
    //update the distributor info
    $arrToUpdate = array(
        'month_year'=>$month,
        'rates_json'=>$clientTypeDetails,
        'updated_by'=>$username,
        'updated_on'=>date("Y-m-d h:m:s")
    );

    $rateSearchArr = array("rate_id"=>$rateId);
    $updateMonthlyRateInfo = updateMonthlyRateInfo($arrToUpdate, $rateSearchArr, $conn);
    
    if (!noError($updateMonthlyRateInfo)) {
        //error updating distributor
        $logMsg = "Distributor Info could not be updated: {$updateMonthlyRateInfo["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error updating Rate info. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }

    //Everything completed successfully
    $logMsg = "Monthly Rate info edited successfully.";
} else {
    //create mode
    //check if month already 
    $rateSearchArr = array("month_year"=>$month);
    $fieldsStr = "month_year";
    $monthlyRateInfo = getMonthlyRateInfo($rateSearchArr, $fieldsStr, null, $conn);
    if (!noError($monthlyRateInfo)) {
        //error fetching distributor info
        $logMsg = "Error fetching distributor info to check for duplicate: {$monthlyRateInfo["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error finding Rate info. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }

    if (!empty($monthlyRateInfo["errMsg"])) {
        //broker with this gst num already exists
        $logMsg = "GST Num already exists: {$month}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 9;
        $returnArr["errMsg"] = getErrMsg(9)." Monthly Rate already exists .";
        echo(json_encode($returnArr));
        exit;
    }    

    //create a new rate    
    $arrToCreate = array( 
        $rateId => array(
            'rates_json'=>"'".$clientTypeDetails."'",
            'month_year'=>"'".$month."'",
            'updated_by'=>"'".$username."'",
            'created_on'=>"'".date("Y-m-d h:m:s")."'"
        )
    );
    $fieldsStr = array_keys($arrToCreate[$rateId]);
    $fieldsStr = implode(",", $fieldsStr);

    $createMonthlyRate = createMonthlyRate($arrToCreate, $fieldsStr, $conn);
    if (!noError($createMonthlyRate)) {
        //error creating distributor
        $logMsg = "Distributor could not be created: {$createMonthlyRate["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error creating Monthly Rate. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }

    $logMsg = "Monthly Rate created successfully.";
}

$logData["step6"]["data"] = "6. {$logMsg}";

$logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

$commit = commitTransaction($conn);

$returnArr["errCode"] = -1;
$returnArr["errMsg"] = getErrMsg(-1)." Monthly Rate Info saved successfuly.";
echo(json_encode($returnArr));
exit;
?>
