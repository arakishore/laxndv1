<?php
//prepare for request
//include necessary helpers
require_once ('../../../config/config.php');
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
require_once(__ROOT__.'/model/reports/reportsModel.php');
require_once(__ROOT__.'/model/activate/activateModel.php');
require_once(__ROOT__.'/model/client/clientDashboardModel.php');
require_once(__ROOT__.'/model/client/clientModel.php');
 

$importFailureEmailMessage = "<div>
    <p>There was an error in Export youtube   data on ".date("Y-m-d")." at ".date("h:m:s")."</p>
</div>";
$importSuccessEmailMessage = "<div>
    <p>There was NO error Export youtube   data into Content Reporting on ".date("Y-m-d")." at ".date("h:m:s")."</p>
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
    $selectedDate = "";
    if ($argv[1]) {
        $selectedDate = $argv[1];
    }  
     
    $year     = date("Y", strtotime($selectedDate));
    $month    = date("m", strtotime($selectedDate)); 
     
   // $youtube_report_table = 'youtube_video_claim_report_'.$year.'_'.$month;
     
    $youtube_report_table = "";
    if ($argv[2]) {
        $youtube_report_table = $argv[2];
    }  
  

    $logMsg = "All parameters are valid. Attempting to start generating";
    $logData["step4"]["data"] = "4. {$logMsg}";
    
   

    $logMsg = "Transaction started. ".'_____'.date("h:m:s");
    $logData["step5"]["data"] = "5. {$logMsg}";

    if(file_exists('../../../../'.$youtube_report_table."_".$year.'_'.$month.'.zip')){
        unlink('../../../../'.$youtube_report_table."_".$year.'_'.$month.'.zip');
    }

    try {
        $export = true;
        $offset = 0;
        $resultsPerPage = 1;
        $fieldsStr = "*";
    
   
  
    // $allClientsInfocount = getClientsYoutubeFinanceReportCount($finance_report_table, $youtube_report_table ,$conn,'',$youtube_report_table);
    $allClientsInfocount  = Exportyoutube_red_music_video_finance_reportv2($youtube_report_table, $conn,$offset,$resultsPerPage,'',$youtube_report_table);
    if (!noError($allClientsInfocount)) {

 
        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error fetching clients details.";
    } else { 

        
    }


         //send success email
         $date2 = $date1->diff(new DateTime());
         $importtime =  $date2->h.' hours '.$date2->i.' minutes '.$date2->s.' seconds';
             
         $emailMessage = $importSuccessEmailMessage."<p>Successfully Exported data in ".$importtime;
         $emailMessage.='<br><a href="'.$rootUrl.$youtube_report_table."_Youtube_dashboard_".$year.'_'.$month.'.zip'.'">Click to Download </a>';
         $emailSubject = "SUCCESS: Youtube data is ready to download of ".$youtube_report_table;
         $sendEmail = sendMail(IMPORTNOTIFIERS, $emailSubject, $emailMessage);
         if (!noError($sendEmail)) {
             //error sending email
             $logMsg = "Mail not sent";
             $logData["step6.1"]["data"] = "6.1. {$logMsg}";
             file_put_contents("testprocesses.php", json_encode($logData). "\n", FILE_APPEND);
             exit;
         }
         exit;
    } catch (\Throwable $th) {
      

     
        //send error email
        $emailMessage = $importFailureEmailMessage."<p>Could not Exported Youtube data</p>". json_encode($logData);
        $emailSubject = "FAILURE: Youtube data is failed to download ";
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