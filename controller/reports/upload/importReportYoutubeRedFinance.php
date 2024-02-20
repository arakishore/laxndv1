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

require_once(__ROOT__.'/model/reports/youtubeRedFinanceModel.php');
 

$importFailureEmailMessage = "<div>
    <p>There was an error importing the Youtube Red Finance Report Claim 1.1 into Content Reporting on ".date("Y-m-d")." at ".date("h:m:s")."</p>
</div>";
$importSuccessEmailMessage = "<div>
    <p>There was NO error importing the Youtube Red Finance Report Claim 1.1 into Content Reporting on ".date("Y-m-d")." at ".date("h:m:s")."</p>
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

    $logMsg = "Import Youtube Red Finance Report Claim 1.1 background process start: ".date("Y-m-d h:i:s");
    $logData['step1']["data"] = "1. {$logMsg}";

    $logMsg = "Database connection successful.";
    $logData["step2"]["data"] = "2. {$logMsg}";

    $logMsg = "Validating arguments: ".json_encode($argv);
    $logData["step3"]["data"] = "3. {$logMsg}";

    $emailSubject = "Import Red Finance Report Claim 1.1";

    //validate filepath
    $filePath = "";
    if ($argv[1]) {
        $filePath = $argv[1];
    }

    if (empty($argv[1])) { //to do: do file path format validation
        $logMsg = "Filepath empty: ".$argv[1];
        $logData["step3.1"]["data"] = "3.1. {$logMsg}";

        file_put_contents("testprocesses.php", json_encode($logData). "\n", FILE_APPEND);

        //send error email
        $emailMessage = $importFailureEmailMessage."<p>Filepath empty</p>";
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
    
    //validate tablename
    $tableName = "";
    if ($argv[2]) {
        $tableName = $argv[2];
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

    $logMsg = "All parameters are valid. Attempting to start transaction";
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

    $logMsg = "Transaction started. Inserting csv report into table".'_____'.date("h:m:s");
    $logData["step5"]["data"] = "5. {$logMsg}";

    $insertInfoArr = insertRedFinanceReportInfo($filePath, $tableName, $conn);
	 
    if (!noError($insertInfoArr)) {
        $rollback = rollbackTransaction($conn);
        $logMsg = "Could not import csv into table: ".json_encode($insertInfoArr);
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";

        file_put_contents("testprocesses.php", json_encode($logData). "\n", FILE_APPEND);

        //send error email
        $emailMessage = $importFailureEmailMessage."<p>Could not import csv into table</p>". json_encode($logData);
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
        commitTransaction($conn);
        $startTransaction = startTransaction($conn);
        $logMsg = "successfully imported csv into table. Run autoAssignChannelCOMap query for partner-provided: ".'_____'.date("h:m:s");
        $logData["step6"]["data"] = "6. {$logMsg}";

        $fieldSearchArr = array("t1.contentType"=>"t2.contentType");
        $contentType = "content_owner";
        $autoAssignChannelCOMap = autoAssignChannelCOMapRed(
            $tableName,
            $fieldSearchArr,
            $contentType,
            $conn
        );
        if (!noError($autoAssignChannelCOMap)) {
            $rollback = rollbackTransaction($conn);
            $logMsg = "Could not run autoAssignChannelCOMap for PP: ".json_encode($autoAssignChannelCOMap);
            $logData["step6.1"]["data"] = "6.1. {$logMsg}";

            file_put_contents("testprocesses.php", json_encode($logData). "\n", FILE_APPEND);

            //send error email
            $emailMessage = $importFailureEmailMessage."<p>Could not run autoAssignChannelCOMap for PP</p>";
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
                $errorsOccurred = array();
                // for ($i=1; $i<=6; $i++) {
                //     $destMonthYear = explode("_", str_replace("youtube_red_finance_report_", "", $tableName));
  
                //     $destMonthYear = "01-".$destMonthYear[1]."-".$destMonthYear[0];  
                //     $sourceMonthYear = date("Y_m",   strtotime($destMonthYear));
                //     $sourceTableName = "youtube_finance_report_".$sourceMonthYear;
                  
                //     $logMsg = "Run auto assign queries {$i} months ago: ".$sourceTableName;
                //     $logData["step9.{$i}"]["data"] = "9.{$i} {$logMsg}";
                    
                //     $autoAssignPrevMonths = autoAssignPrevMonthsRed(
                //         $tableName,
                //         $sourceTableName,
                //         $conn
                //     );
                //     if (!noError($autoAssignPrevMonths)) {
                //        //  $rollback = rollbackTransaction($conn);
                //         $logMsg = $filePath."<br/>Could not run autoAssignPrevMonths for {$i}: ".json_encode($autoAssignPrevMonths);
                //         $logData["step9.{$i}.1"]["data"] = "9.{$i}.1. {$logMsg}";

                //         $errorsOccurred[] = $logMsg;

                //         file_put_contents("testprocesses.php", json_encode($logData). "\n", FILE_APPEND);
                //     } else {
                //         $logMsg = "successfully ran autoAssignPrevMonths for {$i}: ".json_encode($autoAssignPrevMonths).'_____'.date("h:m:s");
                //         $logData["step9.{$i}.1"]["data"] = "9.{$i}.1. {$logMsg}";
                //     }
                // } 

                file_put_contents("testprocesses.php", json_encode($logData). "\n", FILE_APPEND);

                if (empty($errorsOccurred)) {
                    commitTransaction($conn);
					$date2 = $date1->diff(new DateTime());
			                $importtime =  $date2->h.' hours '.$date2->i.' minutes '.$date2->s.' seconds';
					 
                    //send success email
                    $emailMessage = $importSuccessEmailMessage."<p>Successfully imported csv in ".$importtime." </p>Imported files:<br/>".$filePath;
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
                } else {
                    //send failure email
                    $emailMessage = $importFailureEmailMessage."<p>Failed to import csv</p>".implode(",", $errorsOccurred);
                    $emailSubject = "Failed: ".$emailSubject;
                    $sendEmail = sendMail(IMPORTNOTIFIERS, $emailSubject, $emailMessage);
                    if (!noError($sendEmail)) {
                        //error sending email
                        $logMsg = "Mail not sent";
                        $logData["step6.1"]["data"] = "6.1. {$logMsg}";
                        file_put_contents("testprocesses.php", json_encode($logData). "\n", FILE_APPEND);
                        exit;
                    }
                    exit;
                } //close if empty errorsOccured
        
        
    }
}
?>