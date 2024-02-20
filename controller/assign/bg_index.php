<?php
//prepare for request
//include necessary helpers
require_once('../../config/config.php');    
ini_set("memory_limit", "-1");
set_time_limit(0);



//include some more necessary helpers
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/logs/logsProcessor.php');
require_once(__ROOT__.'/config/logs/logsCoreFunctions.php');
require_once(__ROOT__.'/libphp-phpmailer/autoload.php');
require_once(__ROOT__.'/vendor/phpoffice/phpspreadsheet/src/Bootstrap.php');
 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

//TO DO: Logs

//include necessary models
 
require_once(__ROOT__.'/model/export/exportModel.php');
 
 

$importFailureEmailMessage = "<div>
    <p>There was an error in assign content owner   data on ".date("Y-m-d")." at ".date("h:m:s")."</p>
</div>";
$importSuccessEmailMessage = "<div>
    <p>There was NO error assign content owner  into Content Reporting on ".date("Y-m-d")." at ".date("h:m:s")."</p>
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

    $logMsg = "assign content owner background process start: ".date("Y-m-d h:i:s");
    $logData['step1']["data"] = "1. {$logMsg}";

    $logMsg = "Database connection successful.";
    $logData["step2"]["data"] = "2. {$logMsg}";

    $logMsg = "Validating arguments: ".json_encode($argv);
    $logData["step3"]["data"] = "3. {$logMsg}";

    $emailSubject = "Assign content owner";

    //validate filepath
    $selectedDate = "";
    if ($argv[1]) {
        $selectedDate = $argv[1];
    }  
     
    $year     = date("Y", strtotime($selectedDate));
    $month    = date("m", strtotime($selectedDate)); 
     
  
    $table_name = "";
    if ($argv[2]) {
        $table_name = $argv[2];
    }  
  

    $logMsg = "All parameters are valid. Attempting to start generating";
    $logData["step4"]["data"] = "4. {$logMsg}";
    
   

    $logMsg = "Transaction started. ".'_____'.date("h:m:s");
    $logData["step5"]["data"] = "5. {$logMsg}";

     $params['selectedDate']=$argv[1];
     $params['table_name']=$argv[2];
     $params['type_cate']=$argv[3];
     $params['nd']=$argv[4];
     $params['onlyunassigned']=$argv[5];
     $params['contentOwner']=$argv[6];
     $params['asset_id']=$argv[7];
     $params['channel_id']=$argv[8];
     $params['content_type']=$argv[9];
     
     if($params['asset_id']!=""){
        $params['asset_id'] =  base64_decode($params['asset_id']);
     }
    
     if($params['channel_id']!=""){
        $params['channel_id'] =  base64_decode($params['channel_id']);
     }

     try {
        $export = true;
        $offset = 0;
        $resultsPerPage = 1;
        $fieldsStr = "*";

        $allClientsInfocount = assignupdateContentOwnerv2($table_name, $params, $conn);

        if (!noError($allClientsInfocount)) {

            $paramlog['table_name'] = $table_name;
            $paramlog['file_name'] = '';
            $paramlog['status_name'] = "assign-updateContentOwner-update-successfully";
            $paramlog['status_flag'] = "Error";
            $paramlog['date_added'] = date("Y-m-d H:i:s");
            $paramlog['ip_address'] = "";
            $paramlog['login_user'] = (isset($_SESSION["userEmail"])) ? $_SESSION["userEmail"] : '';
            $paramlog['log_file'] = "";
            $paramlog['raw_data'] = json_encode($params);
            $username_temp = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5) . " Error Cannot update content owner.";
        } else {

            $paramlog['table_name'] = $table_name;
            $paramlog['file_name'] = '';
            $paramlog['status_name'] = "assign-updateContentOwner-update-fail";
            $paramlog['status_flag'] = "end";
            $paramlog['date_added'] = date("Y-m-d H:i:s");
            $paramlog['ip_address'] = "";
            $paramlog['login_user'] = (isset($_SESSION["userEmail"])) ? $_SESSION["userEmail"] : '';
            $paramlog['log_file'] = "";
            $paramlog['raw_data'] = json_encode($params);
            $username_temp = activitylogs($paramlog, $conn);

            //send success email
            $date2 = $date1->diff(new DateTime());
            $importtime = $date2->h . ' hours ' . $date2->i . ' minutes ' . $date2->s . ' seconds';

            $emailMessage = $importSuccessEmailMessage . "<p>Successfully   update content owner ".$params['type_cate']." data in " . $importtime;
            
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

    } catch (\Throwable $th) {

            $paramlog['table_name'] = $table_name;
            $paramlog['file_name'] = '';
            $paramlog['status_name'] = "assign-updateContentOwner-update-fail-catch";
            $paramlog['status_flag'] = "Try-Error";
            $paramlog['date_added'] = date("Y-m-d H:i:s");
            $paramlog['ip_address'] = "";
            $paramlog['login_user'] = (isset($_SESSION["userEmail"])) ? $_SESSION["userEmail"] : '';
            $paramlog['log_file'] = "";
            $paramlog['raw_data'] = json_encode($params);
            $username_temp = activitylogs($paramlog, $conn);


        //send error email
        $emailMessage = $importFailureEmailMessage . "<p>Cannot update content owner</p>" . json_encode($logData);
        $emailSubject = "FAILURE: Cannot update content owner ";
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
?>