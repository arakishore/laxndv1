<?php

function createYoutubeRedFinanceReportTable($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `adjustmentType` varchar(50) NOT NULL,
							  `day` timestamp NOT NULL,
							  `country` varchar(5)   NOT NULL,
							  `videoID` varchar(50)  NOT NULL,
							  `cutsomID` varchar(50) NOT NULL,
							  `contentType` varchar(50)  NOT NULL,
							  `videoTitle` varchar(255)  NOT NULL,
							  `videoDuration` int(11)  NOT NULL,
							  `username` varchar(50)  NOT NULL,
							  `uploader` varchar(50)  NOT NULL,
							  `channelDisplayName` varchar(255)  NOT NULL,
							  `channelID` varchar(50) NOT NULL,
							  `claimType` varchar(50)  NOT NULL,
							  `claimOrigin` varchar(50)   NOT NULL,
							  `multipleClaims` varchar(5)   NOT NULL,
							  `assetID` varchar(50) NOT NULL,
							  `policy` varchar(50) NOT NULL,
							  `ownedViews` int(10) NOT NULL,
							  `youtubeRevenueSplit` decimal(18,8) NOT NULL,
							  `partnerRevenue` decimal(18,8) NOT NULL,
							   PRIMARY KEY (id),
							   INDEX i (day,contentType,channelID,assetID,videoID),
							   INDEX assetID (assetID),
							   INDEX partnerRevenue (partnerRevenue),
							   INDEX contentType (contentType)
							) ENGINE=InnoDB DEFAULT CHARSET=UTF8";

    $createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);

    if (noError($createYoutubeTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}

function insertRedFinanceReportInfo($filePath, $tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $res = array();
    $currentFile = '';
    if ($filePath) {
        $files = explode(',', $filePath);
        if (count($files) > 0) {
            $currentFile = $files[0];
            $a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
            $a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
            //$a3 = runQuery("SET sql_log_bin = 0;", $conn);
            $insertTableQuery = "LOAD DATA INFILE '{$currentFile}'
								INTO TABLE {$tableName}
								FIELDS TERMINATED BY ','
								ENCLOSED BY '\"'
								LINES TERMINATED BY '\\n'
								IGNORE 1 ROWS(`adjustmentType`, `day`, `country`, `videoID`, `cutsomID`, `contentType`, `videoTitle`, `videoDuration`, `username`, `uploader`, `channelDisplayName`, `channelID`, `claimType`, `claimOrigin`, `multipleClaims`, `assetID`, `policy`, `ownedViews`, `youtubeRevenueSplit`, `partnerRevenue`);";
            $insertTableQueryResult = runQuery($insertTableQuery, $conn);
            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //$a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }
            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insertRedFinanceReportInfo($filePath, $tableName, $conn);
            } else {
                return setErrorStack($returnArr, -1, $res, null);
            }
        } else {
            return setErrorStack($returnArr, -1, $res, null);
        }
    } else {
        return setErrorStack($returnArr, -1, $res, null);
    }
}

////////////////////////////////////Auto Assign helpers////////////////////////////////////////
function autoAssignChannelCOMapRed($tableName = "", $fieldSearchArr = null, $contentType = "", $conn)
{
    $returnArr = array();
    $res = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }

    if (is_null($fieldSearchArr)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Field Search array cannot be empty", null);
    }

    //looping through array passed to create another array of where clauses
    $whereClause = "t1.assetID=t2.assetID";
    foreach ($fieldSearchArr as $colName => $searchVal) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "{$colName} = {$searchVal}";
    }
    $destMonthYear = explode("youtube_red_finance_report_", $tableName);
    $financeTable = "youtube_finance_report_" . $destMonthYear[1];

    // $autoAssignChannelCOMapQuery = "SELECT DISTINCT assetID,content_owner from youtube_finance_report_2020_10";

    // $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    // $allco=[];

    // while($row = mysqli_fetch_assoc($autoAssignChannelCOMapQueryResult["dbResource"])){
    //     $allco[] = $row;

    //  }
    //  echo count($allco);exit;

    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}  t1, {$financeTable} t2 SET t1.content_owner =t2.{$contentType} WHERE {$whereClause}";
    // $autoAssignChannelCOMapQuery = "UPDATE {$tableName}  t1 INNER JOIN {$financeTable} t2 ON  {$whereClause}  SET t1.content_owner =t2.{$contentType}";
    echo $autoAssignChannelCOMapQuery;
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQuery, null);
    }
}

function autoAssignPrevMonthsRed($lastco = null, $tableName, $sourceTableName, $conn)
{
    $returnArr = array();
    $res = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename cannot be empty", null);
    }

    if (empty($sourceTableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " sourceTableName cannot be empty", null);
    }
    if ($lastco) {
        $autoAssignPreviousMonthsQuery = "UPDATE {$tableName} t1, {$sourceTableName} t2 SET t1.content_owner=t2.content_owner,t1.last_content_owner=t2.content_owner WHERE t1.videoID=t2.videoID AND LOWER(t1.contentType)=LOWER(t2.contentType) AND t1.content_owner=''";
    } else {
        $autoAssignPreviousMonthsQuery = "UPDATE {$tableName} t1, {$sourceTableName} t2 SET t1.content_owner=t2.content_owner WHERE t1.videoID=t2.videoID AND LOWER(t1.contentType)=LOWER(t2.contentType) AND t1.content_owner=''";
    }
    $autoAssignPreviousMonthsQueryResult = runQuery($autoAssignPreviousMonthsQuery, $conn);
    if (noError($autoAssignPreviousMonthsQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $autoAssignPreviousMonthsQuery, null);
    }
}

function addLastContentOwnerColumn($tableName, $conn)
{
    $returnArr = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }

    $res = array();

    $truncateTableQuery = "ALTER TABLE {$tableName} ADD COLUMN  `last_content_owner` VARCHAR(50) NOT NULL,ADD INDEX (last_content_owner)";
    $truncateTableQueryResult = runQuery($truncateTableQuery, $conn);

    if (noError($truncateTableQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $truncateTableQueryResult["errMsg"], null);
    }
}
////////////////////////////////////Auto Assign helpers////////////////////////////////////////
/////////////////////////////////////////v2 code start here ///////////////////////////////////////

function insert_youtube_ecommerce_paid_featuresv2($filePath, $tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $res = array();
    $currentFile = '';
    if ($filePath) {
        $files = explode(',', $filePath);
        if (count($files) > 0) {
            $currentFile = $files[0];
            $a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
            $a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
            //$a3 = runQuery("SET sql_log_bin = 0;", $conn);

            $financeTable = "";

            $findme = 'nd1';
            $pos = strpos($tableName, $findme);

            if ($pos === false) {

                $insertTableQuery = "LOAD DATA INFILE '{$currentFile}'
				INTO TABLE {$tableName}
				FIELDS TERMINATED BY ','
				ENCLOSED BY '\"'
				LINES TERMINATED BY '\\n'
				IGNORE 2 ROWS(`dates`, `purchase_type`,`refund_chargeback`, `country`, `channel_name`, `channel_id`, `video_id`, `status_change`, `retail_Price`, `total_tax`, `partner_earnings`, `earnings`);";

            } else {
                /*  $insertTableQuery = "LOAD DATA INFILE '{$currentFile}'
                INTO TABLE {$tableName}
                FIELDS TERMINATED BY ','
                ENCLOSED BY '\"'
                LINES TERMINATED BY '\\n'
                IGNORE 2 ROWS(`dates`, `purchase_type`,`refund_chargeback`, `country`, `channel_name`, `channel_id`,   `retail_Price`, `total_tax`, `partner_earnings`, `earnings`);";
                 */

                $insertTableQuery = "LOAD DATA INFILE '{$currentFile}'
				INTO TABLE {$tableName}
				FIELDS TERMINATED BY ','
				ENCLOSED BY '\"'
				LINES TERMINATED BY '\\n'
                IGNORE 2 ROWS(`dates`, `purchase_type`,`refund_chargeback`, `country`, `channel_name`, `channel_id`,   `retail_Price`, `total_tax`, `partner_earnings`, `earnings`)
                SET video_id=NULL,status_change=NULL;";
            }

            $insertTableQueryResult = runQuery($insertTableQuery, $conn);
            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //$a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }
            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insert_youtube_ecommerce_paid_featuresv2($filePath, $tableName, $conn);
            } else {
                return setErrorStack($returnArr, -1, $res, null);
            }
        } else {
            return setErrorStack($returnArr, -1, $res, null);
        }
    } else {
        return setErrorStack($returnArr, -1, $res, null);
    }
}

function autoAssignChannelCOMap_youtube_ecommerce_paid_featuresv2($tableName = "", $conn)
{
    $returnArr = array();
    $res = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }

    //looping through array passed to create another array of where clauses

    $rootTable = "";

    $findme = 'youtube_ecommerce_paid_features_report_redmusic';
    $pos = strpos($tableName, $findme);

    if ($pos === false) {
        $table_exp = explode('youtube_ecommerce_paid_features_report_', $tableName);
        $rootTable = "youtube_video_claim_report_" . $table_exp[1];

    } else {
        $table_exp = explode('youtube_ecommerce_paid_features_report_', $tableName);
        $rootTable = "youtuberedmusic_video_report_" . $table_exp[1];
    }

    // $destMonthYear = explode("youtube_ecommerce_paid_features_report_", $tableName);
    //$rootTable = "youtube_video_claim_report_" . $destMonthYear[1];
    $tableView = "view_" . $tableName;

    // $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);

    //drop view if exist. view sample : view_youtube_video_claim_report_nd1_2020_12
    $autoAssignChannelCOMapQuery3 = "DROP VIEW IF EXISTS {$tableView} ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery3, $conn);

    // create updatable view
    $autoAssignChannelCOMapQuery = "CREATE VIEW {$tableView} AS SELECT channelID,content_owner,videoID FROM {$rootTable} where content_owner <> '' and content_owner IS NOT NULL group by channelID";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);

 

    //and t1.channel_id=t2.channelID
    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}  t1, {$tableView} t2  SET t1.content_owner =t2.content_owner  WHERE t1.channel_id=t2.channelID ";

    /* @unlink("polo.txt");
    file_put_contents("polo.txt", $autoAssignChannelCOMapQuery);
    @chmod("polo.txt",0777);
     */
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (noError($autoAssignChannelCOMapQueryResult)) {


            //////////// updating holding_percentage 2022-05-14 ////////////////////////////
    // 
    $result_des = array_map('strrev', explode('_', strrev($tableName)));
    $nd_type = getNDTYPESFORCOMAPPING($result_des[2]);

     //and earnings>0

      $client_youtube_sharesQuery = "UPDATE {$tableName} inner join channel_co_maping on {$tableName}.channel_id = channel_co_maping.Channel_id  SET {$tableName}.holding_percentage = channel_co_maping.client_youtube_shares   where  channel_co_maping.CMS ='{$nd_type}' and country='US'  ";
      file_put_contents('autoassign_shares_'.$tableName . date("ymd") . '.txt', $client_youtube_sharesQuery, FILE_APPEND);
 
     $client_youtube_sharesQueryResult = runQuery($client_youtube_sharesQuery, $conn);
     if (!noError($client_youtube_sharesQueryResult)) {
         return setErrorStack($returnArr, 3, $client_youtube_sharesQueryResult, null);
     }
    
     

        $autoAssignChannelCOMapQuery3 = "DROP VIEW IF EXISTS {$tableView} ";
        $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery3, $conn);


        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQuery, null);
    }

}
//holding_percentage 
function createActivationYoutubeWithholdingPercentage_v2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `content_owner` varchar(50)  NOT NULL,
							  `total_amt_recd` varchar(50)  NOT NULL,
							  `shares` varchar(50)  DEFAULT NULL,
							  `amt_payable` varchar(50)  DEFAULT NULL,
							  `us_payout` varchar(50)  DEFAULT NULL,
							  `witholding` varchar(50)  DEFAULT NULL,
							  `final_payable` varchar(50)  DEFAULT NULL,
							  `status` ENUM('active', 'inactive') DEFAULT 'inactive',
							   PRIMARY KEY (id),
							   INDEX i (content_owner)
							)";

    $createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);

    if (noError($createYoutubeTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}

//-------------------------------end youtube_ecommerce_paid_featuresv----------------------
function createActivationYoutubeRedMusic_v2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `content_owner` varchar(50)  NOT NULL,
							  `total_amt_recd` varchar(50)  NOT NULL,
							  `shares` varchar(50)  DEFAULT NULL,
							  `amt_payable` varchar(50)  DEFAULT NULL,
							  `us_payout` varchar(50)  DEFAULT NULL,
							  `witholding` varchar(50)  DEFAULT NULL,
							  `final_payable` varchar(50)  DEFAULT NULL,
							  `status` ENUM('active', 'inactive') DEFAULT 'inactive',
							   PRIMARY KEY (id),
							   INDEX i (content_owner)
							)";

    $createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);

    if (noError($createYoutubeTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}
function createActivationCommonReportTable_v3($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    //`youtubeRevenueSplit` decimal(18,8) NOT NULL,
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `content_owner` varchar(50)  NOT NULL,
							  `total_amt_recd` varchar(50)  NOT NULL,
							  `shares` varchar(50)  DEFAULT NULL,
							  `amt_payable` varchar(50)  DEFAULT NULL,
							  `us_payout` varchar(50)  DEFAULT NULL,
							  `witholding` varchar(50)  DEFAULT NULL,
							  `final_payable` varchar(50)  DEFAULT NULL,
                              `gst_percentage` decimal(10,2) DEFAULT 0,
                              `holding_percentage` decimal(10,2) DEFAULT 0,
                              `final_payable_with_gst` varchar(50)  DEFAULT NULL,
                              `status` ENUM('active', 'inactive') DEFAULT 'inactive',
							   PRIMARY KEY (id),
							   INDEX i (content_owner)
							)";

    $createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);

    if (noError($createYoutubeTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}
function createActivationYoutubeReportTable_v2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    //`youtubeRevenueSplit` decimal(18,8) NOT NULL,
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `content_owner` varchar(50)  NOT NULL,
							  `total_amt_recd` varchar(50)  NOT NULL,
							  `shares` varchar(50)  DEFAULT NULL,
							  `amt_payable` varchar(50)  DEFAULT NULL,
							  `us_payout` varchar(50)  DEFAULT NULL,
							  `witholding` varchar(50)  DEFAULT NULL,
							  `final_payable` varchar(50)  DEFAULT NULL,
                              `gst_percentage` decimal(10,2) DEFAULT 0,
                              `holding_percentage` decimal(10,2) DEFAULT 0,
                              `final_payable_with_gst` varchar(50)  DEFAULT NULL,
                              `status` ENUM('active', 'inactive') DEFAULT 'inactive',
							   PRIMARY KEY (id),
							   INDEX i (content_owner)
							)";

    $createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);

    if (noError($createYoutubeTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}

function createActivation_youtube_ecommerce_paid_features_activation_reportv2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `content_owner` varchar(50)  NOT NULL,
							  `total_amt_recd` varchar(50)  NOT NULL,
							  `shares` varchar(50)  DEFAULT NULL,
							  `amt_payable` varchar(50)  DEFAULT NULL,
							  `us_payout` varchar(50)  DEFAULT NULL,
							  `witholding` varchar(50)  DEFAULT NULL,
							  `final_payable` varchar(50)  DEFAULT NULL,
							  `status` ENUM('active', 'inactive') DEFAULT 'inactive',
							   PRIMARY KEY (id),
							   INDEX i (content_owner)
							)";

    $createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);

    if (noError($createYoutubeTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}

function createActivation_youtube_red_music_video_financev2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `content_owner` varchar(50)  NOT NULL,
							  `total_amt_recd` varchar(50)  NOT NULL,
							  `shares` varchar(50)  DEFAULT NULL,
							  `amt_payable` varchar(50)  DEFAULT NULL,
							  `us_payout` varchar(50)  DEFAULT NULL,
							  `witholding` varchar(50)  DEFAULT NULL,
							  `final_payable` varchar(50)  DEFAULT NULL,
							  `status` ENUM('active', 'inactive') DEFAULT 'inactive',
							   PRIMARY KEY (id),
							   INDEX i (content_owner)
							)";

    @unlink("polo.txt");
    file_put_contents("polo.txt", $createYoutubeTableQuery);
    @chmod("polo.txt", 0777);
    $createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);

    if (noError($createYoutubeTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}

function autoAssignChannelCOMapRedv2($tableName = "", $conn)
{
    $returnArr = array();
    $res = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }

    //looping through array passed to create another array of where clauses

    $financeTable = "";

    $findme = 'youtube_red_music_video_finance_report_redmusic';
    $pos = strpos($tableName, $findme);

    if ($pos === false) {
        $table_exp = explode('youtube_red_music_video_finance_report_', $tableName);
        $financeTable = "youtube_video_claim_report_" . $table_exp[1];

    } else {
        $table_exp = explode('youtube_red_music_video_finance_report_', $tableName);
        $financeTable = "youtuberedmusic_video_report_" . $table_exp[1];

    }




//    $destMonthYear = explode("youtube_red_music_video_finance_report_",  $tableName);
    //    $financeTable = "youtube_video_claim_report_".$destMonthYear[1];

    // $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    $tableView = "view_" . $tableName;
    //drop view if exist. view sample : view_youtube_video_claim_report_nd1_2020_12
    $autoAssignChannelCOMapQuery3 = "DROP VIEW IF EXISTS {$tableView} ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery3, $conn);

    // create updatable view
    $autoAssignChannelCOMapQuery = "CREATE VIEW {$tableView} AS SELECT distinct org.assetID,org.contentType, org.content_owner FROM {$financeTable} org inner join {$tableName} yrm on org.assetID=yrm.assetID where (org.content_owner!='' and org.content_owner IS NOT NULL) ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);

    @unlink("polo_create_view.txt");
    file_put_contents("polo_create_view.txt", $autoAssignChannelCOMapQuery);
    @chmod("polo_create_view.txt", 0777);

    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}  t1, {$tableView} t2 SET t1.content_owner =t2.content_owner WHERE t1.assetID=t2.assetID and t1.contentType=t2.contentType ";
    // $autoAssignChannelCOMapQuery = "UPDATE {$tableName}  t1 INNER JOIN {$financeTable} t2 ON  {$whereClause}  SET t1.content_owner =t2.{$contentType}";

    @unlink("polo_update.txt");
    file_put_contents("polo_update.txt", $autoAssignChannelCOMapQuery);
    @chmod("polo_update.txt", 0777);

    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (noError($autoAssignChannelCOMapQueryResult)) {


        $result_des = array_map('strrev', explode('_', strrev($tableName)));
        $nd_type = getNDTYPESFORCOMAPPING($result_des[2]);
        // and partnerRevenue>0
        $client_youtube_sharesQuery = "UPDATE {$tableName} inner join channel_co_maping on {$tableName}.channelID = channel_co_maping.Channel_id  SET {$tableName}.holding_percentage = channel_co_maping.client_youtube_shares   where  channel_co_maping.CMS ='{$nd_type}' and country='US' ";
        
        file_put_contents('autoassign_shares_'.$tableName . date("ymd") . '.txt', $client_youtube_sharesQuery, FILE_APPEND);
       
       $client_youtube_sharesQueryResult = runQuery($client_youtube_sharesQuery, $conn);
       if (!noError($client_youtube_sharesQueryResult)) {
           return setErrorStack($returnArr, 3, $client_youtube_sharesQueryResult, null);
       }
      

        $autoAssignChannelCOMapQuery3 = "DROP VIEW IF EXISTS {$tableView} ";
        $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery3, $conn);



        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQuery, null);
    }

}

function insertRedFinanceReportInfov2($filePath, $tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $res = array();
    $currentFile = '';
    if ($filePath) {
        $files = explode(',', $filePath);
        if (count($files) > 0) {
            $currentFile = $files[0];
            $a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
            $a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
            //$a3 = runQuery("SET sql_log_bin = 0;", $conn);
            //datefrom
            if (strpos(strtolower($currentFile), 'music') !== false) {
                $datefrom = "Music";
            } else {
                $datefrom = "Rawdata";
            }
            $insertTableQuery = "LOAD DATA INFILE '{$currentFile}'
								INTO TABLE {$tableName}
								FIELDS TERMINATED BY ','
								ENCLOSED BY '\"'
								LINES TERMINATED BY '\\n'
								IGNORE 2 ROWS(`adjustmentType`, `day`, `country`, `videoID`, `cutsomID`, `contentType`, `videoTitle`, `videoDuration`, `username`, `uploader`, `channelDisplayName`, `channelID`, `claimType`, `claimOrigin`, `multipleClaims`, `assetID`, `policy`, `ownedViews`, `youtubeRevenueSplit`, `partnerRevenue`)
								SET datefrom='{$datefrom}';";
            @unlink("polo.txt");
            file_put_contents("polo.txt", $insertTableQuery);
            @chmod("polo.txt", 0777);

            $insertTableQueryResult = runQuery($insertTableQuery, $conn);
            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //$a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }
            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insertRedFinanceReportInfov2($filePath, $tableName, $conn);
            } else {
                return setErrorStack($returnArr, -1, $res, null);
            }
        } else {
            return setErrorStack($returnArr, -1, $res, null);
        }
    } else {
        return setErrorStack($returnArr, -1, $res, null);
    }
}
function createYoutubeRedFinanceReportTablev2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $US_HOLDING_PERCENTAGE = US_HOLDING_PERCENTAGE;
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`adjustmentType` varchar(50) DEFAULT NULL,
        `day` varchar(10)   DEFAULT NULL,
		`country` varchar(5)   DEFAULT NULL,
		`videoID` varchar(50)  DEFAULT NULL,
		`cutsomID` varchar(50) DEFAULT NULL,
		`contentType` varchar(50)  DEFAULT NULL,
		`videoTitle` varchar(255)  NOT NULL,
		`videoDuration` int(11)  NOT NULL,
		`username` varchar(50)  NOT NULL,
		`uploader` varchar(50)  NOT NULL,
		`channelDisplayName` varchar(255)  NOT NULL,
		`channelID` varchar(50) DEFAULT NULL,
		`claimType` varchar(50)  DEFAULT NULL,
		`claimOrigin` varchar(50)   DEFAULT NULL,
		`multipleClaims` varchar(5)   NOT NULL,
		`assetID` varchar(1024) DEFAULT NULL,
		`policy` varchar(50) DEFAULT NULL,
		`ownedViews` int(10) DEFAULT NULL,
		`youtubeRevenueSplit` decimal(18,8) DEFAULT NULL,
		`partnerRevenue` decimal(18,8) DEFAULT NULL,
	 	`content_owner` varchar(50) DEFAULT NULL,
		`last_content_owner` varchar(50) DEFAULT NULL,
		`Label` varchar(50) DEFAULT NULL,
		`datefrom` varchar(50) DEFAULT NULL,
        `holding_percentage` decimal(10,2) DEFAULT {$US_HOLDING_PERCENTAGE},
		 PRIMARY KEY (id),
         INDEX contentType (contentType),
         INDEX channelID (channelID),
         INDEX assetID (assetID),
         INDEX `Label` (Label),
         INDEX `videoTitle` (videoTitle),
         INDEX `content_owner` (content_owner),
         INDEX `holding_percentage` (holding_percentage),
         INDEX `partnerRevenue` (partnerRevenue)
		 ) ENGINE=InnoDB DEFAULT CHARSET=UTF8";

    $createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);

    if (noError($createYoutubeTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}

function insertRedFinanceReportInfoRedMusicV2($filePath, $tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $res = array();
    $currentFile = '';
    if ($filePath) {
        $files = explode(',', $filePath);
        if (count($files) > 0) {
            $currentFile = $files[0];
            $a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
            $a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
            //$a3 = runQuery("SET sql_log_bin = 0;", $conn);
            //datefrom
            if (strpos(strtolower($currentFile), 'music') !== false) {
                $datefrom = "Music";
            } else {
                $datefrom = "Rawdata";
            }

            $insertTableQuery = "LOAD DATA INFILE '{$currentFile}'
								INTO TABLE {$tableName}
								FIELDS TERMINATED BY ','
								ENCLOSED BY '\"'
								LINES TERMINATED BY '\\n'
								IGNORE 2 ROWS(`adjustmentType`, `country`, `day`, `videoID`, `channelID`, `assetID`, `assetChannelID`, `asset_title`, `asset_labels`, `assetType`, `cutsomID`, `ISRC`, `UPC`, `GRid`,artist, `album`, `Label`, `claimType`, `contentType`, `Offer`, `ownedViews`, `MonetizedViewsAudio`, `MonetizedViewsAudioVisual`, `MonetizedViews`, `youtubeRevenueSplit`, `partnerRevenueProRata`, `partnerRevenuePerSubMin`, `partnerRevenue`, `content_owner`, `datefrom`)
								SET datefrom='{$datefrom}';";
            @unlink("polo.txt");
            file_put_contents("polo.txt", $insertTableQuery);
            @chmod("polo.txt", 0777);

            $insertTableQueryResult = runQuery($insertTableQuery, $conn);
            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //$a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }
            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insertRedFinanceReportInfoRedMusicV2($filePath, $tableName, $conn);
            } else {
                return setErrorStack($returnArr, -1, $res, null);
            }
        } else {
            return setErrorStack($returnArr, -1, $res, null);
        }
    } else {
        return setErrorStack($returnArr, -1, $res, null);
    }
}
function createYoutubeRedFinanceReportTableRedMusicv2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $US_HOLDING_PERCENTAGE = US_HOLDING_PERCENTAGE;
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`adjustmentType` varchar(50) DEFAULT NULL,
		`country` varchar(5)   DEFAULT NULL,
        `day` varchar(10)   DEFAULT NULL,
		`videoID` varchar(50)  DEFAULT NULL,
		`channelID` varchar(50) DEFAULT NULL,
		`assetID` varchar(1024) DEFAULT NULL,
		`assetChannelID` varchar(50)  DEFAULT NULL,
		`asset_title` varchar(50)  DEFAULT NULL,
		`asset_labels` varchar(50) DEFAULT NULL,
		`assetType` varchar(50) DEFAULT NULL,
		`cutsomID` varchar(50) DEFAULT NULL,
		`ISRC` varchar(50) DEFAULT NULL,
		`UPC` varchar(50) DEFAULT NULL,
		`GRid` varchar(100) DEFAULT NULL,
		`artist` varchar(50) DEFAULT NULL,
		`album` varchar(50) DEFAULT NULL,
		`Label` varchar(50) DEFAULT NULL,
		`claimType` varchar(50)  DEFAULT NULL,
		`contentType` varchar(50)  DEFAULT NULL,
		`Offer` varchar(50)  DEFAULT NULL,
		`ownedViews` varchar(50)  DEFAULT NULL,
		`MonetizedViewsAudio` varchar(50)  DEFAULT NULL,
		`MonetizedViewsAudioVisual` varchar(50)  DEFAULT NULL,
		`MonetizedViews`varchar(50)  DEFAULT NULL,
		`youtubeRevenueSplit` decimal(18,8) DEFAULT NULL,
		`partnerRevenueProRata` decimal(18,8) DEFAULT NULL,
		`partnerRevenuePerSubMin` decimal(18,8) DEFAULT NULL,
		`partnerRevenue` decimal(18,8) DEFAULT NULL,
	 	`content_owner` varchar(50) DEFAULT NULL,
		`datefrom` varchar(50) DEFAULT NULL,
        `holding_percentage` decimal(10,2) DEFAULT {$US_HOLDING_PERCENTAGE},
		 PRIMARY KEY (id),
         INDEX contentType (contentType),
         INDEX channelID (channelID),
         INDEX assetID (assetID),
		 INDEX assetChannelID (assetChannelID),
		 INDEX videoID (videoID),
		 INDEX day (day),
		 INDEX asset_title (asset_title),
		 INDEX asset_labels (asset_labels),
		 INDEX assetType (assetType),
		 INDEX youtubeRevenueSplit (youtubeRevenueSplit),
         INDEX `Label` (Label),
         INDEX `content_owner` (content_owner),
         INDEX `holding_percentage` (holding_percentage),
         INDEX `partnerRevenue` (partnerRevenue)
		 ) ENGINE=InnoDB DEFAULT CHARSET=UTF8";

    @unlink("polo.txt");
    file_put_contents("polo.txt", $createYoutubeTableQuery);
    @chmod("polo.txt", 0777);

    $createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);

    if (noError($createYoutubeTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}
// ---------------ecommerce---

function create_youtube_ecommerce_paid_features_report_Tablev2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $US_HOLDING_PERCENTAGE = US_HOLDING_PERCENTAGE;
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`dates` varchar(15) DEFAULT NULL,
        `purchase_type` varchar(10)   DEFAULT NULL,
		`refund_chargeback` varchar(10)   DEFAULT NULL,
		`country` varchar(5)   DEFAULT NULL,
		`channel_name` varchar(150)  DEFAULT NULL,
		`channel_id` varchar(150) DEFAULT NULL,
		`video_id` varchar(150)  DEFAULT NULL,
		`status_change` varchar(255)  DEFAULT NULL,
		`retail_Price` decimal(18,8) DEFAULT NULL,
		`total_tax` decimal(18,8) DEFAULT NULL,
		`partner_earnings` decimal(18,2) DEFAULT NULL,
		`earnings` decimal(18,8) DEFAULT NULL,
		`assetID` varchar(50) DEFAULT NULL,
	 	`content_owner` varchar(50) DEFAULT NULL,
		`Label` varchar(50) DEFAULT NULL,
		`videoTitle` varchar(50) DEFAULT NULL,
        `holding_percentage` decimal(10,2) DEFAULT {$US_HOLDING_PERCENTAGE},
		 PRIMARY KEY (id),
         INDEX purchase_type (purchase_type),
         INDEX channel_id (channel_id),
         INDEX assetID (assetID),
         INDEX `Label` (Label),
         INDEX `videoTitle` (videoTitle),
         INDEX `content_owner` (content_owner),
         INDEX `holding_percentage` (holding_percentage),
		 INDEX `earnings` (earnings)

		 ) ENGINE=InnoDB DEFAULT CHARSET=UTF8";

    $createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);

    if (noError($createYoutubeTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}

function create_youtube_ecommerce_paid_features_report_TableNd1v2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $US_HOLDING_PERCENTAGE = US_HOLDING_PERCENTAGE;
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`dates` varchar(15) DEFAULT NULL,
        `purchase_type` varchar(10)   DEFAULT NULL,
		`refund_chargeback` varchar(10)   DEFAULT NULL,
		`country` varchar(5)   DEFAULT NULL,
		`channel_name` varchar(150)  DEFAULT NULL,
		`channel_id` varchar(150) DEFAULT NULL,
		`video_id` varchar(150)  DEFAULT NULL,
		`status_change` varchar(255)  DEFAULT NULL,
		`retail_Price` decimal(18,8) DEFAULT NULL,
		`total_tax` decimal(18,8) DEFAULT NULL,
		`partner_earnings` decimal(18,2) DEFAULT NULL,
		`earnings` decimal(18,8) DEFAULT NULL,
		`assetID` varchar(50) DEFAULT NULL,
	 	`content_owner` varchar(50) DEFAULT NULL,
		`Label` varchar(50) DEFAULT NULL,
		`videoTitle` varchar(50) DEFAULT NULL,
        `holding_percentage` decimal(10,2) DEFAULT {$US_HOLDING_PERCENTAGE},
		 PRIMARY KEY (id),
         INDEX purchase_type (purchase_type),
         INDEX channel_id (channel_id),
         INDEX assetID (assetID),
         INDEX `Label` (Label),
         INDEX `videoTitle` (videoTitle),
         INDEX `content_owner` (content_owner),
         INDEX `holding_percentage` (holding_percentage),
		 INDEX `earnings` (earnings)

		 ) ENGINE=InnoDB DEFAULT CHARSET=UTF8";

    $createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);

    if (noError($createYoutubeTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}

// ---------------ecommerce---

function create_youtube_labelengine_report_Tablev2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $US_HOLDING_PERCENTAGE = US_HOLDING_PERCENTAGE;
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
		 `id` int(11) NOT NULL AUTO_INCREMENT,
		 `contentType` varchar(50)  NOT NULL,
         `assetID` varchar(50) NOT NULL,
         `partnerRevenue` decimal(18,8) DEFAULT NULL,
	 	 `content_owner` varchar(50) DEFAULT NULL,
          `holding_percentage` decimal(10,2) DEFAULT {$US_HOLDING_PERCENTAGE},
         PRIMARY KEY (id),
         INDEX `holding_percentage` (holding_percentage),
		 INDEX contentType (contentType),
         INDEX assetID (assetID),
         INDEX `partnerRevenue` (partnerRevenue),
         INDEX `content_owner` (content_owner)
		 
		 ) ENGINE=InnoDB DEFAULT CHARSET=UTF8";

    $createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);

    if (noError($createYoutubeTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}


function insert_youtube_labelenginev2($filePath, $tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $res = array();
    $currentFile = '';
    if ($filePath) {
        $files = explode(',', $filePath);
        if (count($files) > 0) {
            $currentFile = $files[0];
            $a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
            $a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
            //$a3 = runQuery("SET sql_log_bin = 0;", $conn);

            
            
            $insertTableQuery = "LOAD DATA INFILE '{$currentFile}'
				INTO TABLE {$tableName}
				FIELDS TERMINATED BY ','
				ENCLOSED BY '\"'
				LINES TERMINATED BY '\\n'
				IGNORE 1 ROWS(`contentType`, `assetID`,`partnerRevenue`);";


            $insertTableQueryResult = runQuery($insertTableQuery, $conn);
            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //$a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }
            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insert_youtube_labelenginev2($filePath, $tableName, $conn);
            } else {
                return setErrorStack($returnArr, -1, $res, null);
            }
        } else {
            return setErrorStack($returnArr, -1, $res, null);
        }
    } else {
        return setErrorStack($returnArr, -1, $res, null);
    }
}


function autoAssign_labelenginev2($tableName = "", $conn)
{
    $returnArr = array();
    $res = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }

    //looping through array passed to create another array of where clauses
//		youtube_video_claim_report_nd1_2021_03
//	youtuberedmusic_video_report_redmusic_2021_03

    $rootTable = "";

 
    $result = array_map('strrev', explode('_', strrev($tableName)));
    $ndtype = $result[2];
    $ndtypes = array('nd1','nd2','ndkids','redmusic');
        if(in_array($ndtype, $ndtypes )){

        }
        if($ndtype == 'redmusic'){
            $rootTable = 'youtuberedmusic_video_report_redmusic_'.$result[1].'_'.$result[0];
        } else {
            $rootTable = 'youtube_video_claim_report_'.$result[2].'_'.$result[1].'_'.$result[0];
        }

    
    $tableView = "view_" . $tableName;

    //drop view if exist. view sample : view_youtube_video_claim_report_nd1_2020_12
    $autoAssignChannelCOMapQuery3 = "DROP TABLE IF EXISTS {$tableView} ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery3, $conn);

    // create   view for PP
    $autoAssignChannelCOMapQuery = "CREATE TABLE {$tableView} AS SELECT r1.contentType, r1.assetID, r1.content_owner  FROM {$rootTable} r1   WHERE  r1.contentType ='UGC' and  r1.content_owner IS NOT NULL   GROUP BY r1.assetID ";

    @unlink("polo_labelengine_view.txt");
    file_put_contents("polo_labelengine_view.txt", $autoAssignChannelCOMapQuery);
    @chmod("polo_labelengine_view.txt",0777);

    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

    addIndexTempTable($tableView,$conn,'content_owner');
    addIndexTempTable($tableView,$conn,'assetID');
 

    //and t1.channel_id=t2.channelID
    $autoAssignChannelCOMapQuery = " UPDATE {$tableName}  t1, {$tableView} t2  SET t1.content_owner =t2.content_owner     WHERE t1.assetID=t2.assetID ";

    @unlink("polo_labelengine_assign.txt");
    file_put_contents("polo_labelengine_assign.txt", $autoAssignChannelCOMapQuery);
    @chmod("polo_labelengine_assign.txt",0777);
     
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);

    
    if (noError($autoAssignChannelCOMapQueryResult)) {

        $autoAssignChannelCOMapQuery3 = "DROP TABLE IF EXISTS {$tableView} ";
       $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery3, $conn);


        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQuery, null);
    }

}