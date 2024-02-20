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
$email = "";
if (isset($_SESSION["userEmail"])) {
    $email = cleanQueryParameter($conn, cleanXSS($_SESSION["userEmail"]));
}

if (empty($email)) {
    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." There seems to be noone logged in or the session has timed out. Please login again.";
    echo(json_encode($returnArr));
    exit;
}

//initialize logs
$logsProcessor = new logsProcessor();
$initLogs = initializeJsonLogs($email);
$logFilePath = $logStorePaths["profile"];
$logFileName="editProfile.json";

$logMsg = "Save profile process start.";
$logData['step1']["data"] = "1. {$logMsg}";

$logMsg = "Database connection successful. Lets validate inputs.";
$logData["step2"]["data"] = "2. {$logMsg}";

//need firstname field
$firstName = "";
if (isset($_POST["firstName"])) {
    $firstName = cleanQueryParameter($conn, cleanXSS($_POST["firstName"]));
}

if (empty($firstName)) {
    $logMsg = "First name field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4).": First name cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need lastname field
$lastName = "";
if (isset($_POST["lastName"])) {
    $lastName = cleanQueryParameter($conn, cleanXSS($_POST["lastName"]));
}

if (empty($lastName)) {
    $logMsg = "Last name field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4).": Last name cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need email field
$userEmail = "";
if (isset($_POST["email"])) {
    $userEmail = cleanQueryParameter($conn, cleanXSS($_POST["email"]));
}

if (empty($userEmail)) {
    $logMsg = "Email field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4).": Email cannot be empty";
    echo(json_encode($returnArr));
    exit;
}


$logMsg = "All fields validated. Attempting to start transaction: {$email}";
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

$logMsg = "Transaction started. Attempting to upload file: {$email}";
$logData["step4"]["data"] = "4. {$logMsg}";

//update the user info
$arrToUpdate = array(
    'firstname'=>$firstName,
    'lastname'=>$lastName,
    'email'=>$userEmail,
    'phone'=>cleanQueryParameter($conn, cleanXSS($_POST["phone"])),
    'department'=>cleanQueryParameter($conn, cleanXSS($_POST["department"])),
    'designation'=>cleanQueryParameter($conn, cleanXSS($_POST["designation"])),
    'comments'=>cleanQueryParameter($conn, cleanXSS($_POST["comments"])),
    'updated_on'=>date("Y-m-d h:m:s")
);
//now try to upload the file
if ($_FILES["profilePic"]["error"]==0) {
    //some file has been uploaded and without any error
    $uploadFileName = $email."_".$_FILES["profilePic"]["name"];
    $uploadedFileType = $_FILES["profilePic"]["type"];

    $acceptedUploadTypes = array("image/png", "image/jpeg", "image/jpg");
    if (!in_array($uploadedFileType, $acceptedUploadTypes)) {
        //invalid file upload type
        $logMsg = "File not image: ".json_encode($_FILES);
        $logData["step4.1"]["data"] = "4.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $returnArr["errCode"] = 7;
        $returnArr["errMsg"] = getErrMsg(7).": Pic upload must be PNG, JPG, JPEG.";
        echo(json_encode($returnArr));
        exit;
    }
    
    $uploadPath = __ROOT__."/assets/img/profile_images/".$uploadFileName;
    if (move_uploaded_file($_FILES["profilePic"]["tmp_name"], $uploadPath)) {
        //add it to the update array
        $arrToUpdate["image"] = $uploadFileName;

        $logMsg = "File uploaded {$uploadFileName}. Attempting to update user info: {$email}";
        $logData["step5"]["data"] = "5. {$logMsg}";
    } else {
        //there has been a file upload error
        $logMsg = "File upload error: ".json_encode($_FILES);
        $logData["step4.1"]["data"] = "4.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $returnArr["errCode"] = 7;
        $returnArr["errMsg"] = getErrMsg(7).": File upload error.";
        echo(json_encode($returnArr));
        exit;
    }
}
$userSearchArr = array("email"=>$email);
$updateUserInfo = updateUserInfo($arrToUpdate, $userSearchArr, $conn);
if (!noError($updateUserInfo)) {
    //error updating password
    $logMsg = "Profile could not be updated: {$updateUserInfo["errMsg"]}";
    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $rollback = rollbackTransaction($conn);

    $returnArr["errCode"] = 5;
    $returnArr["errMsg"] = getErrMsg(5)." Error updating profile. Please try again after some time.";
    echo(json_encode($returnArr));
    exit;
}

//Everything completed successfully
$logMsg = "Profile Updated successfully. Return response";
$logData["step6"]["data"] = "6. {$logMsg}";

$logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

$commit = commitTransaction($conn);

$returnArr["errCode"] = -1;
$returnArr["errMsg"] = getErrMsg(-1)." Profile update successful.";
echo(json_encode($returnArr));
exit;
?>