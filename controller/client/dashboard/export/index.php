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
    $selectedDate = cleanQueryParameter($conn, cleanXSS($_POST["selected_date"]));
    $year = date("Y", strtotime($selectedDate));
    $month = date("m", strtotime($selectedDate));

    $type_table = (isset($_POST['type_table'])) ? $_POST['type_table'] : 'nd';
    
    $downlaodType = (isset($_POST['downlaodType'])) ? $_POST['downlaodType'] : 'normal';


    $startTransaction = startTransaction($conn);
    if (!noError($startTransaction)) {

        $returnArr["errCode"] = 3;
        $returnArr["errMsg"] = getErrMsg(3) . " Couldn't start transaction: {$startTransaction["errMsg"]}";

        echo (json_encode($returnArr));
        exit;
    }

    if ($_POST["type"] == "youtube_finance_report_co_dashboard") {
        $title_name = "Youtube Claim";
        $controller = 'exportReportYoutube.php';
    }
    if ($_POST["type"] == "youtube_red_report_co_dashboard") {
        $title_name = "Youtube Claim v2.0";
        $controller = 'generateReportYoutubeRed.php';

    }

    if ($_POST["type"] == "amazon_video_report_co_dashboard") {
        $title_name = "Youtube Claim v2.0";
        $controller = 'exportReportAmazon.php';

    }
//////////////
    if ($_POST["type"] == "youtuberedmusic") {
        $title_name = "Youtube Red Music";
        $controller = 'exportReportYoutubeRedmusicv2.php';
        $table_type_name = "youtuberedmusic_video_report_redmusic%". $year . '_' . $month;
    }

    if ($_POST["type"] == "youtube_video_claim_report") {
        $title_name = "Youtube Claim report";
        $controller = 'exportReportYoutubev2.php';
        $table_type_name = "youtube_video_claim_report_nd%". $year . '_' . $month;
    }
    if ($_POST["type"] == "youtubeecommercepaidfeaturesv2") {
        $title_name = "Youtube ecommerce paid features report";
        $controller = 'exportyoutubeecommercepaidfeaturesv2.php';
        $table_type_name = "youtube_ecommerce_paid_features_report_".$type_table."%_".$year."_".$month;

    }
    if ($_POST["type"] == "youtube_red_music_video_finance") {
        $title_name = "Youtube red music video finance report";
        $controller = 'exportyoutube_red_music_video_financev2.php';
        $table_type_name  = 'youtube_red_music_video_finance_report_'.$type_table.'%'.$year.'_'.$month;

    }

    if ($_POST["type"] == "youtubeusreport") {
        $title_name = "YouTube US Report";
        $controller = 'exportReportYoutubeUsReportv2.php';
        $table_type_name  = 'youtube_labelengine_report_'.$type_table.'%'.$year.'_'.$month;
    }

    if ($_POST["type"] == "applemusic") {
        $title_name = "Apple Music";
        $controller = 'exportReportAppleMusicv2.php';
        $table_type_name  = 'report_audio_'.$type_table.'_'.$year.'_'.$month;
    }

    if ($_POST["type"] == "itune") {
        $title_name = "Itune Music";
        $controller = 'exportReportItuneMusicv2.php';
        $table_type_name  = 'report_audio_'.$type_table.'_'.$year.'_'.$month;
    }
    if ($_POST["type"] == "saavan") {
        $title_name = "Saavan Music";
        $controller = 'exportReportSaavanMusicv2.php';
        $table_type_name  = 'report_audio_'.$type_table.'_'.$year.'_'.$month;
    }
    if ($_POST["type"] == "gaana") {
        $title_name = "Gaana Music";
        $controller = 'exportReportGaanaMusicv2.php';
        $table_type_name  = 'report_audio_'.$type_table.'_'.$year.'_'.$month;
    }
    
    if ($_POST["type"] == "spotify") {
        $title_name = "Spotify Music";
        $controller = 'exportReportSpotifyMusicv2.php';
        $table_type_name  = 'report_audio_'.$type_table.'_'.$year.'_'.$month;
    }
    
    $reporttime = $_POST["selected_date"];
    $client = $_SESSION["client"];

    //add_param in activity_downlaod_report table for cron job

    $param_data_temp['post'] = $_POST;
    $param_data_temp['session'] = $_SESSION;

    $status_flag = '1';
    $status_message = 'Exporting in Process';
    $date_added = date('Y-m-d H:i:s');
    $date_start = $date_added;
    $table_name = "";
    $file_name = "";
    //$table_type_name = "";
    $status_name = 'export-report';
    $param_data = json_encode($param_data_temp);
    $email = $_SESSION['userEmail'];
    $type_cate = $_POST["type"];

    $controller_name = $controller;

    //$query = "INSERT INTO `activity_downlaod_report` ( `content_owner`,`email`, `type_table`, `date_start`, `date_end`, `status_flag`, `date_added`, `table_name`, file_name,table_type_name,`status_name`, `param_data`,  `selected_date`, `controller_name`,type_cate,title_name,status_message) VALUES ( '{$client}', '{$email}', '{$type_table}', '{$date_start}', NULL, '{$status_flag}', '{$date_added}', '{$table_name}', '{$file_name}', '{$table_type_name}', '{$status_name}', '{$param_data}',   '{$reporttime}' , '{$controller_name}', '{$type_cate}','{$title_name}','{$status_message}')";

    $query = "INSERT INTO `activity_downlaod_report` ( `content_owner`,`email`, `type_table`, `date_start`, `date_end`, `status_flag`, `date_added`, `table_name`, file_name,table_type_name,`status_name`, `param_data`,  `selected_date`, `controller_name`,type_cate,title_name,status_message,downlaodType) VALUES ( '{$client}', '{$email}', '{$type_table}', '{$date_start}', NULL, '{$status_flag}', '{$date_added}', '{$table_name}', '{$file_name}', '{$table_type_name}', '{$status_name}', '{$param_data}',   '{$reporttime}' , '{$controller_name}', '{$type_cate}','{$title_name}','{$status_message}','{$downlaodType}')";
    

    $queryresult = runQuery($query, $conn);

    $commit = commitTransaction($conn);
    $returnArr["errCode"] = -1;
    $returnArr["errMsg"] = "Exporting in Process. We will notify you via email when it is completed";
    echo (json_encode($returnArr));
    exit;
    /*
$backgroundOutput = runBackgroundProcess("{$controller} {$reporttime} {$client} {$type_table}");
if (!($backgroundOutput > 0))
{
$rollback = rollbackTransaction($conn);
$returnArr["errCode"] = 4;
$returnArr["errMsg"] = "Couldn't genrate report " . $file;
echo (json_encode($returnArr));
exit;
}
else
{
$commit = commitTransaction($conn);
$returnArr["errCode"] = - 1;
$returnArr["errMsg"] = "Exporting in Process. We will notify you via email when it is completed";
echo (json_encode($returnArr));
exit;
}

printArr($insertInfoArr);
exit; */

}
