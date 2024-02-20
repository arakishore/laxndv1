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


    /*
     $CMS = "ND1";
    $tableName  = "youtube_video_claim_report_nd1_2021_09";
    $tableName_activation  = "youtube_video_claim_activation_report_nd1_2021_09";
    $tableView = "temp_".$tableName;

   
    $sql3 = "DROP TABLE IF EXISTS {$tableView} ";
    $sql3Result = runQuery($sql3, $conn);
    
    $sql1 = "CREATE TABLE  {$tableView}  AS SELECT assetChannelID , content_owner   FROM  {$tableName} where assetChannelID != ''  GROUP by assetChannelID  ";

    $sql1Result = runQuery($sql1, $conn);

    $sql2 = "select yt.content_owner,ytc.assetChannelID,yt.shares,cm.Label,cm.CMS from {$tableName_activation} yt, channel_co_maping cm,   {$tableView}  ytc      where (cm.partner_provided=yt.content_owner OR cm.ugc=yt.content_owner) and yt.content_owner =  ytc.content_owner and CMS = 'ND1'    group by yt.content_owner  INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/youtube-fileND1_withAssetChannel.csv'";

    $sql2Result = runQuery($sql2, $conn);

    $sql3 = "DROP TABLE IF EXISTS {$tableView} ";
    $sql3Result = runQuery($sql3, $conn); */


/* 
   
//// no assetChannelID column present

    $CMS = "ND1";
    $tableName  = "youtube_red_music_video_finance_report_nd1_2021_09";
    $tableName_activation  = "youtube_red_music_finance_activation_report_nd1_2021_09";
    $tableView = "temp_".$tableName;

   
    $sql3 = "DROP TABLE IF EXISTS {$tableView} ";
    $sql3Result = runQuery($sql3, $conn);
    
    $sql1 = "CREATE TABLE  {$tableView}  AS SELECT assetChannelID , content_owner   FROM  {$tableName} where assetChannelID != ''  GROUP by assetChannelID  ";

    $sql1Result = runQuery($sql1, $conn);

    $sql2 = "select yt.content_owner,ytc.assetChannelID,yt.shares,cm.Label,cm.CMS from {$tableName_activation} yt, channel_co_maping cm,   {$tableView}  ytc      where (cm.partner_provided=yt.content_owner OR cm.ugc=yt.content_owner) and yt.content_owner =  ytc.content_owner and CMS = 'ND1'    group by yt.content_owner  INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/youtube-fileND1_withAssetChannel.csv'";

    $sql2Result = runQuery($sql2, $conn);

    $sql3 = "DROP TABLE IF EXISTS {$tableView} ";
    $sql3Result = runQuery($sql3, $conn);
 */
/* 
$CMS = "ND2";
$tableName  = "youtube_video_claim_report_nd2_2021_09";
$tableName_activation  = "youtube_video_claim_activation_report_nd2_2021_09";
$tableView = "temp_".$tableName;


$sql3 = "DROP TABLE IF EXISTS {$tableView} ";
$sql3Result = runQuery($sql3, $conn);

$sql1 = "CREATE TABLE  {$tableView}  AS SELECT assetChannelID , content_owner   FROM  {$tableName} where assetChannelID != ''  GROUP by assetChannelID  ";

$sql1Result = runQuery($sql1, $conn);

$sql2 = "select yt.content_owner,ytc.assetChannelID,yt.shares,cm.Label,cm.CMS from {$tableName_activation} yt, channel_co_maping cm,   {$tableView}  ytc      where (cm.partner_provided=yt.content_owner OR cm.ugc=yt.content_owner) and yt.content_owner =  ytc.content_owner and CMS = 'ND2'    group by yt.content_owner  INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/youtube-fileND2_withAssetChannel.csv'";

$sql2Result = runQuery($sql2, $conn);

$sql3 = "DROP TABLE IF EXISTS {$tableView} ";
$sql3Result = runQuery($sql3, $conn);

 */
  
    /* 

// no assetChannelID column present    
$CMS = "ND2";
$tableName  = "youtube_red_music_video_finance_report_nd2_2021_09";
$tableName_activation  = "youtube_red_music_finance_activation_report_nd2_2021_09";
$tableView = "temp_".$tableName;


$sql3 = "DROP TABLE IF EXISTS {$tableView} ";
$sql3Result = runQuery($sql3, $conn);

$sql1 = "CREATE TABLE  {$tableView}  AS SELECT assetChannelID , content_owner   FROM  {$tableName} where assetChannelID != ''  GROUP by assetChannelID  ";

$sql1Result = runQuery($sql1, $conn);

$sql2 = "select yt.content_owner,ytc.assetChannelID,yt.shares,cm.Label,cm.CMS from {$tableName_activation} yt, channel_co_maping cm,   {$tableView}  ytc      where (cm.partner_provided=yt.content_owner OR cm.ugc=yt.content_owner) and yt.content_owner =  ytc.content_owner and CMS = 'ND2'    group by yt.content_owner  INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/youtube-fileND2_withAssetChannel.csv'";

$sql2Result = runQuery($sql2, $conn);

$sql3 = "DROP TABLE IF EXISTS {$tableView} ";
$sql3Result = runQuery($sql3, $conn);  */
 
/* 

$CMS = "ndkids";
$tableName  = "youtube_video_claim_report_ndkids_2021_09";
$tableName_activation  = "youtube_video_claim_activation_report_ndkids_2021_09";
$tableView = "temp_".$tableName;


$sql3 = "DROP TABLE IF EXISTS {$tableView} ";
$sql3Result = runQuery($sql3, $conn);

$sql1 = "CREATE TABLE  {$tableView}  AS SELECT assetChannelID , content_owner   FROM  {$tableName} where assetChannelID != ''  GROUP by assetChannelID  ";

$sql1Result = runQuery($sql1, $conn);

$sql2 = "select yt.content_owner,ytc.assetChannelID,yt.shares,cm.Label,cm.CMS from {$tableName_activation} yt, channel_co_maping cm,   {$tableView}  ytc      where (cm.partner_provided=yt.content_owner OR cm.ugc=yt.content_owner) and yt.content_owner =  ytc.content_owner and CMS = 'ND KIDS'    group by yt.content_owner  INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/youtube-fileNDkids_withAssetChannel.csv'";

$sql2Result = runQuery($sql2, $conn);

$sql3 = "DROP TABLE IF EXISTS {$tableView} ";
$sql3Result = runQuery($sql3, $conn);
 */

 

$CMS = "ND Music";
$tableName  = "youtuberedmusic_video_report_redmusic_2021_09";
$tableName_activation  = "youtube_redmusic_activation_report_redmusic_2021_09";
$tableView = "temp_".$tableName;


$sql3 = "DROP TABLE IF EXISTS {$tableView} ";
$sql3Result = runQuery($sql3, $conn);

$sql1 = "CREATE TABLE  {$tableView}  AS SELECT assetChannelID , content_owner   FROM  {$tableName} where assetChannelID != ''  GROUP by assetChannelID  ";

$sql1Result = runQuery($sql1, $conn);

$sql2 = "select yt.content_owner,ytc.assetChannelID,yt.shares,cm.Label,cm.CMS from {$tableName_activation} yt, channel_co_maping cm,   {$tableView}  ytc      where (cm.partner_provided=yt.content_owner OR cm.ugc=yt.content_owner) and yt.content_owner =  ytc.content_owner and CMS = 'ND Music'    group by yt.content_owner  INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/youtube-fileNDkids_withAssetChannel.csv'";

$sql2Result = runQuery($sql2, $conn);

$sql3 = "DROP TABLE IF EXISTS {$tableView} ";
$sql3Result = runQuery($sql3, $conn);
}
