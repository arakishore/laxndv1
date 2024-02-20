<?php
//////////////////////////////Prepare for request/////////////////////////////////
session_start();

//require helpers
require_once '../../../../config/config.php';
 
require_once __ROOT__ . '/config/dbUtils.php';
require_once __ROOT__ . '/config/errorMap.php';
require_once __ROOT__ . '/config/auth.php';

//include necessary models
require_once __ROOT__ . '/model/reports/reportsModel.php';
require_once __ROOT__ . '/model/reports/youtubeVideoModel.php';
require_once __ROOT__ . '/model/reports/youtubeRedFinanceModel.php';
require_once __ROOT__ . '/model/reports/youtubeRedModel.php';
require_once __ROOT__ . '/model/activate/activateModel.php';
//TO DO: Logs
$returnArr = array();
$fileLocation = '';
$controller = '';
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = "Error Connecting to DB";
    echo (json_encode($returnArr));
    exit;
} else {
    //db connection successful
    $conn = $conn["errMsg"];
    // printArr($_POST); exit;
   
   
    $activity_downlaod_report_file = @file_get_contents('activity_downlaod_report.txt');
 
    
    $sql = "select * from activity_downlaod_report where status_flag=1 limit 0,1 ";
    $resultQyery = runQuery($sql, $conn);
    $resultQyeryscheck = mysqli_num_rows($resultQyery["dbResource"]);
   
    if ($resultQyeryscheck > 0) {

        $resultQyerydata = mysqli_fetch_assoc($resultQyery["dbResource"]);
       //  print_r($resultQyerydata);
        $process_id = $resultQyerydata['id'];

        $activity_downlaod_report_file  = (int)$activity_downlaod_report_file;

        if($activity_downlaod_report_file != $process_id){

            $email = $resultQyerydata['email'];
            $client = $resultQyerydata['content_owner'];
            $type_table = $resultQyerydata['type_table'];
            $date_start = $resultQyerydata['date_start'];
          //  $date_end = $resultQyerydata['date_end'];
    
            $status_flag = $resultQyerydata['status_flag'];
            $date_added = $resultQyerydata['date_added'];
            $status_name = $resultQyerydata['status_name'];
            $param_data = $resultQyerydata['param_data'];
            $selected_date = $resultQyerydata['selected_date'];
            $controller_name = $resultQyerydata['controller_name'];
            $type = $resultQyerydata['type_cate'];
    
    
            file_put_contents("activity_downlaod_report.txt", $process_id);
            @chmod("activity_downlaod_report.txt",0777); 
    
            $selectedDate = cleanQueryParameter($conn, cleanXSS($selected_date));
            $year = date("Y", strtotime($selectedDate));
            $month = date("m", strtotime($selectedDate));
    
            $startTransaction = startTransaction($conn);
            if (!noError($startTransaction)) {
    
                $returnArr["errCode"] = 3;
                $returnArr["errMsg"] = getErrMsg(3) . " Couldn't start transaction: {$startTransaction["errMsg"]}";
    
                echo (json_encode($returnArr));
                exit;
            }
    
            if ($type == "youtube_finance_report_co_dashboard") {
    
                $controller = 'exportReportYoutube.php';
            }
            if ($type == "youtube_red_report_co_dashboard") {
                $controller = 'generateReportYoutubeRed.php';
    
            }
    
            if ($type == "amazon_video_report_co_dashboard") {
                $controller = 'exportReportAmazon.php';
    
            }
    
            if ($type == "youtuberedmusic") {
    
                $controller = 'exportReportYoutubeRedmusicv2.php';
            }
    
            if ($type == "youtube_video_claim_report") {
    
                $controller = 'exportReportYoutubev2.php';
            }
            if ($type == "youtubeecommercepaidfeaturesv2") {
                $controller = 'exportyoutubeecommercepaidfeaturesv2.php';
    
            }
            if ($type == "youtube_red_music_video_finance") {
                $controller = 'exportyoutube_red_music_video_financev2.php';
    
            }
    
            if ($type == "youtubeusreport") {
    
                $controller = 'exportReportYoutubeUsReportv2.php';
            }
    
            $type_table = (isset($type_table)) ? $type_table : 'nd';
    
            $reporttime = $selected_date;
             
    
            
            $backgroundOutput = runBackgroundProcess("{$controller} {$reporttime} {$client} {$type_table} {$process_id}");
            if (!($backgroundOutput > 0)) {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't genrate report " . $file;
                echo (json_encode($returnArr));
                exit;
            } else {
                $commit = commitTransaction($conn);
                $returnArr["errCode"] = -1;
                $returnArr["errMsg"] = "Exporting in Process";
                echo (json_encode($returnArr));
                exit;
            }
    
            printArr($insertInfoArr);
            exit;
        }
        
    }

}
