<?php
//prepare for request
//include necessary helpers
require_once '../../../../config/config.php';
ini_set("memory_limit", "-1");
set_time_limit(0);

//include some more necessary helpers
require_once __ROOT__ . '/config/dbUtils.php';
require_once __ROOT__ . '/config/errorMap.php';
require_once __ROOT__ . '/config/logs/logsProcessor.php';
require_once __ROOT__ . '/config/logs/logsCoreFunctions.php';
require_once __ROOT__ . '/libphp-phpmailer/autoload.php';
require_once __ROOT__ . '/vendor/phpoffice/phpspreadsheet/src/Bootstrap.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

//TO DO: Logs

//include necessary models
require_once __ROOT__ . '/model/reports/reportsModel.php';
require_once __ROOT__ . '/model/activate/activateModel.php';
require_once __ROOT__ . '/model/client/clientDashboardModel.php';
require_once __ROOT__ . '/model/client/clientModel.php';

$importFailureEmailMessage = "<div>
    <p>There was an error in Export youtube   data on " . date("Y-m-d") . " at " . date("h:m:s") . "</p>
</div>";
$importSuccessEmailMessage = "<div>
    <p>There was NO error Export youtube   data into Content Reporting on " . date("Y-m-d") . " at " . date("h:m:s") . "</p>
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
    $activity_downlaod_reportv2 = getContentActivityDownlaodJsonv2('activity_downlaod_reportv2.josn');

    //initialize logs
    $logsProcessor = new logsProcessor();
    $initLogs = initializeJsonLogs($email);
    $logFilePath = $logStorePaths["reports"]["import"];
    $logFileName = "importYoutubeBackground.json";

    $logMsg = "Activate Youtube background process start: " . date("Y-m-d h:i:s");
    $logData['step1']["data"] = "1. {$logMsg}";

    $logMsg = "Database connection successful.";
    $logData["step2"]["data"] = "2. {$logMsg}";

    $logMsg = "Validating arguments: " . json_encode($argv);
    $logData["step3"]["data"] = "3. {$logMsg}";

    $emailSubject = "Generate  export  youtube_red_music_video_finance_report Report";

    //validate filepath
    $selectedDate = "";
    if ($argv[1]) {
        $selectedDate = $argv[1];
    }
    //  $selectedDate = "2020-12";
    $year = date("Y", strtotime($selectedDate));
    $month = date("m", strtotime($selectedDate));
    $finance_report_table = 'youtube_red_music_video_finance_report_' . $year . '_' . $month;
    $youtube_report_table = 'youtube_red_music_video_finance_report_' . $year . '_' . $month;

    $clientsession = "";
    if ($argv[2]) {
        $clientsession = $argv[2];
    }
    if ($clientsession == "") {
        // $clientsession = "TeamFilm";
    }
    $clientsession = base64_decode($clientsession);

    
    $type_table = "";
    if ($argv[3]) {
        $type_table = $argv[3];
    }

    if(!empty($argv[5])){
        $downlaodType = $argv[5];
    }else {
        $downlaodType = 'normal';
    }


    $downlaodType_zip = ($downlaodType=="withholding") ? $downlaodType : '';
    
    $logMsg = "All parameters are valid. Attempting to start generating";
    $logData["step4"]["data"] = "4. {$logMsg}";

    $logMsg = "Transaction started. " . '_____' . date("h:m:s");
    $logData["step5"]["data"] = "5. {$logMsg}";

    if (file_exists('../../../../excelreports/' . $clientsession . "_youtube_red_music_video_finance_report_" . $year . '_' . $month .$downlaodType_zip. '.zip')) {
        unlink('../../../../excelreports/' . $clientsession . "_youtube_red_music_video_finance_report_" . $year . '_' . $month .$downlaodType_zip. '.zip');
    }

    try {
        $export = true;
        $offset = 0;
        $resultsPerPage = 1;
        $fieldsStr = "*";

        // $allClientsInfocount = getClientsYoutubeFinanceReportCount($finance_report_table, $youtube_report_table ,$conn,'',$clientsession);
        $table_type_name = 'youtube_red_music_video_finance_report_' . $type_table . '%' . $year . '_' . $month;
        $allClientsInfocount = ExportClientsYoutuberedMusicre_finance_reportCountv3($table_type_name, $conn, $offset, $resultsPerPage, '', $clientsession,$downlaodType);

        if (!noError($allClientsInfocount)) {

            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5) . " Error fetching clients details.";
        } else {
            $allClientsInfocount = $allClientsInfocount["errMsg"];

            $total = $allClientsInfocount['total'];

            if ($export) {
                $searchdata = '';
                $logMsg = "Request is to export all clients data to excel.";
                $logData["step6"]["data"] = "6. {$logMsg}";

                $xcelfiles = [];

                $limit_total = 900000;

                if($downlaodType == "withholding"){
                   
                    
                    if($type_table == "redmusic"){
                        $headtitle = [ 'assetChannelID', 'Youtube payout', 'RevShare', 'USPAYOUT','Holding-Perc', 'WITHHOLDING', 'Final Payable','GST','Final Payable-GST', 'Channel'];
                    } else {
                        $headtitle = [ 'channelID', 'Youtube payout', 'RevShare', 'USPAYOUT','Holding-Perc', 'WITHHOLDING', 'Final Payable','GST','Final Payable-GST', 'Channel'];
                    }
                } else {
                    if($type_table == "redmusic"){
                        //, 'asset_title', 'asset_labels',  'Youtube payout', 'RevShare','USPAYOUT','WITHHOLDING', 'Final Payable','GST','Final Payable-GST', 'Channel'
                        $headtitle = ['country', 'contentType', 'assetChannelID', 'videoID','AssetId','asset_labels','Youtube payout', 'RevShare','USPAYOUT','WITHHOLDING', 'Final Payable','GST','Final Payable-GST', 'Channel'];
                    } else {
                        $headtitle = ['country', 'contentType', 'channelID', 'videoID', 'videoTitle','AssetId', 'Youtube payout', 'RevShare' ,'USPAYOUT','WITHHOLDING', 'Final Payable','GST','Final Payable-GST', 'Channel'];
                    }
                }
               
                
                $noofexcels = $total > $limit_total ? ceil($total / $limit_total) : 1;

                for ($i = 0; $i < $noofexcels; $i++) {

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
                    $activeSheet->fromArray([$header], null, 'A1');

                    //add each client to the spreadsheet
                    $clients = array();
                    $startCell = 2; //starting from A2

                    $offset = $i == 0 ? $i : $i * $limit_total;
                    $resultsPerPage = $noofexcels > 1 ? $limit_total : $total;
                    $allClientsInfo = ExportClientsYoutuberedMusicre_finance_reportv3($table_type_name, $conn, $offset, $resultsPerPage, $searchdata, $clientsession,$downlaodType);
                    $allClientsInfo = $allClientsInfo["errMsg"]['data'];

                    $logMsg = "Exporting from {$offset} to {$resultsPerPage}";
                    $logData["step7"]["data"] = "7.$i {$logMsg}";

                    foreach ($allClientsInfo as $clientEmail => $clientDetails) {
                        $client = array_values($clientDetails);

                        $activeSheet->fromArray([$client], null, 'A' . $startCell, true);
                        $startCell++;
                    }

                    $logMsg = "Array is ready from {$offset} to {$resultsPerPage}";
                    $logData["step7"]["data"] = "7.$i {$logMsg}";

                    //auto width on each column
                    $highestColumn = $spreadsheet->getActiveSheet()->getHighestDataColumn();

                    foreach (range('A', $highestColumn) as $col) {
                        $spreadsheet->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }

                    $logMsg = "Genrating sheet from {$offset} to {$resultsPerPage}";
                    $logData["step7"]["data"] = "7.$i {$logMsg}";

                    //style the header and totals rows
                    $styleArray = [
                        'font' => [
                            'bold' => true,
                            'color' => array('argb' => 'FFC5392A'),
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        ],
                        'borders' => [
                            'top' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            ],
                        ],
                    ];
                    $spreadsheet->getActiveSheet()->getStyle('A1:' . $highestColumn . '1')->applyFromArray($styleArray);

                    // //download the file
                    $filename = $clientsession . '_youtube_red_music_video_finance_report_'.$type_table.'_' . $year . '_' . $month . '_' . $i;
                    $xcelfiles[] = $filename;

                    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

                    $writer->save($filename . '.xlsx');

                    $logMsg = "Saved sheet from {$offset} to {$resultsPerPage}";
                    $logData["step7"]["data"] = "7.$i {$logMsg}";
                }
                //save to zip
                $logMsg = "Excels files are ready now genrating zip";
                $logData["step8"]["data"] = "8.$i {$logMsg}";

                $zip_file_tmp = '../../../../excelreports/' . $clientsession . '_youtube_red_music_video_finance_report_'.$type_table.'_' . $year . '_' . $month .$downlaodType_zip. '.zip';
                $zip = new ZipArchive();
                $zip->open($zip_file_tmp, ZipArchive::CREATE);
                foreach ($xcelfiles as $file) {
                    $zip->addFile($file . '.xlsx');
                }
                $zip->close();
                foreach ($xcelfiles as $file) {
                    unlink($file . '.xlsx');
                }
                ob_flush();
                flush();
                $logMsg = "Zip is ready to download";
                $logData["step9"]["data"] = "9.$i {$logMsg}";
            }

            $returnArr["errCode"] = -1;
        }

        //activity_downlaod_report dowlaod export code added 2021-09-10
        if (isset($argv[4])) {
            $zip_file_name = $clientsession . '_youtube_red_music_video_finance_report_'.$type_table.'_' . $year . '_' . $month .$downlaodType_zip. '.zip';
            $process_id = $argv[4];
            $date_end = date("Y-m-d H:i:s");
            $sql_update = "update activity_downlaod_report set date_end='{$date_end}', status_flag='2',status_message='Successfully Exported data' , file_name ='{$zip_file_name}',table_name ='{$table_type_name}' where id='{$process_id}'";
            $update_result = runQuery($sql_update, $conn);
            setContentActivityDownlaodJsonv2('activity_downlaod_reportv2.josn', $argv[4]);
        }
        //end

        //send success email
        $date2 = $date1->diff(new DateTime());
        $importtime = $date2->h . ' hours ' . $date2->i . ' minutes ' . $date2->s . ' seconds';

        $emailMessage = $importSuccessEmailMessage . "<p>Successfully Exported data in " . $importtime;
        $emailMessage .= '<br><a href="' . $rootUrl . $clientsession . "_Youtube_dashboard_" . $year . '_' . $month .$downlaodType_zip. '.zip' . '">Click to Download </a>';
        $emailSubject = "SUCCESS: Youtube data is ready to download of " . $clientsession;

        //find user email 1 alternate email id
        $crep_cms_clients_sql = "SELECT  *   FROM crep_cms_clients  where client_username ='{$clientsession}' ";

        $crep_cms_clients_result = runQuery($crep_cms_clients_sql, $conn);
        $crep_cms_clients_data = mysqli_fetch_assoc($crep_cms_clients_result["dbResource"]);
        if ($crep_cms_clients_data['email1'] != "") {
            $sendEmail = sendMail($crep_cms_clients_data['email1'], $emailSubject, $emailMessage);
        }

        //end of find user email 1 alternate email id

        $sendEmail = sendMail(IMPORTNOTIFIERS, $emailSubject, $emailMessage);
        if (!noError($sendEmail)) {
            //error sending email
            $logMsg = "Mail not sent";
            $logData["step6.1"]["data"] = "6.1. {$logMsg}";
            file_put_contents("testprocesses.php", json_encode($logData) . "\n", FILE_APPEND);
            exit;
        }
        exit;
    } catch (\Throwable $th) {

        //activity_downlaod_report dowlaod export code added 2021-09-10

         //activity_downlaod_report dowlaod export code added 2021-09-10
         if (isset($argv[4])) {
            $process_id = $argv[4];
            $date_end = date("Y-m-d H:i:s");
            $sql_update = "update activity_downlaod_report set date_end='{$date_end}',status_flag=3, status_message='Could not Exported' where id='{$process_id}'";
            $update_result = runQuery($sql_update, $conn);
            setContentActivityDownlaodJsonv2('activity_downlaod_reportv2.josn',$argv[4]);
        }
        //end
        
        //send error email
        $emailMessage = $importFailureEmailMessage . "<p>Could not Exported Youtube red music video finance report</p>" . json_encode($logData);
        $emailSubject = "FAILURE: Youtube red music video finance report is failed to download ";
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
