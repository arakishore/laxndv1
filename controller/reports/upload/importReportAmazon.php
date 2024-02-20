<?php
//prepare for request
//include necessary helpers
require_once '../../../config/config.php';

//include some more necessary helpers
require_once __ROOT__ . '/config/dbUtils.php';
require_once __ROOT__ . '/config/errorMap.php';
require_once __ROOT__ . '/config/logs/logsProcessor.php';
require_once __ROOT__ . '/config/logs/logsCoreFunctions.php';
require_once __ROOT__ . '/libphp-phpmailer/autoload.php';

//TO DO: Logs

//include necessary models

require_once __ROOT__ . '/model/reports/amazonModel.php';

$importFailureEmailMessage = "<div>
    <p>There was an error importing the Amazon   Video Report  into Content Reporting on " . date("Y-m-d") . " at " . date("h:m:s") . "</p>
</div>";
$importSuccessEmailMessage = "<div>
    <p>There was NO error importing the Amazon   Video Report  into Content Reporting on " . date("Y-m-d") . " at " . date("h:m:s") . "</p>
</div>";
$date1 = new DateTime();
//Connection With Database
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1) . $conn["errMsg"];
} else {
    $conn = $conn["errMsg"];

    $returnArr = array();

    //get the user info
    $email = "importYoutubeReport@background.process";

    //initialize logs
    $logsProcessor = new logsProcessor();
    $initLogs = initializeJsonLogs($email);
    $logFilePath = $logStorePaths["reports"]["import"];
    $logFileName = "importYoutubeBackground.json";

    $logMsg = "Import Amazon Video Report  background process start: " . date("Y-m-d h:i:s");
    $logData['step1']["data"] = "1. {$logMsg}";

    $logMsg = "Database connection successful.";
    $logData["step2"]["data"] = "2. {$logMsg}";

    $logMsg = "Validating arguments: " . json_encode($argv);
    $logData["step3"]["data"] = "3. {$logMsg}";

    $emailSubject = "Import Amazon Video Report";

    //validate filepath
    $filePath = "";
    if ($argv[1]) {
        $filePath = $argv[1];
    }

    if (empty($argv[1])) { //to do: do file path format validation
        $logMsg = "Filepath empty: " . $argv[1];
        $logData["step3.1"]["data"] = "3.1. {$logMsg}";

        file_put_contents("testprocesses.php", json_encode($logData) . "\n", FILE_APPEND);

        //send error email
        $emailMessage = $importFailureEmailMessage . "<p>Filepath empty</p>";
        $emailSubject = "FAILURE: " . $emailSubject;
        $sendEmail = sendMail(IMPORTNOTIFIERS, $emailSubject, $emailMessage);
        if (!noError($sendEmail)) {
            //error sending email
            $logMsg = "Mail not sent";
            $logData["step6.1"]["data"] = "6.1. {$logMsg}";
            file_put_contents("testprocesses.php", json_encode($logData) . "\n", FILE_APPEND);
            exit;
        }
        exit;
    }

    //validate tablename
    $tableName = "";
    if ($argv[2]) {
        $tableName = $argv[2];
    }

    if (empty($argv[2])) { //to do: do tablename format validation
        $logMsg = "tableName empty: " . $argv[2];
        $logData["step3.1"]["data"] = "3.1. {$logMsg}";

        file_put_contents("testprocesses.php", json_encode($logData) . "\n", FILE_APPEND);

        //send error email
        $emailMessage = $importFailureEmailMessage . "<p>Table name empty</p>";
        $emailSubject = "FAILURE: " . $emailSubject;
        $sendEmail = sendMail(IMPORTNOTIFIERS, $emailSubject, $emailMessage);
        if (!noError($sendEmail)) {
            //error sending email
            $logMsg = "Mail not sent";
            $logData["step6.1"]["data"] = "6.1. {$logMsg}";
            file_put_contents("testprocesses.php", json_encode($logData) . "\n", FILE_APPEND);
            exit;
        }

        exit;
    }

    $logMsg = "All parameters are valid. Attempting to start transaction";
    $logData["step4"]["data"] = "4. {$logMsg}";

    $startTransaction = startTransaction($conn);
    if (!noError($startTransaction)) {

        $logMsg = "Could not start transaction";
        $logData["step4.1"]["data"] = "4.1. {$logMsg}";

        file_put_contents("testprocesses.php", json_encode($logData) . "\n", FILE_APPEND);

        //send error email
        $emailMessage = $importFailureEmailMessage . "<p>Could not start transaction</p>";
        $emailSubject = "FAILURE: " . $emailSubject;
        $sendEmail = sendMail(IMPORTNOTIFIERS, $emailSubject, $emailMessage);
        if (!noError($sendEmail)) {
            //error sending email
            $logMsg = "Mail not sent";
            $logData["step6.1"]["data"] = "6.1. {$logMsg}";
            file_put_contents("testprocesses.php", json_encode($logData) . "\n", FILE_APPEND);
            exit;
        }

        exit;
    }

    $logMsg = "Transaction started. Inserting csv report into table" . '_____' . date("h:m:s");
    $logData["step5"]["data"] = "5. {$logMsg}";

    $paramlog['table_name'] = $tableName;
    $paramlog['file_name'] = $filePath;
    $paramlog['status_name'] = "Import";
    $paramlog['status_flag'] = "start";
    $paramlog['date_added'] = date("Y-m-d H:i:s");
    $paramlog['ip_address'] = get_client_ip();
    $paramlog['login_user'] = $_SESSION["userEmail"];
    $paramlog['log_file'] = $logStorePaths["reports"]["import"];
    $paramlog['raw_data'] = json_encode($_POST);
    $username = activitylogs($paramlog, $conn);

    $insertInfoArr = insertReportAmazonVideoInfo($filePath, $tableName, $conn);

    if (!noError($insertInfoArr)) {
        $rollback = rollbackTransaction($conn);
        $logMsg = "Could not import csv into table: " . json_encode($insertInfoArr);
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";

        file_put_contents("testprocesses.php", json_encode($logData) . "\n", FILE_APPEND);

        //send error email
        $emailMessage = $importFailureEmailMessage . "<p>Could not import csv into table</p>" . json_encode($logData);
        $emailSubject = "FAILURE: " . $emailSubject;
        $sendEmail = sendMail(IMPORTNOTIFIERS, $emailSubject, $emailMessage);
        if (!noError($sendEmail)) {
            //error sending email
            $logMsg = "Mail not sent";
            $logData["step6.1"]["data"] = "6.1. {$logMsg}";
            file_put_contents("testprocesses.php", json_encode($logData) . "\n", FILE_APPEND);
            exit;
        }
        exit;
    } else {
        $logMsg = "successfully imported csv into table. Run autoAssignContentOwnerCOMap query for partner-provided: " . '_____' . date("h:m:s");
        $logData["step6"]["data"] = "6. {$logMsg}";

        commitTransaction($conn);
        //$startTransaction = startTransaction($conn);
        $paramlog['table_name'] = $tableName;
        $paramlog['file_name'] = $filePath;
        $paramlog['status_name'] = "Import";
        $paramlog['status_flag'] = "end";
        $paramlog['date_added'] = date("Y-m-d H:i:s");
        $paramlog['ip_address'] = get_client_ip();
        $paramlog['login_user'] = $_SESSION["userEmail"];
        $paramlog['log_file'] = $logStorePaths["reports"]["import"];
        $paramlog['raw_data'] = json_encode($_POST);
        $username = activitylogs($paramlog, $conn);
        


        $paramlog['table_name'] = $tableName;
        $paramlog['file_name'] = 'autoAssignContentOwnerCOMap';
        $paramlog['status_name'] = "Auto-assign";
        $paramlog['status_flag'] = "start";
        $paramlog['date_added'] = date("Y-m-d H:i:s");
        $paramlog['ip_address'] = get_client_ip();
        $paramlog['login_user'] = $_SESSION["userEmail"];
        $paramlog['log_file'] = $logStorePaths["reports"]["import"];
        $paramlog['raw_data'] = json_encode($_POST);
        $username = activitylogs($paramlog, $conn);
    

        $contentType = "partner_provided";
        $autoAssignContentOwnerCOMap = autoAssignContentOwnerCOMap(
            $tableName,
          
            $contentType,
            $conn
        );
        if (!noError($autoAssignContentOwnerCOMap)) {
            $rollback = rollbackTransaction($conn);
            $logMsg = "Could not run autoAssignContentOwnerCOMap for PP: " . json_encode($autoAssignContentOwnerCOMap);
            $logData["step6.1"]["data"] = "6.1. {$logMsg}";

            file_put_contents("testprocesses.php", json_encode($logData) . "\n", FILE_APPEND);

            //send error email
            $emailMessage = $importFailureEmailMessage . "<p>Could not run autoAssignContentOwnerCOMap for PP</p>";
            $emailSubject = "FAILURE: " . $emailSubject;
            $sendEmail = sendMail(IMPORTNOTIFIERS, $emailSubject, $emailMessage);
            if (!noError($sendEmail)) {
                //error sending email
                $logMsg = "Mail not sent";
                $logData["step6.1"]["data"] = "6.1. {$logMsg}";
                file_put_contents("testprocesses.php", json_encode($logData) . "\n", FILE_APPEND);
                exit;
            }
            exit;
        } else {

            $paramlog['table_name'] = $tableName;
            $paramlog['file_name'] = 'autoAssignContentOwnerCOMap';
            $paramlog['status_name'] = "Auto-assign";
            $paramlog['status_flag'] = "end";
            $paramlog['date_added'] = date("Y-m-d H:i:s");
            $paramlog['ip_address'] = get_client_ip();
            $paramlog['login_user'] = $_SESSION["userEmail"];
            $paramlog['log_file'] = $logStorePaths["reports"]["import"];
            $paramlog['raw_data'] = json_encode($_POST);
            $username = activitylogs($paramlog, $conn);

            $date2 = $date1->diff(new DateTime());

            $importtime = $date2->h . ' hours ' . $date2->i . ' minutes ' . $date2->s . ' seconds';

            //send success email
            $emailMessage = $importSuccessEmailMessage . "<p>Successfully imported csv in " . $importtime . " </p>Imported files :<br/>" . $filePath;
            $emailSubject = "SUCCESS: " . $emailSubject;
            $sendEmail = sendMail(IMPORTNOTIFIERS, $emailSubject, $emailMessage);
            if (!noError($sendEmail)) {
                //error sending email
                $logMsg = "Mail not sent";
                $logData["step6.1"]["data"] = "6.1. {$logMsg}";
                file_put_contents("testprocesses.php", json_encode($logData) . "\n", FILE_APPEND);
                exit;
            }
            exit;
        }

    }
}
