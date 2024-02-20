<?php
//prepare for request
//include necessary helpers
require_once('../../../config/config.php');
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/auth.php');
require_once(__ROOT__.'/config/logs/logsProcessor.php');
require_once(__ROOT__.'/config/logs/logsCoreFunctions.php');
require_once(__ROOT__.'/libphp-phpmailer/autoload.php');

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
if (isset($_POST["username"])) {
    $username = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["username"])));
}

if (empty($username)) {
    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4).": Email cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//initialize logs
$logsProcessor = new logsProcessor();
$initLogs = initializeJsonLogs($username);
$logFilePath = $logStorePaths["login"];
$logFileName="login.json";

$logMsg = "Login process start.";
$logData['step1']["data"] = "1. {$logMsg}";

$logMsg = "Database connection successful. Lets validate inputs.";
$logData["step2"]["data"] = "2. {$logMsg}";

//need password field
$password = "";
if (isset($_POST["password"])) {
    $password = cleanQueryParameter($conn, cleanXSS($_POST["password"]));
}

if (empty($password)) {
    $logMsg = "Password field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4).": Password cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

$logMsg = "All fields validated. Attempting to find user: {$username}";
$logData["step3"]["data"] = "3. {$logMsg}";

//get the user info
$userSearchArr = array('LOWER(email)'=>$username);
$fieldsStr = "email, status, firstname, lastname, user_id, salt, password";
$userInfo = getUserInfo($userSearchArr, $fieldsStr, $conn);
if (!noError($userInfo)) {
    //error fetching user info
    $logMsg = "Couldn't fetch user info: {$userInfo["errMsg"]}";
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $rollback = rollbackTransaction($conn);

    $returnArr["errCode"] = 5;
    $returnArr["errMsg"] = getErrMsg(5).": Couldn't get user details.";
    echo(json_encode($returnArr));
    exit;
}

//check if user not found
$userInfo = $userInfo["errMsg"];
if (empty($userInfo)) {
    //user not found
    $logMsg = "User not found: {$username}";
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $rollback = rollbackTransaction($conn);
    
    $returnArr["errCode"] = 5;
    $returnArr["errMsg"] = "Invalid username/password.";
    echo(json_encode($returnArr));
    exit;
}

//check if user is active
if ($userInfo[$username]["status"]!=1) {
    //user not active
    $logMsg = "User not active: {$username}";
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $rollback = rollbackTransaction($conn);
    
    $returnArr["errCode"] = 5;
    $returnArr["errMsg"] = "Invalid username/password.";
    echo(json_encode($returnArr));
    exit;
}

//user is found and is active, lets validate the password
$logMsg = "Email id exists and user is active. Attempting to validate the password";
$logData["step4"]["data"] = "4. {$logMsg}";

$userSalt = $userInfo[$username]["salt"];
$encryptedPassword = encryptPassword($password, $userSalt);
if ($userInfo[$username]["password"] !== $encryptedPassword) {
    //invalid username/password
    $logMsg = "Invalid username/password: {$userInfo[$username]["password"]} !== {$encryptedPassword}";
    $logData["step4.1"]["data"] = "4.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 5;
    $returnArr["errMsg"] = "Invalid username/password.";
    echo(json_encode($returnArr));
    exit;
}

$logMsg = "Password is valid, Lets start a session.";
$logData["step5"]["data"] = "5. {$logMsg}";

session_start();
$_SESSION['user_id'] = $userInfo[$username]["user_id"];
$_SESSION['firstname'] = $userInfo[$username]["firstname"];
$_SESSION['lastname'] = $userInfo[$username]["lastname"];
$_SESSION['userEmail'] = $username;

//Everything completed successfully
$logMsg = "Login successfull";
$logData["step6"]["data"] = "6. {$logMsg}";
$logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

$commit = commitTransaction($conn);

$returnArr["errCode"] = -1;
$returnArr["errMsg"] = "Login successful.";
$returnArr["url"] = $rootUrl."views/dashboard/";
echo(json_encode($returnArr));
exit;
?>
