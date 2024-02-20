<?php
//////////////////////////////Prepare for request/////////////////////////////////
session_start();
  
//require helpers
require_once('../../config/config.php');    
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/auth.php');

//include necessary models
require_once(__ROOT__.'/model/reports/reportsModel.php');
require_once(__ROOT__.'/model/validate/validateModel.php');

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
    $nd = cleanQueryParameter($conn, cleanXSS($_POST["nd"]));
    $selectedDate = cleanQueryParameter($conn, cleanXSS($_POST["reportMonthYear"]));
    $year     = date("Y", strtotime($selectedDate));
    $month    = date("m", strtotime($selectedDate));


    $paramlog['table_name'] = $_POST["report"].'_' .$year.'_'.$month;  
    $paramlog['file_name'] = '';
    $paramlog['status_name'] = "validate-updateContentOwner";
    $paramlog['status_flag'] = "start";
    $paramlog['date_added'] = date("Y-m-d H:i:s");
    $paramlog['ip_address'] = '';
    $paramlog['login_user'] = (isset($_SESSION["userEmail"])) ? $_SESSION["userEmail"] : '';
    $paramlog['log_file'] = '';
    $paramlog['raw_data'] = json_encode($_POST);
  
//v10
//v2 code here 
if ($_POST["report"] == "youtube_whp_report") {
      
    $tablename = 'youtube_whp_report_'.$nd.'_'.$year.'_'.$month; 
    
    $paramlog['table_name'] = $tablename;  
    $usernameaaa = activitylogs($paramlog, $conn);



    $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
    $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
    $tableArr = updateContentOwner($tablename,$contentOwner,$ids, $conn);
    if ($tableArr['errCode'] != '-1') {  
        
        $paramlog['status_flag'] = "error";
        $usernameaaa = activitylogs($paramlog, $conn);

        $returnArr["errCode"] = 4;
        $returnArr["errMsg"] = "Cannot update content owner";
        echo(json_encode($returnArr));
        exit;
    }else{

        $paramlog['status_flag'] = "end";
        $usernameaaa = activitylogs($paramlog, $conn);


        $returnArr["errCode"] = -1;
        $returnArr["errMsg"] = "Updated content owner";
        echo(json_encode($returnArr));
        exit;
    }
}

    if ($_POST["report"] == "youtube_red_finance_report") {
        $tablename = 'youtube_red_finance_report_'.$year.'_'.$month; 
        $paramlog['table_name'] = $tablename;  
        $usernameaaa = activitylogs($paramlog, $conn);

        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
        $tableArr = updateContentOwner($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {            
            
            $paramlog['status_flag'] = "error";
            $usernameaaa = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update content owner";
            echo(json_encode($returnArr));
            exit;
        }else{
            $paramlog['status_flag'] = "end";
            $usernameaaa = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated content owner";
            echo(json_encode($returnArr));
            exit;
        }
    }
    if ($_POST["report"] == "youtube_finance_report") {
        $tablename = 'youtube_finance_report_'.$year.'_'.$month; 

        $paramlog['table_name'] = $tablename;  
        $usernameaaa = activitylogs($paramlog, $conn);


        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
        $tableArr = updateContentOwner($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {   
            
            $paramlog['status_flag'] = "error";
            $usernameaaa = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update content owner";
            echo(json_encode($returnArr));
            exit;
        }else{

            $paramlog['status_flag'] = "end";
            $usernameaaa = activitylogs($paramlog, $conn);

            
            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated content owner";
            echo(json_encode($returnArr));
            exit;
        }
    }
    if ($_POST["report"] == "youtube_audio_finance_report") {
        $tablename = 'youtube_audio_finance_report_'.$year.'_'.$month; 

        $paramlog['table_name'] = $tablename;  
        $usernameaaa = activitylogs($paramlog, $conn);



        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
        $tableArr = updateContentOwner($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') { 
            
            $paramlog['status_flag'] = "error";
            $usernameaaa = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update content owner";
            echo(json_encode($returnArr));
            exit;
        }else{
            $paramlog['status_flag'] = "end";
            $usernameaaa = activitylogs($paramlog, $conn);


            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated content owner";
            echo(json_encode($returnArr));
            exit;
        }
    }

    //v2 code here 
    if ($_POST["report"] == "youtube_labelengine_report") {
      
        $tablename = 'youtube_labelengine_report_'.$nd.'_'.$year.'_'.$month; 
        
        $paramlog['table_name'] = $tablename;  
        $usernameaaa = activitylogs($paramlog, $conn);



        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
        $tableArr = updateContentOwner($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {  
            
            $paramlog['status_flag'] = "error";
            $usernameaaa = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update content owner";
            echo(json_encode($returnArr));
            exit;
        }else{

            $paramlog['status_flag'] = "end";
            $usernameaaa = activitylogs($paramlog, $conn);


            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated content owner";
            echo(json_encode($returnArr));
            exit;
        }
    }

    if ($_POST["report"] == "youtube_ecommerce_paid_features_report") {
      
        $tablename = 'youtube_ecommerce_paid_features_report_'.$nd.'_'.$year.'_'.$month; 

        $paramlog['table_name'] = $tablename;  
        $usernameaaa = activitylogs($paramlog, $conn);



        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
        $tableArr = updateContentOwner($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {    
            
            $paramlog['status_flag'] = "error";
            $usernameaaa = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update content owner";
            echo(json_encode($returnArr));
            exit;
        }else{

            $paramlog['status_flag'] = "end";
            $usernameaaa = activitylogs($paramlog, $conn);


            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated content owner";
            echo(json_encode($returnArr));
            exit;
        }
    }


    if ($_POST["report"] == "youtube_red_music_video_finance_report") {
      
        $tablename = 'youtube_red_music_video_finance_report_'.$nd.'_'.$year.'_'.$month; 
        
        $paramlog['table_name'] = $tablename;  
        $usernameaaa = activitylogs($paramlog, $conn);
 

       
        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
        $tableArr = updateContentOwner($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {       
            
            $paramlog['status_flag'] = "error";
            $usernameaaa = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update content owner";
            echo(json_encode($returnArr));
            exit;
        }else{

            $paramlog['status_flag'] = "end";
            $usernameaaa = activitylogs($paramlog, $conn);


            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated content owner";
            echo(json_encode($returnArr));
            exit;
        }
    }

    if ($_POST["report"] == "youtube_video_claim_report_nd") {

        $tablename = 'youtube_video_claim_report_'.$nd.'_'.$year.'_'.$month; 

        $paramlog['table_name'] = $tablename;  
        $usernameaaa = activitylogs($paramlog, $conn);


        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
        $tableArr = updateContentOwner($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {  
            
            $paramlog['status_flag'] = "error";
            $usernameaaa = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update content owner";
            echo(json_encode($returnArr));
            exit;
        }else{

            $paramlog['status_flag'] = "end";
            $usernameaaa = activitylogs($paramlog, $conn);


            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated content owner";
            echo(json_encode($returnArr));
            exit;
        }
    }


    if ($_POST["report"] == "youtuberedmusic_video_report") {

        $tablename = 'youtuberedmusic_video_report_'.$nd.'_'.$year.'_'.$month; 

        $paramlog['table_name'] = $tablename;  
        $usernameaaa = activitylogs($paramlog, $conn);



        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
        $tableArr = updateContentOwner($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {    
            
            $paramlog['status_flag'] = "error";
            $usernameaaa = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update content owner";
            echo(json_encode($returnArr));
            exit;
        }else{

            $paramlog['status_flag'] = "end";
            $usernameaaa = activitylogs($paramlog, $conn);


            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated content owner";
            echo(json_encode($returnArr));
            exit;
        }
    }


    if ($_POST["report"] == "amazon_video_report") {
        $tablename = 'amazon_video_report_'.$year.'_'.$month; 

        $paramlog['table_name'] = $tablename;  
        $usernameaaa = activitylogs($paramlog, $conn);


        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
        $tableArr = updateContentOwner($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {    
            
            $paramlog['status_flag'] = "error";
            $usernameaaa = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update content owner";
            echo(json_encode($returnArr));
            exit;
        }else{
            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated content owner";
            echo(json_encode($returnArr));
            exit;
        }
    }


    if ($_POST["report"] == "applemusic") {

        $tablename = 'report_audio_'.$nd.'_'.$year.'_'.$month; 

        $paramlog['table_name'] = $tablename;  
        $usernameaaa = activitylogs($paramlog, $conn);


        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
        $tableArr = updateContentOwner($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {  
            
            $paramlog['status_flag'] = "error";
            $usernameaaa = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update content owner";
            echo(json_encode($returnArr));
            exit;
        }else{

            $paramlog['status_flag'] = "end";
            $usernameaaa = activitylogs($paramlog, $conn);


            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated content owner";
            echo(json_encode($returnArr));
            exit;
        }
    }

    if ($_POST["report"] == "itune") {

        $tablename = 'report_audio_'.$nd.'_'.$year.'_'.$month; 

        $paramlog['table_name'] = $tablename;  
        $usernameaaa = activitylogs($paramlog, $conn);


        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
        $tableArr = updateContentOwner($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {  
            
            $paramlog['status_flag'] = "error";
            $usernameaaa = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update content owner";
            echo(json_encode($returnArr));
            exit;
        }else{

            $paramlog['status_flag'] = "end";
            $usernameaaa = activitylogs($paramlog, $conn);


            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated content owner";
            echo(json_encode($returnArr));
            exit;
        }
    }

    if ($_POST["report"] == "saavan") {

        $tablename = 'report_audio_'.$nd.'_'.$year.'_'.$month; 

        $paramlog['table_name'] = $tablename;  
        $usernameaaa = activitylogs($paramlog, $conn);


        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
        $tableArr = updateContentOwner($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {  
            
            $paramlog['status_flag'] = "error";
            $usernameaaa = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update content owner";
            echo(json_encode($returnArr));
            exit;
        }else{

            $paramlog['status_flag'] = "end";
            $usernameaaa = activitylogs($paramlog, $conn);


            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated content owner";
            echo(json_encode($returnArr));
            exit;
        }
    }

    if ($_POST["report"] == "gaana") {

        $tablename = 'report_audio_'.$nd.'_'.$year.'_'.$month; 

        $paramlog['table_name'] = $tablename;  
        $usernameaaa = activitylogs($paramlog, $conn);


        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
        $tableArr = updateContentOwner($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {  
            
            $paramlog['status_flag'] = "error";
            $usernameaaa = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update content owner";
            echo(json_encode($returnArr));
            exit;
        }else{

            $paramlog['status_flag'] = "end";
            $usernameaaa = activitylogs($paramlog, $conn);


            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated content owner";
            echo(json_encode($returnArr));
            exit;
        }
    }


    if ($_POST["report"] == "spotify") {

        $tablename = 'report_audio_'.$nd.'_'.$year.'_'.$month; 

        $paramlog['table_name'] = $tablename;  
        $usernameaaa = activitylogs($paramlog, $conn);


        $ids = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
        $contentOwner = cleanQueryParameter($conn, cleanXSS($_POST["contentOwner"]));
        $tableArr = updateContentOwner($tablename,$contentOwner,$ids, $conn);
        if ($tableArr['errCode'] != '-1') {  
            
            $paramlog['status_flag'] = "error";
            $usernameaaa = activitylogs($paramlog, $conn);

            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update content owner";
            echo(json_encode($returnArr));
            exit;
        }else{

            $paramlog['status_flag'] = "end";
            $usernameaaa = activitylogs($paramlog, $conn);


            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = "Updated content owner";
            echo(json_encode($returnArr));
            exit;
        }
    }


    
    
             $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot update content owner";
            echo(json_encode($returnArr));
            exit;
}
?>