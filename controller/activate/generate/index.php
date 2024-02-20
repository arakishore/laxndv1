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
  
require_once(__ROOT__.'/model/reports/youtubeClaimReportsModel.php');
require_once(__ROOT__.'/model/reports/amazonModel.php');
require_once(__ROOT__.'/model/reports/appleModel.php');
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
    $nd = isset($_POST["nd"]) ? cleanQueryParameter($conn, cleanXSS($_POST["nd"])) :'';
    $selectedDate = cleanQueryParameter($conn, cleanXSS($_POST["selected_date"]));
    $year = date("Y", strtotime($selectedDate));
    $month = date("m", strtotime($selectedDate));
    
    $startTransaction = startTransaction($conn);
    if (!noError($startTransaction))
    {

        $returnArr["errCode"] = 3;
        $returnArr["errMsg"] = getErrMsg(3) . " Couldn't start transaction: {$startTransaction["errMsg"]}";

        echo (json_encode($returnArr));
        exit;
    }
    
    if ($_POST["type"] == "youtube_whp_activation_report")
    {
        $controller = 'generateReportYoutubewithHoldingPercentage.php'; 
        
        $tableName = "youtube_whp_report_".$nd."_" . $year . "_" . $month;
        $activatetableName = "youtube_whp_activation_report_" . $year . "_" . $month;
        $tableArr = checkTableExist($activatetableName, $conn);
        if ($tableArr['errMsg'] == '1')
        {
            $truncateTableArr = truncateReportTable($activatetableName, $conn);
            
            if ($truncateTableArr['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        }
        else
        {
            $tableCreateactivation = createActivationYoutubeWithholdingPercentage_v2($activatetableName, $conn);
            if ($tableCreateactivation['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
        }
    }
    if ($_POST["type"] == "youtube_activation_finance_report")
    {
        $controller = 'generateReportYoutube.php'; 
        $tableName = "youtube_finance_report_" . $year . "_" . $month;
        $activatetableName = "youtube_activation_finance_report_" . $year . "_" . $month;
        $tableArr = checkTableExist($activatetableName, $conn);
        if ($tableArr['errMsg'] == '1')
        {
            $truncateTableArr = truncateReportTable($activatetableName, $conn);
            
            if ($truncateTableArr['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        }
        else
        {
            $tableCreateactivation = createActivationCommonReportTable_v3($activatetableName, $conn);
            if ($tableCreateactivation['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
        }
    }
    if ($_POST["type"] == "youtube_activation_red_report")
    {
        $controller = 'generateReportYoutubeRed.php'; 
      //  $tableName = "youtube_finance_report_" . $year . "_" . $month;
        $tableName = "youtube_red_finance_report_" . $year . "_" . $month;
        $activatetableName = "youtube_activation_red_report_" . $year . "_" . $month;
        $tableArr = checkTableExist($activatetableName, $conn);
        if ($tableArr['errMsg'] == '1')
        {
            $truncateTableArr = truncateReportTable($activatetableName, $conn);
            
            if ($truncateTableArr['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        }
        else
        {
            $tableCreateactivation = createActivationCommonReportTable_v3($activatetableName, $conn);
            if ($tableCreateactivation['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
        }
    }

    
    //// v2

    
    if ($_POST["type"] == "report_audio_activation_spotify")
    {
        
        $controller = 'generateReportSpotifyMusicv2.php'; 
                       
        $tableName = "report_audio_".$nd."_" . $year . "_" . $month;
        $activatetableName = "report_audio_activation_".$nd."_" .  $year . "_" . $month;
        $tableArr = checkTableExist($activatetableName, $conn);
        if ($tableArr['errMsg'] == '1')
        {
            $truncateTableArr = truncateReportTable($activatetableName, $conn);
            
            if ($truncateTableArr['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        }
        else
        {
            $tableCreateactivation = createActivationCommonReportTable_v3($activatetableName, $conn);
            if ($tableCreateactivation['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
        }
    }


    if ($_POST["type"] == "report_audio_activation_gaana")
    {
        
        $controller = 'generateReportGaanaMusicv2.php'; 
                       
        $tableName = "report_audio_".$nd."_" . $year . "_" . $month;
        $activatetableName = "report_audio_activation_".$nd."_" .  $year . "_" . $month;
        $tableArr = checkTableExist($activatetableName, $conn);
        if ($tableArr['errMsg'] == '1')
        {
            $truncateTableArr = truncateReportTable($activatetableName, $conn);
            
            if ($truncateTableArr['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        }
        else
        {
            $tableCreateactivation = createActivationCommonReportTable_v3($activatetableName, $conn);
            if ($tableCreateactivation['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
        }
    }


    if ($_POST["type"] == "report_audio_activation_saavan")
    {
        
        $controller = 'generateReportSaavanMusicv2.php'; 
                       
        $tableName = "report_audio_".$nd."_" . $year . "_" . $month;
        $activatetableName = "report_audio_activation_".$nd."_" .  $year . "_" . $month;
        $tableArr = checkTableExist($activatetableName, $conn);
        if ($tableArr['errMsg'] == '1')
        {
            $truncateTableArr = truncateReportTable($activatetableName, $conn);
            
            if ($truncateTableArr['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        }
        else
        {
            $tableCreateactivation = createActivationCommonReportTable_v3($activatetableName, $conn);
            if ($tableCreateactivation['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
        }
    }


    if ($_POST["type"] == "report_audio_activation_itune")
    {
        
        $controller = 'generateReportItuneMusicv2.php'; 
                       
        $tableName = "report_audio_".$nd."_" . $year . "_" . $month;
        $activatetableName = "report_audio_activation_".$nd."_" .  $year . "_" . $month;
        $tableArr = checkTableExist($activatetableName, $conn);
        if ($tableArr['errMsg'] == '1')
        {
            $truncateTableArr = truncateReportTable($activatetableName, $conn);
            
            if ($truncateTableArr['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        }
        else
        {
            $tableCreateactivation = createActivationCommonReportTable_v3($activatetableName, $conn);
            if ($tableCreateactivation['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
        }
    }

    
    if ($_POST["type"] == "report_audio_activation_applemusic")
    {
        
        $controller = 'generateReportAppleMusicv2.php'; 
                       
        $tableName = "report_audio_".$nd."_" . $year . "_" . $month;
        $activatetableName = "report_audio_activation_".$nd."_" .  $year . "_" . $month;
        $tableArr = checkTableExist($activatetableName, $conn);
        if ($tableArr['errMsg'] == '1')
        {
            $truncateTableArr = truncateReportTable($activatetableName, $conn);
            
            if ($truncateTableArr['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        }
        else
        {
            $tableCreateactivation = createActivationCommonReportTable_v3($activatetableName, $conn);
            if ($tableCreateactivation['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
        }
    }

    
    if ($_POST["type"] == "labelengine_activation_report")
    {
        
        $controller = 'generateReportLabelEnginev2.php'; 
                       
    //    $tableName = ($nd=="redmusic") ? 'youtube_labelengine_report_':'youtube_video_claim_report_' ;
        $tableName = 'youtube_labelengine_report_'.$nd."_" . $year . "_" . $month;
        $activatetableName = "youtube_labelengine_activation_report_".$nd."_" .  $year . "_" . $month;
        $tableArr = checkTableExist($activatetableName, $conn);
        if ($tableArr['errMsg'] == '1')
        {
            $truncateTableArr = truncateReportTable($activatetableName, $conn);
            
            if ($truncateTableArr['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        }
        else
        {
            $tableCreateactivation = createActivationCommonReportTable_v3($activatetableName, $conn);
            if ($tableCreateactivation['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
        }
    }

    
    if ($_POST["type"] == "youtubeRedmusic_activation_report")
    {
        
        $controller = 'generateReportYoutubeYoutubeRedMusicv2.php'; 
                       
        $tableName = "youtuberedmusic_video_report_".$nd."_" . $year . "_" . $month;
        $activatetableName = "youtube_redmusic_activation_report_".$nd."_" .  $year . "_" . $month;
        $tableArr = checkTableExist($activatetableName, $conn);
        if ($tableArr['errMsg'] == '1')
        {
            $truncateTableArr = truncateReportTable($activatetableName, $conn);
            
            if ($truncateTableArr['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        }
        else
        {
            $tableCreateactivation = createActivationCommonReportTable_v3($activatetableName, $conn);
            if ($tableCreateactivation['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
        }
    }

    
    if ($_POST["type"] == "youtubeclaimreport_activation_report")
    {
        
        $controller = 'generateReportYoutubeclaimv2.php'; 
                       
        $tableName = "youtube_video_claim_report_".$nd."_" . $year . "_" . $month;
        $activatetableName = "youtube_video_claim_activation_report_".$nd."_" .  $year . "_" . $month;
        $tableArr = checkTableExist($activatetableName, $conn);
        if ($tableArr['errMsg'] == '1')
        {
            $truncateTableArr = truncateReportTable($activatetableName, $conn);
            
            if ($truncateTableArr['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        }
        else
        {
            $tableCreateactivation = createActivationCommonReportTable_v3($activatetableName, $conn);
            if ($tableCreateactivation['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
        }
    }

    
    if ($_POST["type"] == "youtube_ecommerce_paid_features_activation_report")
    {
        
        $controller = 'generateReport_youtube_ecommerce_paid_featuresv2.php'; 
                       
        $tableName = "youtube_ecommerce_paid_features_report_".$nd."_" . $year . "_" . $month;
        $activatetableName = "youtube_ecom_paid_features_activation_report_".$nd."_" .  $year . "_" . $month;
        $tableArr = checkTableExist($activatetableName, $conn);
        if ($tableArr['errMsg'] == '1')
        {
            $truncateTableArr = truncateReportTable($activatetableName, $conn);
            
            if ($truncateTableArr['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        }
        else
        {
            //
            $tableCreateactivation = createActivationCommonReportTable_v3($activatetableName, $conn);
            if ($tableCreateactivation['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
        }
    }


    if ($_POST["type"] == "youtube_red_music_video_finance_activation_report")
    {
        
        $controller = 'generateReport_red_music_video_financev2.php'; 
                       
        $tableName = "youtube_red_music_video_finance_report_".$nd."_" . $year . "_" . $month;
        $activatetableName = "youtube_red_music_finance_activation_report_".$nd."_" .  $year . "_" . $month;
        $tableArr = checkTableExist($activatetableName, $conn);
        if ($tableArr['errMsg'] == '1')
        {
            $truncateTableArr = truncateReportTable($activatetableName, $conn);
            
            if ($truncateTableArr['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        }
        else
        {
            $tableCreateactivation = createActivationCommonReportTable_v3($activatetableName, $conn);
            if ($tableCreateactivation['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
        }
    }
   

    if ($_POST["type"] == "youtube_activation_audio_finance_report")
    {
        $controller = 'generateReportYoutubeAudio.php'; 
        $tableName = "youtube_audio_finance_report_" . $year . "_" . $month;
        $activatetableName = "youtube_activation_audio_report_" . $year . "_" . $month;
        $tableArr = checkTableExist($activatetableName, $conn);
        if ($tableArr['errMsg'] == '1')
        {
            $truncateTableArr = truncateReportTable($activatetableName, $conn);
            
            if ($truncateTableArr['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        }
        else
        {
            $tableCreateactivation = createActivationCommonReportTable_v3($activatetableName, $conn);
            if ($tableCreateactivation['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
        }
    }
 

    if ($_POST["type"] == "amazon_video_activation_report")
    {
        $controller = 'generateReportAmazon.php'; 
        $tableName = "amazon_video_report_" . $year . "_" . $month;
        $activatetableName = "amazon_video_activation_report_" . $year . "_" . $month;
        $paymentTableName = "amazon_video_payment_report_" . $year . "_" . $month;
        $tableArr = checkTableExist($activatetableName, $conn);
        $tableArr1 = checkTableExist($paymentTableName, $conn);
        if ($tableArr['errMsg'] == '1')
        {
            $truncateTableArr = truncateReportTable($activatetableName, $conn);
            
            if ($truncateTableArr['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        }  
        else
        {
            $tableCreateactivation = createActivationCommonReportTable_v3($activatetableName, $conn);
            if ($tableCreateactivation['errCode'] != '-1')
            {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
        }
    }
    $logedinemail = $_SESSION["userEmail"];

    $backgroundOutput = runBackgroundProcess("{$controller} {$tableName} {$activatetableName} {$logedinemail}");


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
        $returnArr["errMsg"] = "Activation report generating. We will notify you via email when it is completed  ";
        echo (json_encode($returnArr));
        exit;
    }

    printArr($insertInfoArr);
    exit;

}

?>