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
$logFilePath = $logStorePaths["accessControl"];
$logFileName="user.json";

$logMsg = "Delete user process start.";
$logData['step1']["data"] = "1. {$logMsg}";

$logMsg = "Database connection successful. Lets validate inputs.";
$logData["step2"]["data"] = "2. {$logMsg}";

//need email field
$email = "";
if (isset($_POST["userEmail"])) {
    $email = cleanQueryParameter($conn, cleanXSS(urldecode($_POST["userEmail"])));
}

if (empty($email)) {
    $logMsg = "Email field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." Email cannot be empty";
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

$logMsg = "Transaction started. Attempting to delete user";
$logData["step4"]["data"] = "4. {$logMsg}";

//delete the user
$userSearchArr = array( 
    'email'=>"'".$email."'"
);
$deleteUser = deleteUserInfo($userSearchArr, $conn);
if (!noError($deleteUser)) {
    //error deleting user
    $logMsg = "User could not be deleted: {$deleteUser["errMsg"]}";
    $logData["step4.1"]["data"] = "4.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $rollback = rollbackTransaction($conn);

    $returnArr["errCode"] = 5;
    $returnArr["errMsg"] = "Error deleting user. Please try again after some time.";
    echo(json_encode($returnArr));
    exit;
}

//Everything completed successfully
$logMsg = "User deleted successfully. Return response";
$logData["step6"]["data"] = "6. {$logMsg}";

$logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

$commit = commitTransaction($conn);

$returnArr["errCode"] = -1;
$returnArr["errMsg"] = getErrMsg(-1)." User deletion successful.";
echo(json_encode($returnArr));
exit;
?>