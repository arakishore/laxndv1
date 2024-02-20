<?php
//prepare for request
//include necessary helpers
require_once('../../../config/config.php');
require_once('../../../config/dbUtils.php');
require_once('../../../config/errorMap.php');
require_once('../../../config/auth.php');
require_once('../../../config/logs/logsProcessor.php');
require_once('../../../config/logs/logsCoreFunctions.php');
require_once('../../../libphp-phpmailer/autoload.php');

//include necessary models
require_once('../../../model/user/userModel.php');

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
//check if session is active
session_start();
if (!isset($_SESSION["users_id"]) && !isset($_SESSION["user_id"])) {
    //error getting logged in user
    $returnArr["errCode"] = 6;
    $returnArr["errMsg"] = getErrMsg(6);
    echo(json_encode($returnArr));
    exit;
}

//need email first to create logs
$email = "";
if (isset($_SESSION["userEmail"])) {
    $email = cleanQueryParameter($conn, cleanXSS(strtolower($_SESSION["userEmail"])));
}

if (empty($email)) {
    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." Email cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//initialize logs
$logsProcessor = new logsProcessor();
$initLogs = initializeJsonLogs($email);
$logFilePath = $logStorePaths["resetPassword"];
$logFileName="resetPassword.json";

$actionType = "Reset";
if (isset($_POST["type"]) && $_POST["type"]=="changePassword") {
    $actionType = "Change";
}

$logMsg = "{$actionType} password process start.";
$logData['step1']["data"] = "1. {$logMsg}";

$logMsg = "Database connection successful. Validating inputs now.";
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

//need confirm password field
$confirmPassword = "";
if (isset($_POST["confirmPassword"])) {
    $confirmPassword = cleanQueryParameter($conn, cleanXSS($_POST["confirmPassword"]));
}

if (empty($confirmPassword)) {
    $logMsg = "Confirm Password field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4).": Confirm Password cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

if ($actionType=="Change") {
    //need old password field
    $oldPassword = "";
    if (isset($_POST["oldPassword"])) {
        $oldPassword = cleanQueryParameter($conn, cleanXSS($_POST["oldPassword"]));
    }

    if (empty($oldPassword)) {
        $logMsg = "Old Password field empty: ".json_encode($_POST);;
        $logData["step2.1"]["data"] = "2.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $returnArr["errCode"] = 4;
        $returnArr["errMsg"] = getErrMsg(4).": Old Password cannot be empty";
        echo(json_encode($returnArr));
        exit;
    }
}

if ($password !== $confirmPassword) {
    $logMsg = "Confirm Password and Password fields do not match: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 7;
    $returnArr["errMsg"] = getErrMsg(7).": Confirm Password and Password fields do not match";
    echo(json_encode($returnArr));
    exit;
}

$logMsg = "All fields validations passed. Attempting to start transaction";
$logData["step3"]["data"] = "3. {$logMsg}";

$startTransaction = startTransaction($conn);
if (!noError($startTransaction)) {
    //error starting transaction
    $logMsg = "Couldn't start transaction: ".$startTransaction["errMsg"];
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 3;
    $returnArr["errMsg"] = getErrMsg(3).": Couldn't start transaction: {$startTransaction["errMsg"]}";

    echo(json_encode($returnArr));
    exit;
}

//transaction started successfully
$logMsg = "Attempting to find user: {$email}";
$logData["step4"]["data"] = "4. {$logMsg}";

//get the user info
$userSearchArr = array('LOWER(email)'=>$email);
$fieldsStr = "email, salt, password, status";
$userInfo = getUserInfo($userSearchArr, $fieldsStr, $conn);
if (!noError($userInfo)) {
    //error fetching user info
    $logMsg = "Couldn't fetch user info: {$userInfo["errMsg"]}";
    $logData["step4.1"]["data"] = "4.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $rollback = rollbackTransaction($conn);

    $returnArr["errCode"] = 5;
    $returnArr["errMsg"] = "Couldn't get user details";
    echo(json_encode($returnArr));
    exit;
}

//check if user not found
$userInfo = $userInfo["errMsg"];
if (empty($userInfo)) {
    //user not found
    $logMsg = "User not found: {$email}";
    $logData["step4.1"]["data"] = "4.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $rollback = rollbackTransaction($conn);
    
    $returnArr["errCode"] = 5;
    $returnArr["errMsg"] = "User not found.";
    echo(json_encode($returnArr));
    exit;
}

//check if user is active
if ($userInfo[$email]["status"]!=1) {
    //user not active
    $logMsg = "User not active: {$email}";
    $logData["step4.1"]["data"] = "4.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $rollback = rollbackTransaction($conn);
    
    $returnArr["errCode"] = 5;
    $returnArr["errMsg"] = "User not found.";
    echo(json_encode($returnArr));
    exit;
}

//user is found and is active, Need to encrypt and then update password in DB if old password is correct (in case it is a change password request)
$logMsg = "Email id exists and user is active. Need to check old password in case it is a change password request";
$logData["step5"]["data"] = "5. {$logMsg}";

$userSalt = $userInfo[$email]["salt"];
if($actionType=="Change") {
    $encryptedOldPassword = encryptPassword($oldPassword, $userSalt);
    $userOldPassword = $userInfo[$email]["password"];

    if($encryptedOldPassword!==$userOldPassword){
        //Incorrect old password
        $logMsg = "Incorrect old password: ".json_encode($_POST);
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);
        
        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = "Incorrect old password.";
        echo(json_encode($returnArr));
        exit;
    }
}

//correct old pwd, need to encrypt and then update new password in DB
$logMsg = "Valid old password. Attempting to update new password";
$logData["step6"]["data"] = "6. {$logMsg}";

$encryptedPassword = encryptPassword($password, $userSalt);
$arrToUpdate = array('password'=>$encryptedPassword);
$updateUserInfo = updateUserInfo($arrToUpdate, $userSearchArr, $conn);
if (!noError($updateUserInfo)) {
    //error updating password
    $logMsg = "Password could not be updated: {$updateUserInfo["errMsg"]}";
    $logData["step6.1"]["data"] = "6.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $rollback = rollbackTransaction($conn);

    $returnArr["errCode"] = 5;
    $returnArr["errMsg"] = "Error updating password. Please try again after some time.";
    echo(json_encode($returnArr));
    exit;
}

$logMsg = "Password Updated successfully. Time to send email.";
$logData["step7"]["data"] = "7. {$logMsg}";

$to = $email;
$subject = APPNAME." - Password {$actionType}.";

$userName = $_SESSION["firstname"]." ".$_SESSION["lastname"];
$ipAddress = ($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:"Unknown IP Address";
$emailBody = createPasswordChangedEmail($userName, $ipAddress, $rootUrl);
$sendMail = sendMail($to, $subject, $emailBody);

if (!noError($sendMail)) {
    //error sending email
    $logMsg = "Error sending email: {$sendMail["errMsg"]}";
    $logData["step7.1"]["data"] = "7.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $rollback = rollbackTransaction($conn);

    $returnArr["errCode"] = 8;
    $returnArr["errMsg"] = getErrMsg(8).": Error sending email with reset password link.";
    echo(json_encode($returnArr));
    exit;
} 

//Everything completed successfully
$logMsg = "Email sent successfully";
$logData["step8"]["data"] = "8. {$logMsg}";
$logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

$commit = commitTransaction($conn);
session_destroy();

$returnArr["errCode"] = -1;
$returnArr["errMsg"] = getErrMsg(-1).". Your password has been reset.".
                        " Please <a href='{$rootUrl}views/login/'>Login</a> again to your investor portal dashboard.";
echo(json_encode($returnArr));
exit;
?>