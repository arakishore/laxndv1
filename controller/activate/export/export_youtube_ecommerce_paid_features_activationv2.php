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

    $emailSubject = "Generate Activate export_youtube_ecommerce_paid_features_activationv Report";

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
  //  $youtube_report_table = '	youtube_ecom_paid_features_activation_report_nd1_2020_12';

    $logMsg = "All parameters are valid. Attempting to start generating";
    $logData["step4"]["data"] = "4. {$logMsg}";
    
   

    $logMsg = "Transaction started. ".'_____'.date("h:m:s");
    $logData["step5"]["data"] = "5. {$logMsg}";
//excelreports
    if(file_exists('../../../excelreports/'.$youtube_report_table.'.zip')){
        unlink('../../../excelreports/'.$youtube_report_table.'.zip');
    }

    try {
        $export = true;
        $offset = 0;
        $resultsPerPage = 1;
        $fieldsStr = "*";
    
   
  
    // $allClientsInfocount = getClientsYoutubeFinanceReportCount($finance_report_table, $youtube_report_table ,$conn,'',$youtube_report_table);
    $allClientsInfocount  = ExportActivateCommonReportv3($youtube_report_table, $conn);
    
    if (!noError($allClientsInfocount)) {

 
        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error fetching clients details.";
    } else {  
         $allClientsInfocount = $allClientsInfocount["errMsg"];
          
          
      
         $total=$allClientsInfocount['total']; 
       
         if ($export) {
            $searchdata='';
            $logMsg = "Request is to export all clients data to excel.";
            $logData["step6"]["data"] = "6. {$logMsg}";
     
            $xcelfiles = [];
         //   $headtitle = ['content_owner','total_amt_recd','shares','amt_payable', 'us_payout', 'Holding Perc','witholding', 'final_payable','gst_per','final_payable_with_gst','status'];

            $headtitle = ['Content Owner','Amount Recd','shares','Amount Payable', 'US Payout','Holding-Perc', 'Holding-Amt', 'Final Payable','GST-Perc','Final Payable-GST','Status'];

            $noofexcels = $total>100000 ? ceil($total/100000) : 1;
        
            for($i=0;$i<$noofexcels;$i++){

                $logMsg = "Exporting ";
                $logData["step7"]["data"] = "7. {$logMsg}";

                $spreadsheet = new Spreadsheet();
                $spreadsheet->setActiveSheetIndex(0);
                $activeSheet = $spreadsheet->getActiveSheet();
                
                //add header to spreadsheet
                $header = array_keys($headtitle);
                
                $header = $header[0];
                $header = $headtitle;
                $header = array_values($header);
                $activeSheet->fromArray([$header], NULL, 'A1');

                //add each client to the spreadsheet
                $clients = array();
                $startCell = 2; //starting from A2

                $offset=$i==0 ? $i : $i*100000;
                $resultsPerPage = $noofexcels>1 ? 100000 : $total;
                $allClientsInfo  = ExportActivateCommonReportv3($youtube_report_table ,$conn,$offset,$resultsPerPage,$searchdata,'');
                $allClientsInfo = $allClientsInfo["errMsg"]['data'];
                
                $logMsg = "Exporting from {$offset} to {$resultsPerPage}";
                $logData["step7"]["data"]  = "7.$i {$logMsg}";
               
                foreach($allClientsInfo as $clientEmail=>$clientDetails) {
                    $client = array_values($clientDetails);
                    $activeSheet->fromArray([$client], NULL, 'A'.$startCell);
                    $startCell++;
                }
                
                $logMsg = "Array is ready from {$offset} to {$resultsPerPage}";
                $logData["step7"]["data"]  = "7.$i {$logMsg}";

                //auto width on each column
                $highestColumn = $spreadsheet->getActiveSheet()->getHighestDataColumn();

                foreach (range('A', $highestColumn) as $col) {
                    $spreadsheet->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }

                $logMsg = "Genrating sheet from {$offset} to {$resultsPerPage}";
                $logData["step7"]["data"]  = "7.$i {$logMsg}";

                //style the header and totals rows
                $styleArray = [
                    'font' => [
                        'bold' => true,
                        'color'=>array('argb' => 'FFC5392A'),
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        ]
                    ]
                ];
                $spreadsheet->getActiveSheet()->getStyle('A1:'.$highestColumn.'1')->applyFromArray($styleArray);
                
                // //download the file
                $filename = $youtube_report_table.'_'.$i;
                $xcelfiles[]=$filename;

                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                 
                $writer->save($filename.'.xlsx');

                $logMsg = "Saved sheet from {$offset} to {$resultsPerPage}";
                $logData["step7"]["data"] = "7.$i {$logMsg}";
            }
            //save to zip
            $logMsg = "Excels files are ready now genrating zip";
            $logData["step8"]["data"] = "8.$i {$logMsg}";

            $zip_file_tmp= '../../../excelreports/'.$youtube_report_table.'.zip';
            $zip = new ZipArchive();
            $zip->open($zip_file_tmp, ZipArchive::CREATE);
            foreach ($xcelfiles as $file) {
               $zip->addFile($file.'.xlsx');
            }
            $zip->close();
            foreach ($xcelfiles as $file) {
                  unlink($file.'.xlsx');
             }
             ob_flush();
             flush();
             $logMsg = "Zip is ready to download";
             $logData["step9"]["data"] = "9.$i {$logMsg}";
        }
    
        $returnArr["errCode"] = -1;
    }


         //send success email
         $date2 = $date1->diff(new DateTime());
         $importtime =  $date2->h.' hours '.$date2->i.' minutes '.$date2->s.' seconds';
             
         $emailMessage = $importSuccessEmailMessage."<p>Successfully Exported data in ".$importtime;
         $emailMessage.='<br><a href="'.$rootUrl.$youtube_report_table."_".$year.'_'.$month.'.zip'.'">Click to Download </a>';
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