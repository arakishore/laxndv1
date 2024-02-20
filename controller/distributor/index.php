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
require_once(__ROOT__.'/model/distributor/distributorModel.php');

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
$logFilePath = $logStorePaths["distributors"];
$logFileName="addEditDistributor.json";

$logMsg = "Add/edit Distributor controller process start.";
$logData['step1']["data"] = "1. {$logMsg}";

$logMsg = "Database connection successful. Lets validate inputs.";
$logData["step2"]["data"] = "2. {$logMsg}";

//need Distributor id field
$distributorId = "";
if (isset($_POST["distributorId"])) {
    $distributorId = cleanQueryParameter($conn, cleanXSS(strtolower($_POST["distributorId"])));
}

//need distributor name field
$distributorName = "";
if (isset($_POST["distributorName"])) {
    $distributorName = cleanQueryParameter($conn, cleanXSS($_POST["distributorName"]));
}

if (empty($distributorName)) {
    $logMsg = "distributor name field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." Distributor name cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need distributor name field
$email = "";
if (isset($_POST["email"])) {
    $email = cleanQueryParameter($conn, cleanXSS($_POST["email"]));
}

if (empty($email)) {
    $logMsg = "distributor name field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." Email cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need distributor status field
$distributorStatus = "";
if (isset($_POST["distributorStatus"])) {
    $distributorStatus = cleanQueryParameter($conn, cleanXSS($_POST["distributorStatus"]));
}

if (!is_numeric($distributorStatus)) {
    $logMsg = "distributor status field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." Distributor status cannot be empty";
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
    $returnArr["errMsg"] = getErrMsg(4)." GST Num cannot be empty";
    echo(json_encode($returnArr));
    exit;
}

//need mgmt fee sharing field
$managementFeeSharing = "";
if (isset($_POST["managementFeeSharing"])) {
    $managementFeeSharing = cleanQueryParameter($conn, cleanXSS($_POST["managementFeeSharing"]));
}

if (!is_numeric($managementFeeSharing) || $managementFeeSharing<0 || $managementFeeSharing>100) {
    $logMsg = "managementFeeSharing field empty: ".json_encode($_POST);;
    $logData["step2.1"]["data"] = "2.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4)." Management Fee sharing % must be a number between 0 and 100";
    echo(json_encode($returnArr));
    exit;
}

//need client resident field
$oldGstNum = "";
if (isset($_POST["oldGstNum"])) {
    $oldGstNum = cleanQueryParameter($conn, cleanXSS($_POST["oldGstNum"]));
}

$oldEmail = "";
if (isset($_POST["oldEmail"])) {
    $oldEmail = cleanQueryParameter($conn, cleanXSS($_POST["oldEmail"]));
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

$logMsg = "All fields validated. Setting add/edit: {$distributorId}";
$logData["step3"]["data"] = "3. {$logMsg}";

$editMode = true;
if (empty($oldGstNum)) {
    $editMode = false;
    $logMsg = "Request is to add a new distributor: ".json_encode($_POST);;
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
} else {
    $logMsg = "Request is to edit an existing distributor: ".json_encode($_POST);
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

$logMsg = "Transaction started. Attempting to save distributor info";
$logData["step5"]["data"] = "5. {$logMsg}";

//get distributor details and comments
$noOfClients = "";
if (isset($_POST["noOfClients"])) {
    $noOfClients = cleanQueryParameter($conn, cleanXSS($_POST["noOfClients"]));
}

$pos = "";
$poc = $_POST["poc"];
foreach ($poc as $pocNum=>$pocDetails) {
    $poc[$pocNum]["address"] = cleanQueryParameter($conn, cleanXSS($pocDetails["address"]));
    $poc[$pocNum]["email"] = cleanQueryParameter($conn, cleanXSS($pocDetails["email"]));
    $poc[$pocNum]["mobile_num"] = cleanQueryParameter($conn, cleanXSS($pocDetails["mobile_num"]));
}
$pocDetails = json_encode($poc);

$comments ="";
$comments = (isset($_POST["comments"]))?cleanQueryParameter($conn, cleanXSS($_POST["comments"])):"";

if ($editMode===true) {
    //update the distributor info
    $arrToUpdate = array(
        'distributor_name'=>$distributorName,
        'email'=>$email,
        'no_of_client'=>$noOfClients,
        'gst_no'=>$gstNum,
        'beneficiary_name'=>$benfName,
        'ifsc_code'=>$ifscCode,
        'bank_account_no'=>$bankAccNum,
        'account_branch'=>$bankBranch,
        'management_fee_sharing'=>$managementFeeSharing,
        'poc_details'=>$pocDetails,
        "comments" => $comments,
        'status'=>$distributorStatus,
        'updated_on'=>date("Y-m-d h:m:s")
    );

        if ($gstNum != $oldGstNum) {
        //check if distributor already exists with this Gst number
        $distributorSearchArr = array("gst_no" => $gstNum);
        $fieldsStr = "email";
        $distributorInfo = getDistributorsInfo($distributorSearchArr, $fieldsStr, null, $conn);
        if (!noError($distributorInfo)) {
            //error fetching distributor info
            $logMsg = "Error fetching distributor info to check for duplicate: {$distributorInfo["errMsg"]}";
            $logData["step5.1"]["data"] = "5.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
            $rollback = rollbackTransaction($conn);
            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5) . " Error finding distributor info. Please try again after some time.";
            echo (json_encode($returnArr));
            exit;
        }

        if (!empty($distributorInfo["errMsg"])) {
            //client with this GST Number already exists
            $logMsg = "Distributor GST Number already exists: {$gstNum}";
            $logData["step5.1"]["data"] = "5.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
            $rollback = rollbackTransaction($conn);
            $returnArr["errCode"] = 9;
            $returnArr["errMsg"] = getErrMsg(9) . " Distributor already exists with GST Number: {$gstNum}.";
            echo (json_encode($returnArr));
            exit;
        }
    }

    if ($oldEmail != $email) {
        //check if distributor already exists with this email
        $distributorSearchArr = array("email" => $email);
        $fieldsStr = "email";
        $distributorInfo = getDistributorsInfo($distributorSearchArr, $fieldsStr, null, $conn);
        if (!noError($distributorInfo)) {
            //error fetching distributor info
            $logMsg = "Error fetching distributor info to check for duplicate: {$distributorInfo["errMsg"]}";
            $logData["step5.1"]["data"] = "5.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
            $rollback = rollbackTransaction($conn);
            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5) . " Error finding distributor info. Please try again after some time.";
            echo (json_encode($returnArr));
            exit;
        }

        if (!empty($distributorInfo["errMsg"])) {
            //client with this Email already exists
            $logMsg = "Distributor GST Number already exists: {$email}";
            $logData["step5.1"]["data"] = "5.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
            $rollback = rollbackTransaction($conn);
            $returnArr["errCode"] = 9;
            $returnArr["errMsg"] = getErrMsg(9) . " Distributor already exists with Email ID: {$email}.";
            echo (json_encode($returnArr));
            exit;
        }
    }
    
    $distributorSearchArr = array("email"=>$oldEmail);
    $distributorSearchArr = array("gst_no"=>$oldGstNum);
    $updateDistributorInfo = updateDistributorInfo($arrToUpdate, $distributorSearchArr, $conn);

    if (!noError($updateDistributorInfo)) {
        //error updating distributor
        $logMsg = "Distributor Info could not be updated: {$updateDistributorInfo["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error updating distributor info. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }

    //Everything completed successfully
    $logMsg = "Distributor info edited successfully.";
} else {
    //create mode
    //check if distributor already exists with this gst num
    $distributorSearchArr = array("gst_no"=>$gstNum);
    $fieldsStr = "email";
    $distributorInfo = getDistributorsInfo($distributorSearchArr, $fieldsStr, null, $conn);
    if (!noError($distributorInfo)) {
        //error fetching distributor info
        $logMsg = "Error fetching distributor info to check for duplicate: {$distributorInfo["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error finding distributor info. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }

    if (!empty($distributorInfo["errMsg"])) {
        //broker with this gst num already exists
        $logMsg = "GST Num already exists: {$gstNum}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 9;
        $returnArr["errMsg"] = getErrMsg(9)." Distributor already exists with GST Num {$gstNum}.";
        echo(json_encode($returnArr));
        exit;
    }    

    //check if distributor already exists with this gst num
    $distributorSearchArr = array("email"=>$email);
    $fieldsStr = "email";
    $distributorInfo = getDistributorsInfo($distributorSearchArr, $fieldsStr, null, $conn);
    if (!noError($distributorInfo)) {
        //error fetching distributor info
        $logMsg = "Error fetching distributor info to check for duplicate: {$distributorInfo["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error finding distributor info. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }

    if (!empty($distributorInfo["errMsg"])) {
        //broker with this gst num already exists
        $logMsg = "Email Id already exists: {$email}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 9;
        $returnArr["errMsg"] = getErrMsg(9)." Distributor already exists with Email Id {$email}.";
        echo(json_encode($returnArr));
        exit;
    } 

    //create a new distributor    
    $arrToCreate = array( 
        $distributorId => array(
            'distributor_name'=>"'".$distributorName."'",
            'no_of_client'=>"'".$noOfClients."'",
            'email'=>"'".$email."'",
            'gst_no'=>"'".$gstNum."'",
            'beneficiary_name'=>"'".$benfName."'",
            'ifsc_code'=>"'".$ifscCode."'",
            'bank_account_no'=>"'".$bankAccNum."'",
            'account_branch'=>"'".$bankBranch."'",
            'management_fee_sharing'=>"'".$managementFeeSharing."'",
            'poc_details'=>"'".$pocDetails."'",
            "comments" =>"'". $comments."'",
            'created_on'=>"'".date("Y-m-d h:m:s")."'",
            'status'=>"'".$distributorStatus."'",
            'updated_on'=>"'".date("Y-m-d h:m:s")."'"
        )
    );
    
    $fieldsStr = array_keys($arrToCreate[$distributorId]);
    $fieldsStr = implode(",", $fieldsStr);

    $createDistributor = createDistributor($arrToCreate, $fieldsStr, $conn);
    if (!noError($createDistributor)) {
        //error creating distributor
        $logMsg = "Distributor could not be created: {$createDistributor["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $rollback = rollbackTransaction($conn);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error creating distributor. Please try again after some time.";
        echo(json_encode($returnArr));
        exit;
    }

    $logMsg = "Distributor created successfully.";
}

$logData["step6"]["data"] = "6. {$logMsg}";

$logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

$commit = commitTransaction($conn);

$returnArr["errCode"] = -1;
$returnArr["errMsg"] = getErrMsg(-1)." Distributor Info saved successfuly.";
echo(json_encode($returnArr));
exit;
?>