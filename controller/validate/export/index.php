<?php
//////////////////////////////Prepare for request/////////////////////////////////
session_start();

//require helpers
require_once ('../../../config/config.php');
 
require_once (__ROOT__ . '/config/dbUtils.php');
require_once (__ROOT__ . '/config/errorMap.php');
require_once (__ROOT__ . '/config/auth.php');

//include necessary models
require_once (__ROOT__ . '/model/reports/reportsModel.php');
require_once (__ROOT__ . '/model/reports/youtubeVideoModel.php');
require_once (__ROOT__ . '/model/reports/youtubeRedFinanceModel.php');
require_once (__ROOT__ . '/model/reports/youtubeRedModel.php');
require_once (__ROOT__ . '/model/activate/activateModel.php');
//TO DO: Logs
$returnArr = array();
$fileLocation = '';
$controller = '';
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn))
{
    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = "Error Connecting to DB";
    echo (json_encode($returnArr));
    exit;
}
else
{
    //db connection successful
    $conn = $conn["errMsg"];
    // printArr($_POST); exit;
   
    $selectedDate = "";
    if (isset($_POST["selected_date"])) {
        $selectedDate = cleanQueryParameter($conn, cleanXSS($_POST["selected_date"]));
    }
   $nd  = "";
   if (isset($_POST["nd"])) {
    $nd = cleanQueryParameter($conn, cleanXSS($_POST["nd"]));
    }

   $year     = date("Y", strtotime($selectedDate));
   $month    = date("m", strtotime($selectedDate));
  
  
    $startTransaction = startTransaction($conn);
    if (!noError($startTransaction))
    {

        $returnArr["errCode"] = 3;
        $returnArr["errMsg"] = getErrMsg(3) . " Couldn't start transaction: {$startTransaction["errMsg"]}";

        echo (json_encode($returnArr));
        exit;
    }

    if ($_POST["type"] == "youtube_labelengine_report")
    {
        $table_name = 'youtube_labelengine_report_'.$nd.'_'.$year.'_'.$month; ;
        $controller = 'exportyoutube_labelengine_reportv2.php'; 
    }

    if ($_POST["type"] == "youtuberedmusic_video_report")
    {
        $table_name = 'youtuberedmusic_video_report_'.$nd.'_'.$year.'_'.$month; ;
        $controller = 'exportyoutubeRedMusicv2.php'; 
    }

    if ($_POST["type"] == "youtube_video_claim_report")
    {
        $table_name = 'youtube_video_claim_report_'.$nd.'_'.$year.'_'.$month; ;
        $controller = 'exportyoutubev2.php'; 
    }
    if ($_POST["type"] == "youtube_red_music_video_finance_report")
    {
        $table_name = 'youtube_red_music_video_finance_report_'.$nd.'_'.$year.'_'.$month; ;
        $controller = 'exportyoutube_red_music_video_finance_reportv2.php'; 
    }
     
    if ($_POST["type"] == "audio_applemusic")
    {
        $table_name =  'report_audio_'.$nd.'_'.$year.'_'.$month; ;
        $controller = 'exportyoutube_applemusic_reportv2.php'; 
    }
    if ($_POST["type"] == "audio_itune")
    {
        $table_name =  'report_audio_'.$nd.'_'.$year.'_'.$month; ;
        $controller = 'exportyoutube_itune_reportv2.php'; 
    }
    if ($_POST["type"] == "audio_gaana")
    {
        $table_name =  'report_audio_'.$nd.'_'.$year.'_'.$month; ;
        $controller = 'export_gaana_reportv2.php'; 
    }
    if ($_POST["type"] == "audio_saavan")
    {
        $table_name =  'report_audio_'.$nd.'_'.$year.'_'.$month; ;
        $controller = 'export_saavan_reportv2.php'; 
    }

    if ($_POST["type"] == "audio_spotify")
    {
        $table_name =  'report_audio_'.$nd.'_'.$year.'_'.$month; ;
        $controller = 'export_spotify_reportv2.php'; 
    }

    $reporttime = $_POST["selected_date"];

  
    $backgroundOutput = runBackgroundProcess("{$controller} {$reporttime} {$table_name}");
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
    exit;

}

?>
