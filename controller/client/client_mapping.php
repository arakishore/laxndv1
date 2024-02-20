<?php
//prepare for request
session_start();

//include necessary helpers
require_once '../../config/config.php';
require_once __ROOT__ . '/config/dbUtils.php';
require_once __ROOT__ . '/config/errorMap.php';
require_once __ROOT__ . '/config/auth.php';
require_once __ROOT__ . '/config/logs/logsProcessor.php';
require_once __ROOT__ . '/config/logs/logsCoreFunctions.php';
require_once __ROOT__ . '/libphp-phpmailer/autoload.php';

//include necessary models
require_once __ROOT__ . '/model/user/userModel.php';
require_once __ROOT__ . '/model/client/clientModel.php';

//Connection With Database
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1) . $conn["errMsg"];
    echo (json_encode($returnArr));
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
    $returnArr["errMsg"] = getErrMsg(4) . ": There seems to be noone logged in or the session has timed out. Please login again.";
    echo (json_encode($returnArr));
    exit;
}

//initialize logs
$logsProcessor = new logsProcessor();
$initLogs = initializeJsonLogs($username);
$logFilePath = $logStorePaths["clients"];
$logFileName = "addEditClient.json";

$logMsg = "Add/edit client controller process start.";
$logData['step1']["data"] = "1. {$logMsg}";

$logMsg = "Database connection successful. Lets validate inputs.";
$logData["step2"]["data"] = "2. {$logMsg}";

$clientSearchArr = array();
//need client code field
$userName = "";
if (isset($_POST["userName"])) {
    // $userName = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["userName"])));
    $userName = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["userName"])));
}
if (empty($userName)) {
    $logMsg = "Client Code field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " Client Code cannot be empty";
    echo (json_encode($returnArr));
    exit;
}

//need client name field
$title_name = "";
if (isset($_POST["title_name"])) {
    $title_name = cleanQueryParameter($conn, cleanXSS($_POST["title_name"]));
}
if (empty($title_name)) {
    $logMsg = "title name field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " Title name cannot be empty";
    echo (json_encode($returnArr));
    exit;
}

//need client lastname field
$session_id = "";
if (isset($_POST["session_id"])) {
    $session_id = cleanQueryParameter($conn, cleanXSS($_POST["session_id"]));
}
if (empty($session_id)) {
    $logMsg = " session_id field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " session_id cannot be empty";
    echo (json_encode($returnArr));
    exit;
}    

//need client resident field
$oldSession_id = "";
if (isset($_POST["oldSession_id"])) {
    $oldSession_id = cleanQueryParameter($conn, cleanXSS($_POST["oldSession_id"]));
}

$editMode = true;
if (empty($oldSession_id)) {
    $editMode = false;
    $logMsg = "Request is to add a new client mapping: " . json_encode($_POST);
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
} else {
    $logMsg = "Request is to edit an existing client mapping: " . json_encode($_POST);
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
}

$logMsg = "Attempting to start a transaction";
$logData["step4"]["data"] = "4. {$logMsg}";

$startTransaction = startTransaction($conn);
if (!noError($startTransaction)) {
    //error starting transaction
    $logMsg = "Couldn't start transaction: " . $startTransaction["errMsg"];
    $logData["step4.1"]["data"] = "4.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 3;
    $returnArr["errMsg"] = getErrMsg(3) . " Couldn't start transaction: {$startTransaction["errMsg"]}";

    echo (json_encode($returnArr));
    exit;
}

$logMsg = "Transaction started. Attempting to save client mapping info";
$logData["step5"]["data"] = "5. {$logMsg}";
  
 
if ($editMode === true) {

    //update the client info
    $arrToUpdate = array(
        'partner_provided' => strtoupper($userName),
        'title_name' => $title_name,
        'session_id' => $session_id,
        'created_on' => date("Y-m-d h:m:s"),
        'updated_on' => date("Y-m-d h:m:s")
    );
    // exit;

    $clientSearchArr = array("partner_provided" => $userName);
    $clientSearchArr =  array("session_id" => $oldSession_id);
    $updateClientInfoMapping = updateClientInfoMapping($arrToUpdate, $clientSearchArr, $conn);

    if (!noError($updateClientInfoMapping)) {
        //client with this client code already exists
        $logMsg = "Error updating client info Mapping : {$userName}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 9;
        $returnArr["errMsg"] = getErrMsg(9) . " Error updating client info Mapping.";
        echo (json_encode($returnArr));
        exit;
    }

    //Everything completed successfully
    $logMsg = "Client info saved successfully.";
    $logData["step6"]["data"] = "6. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
    $commit = commitTransaction($conn);
    $returnArr["errCode"] = -1;
    $returnArr["errMsg"] = getErrMsg(-1) . " Client Info Mapping saved successfuly.";
    echo (json_encode($returnArr));
    exit;
} else {
    //create mode
    //check if client already exists with this client code
    $clientSearchArr = array("partner_provided" => $userName,"session_id" => $session_id);
    $fieldsStr = "session_id";
    $clientInfo = getClientsInfoAmazon($clientSearchArr, $fieldsStr, null, $conn);
    if (!noError($clientInfo)) {
        //error fetching client info
        $logMsg = "Error fetching client info to check for duplicate: {$clientInfo["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5) . " Error finding client info. Please try again after some time.";
        echo (json_encode($returnArr));
        exit;
    }

    if (!empty($clientInfo["errMsg"])) {
        //client with this client code already exists
        $logMsg = "Client code already exists: {$session_id}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 9;
        $returnArr["errMsg"] = getErrMsg(9) . " Client already exists with session_id {$session_id}.";
        echo (json_encode($returnArr));
        exit;
    }

    //check if client already exists with this session_id
    $clientSearchArr = array("session_id" => $session_id);
    $fieldsStr = "session_id";
    $clientInfo = getClientsInfoAmazon($clientSearchArr, $fieldsStr, null, $conn);
    if (!noError($clientInfo)) {
        //error fetching client info
        $logMsg = "Error fetching client info to check for duplicate: {$clientInfo["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5) . " Error finding client info Mapping. Please try again after some time.";
        echo (json_encode($returnArr));
        exit;
    }

    if (!empty($clientInfo["errMsg"])) {
        //client with this client code already exists
        $logMsg = "Client session_id already exists: {$email}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 9;
        $returnArr["errMsg"] = getErrMsg(9) . " Client already exists with session_id: {$session_id}.";
        echo (json_encode($returnArr));
        exit;
    }

    //create a new client
    $arrToCreate = array(
        $userName => array(
            'partner_provided' => "'" . strtoupper($userName) . "'",
            'session_id' => "'" . $session_id . "'",
            'title_name' => "'" . $title_name . "'",
            'created_on' => "'" . date("Y-m-d h:m:s") . "'",
            'updated_on' => "'" . date("Y-m-d h:m:s") . "'"
        ),
    );

    $fieldsStr = array_keys($arrToCreate[$userName]);
    $fieldsStr = implode(",", $fieldsStr);
        //  @unlink("clientModel.txt");
       // file_put_contents("clientModel1.txt",$fieldsStr);
    $createClientMapping = createClientMapping($arrToCreate, $fieldsStr, $conn);
    if (!noError($createClientMapping)) {
        //error creating client
        $logMsg = "Client could not be created: {$createClientMapping["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5) . " Error creating client. Please try again after some time.";
        echo (json_encode($returnArr));
        exit;
    }

    $logMsg = "Client Mapping created successfully.";
    $logData["step6"]["data"] = "6. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
    $commit = commitTransaction($conn);
    $returnArr["errCode"] = -1;
    $returnArr["errMsg"] = getErrMsg(-1) . " Client Info Mapping added successful.";
    echo (json_encode($returnArr));
    exit;
}
