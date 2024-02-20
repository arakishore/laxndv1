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
require_once(__ROOT__.'/model/access-control/accessControlModel.php');

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
$logFileName="groups.json";

$logMsg = "Add/edit group process start.";
$logData['step1']["data"] = "1. {$logMsg}";

$logMsg = "Database connection successful. Lets validate inputs.";
$logData["step2"]["data"] = "2. {$logMsg}";

//need group name field
$groupName = "";
if (isset($_POST["groupName"])) {
    $groupName = cleanQueryParameter($conn, cleanXSS($_POST["groupName"]));
}

if (empty($groupName)) {
    $logMsg = "Group Name field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." Group Name cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

$logMsg = "All fields validated. Validating groupID field: {$groupName}";
$logData["step3"]["data"] = "3. {$logMsg}";

//check user email field
$groupID = "";
if (isset($_POST["groupID"])) {
    $groupID = cleanQueryParameter($conn, cleanXSS($_POST["groupID"]));
}

$editMode = true;
if (empty($groupID)) {
    $editMode = false;
    $logMsg = "Request is to add a new group: ".json_encode($_POST);;
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
} else {
    $logMsg = "Request is to edit an existing group: ".json_encode($_POST);;
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

$logMsg = "Transaction started. Attempting to save group info";
$logData["step5"]["data"] = "5. {$logMsg}";

if ($editMode===true) {
    //update the group info
    $rightsStr = "";
    $rightsStr = (isset($_POST["groupPerms"][0]))?cleanQueryParameter($conn, cleanXSS($_POST["groupPerms"][0])):"";
    if (isset($_POST["groupPerms"][1])) {
        if (!empty($rightsStr)) {
            $rightsStr .= ",";
        }
        $rightsStr .= cleanQueryParameter($conn, cleanXSS($_POST["groupPerms"][1]));
    }
    $modulesWithAccess = "";
    if (isset($_POST["modulesWithAccess"]) && count($_POST["modulesWithAccess"])>0) {
        $modulesWithAccess = implode(",", $_POST["modulesWithAccess"]);
        $modulesWithAccess = cleanQueryParameter($conn, cleanXSS($modulesWithAccess));
    }
    $submodulesWithAccess = "";
    if (isset($_POST["submodulesWithAccess"]) && count($_POST["submodulesWithAccess"])>0) {
        $submodulesWithAccess = implode(",", $_POST["submodulesWithAccess"]);
        $submodulesWithAccess = cleanQueryParameter($conn, cleanXSS($submodulesWithAccess));
    }

    $arrToUpdate = array(
        'group_name'=>"'".$groupName."'",
        'group_rights'=>"'".$rightsStr."'",
        'right_on_module'=>"'".$modulesWithAccess."'",
        'right_on_submodule'=>"'".$submodulesWithAccess."'",
        'updated_on'=>"'".date("Y-m-d h:m:s")."'"
    );
    $groupSearchArr = array("group_id"=>$groupID);
    $updateGroupInfo = updateGroupInfo($arrToUpdate, $groupSearchArr, $conn);
    if (!noError($updateGroupInfo)) {
        //error updating group
        $logMsg = "Group Info could not be updated: {$updateGroupInfo["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error updating group info. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }
} else {
    //create mode
    //check if group already exists
    $groupSearchArr = array("LOWER(group_name)"=>$groupName);
    $fieldsStr = "group_id";
    $groupInfo = getGroupInfo($groupSearchArr, $fieldsStr, $conn, 0, 1);
    if (!noError($groupInfo)) {
        //error fetching group info
        $logMsg = "Error fetching group info to check for duplicate: {$groupInfo["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error finding group info. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }

    if (!empty($groupInfo["errMsg"])) {
        //group with this name already exists
        $logMsg = "Group already exists: {$groupName}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 9;
        $returnArr["errMsg"] = getErrMsg(9)." Group already exists with same name {$groupName}.";
        echo(json_encode($returnArr));
        exit;
    }

    //create a new group
    $rightsStr = "";
    $rightsStr = (isset($_POST["groupPerms"][0]))?cleanQueryParameter($conn, cleanXSS($_POST["groupPerms"][0])):"";
    if (isset($_POST["groupPerms"][1])) {
        if (!empty($rightsStr)) {
            $rightsStr .= ",";
        }
        $rightsStr .= cleanQueryParameter($conn, cleanXSS($_POST["groupPerms"][1]));
    }
    $modulesWithAccess = "";
    if (isset($_POST["modulesWithAccess"]) && count($_POST["modulesWithAccess"])>0) {
        $modulesWithAccess = implode(",", $_POST["modulesWithAccess"]);
        $modulesWithAccess = cleanQueryParameter($conn, cleanXSS($modulesWithAccess));
    }
    $submodulesWithAccess = "";
    if (isset($_POST["submodulesWithAccess"]) && count($_POST["submodulesWithAccess"])>0) {
        $submodulesWithAccess = implode(",", $_POST["submodulesWithAccess"]);
        $submodulesWithAccess = cleanQueryParameter($conn, cleanXSS($submodulesWithAccess));
    }

    $arrToCreate = array(
        $groupName => array(
            'group_name'=>"'".$groupName."'",
            'group_rights'=>"'".$rightsStr."'",
            'right_on_module'=>"'".$modulesWithAccess."'",
            'right_on_submodule'=>"'".$submodulesWithAccess."'",
            'created_on'=>"'".date("Y-m-d h:m:s")."'",
            'updated_on'=>"'".date("Y-m-d h:m:s")."'"
        )
    );
    $fieldsStr = "group_name, group_rights, right_on_module, right_on_submodule, created_on, updated_on";
    $createGroup = createGroup($arrToCreate, $fieldsStr, $conn);
    if (!noError($createGroup)) {
        //error updating password
        $logMsg = "Group could not be created: {$createGroup["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error creating Group. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }
}

//Everything completed successfully
$logMsg = "Group info saved successfully.";
$logData["step6"]["data"] = "6. {$logMsg}";

$logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

$commit = commitTransaction($conn);

$returnArr["errCode"] = -1;
$returnArr["errMsg"] = getErrMsg(-1)." Group Info save successful.";
echo(json_encode($returnArr));
exit;
?>