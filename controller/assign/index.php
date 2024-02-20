<?php
//////////////////////////////Prepare for request/////////////////////////////////
session_start();

//require helpers
require_once '../../config/config.php';
require_once __ROOT__ . '/config/dbUtils.php';
require_once __ROOT__ . '/config/errorMap.php';
require_once __ROOT__ . '/config/auth.php';

//include some more necessary helpers

//include necessary models
require_once __ROOT__ . '/model/reports/reportsModel.php';
require_once __ROOT__ . '/model/validate/validateModel.php';

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
    $nd = cleanQueryParameter($conn, cleanXSS($_POST["cmstype"]));
    $selectedDate = cleanQueryParameter($conn, cleanXSS($_POST["selected_date"]));
    $asset_id = (isset($_POST['asset_id'])) ? cleanQueryParameter($conn, cleanXSS($_POST["asset_id"])) : '';
    $channel_id = (isset($_POST["channel_id"])) ? cleanQueryParameter($conn, cleanXSS($_POST["channel_id"])) :'';
    $content_type = cleanQueryParameter($conn, cleanXSS($_POST["content_type"]));
    $type_cate = cleanQueryParameter($conn, cleanXSS($_POST["type_cate"]));
    $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
    $onlyunassigned = cleanQueryParameter($conn, cleanXSS($_POST["onlyunassigned"]));

    $year = date("Y", strtotime($selectedDate));
    $month = date("m", strtotime($selectedDate));

    $params['contentOwner'] = $contentOwner;
    $params['cmstype'] = $nd;
    $params['selectedDate'] = $selectedDate;
    $params['asset_id'] = $asset_id;
    $params['channel_id'] = $channel_id;
    $params['content_type'] = $content_type;
    $params['type_cate'] = $type_cate;
    $params['year'] = $year;
    $params['month'] = $month;
    $params['onlyunassigned'] = $onlyunassigned;

    $paramlog['table_name'] = $type_cate . '_' . $year . '_' . $month;
    $paramlog['file_name'] = '';
    $paramlog['status_name'] = "assign-updateContentOwner";
    $paramlog['status_flag'] = "start";
    $paramlog['date_added'] = date("Y-m-d H:i:s");
    $paramlog['ip_address'] = '';
    $paramlog['login_user'] = (isset($_SESSION["userEmail"])) ? $_SESSION["userEmail"] : '';
    $paramlog['log_file'] = '';
    $paramlog['raw_data'] = json_encode($_POST);
    
    //initialize logs

    if (empty($nd)) {

        $returnArr["errCode"] = 4;
        $returnArr["errMsg"] = getErrMsg(4) . " Nirvana Disgital type";
        echo (json_encode($returnArr));
        exit;
    }
    if (empty($selectedDate)) {

        $returnArr["errCode"] = 4;
        $returnArr["errMsg"] = getErrMsg(4) . " Date";
        echo (json_encode($returnArr));
        exit;
    }
    if (empty($type_cate)) {

        $returnArr["errCode"] = 4;
        $returnArr["errMsg"] = getErrMsg(4) . " select report";
        echo (json_encode($returnArr));
        exit;
    }
    if (empty($contentOwner)) {
        
        $returnArr["errCode"] = 4;
        $returnArr["errMsg"] = getErrMsg(4) . " select content Owner";
        echo (json_encode($returnArr));
        exit;
    } 
    //v2 code here
    if ($type_cate == "youtube_labelengine_report") {

        $tablename = 'youtube_labelengine_report_' . $nd . '_' . $year . '_' . $month;
        $paramlog['table_name'] = $tablename;

    }

    if ($type_cate == "youtube_ecommerce_paid_features_report") {

        $tablename = 'youtube_ecommerce_paid_features_report_' . $nd . '_' . $year . '_' . $month;
        $paramlog['table_name'] = $tablename;

    }

    if ($type_cate == "youtube_red_music_video_finance_report") {

        $tablename = 'youtube_red_music_video_finance_report_' . $nd . '_' . $year . '_' . $month;
        $paramlog['table_name'] = $tablename;

    }

    if ($type_cate == "youtube_video_claim_report_nd") {

        $tablename = 'youtube_video_claim_report_' . $nd . '_' . $year . '_' . $month;
        $paramlog['table_name'] = $tablename;

    }

    if ($type_cate == "youtuberedmusic_video_report") {

        $tablename = 'youtuberedmusic_video_report_' . $nd . '_' . $year . '_' . $month;

        $paramlog['table_name'] = $tablename;

        //assignupdateContentOwnerv2
    }

    if ($type_cate == "report_audio") {

        $tablename = 'report_audio_' . $nd . '_' . $year . '_' . $month;

        $paramlog['table_name'] = $tablename;

        //assignupdateContentOwnerv2
    }
    $usernameaaa = activitylogs($paramlog, $conn);

  
    $controller = 'bg_index.php'; 
 
    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = "Cannot update content owner  " ;

    $asset_id_en =  ($asset_id!="") ?  base64_encode($asset_id) :'';
    $channel_id_en = ($channel_id!="") ?  base64_encode($channel_id) :'';

    $backgroundOutput = runBackgroundProcess("{$controller} {$selectedDate} {$paramlog['table_name']} {$type_cate} {$nd} {$onlyunassigned} {$contentOwner} '{$asset_id_en}' '{$channel_id_en}' {$content_type}");
    if (!($backgroundOutput > 0))
    {
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 4;
        $returnArr["errMsg"] = "Cannot update content owner ";
        echo (json_encode($returnArr));
        exit;
    }
    else
    {
        $commit = commitTransaction($conn);
        $returnArr["errCode"] = - 1;
        $returnArr["errMsg"] = "content owner assigning in Process. We will notify you via email when it is completed";
        echo (json_encode($returnArr));
        exit;
    }

    echo (json_encode($returnArr));
    exit;

     
    $tableArr = assignupdateContentOwnerv2($tablename, $params, $conn);
    if ($tableArr['errCode'] != '-1') {

        $paramlog['status_flag'] = "error";
        $usernameaaa = activitylogs($paramlog, $conn);

        $returnArr["errCode"] = 4;
        $returnArr["errMsg"] = "Cannot update content owner";
        echo (json_encode($returnArr));
        exit;
    } else {

        $paramlog['status_flag'] = "end";
        $usernameaaa = activitylogs($paramlog, $conn);

        $returnArr["errCode"] = -1;
        $returnArr["errMsg"] = "Updated content owner";
        echo (json_encode($returnArr));
        exit;
    }

    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = "Cannot update content owner";
    echo (json_encode($returnArr));
    exit;
     
}
