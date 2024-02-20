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
require_once(__ROOT__.'/model/client/clientModel.php');

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


$paramlog['table_name'] = "crep_cms_clients";
$paramlog['file_name'] = '';
$paramlog['status_name'] = "Insert-update";
$paramlog['status_flag'] = "Start";
$paramlog['date_added'] = date("Y-m-d H:is:");
$paramlog['ip_address'] = get_client_ip();
$paramlog['login_user'] = $_SESSION["userEmail"];
$paramlog['log_file'] = $logStorePaths["clients"];
$paramlog['raw_data'] = json_encode($_POST);
activitylogs($paramlog, $conn);


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
$firstName = "";
if (isset($_POST["firstName"])) {
    $firstName = cleanQueryParameter($conn, cleanXSS($_POST["firstName"]));
}
if (empty($firstName)) {
    $logMsg = "Client name field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " Client name cannot be empty";
    echo (json_encode($returnArr));
    exit;
}

//need client lastname field
$lastName = "";
if (isset($_POST["lastName"])) {
    $lastName = cleanQueryParameter($conn, cleanXSS($_POST["lastName"]));
}
if (empty($lastName)) {
    $logMsg = "Client lastname field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " Client name cannot be empty";
    echo (json_encode($returnArr));
    exit;
}

//need client source field
$clientSource = "";
if (isset($_POST["clientSource"])) {
    $clientSource = cleanQueryParameter($conn, cleanXSS($_POST["clientSource"]));
}

if (empty($clientSource)) {
    $logMsg = "Client source field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." Client source cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need PAN Num field
$panNum = "";
if (isset($_POST["panNum"])) {
    $panNum = cleanQueryParameter($conn, cleanXSS($_POST["panNum"]));
}
if (empty($panNum)) {
    $logMsg = "PAN Num field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " PAN Num cannot be empty";
    echo (json_encode($returnArr));
    exit;
}

//need email field
$email = "";
if (isset($_POST["email"])) {
    $email = cleanQueryParameter($conn, cleanXSS($_POST["email"]));
}
if (empty($email)) {
    $logMsg = "Client email field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " Client email cannot be empty";
    echo (json_encode($returnArr));
    exit;
}

//need client status field
$clientStatus = "";
if (isset($_POST["status"])) {
    $clientStatus = cleanQueryParameter($conn, cleanXSS($_POST["status"]));
}
if (!is_numeric($clientStatus)) {
    $logMsg = "Client status field empty: " . json_encode($_POST);
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4) . " Client status cannot be empty";
    echo (json_encode($returnArr));
    exit;
}

//need client resident field
$oldUsername = "";
if (isset($_POST["oldUsername"])) {
    $oldUsername = cleanQueryParameter($conn, cleanXSS($_POST["oldUsername"]));
}

$oldEmail = "";
if (isset($_POST["oldEmail"])) {
    $oldEmail = cleanQueryParameter($conn, cleanXSS($_POST["oldEmail"]));
}

$editMode = true;
if (empty($oldUsername)) {
    $editMode = false;
    $logMsg = "Request is to add a new client: " . json_encode($_POST);
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
} else {
    $logMsg = "Request is to edit an existing client: " . json_encode($_POST);
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

$logMsg = "Transaction started. Attempting to save client info";
$logData["step5"]["data"] = "5. {$logMsg}";

//get mob num and address
$mobNum = "";
$clientAddress = "";
$mobNum = (isset($_POST["phone"])) ? cleanQueryParameter($conn, cleanXSS($_POST["phone"])) : "";
$clientAddress = (isset($_POST["clientAddress"])) ? cleanQueryParameter($conn, cleanXSS($_POST["clientAddress"])) : "";

//get alt emails
$altEmail1 = "";
$altEmail2 = "";
$altEmail3 = "";
$altEmail4 = "";
$altEmail1 = (isset($_POST["altEmail"][1])) ? cleanQueryParameter($conn, cleanXSS(strtolower($_POST["altEmail"][1]))) : "";
$altEmail2 = (isset($_POST["altEmail"][2])) ? cleanQueryParameter($conn, cleanXSS(strtolower($_POST["altEmail"][2]))) : "";
$altEmail3 = (isset($_POST["altEmail"][3])) ? cleanQueryParameter($conn, cleanXSS(strtolower($_POST["altEmail"][3]))) : "";
$altEmail4 = (isset($_POST["altEmail"][4])) ? cleanQueryParameter($conn, cleanXSS(strtolower($_POST["altEmail"][4]))) : "";

//get inception date and format it
//get gst num and comments
$gstNum = "";
$gst_per = "";
$comment = "";
$gstNum = (isset($_POST["gstNum"])) ? cleanQueryParameter($conn, cleanXSS($_POST["gstNum"])) : "";
$comment = (isset($_POST["comments"])) ? cleanQueryParameter($conn, cleanXSS($_POST["comments"])) : "";
$gst_per = (isset($_POST["gst_per"])) ? cleanQueryParameter($conn, cleanXSS($_POST["gst_per"])) : "";
//get client type and type details
$clientTypeDetails = "";
$clientTypeDetails = (isset($_POST["clientTypeDetails"])) ? $_POST["clientTypeDetails"] : "";
foreach ($clientTypeDetails as $detailName => $detailValue) {
    $clientTypeDetails[$detailName] = cleanQueryParameter($conn, cleanXSS($detailValue));
}
$clientTypeDetails = json_encode($clientTypeDetails);

$client_youtube_shares = "";
$client_youtube_shares = (isset($_POST["client_youtube_shares"])) ? $_POST["client_youtube_shares"] : "";
foreach ($client_youtube_shares as $detailName => $detailValue) {
    $client_youtube_shares[$detailName] = cleanQueryParameter($conn, cleanXSS($detailValue));
}
$client_youtube_shares = json_encode($client_youtube_shares);

//get company type details
$companyTypeDetails = "";
$companyTypeDetails = (isset($_POST["companyTypeDetails"])) ? $_POST["companyTypeDetails"] : "";
foreach ($companyTypeDetails as $detailName => $detailValue) {
    $companyTypeDetails[$detailName] = cleanQueryParameter($conn, cleanXSS($detailValue));
}
$companyTypeDetails = json_encode($companyTypeDetails);
if ($editMode === true) {
    
    //update the client info
    $arrToUpdate = array( 
        'client_username' => $userName,
        'client_firstname' => $firstName,
        'client_lastname' => $lastName,
        'pan' => $panNum,
        'gst_no' => $gstNum,
        'gst_per' => $gst_per,
        'address' => $clientAddress,
        'status' => $clientStatus,
        'mobile_number' => $mobNum,
        'source'=>$clientSource,
        'email' => $email,
        'email1' => $altEmail1,
        'email2' => $altEmail2,
        'email3' => $altEmail3,
        'email4' => $altEmail4,
        'client_type_details' => $clientTypeDetails,
        'client_youtube_shares' => $client_youtube_shares,
        'company_details' => $companyTypeDetails,
        'comments' => $comment,
        'created_on'=>date("Y-m-d h:m:s"),
        'updated_on'=>date("Y-m-d h:m:s")  
    );
    // exit;
    if (strtolower($userName) != strtolower($oldUsername)) {
        //check if client already exists with this client code
        $clientSearchArr = array("client_username" => $userName);
        $fieldsStr = "email";
        $clientInfo = getClientsInfo($clientSearchArr, $fieldsStr, null, $conn);
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
            $logMsg = "Client code already exists: {$userName}";
            $logData["step5.1"]["data"] = "5.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
            $rollback = rollbackTransaction($conn);
            $returnArr["errCode"] = 9;
            $returnArr["errMsg"] = getErrMsg(9) . " Client already exists with username {$userName}.";
            echo (json_encode($returnArr));
            exit;
        }
    }
    
   /*  if ($oldEmail != $email) {
        //check if client already exists with this email
        $clientSearchArr = array("LOWER(email)" => $email);
        $fieldsStr = "email";
        $clientInfo = getClientsInfo($clientSearchArr, $fieldsStr, null, $conn);
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
            $logMsg = "Client email already exists: {$email}";
            $logData["step5.1"]["data"] = "5.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
            $rollback = rollbackTransaction($conn);
            $returnArr["errCode"] = 9;
            $returnArr["errMsg"] = getErrMsg(9) . " Client already exists with email: {$email}.";
            echo (json_encode($returnArr));
            exit;
        }
    } */
    
//    $clientSearchArr = array("client_username" => $oldUsername);
    $clientSearchArr = array("LOWER(client_username)" => strtolower($oldUsername));
    $updateClientInfo = updateClientInfo($arrToUpdate, $clientSearchArr, $conn);
	@unlink("polo_user_update.txt");
	file_put_contents("polo_user_update.txt", json_encode($arrToUpdate));
	@chmod("polo_user_update.txt",0777);

    if (!noError($updateClientInfo)) {
        //client with this client code already exists
        $logMsg = "Error updating client info : {$userName}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 9;
        $returnArr["errMsg"] = getErrMsg(9) . " Error updating client info.";
        echo (json_encode($returnArr));
        exit;
    }
  
    //Everything completed successfully
    $logMsg = "Client info saved successfully.";
    $logData["step6"]["data"] = "6. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
    $commit = commitTransaction($conn);
    $returnArr["errCode"] = -1;
    $returnArr["errMsg"] = getErrMsg(-1) . " Client Info saved successfuly.";
    echo (json_encode($returnArr));
    exit;
} else {
    //create mode
    //check if client already exists with this client code
    $clientSearchArr = array("client_username" => $userName);
    $fieldsStr = "client_username";
    $clientInfo = getClientsInfo($clientSearchArr, $fieldsStr, null, $conn);
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
        $logMsg = "Client code already exists: {$userName}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 9;
        $returnArr["errMsg"] = getErrMsg(9) . " Client already exists with username {$userName}.";
        echo (json_encode($returnArr));
        exit;
    }

 /*    //check if client already exists with this email
    $clientSearchArr = array("LOWER(email)" => $email);
    $fieldsStr = "email";
    $clientInfo = getClientsInfo($clientSearchArr, $fieldsStr, null, $conn);
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
        $logMsg = "Client email already exists: {$email}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 9;
        $returnArr["errMsg"] = getErrMsg(9) . " Client already exists with email: {$email}.";
        echo (json_encode($returnArr));
        exit;
    } */


    //create a new client
    $arrToCreate = array(
        $userName => array(
            'client_username' => "'" . $userName . "'",
            'client_firstname' => "'" . $firstName . "'",
            'client_lastname' => "'" . $lastName . "'",
            'pan' => "'" . $panNum . "'",
            'gst_no' => "'" . $gstNum . "'",
            'gst_per' => $gst_per,
            'address' => "'" . $clientAddress . "'",
            'mobile_number' => "'" . $mobNum . "'",
            'source'=>"'".$clientSource."'",
            'email' => "'" . $email . "'",
            'email1' => "'" . $altEmail1 . "'",
            'email2' => "'" . $altEmail2 . "'",
            'email3' => "'" . $altEmail3 . "'",
            'email4' => "'" . $altEmail4 . "'",
            'client_type_details' => "'" . $clientTypeDetails . "'",
            'client_youtube_shares' => "'" . $client_youtube_shares . "'",
            'company_details' => "'" . $companyTypeDetails . "'",
            "comments" => "'" . $comment . "'",
            'created_on' => "'" . date("Y-m-d h:m:s") . "'",
            'status' => $clientStatus,
            'updated_on' => "'" . date("Y-m-d h:m:s") . "'",
        ),
    );

    $fieldsStr = array_keys($arrToCreate[$userName]);
    $fieldsStr = implode(",", $fieldsStr);

    $createClient = createClient($arrToCreate, $fieldsStr, $conn);
    if (!noError($createClient)) {
        //error creating client
        $logMsg = "Client could not be created: {$createClient["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5) . " Error creating client. Please try again after some time.";
        echo (json_encode($returnArr));
        exit;
    }

    $logMsg = "Client created successfully.";
    $logData["step6"]["data"] = "6. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
    $commit = commitTransaction($conn);
    $returnArr["errCode"] = -1;
    $returnArr["errMsg"] = getErrMsg(-1) . " Client Info save successful.";
    echo (json_encode($returnArr));
    exit;
}
