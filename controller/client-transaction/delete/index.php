<?php
//prepare for request
session_start();

//include necessary helpers
require_once('../../../config/config.php');
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/logs/logsProcessor.php');
require_once(__ROOT__.'/config/logs/logsCoreFunctions.php');

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
    $returnArr["errMsg"] = getErrMsg(4)." There seems to be noone logged in or the session has timed out. Please login again.";
    echo(json_encode($returnArr));
    exit;
}

//initialize logs
$logsProcessor = new logsProcessor();
$initLogs = initializeJsonLogs($username);
$logFilePath = $logStorePaths["distributors"];
$logFileName="deleteDitributor.json";

$logMsg = "Delete distributor process start.";
$logData['step1']["data"] = "1. {$logMsg}";

$logMsg = "Database connection successful. Lets validate inputs.";
$logData["step2"]["data"] = "2. {$logMsg}";

//need distributor id field
$rateId = "";
if (isset($_POST["rateId"])) {
    $rateId = cleanQueryParameter($conn, cleanXSS(urldecode($_POST["rateId"])));
}

if (empty($rateId)) {
    $logMsg = "Rate ID field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." Rate ID cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

$logMsg = "All fields valid. Attempting to start a transaction";
$logData["step3"]["data"] = "3. {$logMsg}";

$startTransaction = startTransaction($conn);
if (!noError($startTransaction)) {
    //error starting transaction
    $logMsg = "Couldn't start transaction: ".$startTransaction["errMsg"];
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 3;
    $returnArr["errMsg"] = getErrMsg(3)." Couldn't start transaction: {$startTransaction["errMsg"]}";

    echo(json_encode($returnArr));
    exit;
}

$logMsg = "Transaction started. Attempting to delete distributor";
$logData["step4"]["data"] = "4. {$logMsg}";

//delete the distributor
$rateSearchArr = array(
    'rate_id'=>"'".$rateId."'"
);

$deleteRate = deleteMonthlyRate($rateSearchArr, $conn);
if (!noError($deleteRate)) {
    //error deleting Distributor
    $logMsg = "Distributor could not be deleted: {$deleteRate["errMsg"]}";
    $logData["step4.1"]["data"] = "4.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $rollback = rollbackTransaction($conn);

    $returnArr["errCode"] = 5;
    $returnArr["errMsg"] = "Error deleting Rate. Please try again after some time.";
    echo(json_encode($returnArr));
    exit;
}

//Everything completed successfully
$logMsg = "Rate deleted successfully. Return response";
$logData["step6"]["data"] = "6. {$logMsg}";

$logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

$commit = commitTransaction($conn);

$returnArr["errCode"] = -1;
$returnArr["errMsg"] = getErrMsg(-1)." Rate deletion successful.";
echo(json_encode($returnArr));
exit;
?>