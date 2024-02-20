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
require_once __ROOT__ . '/model/client/clientDashboardModel.php';

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

    $max_process_allowed = 10;
    $max_total_time_allowed = 100;
    $activity_downlaod_reportv2 = getContentActivityDownlaodJsonv2('activity_downlaod_reportv2.josn');

    // print_r($activity_downlaod_reportv2);
    $get_time_minutes = (int)get_time_minutes($activity_downlaod_reportv2);

  echo   $sql = "select * from activity_downlaod_report where status_flag=1 limit 0,1 ";
    $resultQyery = runQuery($sql, $conn);
        $resultQyeryscheck = mysqli_num_rows($resultQyery["dbResource"]);
 
    if ($resultQyeryscheck > 0) {

        $resultQyerydata = mysqli_fetch_assoc($resultQyery["dbResource"]);
        //  print_r($resultQyerydata);
     echo   $process_id = $resultQyerydata['id'];
//&& $get_time_minutes <= $max_total_time_allowed
        if (!array_key_exists($process_id, $activity_downlaod_reportv2) && count($activity_downlaod_reportv2) <= $max_process_allowed ) {

            echo "<br> in if condition ";
            echo"<br>downlaodType ".       $downlaodType = $resultQyerydata['downlaodType'];
            echo"<br>email ".       $email = $resultQyerydata['email'];
            echo"<br>content_owner ".       $client = $resultQyerydata['content_owner'];
            echo"<br>type_table ".      $type_table = $resultQyerydata['type_table'];
            $date_start = $resultQyerydata['date_start'];
            //  $date_end = $resultQyerydata['date_end'];

            $status_flag = $resultQyerydata['status_flag'];
            $date_added = $resultQyerydata['date_added'];
            echo"<br>status_name ".      $status_name = $resultQyerydata['status_name'];
            $param_data = $resultQyerydata['param_data'];
            echo"<br>selected_date ".     $selected_date = $resultQyerydata['selected_date'];
            echo"<br>controller_name " .      $controller_name = $controller = $resultQyerydata['controller_name'];
         echo"<br>Type ".   $type = $resultQyerydata['type_cate'];

            $array_file[$process_id] = time();
            $activity_downlaod_reportv2[$process_id] = time();

            file_put_contents("activity_downlaod_reportv2.josn", json_encode($activity_downlaod_reportv2));
            @chmod("activity_downlaod_reportv2.josn", 0777);

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

      ///////////////////    

            if ($type == "youtube_video_claim_report") {

                $controller = 'exportReportYoutubev2.php';
            }
            if ($type == "youtuberedmusic") {

                $controller = 'exportReportYoutubeRedmusicv2.php';
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

            if ($type == "applemusic") {

                $controller = 'exportReportAppleMusicv2.php';
            }
            if ($type == "itune") {

                $controller = 'exportReportItuneMusicv2.php';
            }
            if ($type == "gaana") {

                $controller = 'exportReportGaanaMusicv2.php';
            }
            if ($type == "saavan") {

                $controller = 'exportReportSaavanMusicv2.php';
            }
            if ($type == "spotify") {

                $controller = 'exportReportSpotifyMusicv2.php';
            }

            
            $type_table = (isset($type_table)) ? $type_table : 'nd';

            $reporttime = $selected_date;

            //$activity_downlaod_reportv2_encode = json_encode($activity_downlaod_reportv2);
           // echo "param : {$controller} {$reporttime} {$client} {$type_table} {$process_id}";
            $client_en = base64_encode($client);
            $backgroundOutput = runBackgroundProcess("{$controller} {$reporttime} {$client_en} {$type_table} {$process_id} {$downlaodType}");
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

    } else {
        echo "No record";
    }

}
