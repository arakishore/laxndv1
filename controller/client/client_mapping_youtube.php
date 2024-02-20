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

$logMsg = "  client co-maping controller process start.";
$logData['step1']["data"] = "1. {$logMsg}";

$logMsg = "Database connection successful. Lets validate inputs.";
$logData["step2"]["data"] = "2. {$logMsg}";

$clientSearchArr = array();
//need Channel code field
$Channel = "";
if (isset($_POST["Channel"])) {
    // $Channel = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["Channel"])));
    $Channel = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["Channel"])));
}
if (empty($Channel)) {
    $logMsg = "Client Code field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " Channel Code cannot be empty";
    echo (json_encode($returnArr));
    exit;
}
 
//need partner_provided code field
$partner_provided = "";
if (isset($_POST["partner_provided"])) {
    // $partner_provided = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["partner_provided"])));
    $partner_provided = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["partner_provided"])));
}
if (empty($partner_provided)) {
    $logMsg = "Client Code field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " partner_provided Code cannot be empty";
    echo (json_encode($returnArr));
    exit;
}   
//======================
//need ugc code field
$ugc = "";
if (isset($_POST["ugc"])) {
    // $ugc = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["ugc"])));
    $ugc = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["ugc"])));
}
if (empty($ugc)) {
    $logMsg = "Client Code field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " ugc Code cannot be empty";
    echo (json_encode($returnArr));
    exit;
}   

//need Label code field
$Label = "";
if (isset($_POST["Label"])) {
    // $Label = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["Label"])));
    $Label = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["Label"])));
}
if (empty($Label)) {
    $logMsg = "Client Code field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " Label Code cannot be empty";
    echo (json_encode($returnArr));
    exit;
}   
$Channel_id = "";
if (isset($_POST["Channel_id"])) {
    // $Channel_id = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["Channel_id"])));
    $Channel_id = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["Channel_id"])));
}
if (empty($Channel_id)) {
    $logMsg = "Client Code field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " Channel_id Code cannot be empty";
    echo (json_encode($returnArr));
    exit;
}   


//need assetChannelID code field
$assetChannelID = "";
if (isset($_POST["assetChannelID"])) {
    // $assetChannelID = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["assetChannelID"])));
    $assetChannelID = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["assetChannelID"])));
}
if (empty($assetChannelID)) {
    $logMsg = "Client Code field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " assetChannelID Code cannot be empty";
    echo (json_encode($returnArr));
    exit;
}   

//need Label2 code field
$Label2 = "";
if (isset($_POST["Label2"])) {
    // $Label2 = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["Label2"])));
    $Label2 = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["Label2"])));
}
if (empty($Label2)) {
    $logMsg = "Client Code field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " Label2 Code cannot be empty";
    echo (json_encode($returnArr));
    exit;
}   

$client_youtube_shares = "0";
if (isset($_POST["client_youtube_shares"])) {
    
    $client_youtube_shares = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["client_youtube_shares"])));
}

//need CMS code field
$CMS = "";
if (isset($_POST["CMS"])) {
    // $CMS = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["CMS"])));
    $CMS = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["CMS"])));
}
if (empty($CMS)) {
    $logMsg = "Client Code field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " CMS Code cannot be empty";
    echo (json_encode($returnArr));
    exit;
}   


//need client resident field
$oldid = "";
if (isset($_POST["oldid"])) {
    $oldid = cleanQueryParameter($conn, cleanXSS($_POST["oldid"]));
}

$editMode = true;
if (empty($oldid)) {
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


    $clientSearchArr = array("client_username" => $partner_provided);
    $fieldsStr = "client_username";
    $clientInfo = getClientsInfo($clientSearchArr, $fieldsStr, null, $conn);
    if (!noError($clientInfo)) {
        //error fetching client info
        $logMsg = "Error fetching client info to check for partner_provided exist or not: {$clientInfo["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5) . " Error finding client info. Please try again after some time.";
        echo (json_encode($returnArr));
        exit;
    }

    if (empty($clientInfo["errMsg"])) {
        //client with this client code already exists
        $logMsg = "partner_provided doesnot exist exists: {$partner_provided}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 9;
        $returnArr["errMsg"] = getErrMsg(9) . " partner_provided doesnot exist exists   {$partner_provided}.";
        echo (json_encode($returnArr));
        exit;
    }

    //update the client info
    $arrToUpdate = array(
        'partner_provided' => strtoupper($partner_provided),
        'Channel' => $Channel,
        'partner_provided' => $partner_provided,
        'ugc' => $ugc,
        'Channel_id' => $Channel_id,
        'Label' => $Label,
        'assetChannelID' => $assetChannelID,
        'Label2' => $Label2,
        'CMS' => $CMS,
        'client_youtube_shares' => $client_youtube_shares
         
    );
    // exit;

    
    $clientSearchArr =  array("id" => $oldid);
    $updateClientInfoMapping = updateClientInfoMappingYoutube($arrToUpdate, $clientSearchArr, $conn);

    if (!noError($updateClientInfoMapping)) {
        //client with this client code already exists
        $logMsg = "Error updating client info Mapping updateClientInfoMappingYoutube: {$partner_provided}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 9;
        $returnArr["errMsg"] = getErrMsg(9) . " Error updating client info Mapping.";
        echo (json_encode($returnArr));
        exit;
    }

    //Everything completed successfully
    $logMsg = "updateClientInfoMappingYoutube Client info saved successfully.";
    $logData["step6"]["data"] = "6. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
    $commit = commitTransaction($conn);
    $returnArr["errCode"] = -1;
    $returnArr["errMsg"] = getErrMsg(-1) . " update Client  Mapping Youtube saved successfuly.";
    echo (json_encode($returnArr));
    exit;
} else {
    //create mode
    //check if client already exists with this client code
     //check if client already exists with this client code
     $clientSearchArr = array("client_username" => $partner_provided);
     $fieldsStr = "client_username";
     $clientInfo = getClientsInfo($clientSearchArr, $fieldsStr, null, $conn);
     if (!noError($clientInfo)) {
         //error fetching client info
         $logMsg = "Error fetching client info to check for partner_provided exist or not: {$clientInfo["errMsg"]}";
         $logData["step5.1"]["data"] = "5.1. {$logMsg}";
         $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
         $rollback = rollbackTransaction($conn);
         $returnArr["errCode"] = 5;
         $returnArr["errMsg"] = getErrMsg(5) . " Error finding client info. Please try again after some time.";
         echo (json_encode($returnArr));
         exit;
     }
 
     if (empty($clientInfo["errMsg"])) {
         //client with this client code already exists
         $logMsg = "partner_provided doesnot exist exists: {$partner_provided}";
         $logData["step5.1"]["data"] = "5.1. {$logMsg}";
         $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
         $rollback = rollbackTransaction($conn);
         $returnArr["errCode"] = 9;
         $returnArr["errMsg"] =  " partner provided '{$partner_provided}' does not exist exists   .";
         echo (json_encode($returnArr));
         exit;
     }
//
    $clientSearchArr = array("Channel" => $Channel,"partner_provided" => $partner_provided,"ugc" => $ugc,"assetChannelID" => $assetChannelID,"CMS" => $CMS,"Label" => $Label,"Label2" => $Label2 );
    $fieldsStr = "assetChannelID";
    $clientInfo = getClientsInfoYoutube($clientSearchArr, $fieldsStr, null, $conn);
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
        $logMsg = "Client code already exists: {$partner_provided}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 9;
        $returnArr["errMsg"] = getErrMsg(9) . " Client already exists with partner_provided {$partner_provided}.";
        echo (json_encode($returnArr));
        exit;
    }

    

    //create a new client
    $arrToCreate = array(
        'userName' => array(
            'partner_provided' => "'" . strtoupper($partner_provided) . "'",
            'Channel' => "'" . $Channel . "'",
            'ugc' => "'" .  strtoupper($ugc) . "'",
            'Channel_id' => "'" . $Channel_id . "'",
            'Label' => "'" . strtoupper($Label) . "'",
            'Label2' => "'" . $Label2 . "'",
            'CMS' => "'" . $CMS . "'",
            'assetChannelID' => "'" . $assetChannelID . "'",
            'client_youtube_shares' => "'" . $client_youtube_shares . "'"
             
        ),
    );

    $fieldsStr = array_keys($arrToCreate['userName']);
    $fieldsStr = implode(",", $fieldsStr);
        //  @unlink("clientModel.txt");
       // file_put_contents("clientModel1.txt",$fieldsStr);
    $createClientMapping = createClientMappingYoutube($arrToCreate, $fieldsStr, $conn);
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
