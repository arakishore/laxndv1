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
$email = "";
if (isset($_POST["email"])) {
    $email = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["email"])));
}

if (empty($email)) {
    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4).": Email cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//initialize logs
$logsProcessor = new logsProcessor();
$initLogs = initializeJsonLogs($email);
$logFilePath = $logStorePaths["forgotPassword"];
$logFileName="forgotPassword.json";

$logMsg = "Forgot password process start.";
$logData['step1']["data"] = "1. {$logMsg}";

$logMsg = "Database connection successful.";
$logData["step2"]["data"] = "2. {$logMsg}";

$logMsg = "Attempting to start transaction";
$logData["step3"]["data"] = "3. {$logMsg}";

$startTransaction = startTransaction($conn);
if (!noError($startTransaction)) {
    //error starting transaction
    $logMsg = "Couldn't start transaction: ".$startTransaction["errMsg"];
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 3;
    $returnArr["errMsg"] = getErrMsg(3).": Couldn't start transaction";

    echo(json_encode($returnArr));
    exit;
}

//transaction started successfully
$logMsg = "Attempting to find user: {$email}";
$logData["step4"]["data"] = "4. {$logMsg}";

//get the user info
$userSearchArr = array('LOWER(email)'=>$email);
$fieldsStr = "email, status, firstname, lastname, user_id";
$userInfo = getUserInfo($userSearchArr, $fieldsStr, $conn);
if (!noError($userInfo)) {
    //error fetching user info
    $logMsg = "Couldn't fetch user info: {$userInfo["errMsg"]}";
    $logData["step4.1"]["data"] = "4.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $rollback = rollbackTransaction($conn);

    $returnArr["errCode"] = 5;
    $returnArr["errMsg"] = getErrMsg(5).": Couldn't get user details";
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

//user is found and is active, lets generate a token and send an email to him
$logMsg = "Email id exists and user is active. Generating a token and updating in DB";
$logData["step5"]["data"] = "5. {$logMsg}";

$resetPasswordLinkToken = generateToken(12);

$arrToUpdate = array('token'=>$resetPasswordLinkToken);
$updateUserInfo = updateUserInfo($arrToUpdate, $userSearchArr, $conn);
if (!noError($updateUserInfo)) {
    //error updating token
    $logMsg = "Token could not be updated: {$updateUserInfo["errMsg"]}";
    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $rollback = rollbackTransaction($conn);

    $returnArr["errCode"] = 5;
    $returnArr["errMsg"] = "Error creating user email with token.";
    echo(json_encode($returnArr));
    exit;
}

$logMsg = "Token Updated successfully. Time to send email.";
$logData["step6"]["data"] = "6. {$logMsg}";

$to = $email;
$subject = APPNAME.' - Reset password.';
$resetPasswordUrl = $rootUrl.'views/password/reset/?token='.$resetPasswordLinkToken;

$userName = $userInfo[$email]["firstname"]." ".$userInfo[$email]["lastname"];
$emailBody = createResetPasswordEmail($userName, $resetPasswordUrl, $rootUrl, $resetPasswordLinkToken);
$sendMail = sendMail($to, $subject, $emailBody);

if (!noError($sendMail)) {
    //error sending email
    $logMsg = "Error sending email: {$sendMail["errMsg"]}";
    $logData["step6.1"]["data"] = "6.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $rollback = rollbackTransaction($conn);

    $returnArr["errCode"] = 8;
    $returnArr["errMsg"] = getErrMsg(8)." Error sending email with reset password link.";
    echo(json_encode($returnArr));
    exit;
} 

//Everything completed successfully
$logMsg = "Email sent successfully";
$logData["step7"]["data"] = "7. {$logMsg}";
$logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

$commit = commitTransaction($conn);

$returnArr["errCode"] = -1;
$returnArr["errMsg"] = getErrMsg(-1).". An email has been sent to you with a link to reset your password.".
                                    " Please follow the instructions in that email to reset your password.";
echo(json_encode($returnArr));
exit;
?>
