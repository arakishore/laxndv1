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
    if ($_POST["type"] == "labelengine_activation_report")
    {
        $table_name = 'youtube_labelengine_activation_report_'.$nd.'_'.$year.'_'.$month; ;
        $controller = 'exportlabelenginev2.php'; 
    }
    if ($_POST["type"] == "youtuberedmusic_video_report")
    {
        $table_name = 'youtube_redmusic_activation_report_'.$nd.'_'.$year.'_'.$month; ;
        $controller = 'exportyoutubev2.php'; 
    }
     

    if ($_POST["type"] == "youtube_video_claim_activation_report")
    {
        $table_name = 'youtube_video_claim_activation_report_'.$nd.'_'.$year.'_'.$month; ;
        $controller = 'exportyoutubev2.php'; 
    }
     
    if ($_POST["type"] == "youtube_ecommerce_paid_features_activation_report")
    {
        $table_name = 'youtube_ecom_paid_features_activation_report_'.$nd.'_'.$year.'_'.$month; ;
        $controller = 'export_youtube_ecommerce_paid_features_activationv2.php'; 
    }
     
    if ($_POST["type"] == "youtube_red_music_video_finance_activation_report")
    {
        $table_name = 'youtube_red_music_finance_activation_report_'.$nd.'_'.$year.'_'.$month; ;
        $controller = 'export_youtube_red_music_video_finance_activation_reportv2.php'; 
    }
     
    if ($_POST["type"] == "report_audio_activation")
    {
        $table_name = 'report_audio_activation_'.$nd.'_'.$year.'_'.$month; ;
        $controller = 'export_report_audio_activation_v2.php'; 
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
