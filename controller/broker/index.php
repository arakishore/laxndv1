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
require_once(__ROOT__.'/model/broker/brokerModel.php');

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
$logFilePath = $logStorePaths["brokers"];
$logFileName="addEditBroker.json";

$logMsg = "Add/edit broker controller process start.";
$logData['step1']["data"] = "1. {$logMsg}";

$logMsg = "Database connection successful. Lets validate inputs.";
$logData["step2"]["data"] = "2. {$logMsg}";

//need broker id field
$brokerId = "";
if (isset($_POST["brokerId"])) {
    $brokerId = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["brokerId"])));
}

//need broker name field
$brokerName = "";
if (isset($_POST["brokerName"])) {
    $brokerName = cleanQueryParameter($conn, cleanXSS($_POST["brokerName"]));
}

if (empty($brokerName)) {
    $logMsg = "Broker name field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." Broker name cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need broker status field
$brokerStatus = "";
if (isset($_POST["brokerStatus"])) {
    $brokerStatus = cleanQueryParameter($conn, cleanXSS($_POST["brokerStatus"]));
}

if (!is_numeric($brokerStatus)) {
    $logMsg = "Broker status field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." Broker status cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need broker bse reg num field
$bseRegNum = "";
if (isset($_POST["bseRegNum"])) {
    $bseRegNum = cleanQueryParameter($conn, cleanXSS($_POST["bseRegNum"]));
}

if (empty($bseRegNum)) {
    $logMsg = "bseRegNum field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." bseRegNum cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need broker nse reg num field
$nseRegNum = "";
if (isset($_POST["nseRegNum"])) {
    $nseRegNum = cleanQueryParameter($conn, cleanXSS($_POST["nseRegNum"]));
}

if (empty($nseRegNum)) {
    $logMsg = "nseRegNum field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." nseRegNum cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need broker code field
$brokerCode = "";
if (isset($_POST["brokerCode"])) {
    $brokerCode = cleanQueryParameter($conn, cleanXSS($_POST["brokerCode"]));
}

if (empty($brokerCode)) {
    $logMsg = "brokerCode field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." brokerCode cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need deliveryType field
$deliveryType = "";
if (isset($_POST["deliveryType"])) {
    $deliveryType = cleanQueryParameter($conn, cleanXSS($_POST["deliveryType"]));
}

if (empty($deliveryType)) {
    $logMsg = "deliveryType field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." deliveryType cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need gstNum field
$gstNum = "";
if (isset($_POST["gstNum"])) {
    $gstNum = cleanQueryParameter($conn, cleanXSS($_POST["gstNum"]));
}

if (empty($gstNum)) {
    $logMsg = "gstNum field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." gstNum cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need benfName field
$benfName = "";
if (isset($_POST["benfName"])) {
    $benfName = cleanQueryParameter($conn, cleanXSS($_POST["benfName"]));
}

if (empty($benfName)) {
    $logMsg = "benfName field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." benfName cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need ifscCode field
$ifscCode = "";
if (isset($_POST["ifscCode"])) {
    $ifscCode = cleanQueryParameter($conn, cleanXSS($_POST["ifscCode"]));
}

if (empty($ifscCode)) {
    $logMsg = "ifscCode field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." ifscCode cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need bankAccNum field
$bankAccNum = "";
if (isset($_POST["bankAccNum"])) {
    $bankAccNum = cleanQueryParameter($conn, cleanXSS($_POST["bankAccNum"]));
}

if (empty($bankAccNum)) {
    $logMsg = "bankAccNum field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." bankAccNum cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need bankBranch field
$bankBranch = "";
if (isset($_POST["bankBranch"])) {
    $bankBranch = cleanQueryParameter($conn, cleanXSS($_POST["bankBranch"]));
}

if (empty($bankBranch)) {
    $logMsg = "bankBranch field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." bankBranch cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

$logMsg = "All fields validated. Setting add/edit: {$brokerCode}";
$logData["step3"]["data"] = "3. {$logMsg}";

$editMode = true;
if (empty($brokerId)) {
    $editMode = false;
    $logMsg = "Request is to add a new broker: ".json_encode($_POST);;
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
} else {
    $logMsg = "Request is to edit an existing broker: ".json_encode($_POST);
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

$logMsg = "Transaction started. Attempting to save broker info";
$logData["step5"]["data"] = "5. {$logMsg}";

//get broker type and comments
$brokerType = $_POST["brokerType"];
foreach ($brokerType as $brokerTypeNum=>$brokerTypeName) {
    $brokerType[$brokerTypeNum] = cleanQueryParameter($conn, cleanXSS($brokerTypeName));
}
$brokerType = implode(",", $brokerType);
$comments = (isset($_POST["comments"]))?cleanQueryParameter($conn, cleanXSS($_POST["comments"])):"";

if ($editMode===true) {
    //update the broker info
    
    $arrToUpdate = array(
        'broker_name'=>$brokerName,
        'broker_type'=>$brokerType,
        'gst_no'=>$gstNum,
        'bse_reg_no'=>$bseRegNum,
        'nse_reg_no'=>$nseRegNum,
        'broker_code'=>$brokerCode,
        'delivery_type'=>$deliveryType,
        'beneficiary_name'=>$benfName,
        'ifsc_code'=>$ifscCode,
        'bank_account_no'=>$bankAccNum,
        'account_branch'=>$bankBranch,
        "comments" => $comments,
        'created_on'=>date("Y-m-d h:m:s"),
        'status'=>$brokerStatus,
        'updated_on'=>date("Y-m-d h:m:s")            
    );
    $brokerSearchArr = array("broker_id"=>$brokerId);
    $updatebrokerInfo = updatebrokerInfo($arrToUpdate, $brokerSearchArr, $conn);

    if (!noError($updatebrokerInfo)) {
        //error updating broker
        $logMsg = "broker Info could not be updated: {$updatebrokerInfo["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error updating broker info. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }

    //Everything completed successfully
    $logMsg = "broker info edited successfully.";
} else {
    //create mode
    //check if broker already exists with this broker code
    $brokerSearchArr = array("broker_code"=>$brokerCode);
    $fieldsStr = "broker_id";
    $brokerInfo = getbrokersInfo($brokerSearchArr, $fieldsStr, null, $conn);
    if (!noError($brokerInfo)) {
        //error fetching broker info
        $logMsg = "Error fetching broker info to check for duplicate: {$brokerInfo["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error finding broker info. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }

    if (!empty($brokerInfo["errMsg"])) {
        //broker with this broker code already exists
        $logMsg = "broker code already exists: {$brokerCode}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 9;
        $returnArr["errMsg"] = getErrMsg(9)." broker already exists with broker code {$brokerCode}.";
        echo(json_encode($returnArr));
        exit;
    }    

    //create a new broker    
    $arrToCreate = array( 
        $brokerId => array(
            'broker_name'=>"'".$brokerName."'",
            'broker_type'=>"'".$brokerType."'",
            'gst_no'=>"'".$gstNum."'",
            'bse_reg_no'=>"'".$bseRegNum."'",
            'nse_reg_no'=>"'".$nseRegNum."'",
            'broker_code'=>"'".$brokerCode."'",
            'delivery_type'=>"'".$deliveryType."'",
            'beneficiary_name'=>"'".$benfName."'",
            'ifsc_code'=>"'".$ifscCode."'",
            'bank_account_no'=>"'".$bankAccNum."'",
            'account_branch'=>"'".$bankBranch."'",
            "comments" =>"'". $comments."'",
            'created_on'=>"'".date("Y-m-d h:m:s")."'",
            'status'=>"'".$brokerStatus."'",
            'updated_on'=>"'".date("Y-m-d h:m:s")."'"
        )
    );
    
    $fieldsStr = array_keys($arrToCreate[$brokerId]);
    $fieldsStr = implode(",", $fieldsStr);

    $createbroker = createbroker($arrToCreate, $fieldsStr, $conn);
    if (!noError($createbroker)) {
        //error creating broker
        $logMsg = "broker could not be created: {$createbroker["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error creating broker. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }

    $logMsg = "broker created successfully.";
}

$logData["step6"]["data"] = "6. {$logMsg}";

$logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

$commit = commitTransaction($conn);

$returnArr["errCode"] = -1;
$returnArr["errMsg"] = getErrMsg(-1)." Broker Info saved successfuly.";
echo(json_encode($returnArr));
exit;
?>