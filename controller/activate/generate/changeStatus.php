<?php
//////////////////////////////Prepare for request/////////////////////////////////
session_start();

//require helpers
require_once('../../../config/config.php');    
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/auth.php');

//include necessary models
require_once(__ROOT__.'/model/reports/reportsModel.php');
 
require_once(__ROOT__.'/model/activate/activateModel.php');
 
//TO DO: Logs

$returnArr = array();
$fileLocation = '';
$controller = '';
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = "Error Connecting to DB";
    echo(json_encode($returnArr));
    exit;
} else {
    //db connection successful
    $conn = $conn["errMsg"];
    // printArr($_POST); exit;
    $selectedDate = cleanQueryParameter($conn, cleanXSS($_POST["reportMonthYear"]));
    $nd = cleanQueryParameter($conn, cleanXSS($_POST["nd"]));
    $year     = date("Y", strtotime($selectedDate));
    $month    = date("m", strtotime($selectedDate));


    if ($_POST["report"] == "report_audio_activation") {
        $tablename = 'report_audio_activation_'.$nd.'_'.$year.'_'.$month; 
        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["status"]));
        
        $tableArr = updateStatus($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {                
            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update status";
            echo(json_encode($returnArr));
            exit;
        }else{
            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated status";
            echo(json_encode($returnArr));
            exit;
        }
    }

    if ($_POST["report"] == "youtubeRedmusic_activation_report") {
        $tablename = 'youtube_redmusic_activation_report_'.$nd.'_'.$year.'_'.$month; 
        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["status"]));
        
        $tableArr = updateStatus($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {                
            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update status";
            echo(json_encode($returnArr));
            exit;
        }else{
            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated status";
            echo(json_encode($returnArr));
            exit;
        }
    }
    if ($_POST["report"] == "labelengine_activation_report") {
        $tablename = 'youtube_labelengine_activation_report_'.$nd.'_'.$year.'_'.$month; 
        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["status"]));
        
        $tableArr = updateStatus($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {                
            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update status";
            echo(json_encode($returnArr));
            exit;
        }else{
            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated status";
            echo(json_encode($returnArr));
            exit;
        }
    }

    if ($_POST["report"] == "youtube_video_claim_activation_report") {
        $tablename = 'youtube_video_claim_activation_report_'.$nd.'_'.$year.'_'.$month; 
        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["status"]));
        
        $tableArr = updateStatus($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {                
            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update status";
            echo(json_encode($returnArr));
            exit;
        }else{
            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated status";
            echo(json_encode($returnArr));
            exit;
        }
    }
    if ($_POST["report"] == "youtube_ecommerce_paid_features_activation_report") {
        $tablename = 'youtube_ecom_paid_features_activation_report_'.$nd.'_'.$year.'_'.$month; 
        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["status"]));
        
        $tableArr = updateStatus($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {                
            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update status";
            echo(json_encode($returnArr));
            exit;
        }else{
            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated status";
            echo(json_encode($returnArr));
            exit;
        }
    }
    if ($_POST["report"] == "youtube_red_music_video_finance_activation_report") {
        $tablename = 'youtube_red_music_finance_activation_report_'.$nd.'_'.$year.'_'.$month; 
        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["status"]));
        
        $tableArr = updateStatus($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {                
            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update status";
            echo(json_encode($returnArr));
            exit;
        }else{
            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated status";
            echo(json_encode($returnArr));
            exit;
        }
    }

    
    if ($_POST["report"] == "youtube_activation_finance_report") {
        $tablename = 'youtube_activation_finance_report_'.$year.'_'.$month; 
        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["status"]));
        
        $tableArr = updateStatus($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {                
            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update status";
            echo(json_encode($returnArr));
            exit;
        }else{
            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated status";
            echo(json_encode($returnArr));
            exit;
        }
    }
    if ($_POST["report"] == "youtube_activation_red_report") {
        $tablename = 'youtube_activation_red_report_'.$year.'_'.$month; 
        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["status"]));
        
        $tableArr = updateStatus($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {                
            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update status";
            echo(json_encode($returnArr));
            exit;
        }else{
            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated status";
            echo(json_encode($returnArr));
            exit;
        }
    }

    if ($_POST["report"] == "youtube_activation_audio_report") {
        $tablename = 'youtube_activation_audio_report_'.$year.'_'.$month; 
        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["status"]));
        
        $tableArr = updateStatus($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {                
            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update status";
            echo(json_encode($returnArr));
            exit;
        }else{
            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated status";
            echo(json_encode($returnArr));
            exit;
        }
    }
    exit;
}
?>