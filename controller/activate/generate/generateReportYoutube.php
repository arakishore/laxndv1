<?php
//prepare for request
//include necessary helpers
require_once('../../../config/config.php');

//include some more necessary helpers
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/logs/logsProcessor.php');
require_once(__ROOT__.'/config/logs/logsCoreFunctions.php');
require_once(__ROOT__.'/libphp-phpmailer/autoload.php');

//TO DO: Logs

//include necessary models
require_once(__ROOT__.'/model/reports/reportsModel.php');
require_once(__ROOT__ .'/model/activate/activateModel.php');

$importFailureEmailMessage = "<div>
    <p>There was an error Activation genrating the Youtube Report into Content Reporting on ".date("Y-m-d")." at ".date("h:m:s")."</p>
</div>";
$importSuccessEmailMessage = "<div>
    <p>There was NO error Activation genrating the Youtube Report into Content Reporting on ".date("Y-m-d")." at ".date("h:m:s")."</p>
</div>";
$date1 = new DateTime();
//Connection With Database
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1).$conn["errMsg"];
} else {
    $conn = $conn["errMsg"];

    $returnArr = array();

    //get the user info
    $email = "importYoutubeReport@background.process";

    //initialize logs
    $logsProcessor = new logsProcessor();
    $initLogs = initializeJsonLogs($email);
    $logFilePath = $logStorePaths["reports"]["import"];
    $logFileName="importYoutubeBackground.json";

    $logMsg = "Activate Youtube background process start: ".date("Y-m-d h:i:s");
    $logData['step1']["data"] = "1. {$logMsg}";

    $logMsg = "Database connection successful.";
    $logData["step2"]["data"] = "2. {$logMsg}";

    $logMsg = "Validating arguments: ".json_encode($argv);
    $logData["step3"]["data"] = "3. {$logMsg}";

    $emailSubject = "Generate Activate Youtube Report";

    //validate filepath
    $tableName = "";
    if ($argv[1]) {
        $tableName = $argv[1];
    }
 
    //validate tablename
    $actableName = "";
    if ($argv[2]) {
        $actableName = $argv[2];
    }
 

    if (empty($argv[2])) { //to do: do tablename format validation
        $logMsg = "tableName empty: ".$argv[2];
        $logData["step3.1"]["data"] = "3.1. {$logMsg}";

        file_put_contents("testprocesses.php", json_encode($logData). "\n", FILE_APPEND);

        //send error email
        $emailMessage = $importFailureEmailMessage."<p>Table name empty</p>";
        $emailSubject = "FAILURE: ".$emailSubject;
        $sendEmail = sendMail(IMPORTNOTIFIERS, $emailSubject, $emailMessage);
        if (!noError($sendEmail)) {
            //error sending email
            $logMsg = "Mail not sent";
            $logData["step6.1"]["data"] = "6.1. {$logMsg}";
            file_put_contents("testprocesses.php", json_encode($logData). "\n", FILE_APPEND);
            exit;
        }

        exit;
    }

    $logMsg = "All parameters are valid. Attempting to start generating";
    $logData["step4"]["data"] = "4. {$logMsg}";
    
    $startTransaction = startTransaction($conn);
    if (!noError($startTransaction)) {

        $logMsg = "Could not start transaction";
        $logData["step4.1"]["data"] = "4.1. {$logMsg}";

        file_put_contents("testprocesses.php", json_encode($logData). "\n", FILE_APPEND);

        //send error email
        $emailMessage = $importFailureEmailMessage."<p>Could not start transaction</p>";
        $emailSubject = "FAILURE: ".$emailSubject;
        $sendEmail = sendMail(IMPORTNOTIFIERS, $emailSubject, $emailMessage);
        if (!noError($sendEmail)) {
            //error sending email
            $logMsg = "Mail not sent";
            $logData["step6.1"]["data"] = "6.1. {$logMsg}";
            file_put_contents("testprocesses.php", json_encode($logData). "\n", FILE_APPEND);
            exit;
        }
        exit;
    }

    $logMsg = "Transaction started. ".'_____'.date("h:m:s");
    $logData["step5"]["data"] = "5. {$logMsg}";

    
    $insertInfoArr = generateActicationReport($tableName,$actableName,$conn);
    if (!noError($insertInfoArr)) {
        $rollback = rollbackTransaction($conn);
        $logMsg = "Could not generate into table: ".json_encode($insertInfoArr);
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";

        file_put_contents("testprocesses.php", json_encode($logData). "\n", FILE_APPEND);

        //send error email
        $emailMessage = $importFailureEmailMessage."<p>Could not generate csv into table</p>". json_encode($logData);
        $emailSubject = "FAILURE: ".$emailSubject;
        $sendEmail = sendMail(IMPORTNOTIFIERS, $emailSubject, $emailMessage);
        if (!noError($sendEmail)) {
            //error sending email
            $logMsg = "Mail not sent";
            $logData["step6.1"]["data"] = "6.1. {$logMsg}";
            file_put_contents("testprocesses.php", json_encode($logData). "\n", FILE_APPEND);
            exit;
        }
        exit;
    } else {
        $logMsg = "successfully genrated ";
        $logData["step6"]["data"] = "6. {$logMsg}";

        commitTransaction($conn);
        $date2 = $date1->diff(new DateTime());
        $importtime =  $date2->h.' hours '.$date2->i.' minutes '.$date2->s.' seconds';
            
        //send success email
        $emailMessage = $importSuccessEmailMessage."<p>Successfully Genrated activation in ".$importtime;
        $emailSubject = "SUCCESS: ".$emailSubject;
        $sendEmail = sendMail(IMPORTNOTIFIERS, $emailSubject, $emailMessage);
        if (!noError($sendEmail)) {
            //error sending email
            $logMsg = "Mail not sent";
            $logData["step6.1"]["data"] = "6.1. {$logMsg}";
            file_put_contents("testprocesses.php", json_encode($logData). "\n", FILE_APPEND);
            exit;
        }
        exit;
    
      
    }
}
?>