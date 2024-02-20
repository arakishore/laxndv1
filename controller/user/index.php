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
$logFilePath = $logStorePaths["accessControl"];
$logFileName="user.json";

$logMsg = "Add/edit user process start.";
$logData['step1']["data"] = "1. {$logMsg}";

$logMsg = "Database connection successful. Lets validate inputs.";
$logData["step2"]["data"] = "2. {$logMsg}";

//need email field
$email = "";
if (isset($_POST["email"])) {
    $email = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["email"])));
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
    $returnArr["errMsg"] = getErrMsg(4)." First name cannot be empty";
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
    $returnArr["errMsg"] = getErrMsg(4)." Last name cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

$logMsg = "All fields validated. Validating userEmail field: {$username}";
$logData["step3"]["data"] = "3. {$logMsg}";

//check user email field
$userEmail = "";
if (isset($_POST["userEmail"])) {
    $userEmail = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["userEmail"])));
}

$editMode = true;
if (empty($userEmail)) {
    $editMode = false;
    $logMsg = "Request is to add a new user: ".json_encode($_POST);;
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
} else {
    $logMsg = "Request is to edit an existing user: ".json_encode($_POST);;
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

$logMsg = "Transaction started. Attempting to save user info";
$logData["step5"]["data"] = "5. {$logMsg}";

if ($editMode===true) {
    //update the user info
    $new_password = (isset($_POST["new_password"]))?cleanQueryParameter($conn, cleanXSS($_POST["new_password"])):"";
    $phone = (isset($_POST["phone"]))?cleanQueryParameter($conn, cleanXSS($_POST["phone"])):"";
    $status = (isset($_POST["status"]))?cleanQueryParameter($conn, cleanXSS($_POST["status"])):"";
    $department = (isset($_POST["department"]))?cleanQueryParameter($conn, cleanXSS($_POST["department"])):"";
    $designation = (isset($_POST["designation"]))?cleanQueryParameter($conn, cleanXSS($_POST["designation"])):"";
    $comments = (isset($_POST["comments"]))?cleanQueryParameter($conn, cleanXSS($_POST["comments"])):"";
    $rightsStr = "";
    $rightsStr = (isset($_POST["access"][0]))?cleanQueryParameter($conn, cleanXSS($_POST["access"][0])):"";
    if (isset($_POST["access"][1])) {
        if (!empty($rightsStr)) {
            $rightsStr .= ",";
        }
        $rightsStr .= cleanQueryParameter($conn, cleanXSS($_POST["access"][1]));
    }
    $groupsStr = "";
    if (isset($_POST["groups"]) && count($_POST["groups"])>0) {
        $groupsStr = implode(",", $_POST["groups"]);
        $groupsStr = cleanQueryParameter($conn, cleanXSS($groupsStr));
    }

    $arrToUpdate = array(
        'firstname'=>$firstName,
        'lastname'=>$lastName,
        'phone'=>$phone,
        'department'=>$department,
        'designation'=>$designation,
        'comments'=>$comments,
        '`groups`'=>$groupsStr,
        'rights'=>$rightsStr,
        'status'=> (int)$status,
        'updated_on'=>date("Y-m-d h:m:s")
    );
    $userSearchArr = array("LOWER(email)"=>$userEmail);
    $updateUserInfo = updateUserInfo($arrToUpdate, $userSearchArr, $conn);
    if (!noError($updateUserInfo)) {
        //error updating password
        $logMsg = "User Info could not be updated: {$updateUserInfo["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error updating user info. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }

    if($new_password!=""){
        $salt = generateSalt();
        $encryptedPassword = encryptPassword($new_password, $salt);

        $arrToUpdate = array(
            "salt" => $salt,
            "password" => $encryptedPassword
            
        );
        $fieldSearchArr = array("email"=>$email);
        $updateUserInfo = updateUserInfo($arrToUpdate, $fieldSearchArr, $conn);
        if (!noError($updateUserInfo)) {
            //there was some error updating new_password
            $logMsg = "Error updating new_password: {$new_password}";
            $logData["step4.1"]["data"] = "4.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            rollbackTransaction($conn);

            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5)."Error processing new_password.";
        }
    }
 
    //Everything completed successfully
    $logMsg = "User info saved successfully.";
    $logData["step6"]["data"] = "6. {$logMsg}";

    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $commit = commitTransaction($conn);

    $returnArr["errCode"] = -1;
    $returnArr["errMsg"] = getErrMsg(-1)." User Info save successful.";
    echo(json_encode($returnArr));
    exit;
} else {
    //create mode
    //check if user already exists
    $userSearchArr = array("LOWER(email)"=>$email);
    $fieldsStr = "email";
    $userInfo = getUserInfo($userSearchArr, $fieldsStr, $conn);
    if (!noError($userInfo)) {
        //error fetching user info
        $logMsg = "Error fetching user info to check for duplicate: {$userInfo["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error finding user info. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }

    if (!empty($userInfo["errMsg"])) {
        //user with this email already exists
        $logMsg = "User already exists: {$email}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 9;
        $returnArr["errMsg"] = getErrMsg(9)." User already exists with email {$email}.";
        echo(json_encode($returnArr));
        exit;
    }

    //create a new user
    $status = (isset($_POST["status"]))?cleanQueryParameter($conn, cleanXSS($_POST["status"])):"0";
    $phone = (isset($_POST["phone"]))?cleanQueryParameter($conn, cleanXSS($_POST["phone"])):"";
    $department = (isset($_POST["department"]))?cleanQueryParameter($conn, cleanXSS($_POST["department"])):"";
    $designation = (isset($_POST["designation"]))?cleanQueryParameter($conn, cleanXSS($_POST["designation"])):"";
    $comments = (isset($_POST["comments"]))?cleanQueryParameter($conn, cleanXSS($_POST["comments"])):"";
    $rightsStr = "";
    $rightsStr = (isset($_POST["access"][0]))?cleanQueryParameter($conn, cleanXSS($_POST["access"][0])):"";
    if (isset($_POST["access"][1])) {
        if (!empty($rightsStr)) {
            $rightsStr .= ",";
        }
        $rightsStr .= cleanQueryParameter($conn, cleanXSS($_POST["access"][1]));
    }
    $groupsStr = "";
    if (isset($_POST["groups"]) && count($_POST["groups"])>0) {
        $groupsStr = implode(",", $_POST["groups"]);
        $groupsStr = cleanQueryParameter($conn, cleanXSS($groupsStr));
    }

    $token = generateToken(12);

    $arrToCreate = array( 
        $email => array(
            'email'=>"'".$email."'",
            'firstname'=>"'".$firstName."'",
            'lastname'=>"'".$lastName."'",
            'phone'=>"'".$phone."'",
            'department'=>"'".$department."'",
            'designation'=>"'".$designation."'",
            'comments'=>"'".$comments."'",
            'groups'=>"'".$groupsStr."'",
            'rights'=>"'".$rightsStr."'",
            'created_on'=>"'".date("Y-m-d h:m:s")."'",
            'status'=>(int)$status,
            'updated_on'=>"'".date("Y-m-d h:m:s")."'",
            "token" => "'".$token."'"
        )
    );
    $fieldsStr = "email, firstname, lastname, phone, department, designation, comments, `groups`, rights, created_on, status, updated_on, token"; 
    $createUser = createUser($arrToCreate, $fieldsStr, $conn); 
    if (!noError($createUser)) {
        //error updating password
        $logMsg = "User could not be created: {$createUser["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error creating user. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }

    $logMsg = "User created successfully. Send email with details.";
    $activationUrl = $rootUrl.'views/user/activate/?token='.$token;
    $emailSubject = "Welcome To Buoyant Capital";
    $emailMessage = createUserActivationEmail($activationUrl);
    $sendEmail = sendMail($email, $emailSubject, $emailMessage);
    if (!noError($sendEmail)) {
        //error sending email - {$createUser["errMsg"]}
        $logMsg = "User created but mail failure: ";
        $logData["step6.1"]["data"] = "6.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error creating user. Could net send welcome email.";
        echo(json_encode($returnArr));
        exit;
    }
    $logMsg = "User info saved successfully and mail sent to user: ".$activationUrl;
    $logData["step6"]["data"] = "6. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $commit = commitTransaction($conn);

    $returnArr["errCode"] = -1;
    $returnArr["errMsg"] = getErrMsg(-1)." User Info save successful.";

    echo(json_encode($returnArr));
    exit;
}
?>