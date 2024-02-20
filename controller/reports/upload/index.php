<?php
//////////////////////////////Prepare for request/////////////////////////////////
session_start();

//require helpers
require_once '../../../config/config.php';

require_once __ROOT__ . '/config/dbUtils.php';
require_once __ROOT__ . '/config/errorMap.php';
require_once __ROOT__ . '/config/auth.php';

//include necessary models

require_once __ROOT__ . '/model/reports/reportsModel.php';

require_once __ROOT__ . '/model/reports/youtubeVideoModel.php';
require_once __ROOT__ . '/model/reports/youtubeRedFinanceModel.php';
require_once __ROOT__ . '/model/reports/youtubeRedModel.php';
require_once __ROOT__ . '/model/reports/youtubeAudioFinanceModel.php';
require_once __ROOT__ . '/model/reports/youtubeAudioRedModel.php';

require_once __ROOT__ . '/model/reports/youtubeClaimReportsModel.php';
require_once __ROOT__ . '/model/reports/amazonModel.php';

require_once __ROOT__ . '/model/reports/appleModel.php';

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
    //echo $selectedDate;exit;
    $startTransaction = startTransaction($conn);
    if (!noError($startTransaction)) {

        $returnArr["errCode"] = 3;
        $returnArr["errMsg"] = getErrMsg(3) . " Couldn't start transaction: {$startTransaction["errMsg"]}";

        echo (json_encode($returnArr));
        exit;
    }

////////////////code start from here v2.0//////////////////////

    if ($_POST["type"] == "youtube_ecommerce_paid_features_report_file_upload") {
        $fileLocation = '/var/lib/mysql-files/youtube_ecommerce_paid_features/';
        $nd = $_POST['nd'];

        $youtubeUploadFilename = cleanQueryParameter($conn, cleanXSS($_POST["csv_files"]));
        $tableExist = 0;

        if ($nd == "nd1") {
            $controller = 'importyoutube_ecommerce_paid_featuresv2.php';
            $tableName = 'youtube_ecommerce_paid_features_report_' . $nd . '_' . $year . '_' . $month;
        } else {
            $controller = 'importyoutube_ecommerce_paid_featuresv2.php';
            $tableName = 'youtube_ecommerce_paid_features_report_' . $nd . '_' . $year . '_' . $month;
        }

        $tableArr = checkTableExist($tableName, $conn);
        if ($tableArr['errMsg'] == '1') {
            $truncateTableArr = truncateReportTable($tableName, $conn);
            if ($truncateTableArr['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        } else {
            if ($nd == "nd1") {
                $tableCreate = create_youtube_ecommerce_paid_features_report_Tablev2($tableName, $conn);
            } else {
                $tableCreate = create_youtube_ecommerce_paid_features_report_Tablev2($tableName, $conn);
            }

            // $tableCreate = create_youtube_ecommerce_paid_features_report_Tablev2($tableName, $conn);

            if ($tableCreate['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }

        }
    }

    if ($_POST["type"] == "youtube_red_music_video_finance_report_file_upload") {
        $fileLocation = '/var/lib/mysql-files/youtube_red_finance_report/';
        $nd = $_POST['nd'];

        $youtubeUploadFilename = cleanQueryParameter($conn, cleanXSS($_POST["csv_files"]));

        if ($nd == "redmusic") {
            $controller = 'importReportYoutubeRedMusicFinancev2.php';
            $tableName = "youtube_red_music_video_finance_report_" . $nd . "_" . $year . "_" . $month;
        } else {
            $controller = 'importReportYoutubeRedFinancev2.php';
            $tableName = "youtube_red_music_video_finance_report_" . $nd . "_" . $year . "_" . $month;
        }

        $tableArr = checkTableExist($tableName, $conn);
        if ($tableArr['errMsg'] == '1') {
            $truncateTableArr = truncateReportTable($tableName, $conn);
            if ($truncateTableArr['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        } else {

            if ($nd == "redmusic") {
                $tableCreate = createYoutubeRedFinanceReportTableRedMusicv2($tableName, $conn);
            } else {
                $tableCreate = createYoutubeRedFinanceReportTablev2($tableName, $conn);
            }

            if ($tableCreate['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }

        }
    }

    if ($_POST["type"] == "youtube_video_claim_report_file_upload") {
        $fileLocation = '/var/lib/mysql-files/youtube_video_claim_report/';
        $nd = $_POST['nd'];

        $youtubeUploadFilename = cleanQueryParameter($conn, cleanXSS($_POST["csv_files"]));
/*     $files = explode(',', $youtubeUploadFilename);
if (count($files) > 0) {
foreach($files as $key => $file){

$checkFileexist =   checkFileexist($fileLocation.$file);

if ($checkFileexist['errCode'] != '-1') {

echo(json_encode($checkFileexist));
exit;
}
}

} */
        $tableExist = 0;
        if ($nd == "redmusic") {
            $controller = 'importReportYoutubeClaimredMusic.php';
            $tableName = "youtuberedmusic_video_report_" . $nd . "_" . $year . "_" . $month;
        } else {
            $controller = 'importReportYoutubeClaim.php';
            $tableName = "youtube_video_claim_report_" . $nd . "_" . $year . "_" . $month;
        }

        $tableArr = checkTableExist($tableName, $conn);
        if ($tableArr['errMsg'] == '1') {

            //need to uncomment this AIMS
            $truncateTableArr = truncateReportTable($tableName, $conn);
            if ($truncateTableArr['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        } else {
            if ($nd == "redmusic") {
                $tableCreate = createYoutubeRedMusicReportTable($tableName, $conn);
            } else {
                $tableCreate = createYoutubeVideoClaimReportTable($tableName, $conn);
            }

            if ($tableCreate['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }

        }
    }

    if ($_POST["type"] == "youtube_video_claim_report_2_file_upload") {
        $fileLocation = '/var/lib/mysql-files/youtube_video_claim_report/';
        $nd = $_POST['nd'];

        $tableExist = 0;

        if ($nd == "redmusic") {
            $controller = 'importReportYoutubeClaim2.php';
            $tableName1 = "youtuberedmusic_video_report_" . $nd . "_" . $year . "_" . $month;
            $tableName = "youtuberedmusic_video_report2_" . $nd . "_" . $year . "_" . $month;
        } else {
            $controller = 'importReportYoutubeClaim2.php';
            $tableName1 = "youtube_video_claim_report_" . $nd . "_" . $year . "_" . $month;
            $tableName = "youtube_video_claim_report2_" . $nd . "_" . $year . "_" . $month;
        }

        $youtubeUploadFilename = cleanQueryParameter($conn, cleanXSS($_POST["csv_files"]));

        $tableArr = checkTableExist($tableName1, $conn);
        if ($tableArr['errMsg'] != '1') {
            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Table " . $tableName1 . " does not exist, Please uplaod first  step1 csv file";
            echo (json_encode($returnArr));
            exit;
        } else {
            $tableExist = 0;

            $tableArr = checkTableExist($tableName, $conn);
            if ($tableArr['errMsg'] == '1') {
                $truncateTableArr = truncateReportTable($tableName, $conn);
                if ($truncateTableArr['errCode'] != '-1') {
                    $rollback = rollbackTransaction($conn);
                    $returnArr["errCode"] = 4;
                    $returnArr["errMsg"] = "Cannot truncate table";
                    echo (json_encode($returnArr));
                    exit;
                }
            } else {
                $tableCreate = createYoutubeVideoClaimReportTable2($tableName, $conn);

                if ($tableCreate['errCode'] != '-1') {
                    $rollback = rollbackTransaction($conn);
                    $returnArr["errCode"] = 4;
                    $returnArr["errMsg"] = "Couldn't create table";
                    echo (json_encode($returnArr));
                    exit;
                }

            }
        }

    }

    if ($_POST["type"] == "youtube_video_claim_report_3_file_upload") {
        $fileLocation = '/var/lib/mysql-files/youtube_video_claim_report/';
        $nd = $_POST['nd'];

        $youtubeUploadFilename = cleanQueryParameter($conn, cleanXSS($_POST["csv_files"]));
        $tableExist = 0;

        if ($nd == "redmusic") {
            $controller = 'importReportYoutubeClaimredMusic3.php';
            $tableName1 = "youtuberedmusic_video_report_" . $nd . "_" . $year . "_" . $month;
            $tableName = "youtuberedmusic_video_report3_" . $nd . "_" . $year . "_" . $month;
        } else {
            $controller = 'importReportYoutubeClaim3.php';
            $tableName1 = "youtube_video_claim_report_" . $nd . "_" . $year . "_" . $month;
            $tableName = "youtube_video_claim_report3_" . $nd . "_" . $year . "_" . $month;
        }

        $tableArr = checkTableExist($tableName1, $conn);
        if ($tableArr['errMsg'] != '1') {
            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Table " . $tableName1 . " does not exist, Please uplaod first youtube_video_claim_report step2 csv file";
            echo (json_encode($returnArr));
            exit;
        } else {
            $tableExist = 0;

            $tableArr = checkTableExist($tableName, $conn);
            if ($tableArr['errMsg'] == '1') {
                $truncateTableArr = truncateReportTable($tableName, $conn);
                if ($truncateTableArr['errCode'] != '-1') {
                    $rollback = rollbackTransaction($conn);
                    $returnArr["errCode"] = 4;
                    $returnArr["errMsg"] = "Cannot truncate table";
                    echo (json_encode($returnArr));
                    exit;
                }
            } else {
                if ($nd == "redmusic") {
                    $tableCreate = createYoutubeRedMusicReportTable3($tableName, $conn);
                } else {
                    $tableCreate = createYoutubeVideoClaimReportTable3($tableName, $conn);
                }

                // $tableCreate = createYoutubeVideoClaimReportTable3($tableName, $conn);

                if ($tableCreate['errCode'] != '-1') {
                    $rollback = rollbackTransaction($conn);
                    $returnArr["errCode"] = 4;
                    $returnArr["errMsg"] = "Couldn't create table";
                    echo (json_encode($returnArr));
                    exit;
                }

            }
        }

    }

    if ($_POST["type"] == "youtube_labelengine_report_file_upload") {
        $fileLocation = '/var/lib/mysql-files/youtube_video_claim_report/';
        $nd = $_POST['nd'];

        $youtubeUploadFilename = cleanQueryParameter($conn, cleanXSS($_POST["csv_files"]));
        $tableExist = 0;

        $controller = 'importyoutube_labelenginev2.php';
        $tableName = 'youtube_labelengine_report_' . $nd . '_' . $year . '_' . $month;

        $tableArr = checkTableExist($tableName, $conn);
        if ($tableArr['errMsg'] == '1') {
            $truncateTableArr = truncateReportTable($tableName, $conn);
            if ($truncateTableArr['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        } else {
            $tableCreate = create_youtube_labelengine_report_Tablev2($tableName, $conn);

            if ($tableCreate['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }

        }
    }

//apple_music

    if ($_POST["type"] == "applemusic_report_file_upload") {
        $fileLocation = '/var/lib/mysql-files/music_reports/';
        $nd = $_POST['nd'];

        $youtubeUploadFilename = cleanQueryParameter($conn, cleanXSS($_POST["csv_files"]));
        $tableExist = 0;

        $controller = 'importReportAppleMusic.php';
        $tableName = 'report_audio_' . $nd . '_' . $year . '_' . $month;

        $tableArr = checkTableExist($tableName, $conn);
        if ($tableArr['errMsg'] == '1') {
            $truncateTableArr = truncateReportTable($tableName, $conn);
            if ($truncateTableArr['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        } else {
            $tableCreate = create_appleMusic_report_Tablev2($tableName, $conn);

            if ($tableCreate['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }

        }
    }

//itune

    if ($_POST["type"] == "itunemusic_report_file_upload") {
        $fileLocation = '/var/lib/mysql-files/music_reports/';
        $nd = $_POST['nd'];

        $youtubeUploadFilename = cleanQueryParameter($conn, cleanXSS($_POST["csv_files"]));
        $tableExist = 0;

        $controller = 'importReportItuneMusic.php';
        $tableName = 'report_audio_' . $nd . '_' . $year . '_' . $month;

        $tableArr = checkTableExist($tableName, $conn);
        if ($tableArr['errMsg'] == '1') {
            $truncateTableArr = truncateReportTable($tableName, $conn);
            if ($truncateTableArr['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
        } else {
            $tableCreate = create_itune_report_Tablev2($tableName, $conn);

            if ($tableCreate['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }

        }
    }

//gana

    if ($_POST["type"] == "gaana_report_file_upload") {
        $fileLocation = '/var/lib/mysql-files/music_reports/';
        $nd = $_POST['nd'];

        $youtubeUploadFilename = cleanQueryParameter($conn, cleanXSS($_POST["csv_files"]));
        $tableExist = 0;

        $controller = 'importReportGaanaMusic.php';
        $tableName = 'report_audio_' . $nd . '_' . $year . '_' . $month;

        $free_playout_revenue = cleanQueryParameter($conn, cleanXSS($_POST["free_playout_revenue"]));
        $paid_playout_revenue = cleanQueryParameter($conn, cleanXSS($_POST["paid_playout_revenue"]));

        $other_params['free_playout_revenue'] = $free_playout_revenue;
        $other_params['paid_playout_revenue'] = $paid_playout_revenue;

        $tableArr = checkTableExist($tableName, $conn);
        if ($tableArr['errMsg'] == '1') {
            $truncateTableArr = truncateReportTable($tableName, $conn);
            if ($truncateTableArr['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }

            $date_edit = date("Y-m-d H:i:s");
            $ndtype = $nd;
            $sql = "update monthly_rate_saavan_gaana_other set free_playout_revenue='{$free_playout_revenue}' , paid_playout_revenue = '{$paid_playout_revenue}' , date_edit ='{$date_edit}'  where month='{$month}' and year='{$year}' and ndtype='{$ndtype}' ";
            $sqlResult = runQuery($sql, $conn);

        } else {
            $tableCreate = create_gaana_report_Tablev2($tableName, $conn);

            if ($tableCreate['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }

            $ndtype = $nd;
            $date_added = date("Y-m-d H:i:s");
            $sql = "insert into  monthly_rate_saavan_gaana_other (`year`,`month`,`free_playout_revenue`,`paid_playout_revenue`,`Trial_Streams_Revenue`,`status_activation`,`date_added`,`ndtype` ) values('{$year}','{$month}','{$free_playout_revenue}','{$paid_playout_revenue}','0','0','{$date_added}' , '{$ndtype}') ";
            $sqlResult = runQuery($sql, $conn);

        }
    }

//Saavan

    if ($_POST["type"] == "saavan_report_file_upload") {
        $fileLocation = '/var/lib/mysql-files/music_reports/';
        $nd = $_POST['nd'];

        $Ad_Supported_Revenue = cleanQueryParameter($conn, cleanXSS($_POST["Ad_Supported_Revenue"]));
        $Subscription_Revenue = cleanQueryParameter($conn, cleanXSS($_POST["Subscription_Revenue"]));

        $other_params['Ad_Supported_Revenue'] = $Ad_Supported_Revenue;
        $other_params['Subscription_Revenue'] = $Subscription_Revenue;

        $youtubeUploadFilename = cleanQueryParameter($conn, cleanXSS($_POST["csv_files"]));
        $tableExist = 0;

        $controller = 'importReportSaavanMusic.php';
        $tableName = 'report_audio_' . $nd . '_' . $year . '_' . $month;

        $tableArr = checkTableExist($tableName, $conn);
        if ($tableArr['errMsg'] == '1') {
            $truncateTableArr = truncateReportTable($tableName, $conn);
            if ($truncateTableArr['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }
            $date_edit = date("Y-m-d H:i:s");
            $ndtype = $nd;
            $sql = "update monthly_rate_saavan_gaana_other set Ad_Supported_Revenue='{$Ad_Supported_Revenue}' , Subscription_Revenue = '{$Subscription_Revenue}' , date_edit ='{$date_edit}'  where month='{$month}' and year='{$year}' and ndtype='{$ndtype}' ";
            $sqlResult = runQuery($sql, $conn);

        } else {
            $tableCreate = create_saavan_report_Tablev2($tableName, $conn);
            $date_added = date("Y-m-d H:i:s");

            if ($tableCreate['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }
            $ndtype = $nd;
            $sql = "insert into  monthly_rate_saavan_gaana_other (`year`,`month`,`Ad_Supported_Revenue`,`Subscription_Revenue`,`Trial_Streams_Revenue`,`status_activation`,`date_added`,`ndtype` ) values('{$year}','{$month}','{$Ad_Supported_Revenue}','{$Subscription_Revenue}','0','0','{$date_added}' , '{$ndtype}') ";
            $sqlResult = runQuery($sql, $conn);

        }
    }


//spotify

    if ($_POST["type"] == "spotify_report_file_upload") {
        $fileLocation = '/var/lib/mysql-files/music_reports/';
        $nd = $_POST['nd'];

        $youtubeUploadFilename = cleanQueryParameter($conn, cleanXSS($_POST["csv_files"]));
        $tableExist = 0;

        $controller = 'importReportSpotifyMusic.php';
        $tableName = 'report_audio_' . $nd . '_' . $year . '_' . $month;

        $tableArr = checkTableExist($tableName, $conn);
        if ($tableArr['errMsg'] == '1') {
            $truncateTableArr = truncateReportTable($tableName, $conn);
            if ($truncateTableArr['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Cannot truncate table";
                echo (json_encode($returnArr));
                exit;
            }

        } else {
            $tableCreate = create_spotify_report_Tablev2($tableName, $conn);
            $date_added = date("Y-m-d H:i:s");

            if ($tableCreate['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }

        }
    }

    //non_cmg_main_report_file_upload
    if ($_POST["type"] == "non_cmg_main_report_file_upload") {
        $fileLocation = '/var/lib/mysql-files/youtube_video_claim_report/';

        $truncate_data1 = (isset($_POST['truncate_data1'])) ? $_POST['truncate_data1'] : 0;
        $nd = $_POST['nd'];
        $youtubeUploadFilename = cleanQueryParameter($conn, cleanXSS($_POST["csv_files"]));
        $tableExist = 0;

        $controller = 'importReportMainNonCGMv4.php';
        $tableName = 'report_publishing_main_' . $nd . '_' . $year . '_' . $month;

        $tableArr = checkTableExist($tableName, $conn);
        if ($tableArr['errMsg'] == '1') {
            if($truncate_data1==1){
                $truncateTableArr = truncateReportTable($tableName, $conn);
                if ($truncateTableArr['errCode'] != '-1') {
                    $rollback = rollbackTransaction($conn);
                    $returnArr["errCode"] = 4;
                    $returnArr["errMsg"] = "Cannot truncate table";
                    echo (json_encode($returnArr));
                    exit;
                }
            }
        } else {
            $tableCreate = create_non_cgm_main_report_Tablev4($tableName, $conn);
            $date_added = date("Y-m-d H:i:s");

            if ($tableCreate['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }

        }
    }

    if ($_POST["type"] == "non_cmg_red_report_file_upload") {
        $fileLocation = '/var/lib/mysql-files/youtube_video_claim_report/';

        $truncate_data2 = (isset($_POST['truncate_data2'])) ? $_POST['truncate_data2'] : 0;
        $nd = $_POST['nd'];
        $youtubeUploadFilename = cleanQueryParameter($conn, cleanXSS($_POST["csv_files"]));
        $tableExist = 0;

        $controller = 'importReportRedSubscriptionNonCGMv4.php';
        $tableName = 'report_publishing_redsubscription_' . $nd . '_' . $year . '_' . $month;

        $tableArr = checkTableExist($tableName, $conn);
        if ($tableArr['errMsg'] == '1') {
            if($truncate_data1==1){
                $truncateTableArr = truncateReportTable($tableName, $conn);
                if ($truncateTableArr['errCode'] != '-1') {
                    $rollback = rollbackTransaction($conn);
                    $returnArr["errCode"] = 4;
                    $returnArr["errMsg"] = "Cannot truncate table";
                    echo (json_encode($returnArr));
                    exit;
                }
            }
        } else {
            $tableCreate = create_non_cgm_redsubscription_report_Tablev4($tableName, $conn);
            $date_added = date("Y-m-d H:i:s");

            if ($tableCreate['errCode'] != '-1') {
                $rollback = rollbackTransaction($conn);
                $returnArr["errCode"] = 4;
                $returnArr["errMsg"] = "Couldn't create table";
                echo (json_encode($returnArr));
                exit;
            }

        }
    }

//non_cmg_main_report_file_upload


// youTube_withholding_percentage_report_file_upload
if ($_POST["type"] == "youTube_withholding_percentage_report_file_upload") {
    $fileLocation = '/var/lib/mysql-files/youtube_video_claim_report/';
    $nd = $_POST['nd'];

    $youtubeUploadFilename = cleanQueryParameter($conn, cleanXSS($_POST["csv_files"]));
    $tableExist = 0;

    $controller = 'youTube_withholding_percentage_report_file_upload.php';
    $tableName = 'youtube_whp_report_' . $nd . '_' . $year . '_' . $month;
//YouTube_Nirvanadigital2_M_202211_affiliate_payment_summary_v1-0.csv
    $tableArr = checkTableExist($tableName, $conn);
    if ($tableArr['errMsg'] == '1') {
        $truncateTableArr = truncateReportTable($tableName, $conn);
        if ($truncateTableArr['errCode'] != '-1') {
            $rollback = rollbackTransaction($conn);
            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Cannot truncate table";
            echo (json_encode($returnArr));
            exit;
        }

    } else {
        $tableCreate = create_youTube_withholding_percentage_report_v5($tableName, $conn);

        //adding new column to old table to store  actual withholding percentage
       
        $date_added = date("Y-m-d H:i:s");

        if ($tableCreate['errCode'] != '-1') {
            $rollback = rollbackTransaction($conn);
            $returnArr["errCode"] = 4;
            $returnArr["errMsg"] = "Couldn't create table";
            echo (json_encode($returnArr));
            exit;
        }
        // $explode_table = array_reverse(explode('_', $tableName));
        // $destination_table = "youtube_video_claim_report_".$explode_table[2]."_".$explode_table[1]."_".$explode_table[0];
        // $addcolumn = addActualHoldingPercentageColumn($destination_table, $conn);
        
    }
}
//youTube_withholding_percentage_report_file_upload


    //$filePath = "/var/lib/mysql-files/".$year."/".$month."/".$youtubeUploadFilename;
    $files = explode(',', $youtubeUploadFilename);
    $allpath = [];
    foreach ($files as $file) {
        //$filePath =  $filePath"/var/lib/mysql-files/".trim($file);
        $allpath[] = $fileLocation . trim($file);
    }

    if (count($allpath) > 30) {
        $returnArr["errCode"] = 4;
        $returnArr["errMsg"] = "Sorry,Can not upload more than 30 files";
        echo (json_encode($returnArr));
        exit;

    }
    if (count($allpath) > 0) {
        $filePath = implode(',', $allpath);
    }
    /*
    $returnArr["errCode"] = -1;
    $returnArr["errMsg"] = $filePath;
    echo(json_encode($returnArr));
    exit;
     */
    $loged_email_id = $_SESSION["userEmail"];

    $backgroundOutput = runBackgroundProcess("{$controller} {$filePath} {$tableName} {$loged_email_id}");
    /*  @unlink("polo.txt");
    file_put_contents("polo.txt", $backgroundOutput);
    @chmod("polo.txt",0777); */
    if (!($backgroundOutput > 0)) {
        $rollback = rollbackTransaction($conn);
        $returnArr["errCode"] = 4;
        $returnArr["errMsg"] = "Couldn't upload file " . $file;
        echo (json_encode($returnArr));
        exit;
    } else {
        $commit = commitTransaction($conn);
        $returnArr["errCode"] = -1;
        $returnArr["errMsg"] = "File import has started. We will notify you via email when it is completed";
        echo (json_encode($returnArr));
        exit;
    }

    printArr($insertInfoArr);
    exit;

}
