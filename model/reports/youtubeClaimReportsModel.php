<?php
function createYoutubeRedMusicReportTable($tableName, $conn)
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
		`channelID` varchar(50) DEFAULT NULL,
		`assetID` varchar(50) DEFAULT NULL,
		`assetChannelID` varchar(50)  DEFAULT NULL,
		`assetType` varchar(50) DEFAULT NULL,
		`cutsomID` varchar(50) DEFAULT NULL,
        `ISRC` varchar(50) DEFAULT NULL,
        `UPC` varchar(50) DEFAULT NULL,
        `GRid` varchar(50) DEFAULT NULL,
		`contentType` varchar(50)  DEFAULT NULL,
		`policy` varchar(50) DEFAULT NULL,
		`claimType` varchar(50)  DEFAULT NULL,
		`claimOrigin` varchar(50)   DEFAULT NULL,
		`ownedViews` int(10) DEFAULT NULL,
		`youtubeRevenueSplitAuction` decimal(18,8) DEFAULT NULL,
		`youtubeRevenueSplitReserved` decimal(18,8) DEFAULT NULL,
		`youtubeRevenueSplitPartnerSoldYoutubeServed` decimal(18,8) DEFAULT NULL,
		`youtubeRevenueSplitPartnerSoldPartnerServed` decimal(18,8) DEFAULT NULL,
		`youtubeRevenueSplit` decimal(18,8) DEFAULT NULL,
		`partnerRevenueAuction` decimal(18,8) DEFAULT NULL,
		`partnerRevenueReserved` decimal(18,8) DEFAULT NULL,
		`partnerRevenuePartnerSoldYouTubeServed` decimal(18,8) DEFAULT NULL,
		`partnerRevenuePartnerSoldPartnerServed` decimal(18,8) DEFAULT NULL,
		`partnerRevenue` decimal(18,8) DEFAULT NULL,
		`content_owner` varchar(50) DEFAULT NULL,
		`Label` varchar(50) DEFAULT NULL,
        `video_title` varchar(150) DEFAULT NULL,
        `autoassign_steps` varchar(10) DEFAULT NULL,
        `holding_percentage` decimal(10,2) DEFAULT {$US_HOLDING_PERCENTAGE},
		 PRIMARY KEY (id),
         INDEX i (assetChannelID),
         INDEX contentType (contentType),
         INDEX channelID (channelID),
         INDEX assetID (assetID),
         INDEX `policy` (policy),
         INDEX `Label` (Label),
         INDEX `videoID` (videoID),
         INDEX `video_title` (video_title),
         INDEX `holding_percentage` (holding_percentage),
         INDEX `partnerRevenue` (partnerRevenue),
         INDEX `content_owner` (content_owner)
		 ) ENGINE=InnoDB DEFAULT CHARSET=UTF8";

    $createAmazonTableQueryResult = runQuery($createYoutubeTableQuery, $conn);
    // printArr($createAmazonTableQueryResult);exit;

    if (noError($createAmazonTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createAmazonTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}

function createYoutubeVideoClaimReportTable($tableName, $conn)
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
		`channelID` varchar(50) DEFAULT NULL,
		`assetID` varchar(50) DEFAULT NULL,
		`assetChannelID` varchar(50)  DEFAULT NULL,
		`assetType` varchar(50) DEFAULT NULL,
		`cutsomID` varchar(50) DEFAULT NULL,
        `contentType` varchar(50)  DEFAULT NULL,
		`policy` varchar(50) DEFAULT NULL,
		`claimType` varchar(50)  DEFAULT NULL,
		`claimOrigin` varchar(50)   DEFAULT NULL,
		`ownedViews` int(10) DEFAULT NULL,
		`youtubeRevenueSplitAuction` decimal(18,8) DEFAULT NULL,
		`youtubeRevenueSplitReserved` decimal(18,8) DEFAULT NULL,
		`youtubeRevenueSplitPartnerSoldYoutubeServed` decimal(18,8) DEFAULT NULL,
		`youtubeRevenueSplitPartnerSoldPartnerServed` decimal(18,8) DEFAULT NULL,
		`youtubeRevenueSplit` decimal(18,8) DEFAULT NULL,
		`partnerRevenueAuction` decimal(18,8) DEFAULT NULL,
		`partnerRevenueReserved` decimal(18,8) DEFAULT NULL,
		`partnerRevenuePartnerSoldYouTubeServed` decimal(18,8) DEFAULT NULL,
		`partnerRevenuePartnerSoldPartnerServed` decimal(18,8) DEFAULT NULL,
		`partnerRevenue` decimal(18,8) DEFAULT NULL,
		`content_owner` varchar(50) DEFAULT NULL,
		`Label` varchar(50) DEFAULT NULL,
        `video_title` varchar(150) DEFAULT NULL,
        `autoassign_steps` varchar(10) DEFAULT NULL,
        `holding_percentage` decimal(10,2) DEFAULT {$US_HOLDING_PERCENTAGE},
		 PRIMARY KEY (id),
         INDEX i (assetChannelID),
         INDEX contentType (contentType),
         INDEX channelID (channelID),
         INDEX assetID (assetID),
         INDEX `policy` (policy),
         INDEX `Label` (Label),
         INDEX `videoID` (videoID),
         INDEX `video_title` (video_title),
         INDEX partnerRevenue (partnerRevenue),
         INDEX `holding_percentage` (holding_percentage),
         INDEX `content_owner` (content_owner)
		 ) ENGINE=InnoDB DEFAULT CHARSET=UTF8";

        @unlink("polo_create.txt");
        file_put_contents("polo_create.txt", $createYoutubeTableQuery);
        @chmod("polo_create.txt", 0777);

    $createAmazonTableQueryResult = runQuery($createYoutubeTableQuery, $conn);
    // printArr($createAmazonTableQueryResult);exit;

    if (noError($createAmazonTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createAmazonTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}
function createYoutubeVideoClaimReportTable2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $US_HOLDING_PERCENTAGE = US_HOLDING_PERCENTAGE;
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`claim_id` varchar(15) DEFAULT NULL,
		`claim_status` varchar(10) DEFAULT NULL,
		`claim_status_detail` text DEFAULT NULL,
		`claim_origin` varchar(50) DEFAULT NULL,
		`claim_type` varchar(50) DEFAULT NULL,
		`asset_id` varchar(50) DEFAULT NULL,
		`video_id` varchar(50) DEFAULT NULL,
		`uploader` varchar(50) DEFAULT NULL,
		`channel_id` varchar(50) DEFAULT NULL,
		`channel_display_name` varchar(150) DEFAULT NULL,
		`video_title` varchar(255) DEFAULT NULL,
		`view` int(11) DEFAULT NULL,
		`matching_duration` int(11) DEFAULT NULL,
		`longest_match` int(11) DEFAULT NULL,
		`content_type` varchar(50) DEFAULT NULL,
		`reference_video_id` varchar(50) DEFAULT NULL,
		`reference_id` varchar(50) DEFAULT NULL,
		`claim_policy_id` varchar(50) DEFAULT NULL,
		`asset_policy_id` varchar(50) DEFAULT NULL,
		`claim_policy_monetize` text DEFAULT NULL,
		`claim_policy_track` text DEFAULT NULL,
		`claim_policy_block` text DEFAULT NULL,
		`asset_policy_monetize` varchar(50) DEFAULT NULL,
		`asset_policy_track` varchar(50) DEFAULT NULL,
		`asset_policy_block` varchar(50) DEFAULT NULL,
		`claim_created_date` date DEFAULT NULL,
		`video_upload_date` date DEFAULT NULL,
		`custom_id` varchar(50) DEFAULT NULL,
		`video_duration_sec` int(11) DEFAULT NULL,
		`asset_title` text DEFAULT NULL,
		`asset_labels` varchar(50) DEFAULT NULL,
		`tms` varchar(50) DEFAULT NULL,
		`director` varchar(50) DEFAULT NULL,
		`studio` varchar(50) DEFAULT NULL,
		`season` varchar(50) DEFAULT NULL,
		`episode_number` varchar(50) DEFAULT NULL,
		`episode_title` text DEFAULT NULL,
		`release_date` date DEFAULT NULL,
		`hfa_song_code` varchar(50) DEFAULT NULL,
		`isrc` varchar(50) DEFAULT NULL,
		`grid` varchar(50) DEFAULT NULL,
		`artist` varchar(50) DEFAULT NULL,
		`album` varchar(50) DEFAULT NULL,
		`record_label` varchar(50) DEFAULT NULL,
		`upc` varchar(50) DEFAULT NULL,
		`iswc` varchar(50) DEFAULT NULL,
		`writers` varchar(50) DEFAULT NULL,
		`content_owner` varchar(50) DEFAULT NULL,
        `holding_percentage` decimal(10,2) DEFAULT {$US_HOLDING_PERCENTAGE},
         PRIMARY KEY (id),
         INDEX j (asset_id,video_id,uploader,channel_id,channel_display_name,video_title,content_type,content_owner),
         INDEX composite (video_id,channel_id),
         INDEX `holding_percentage` (holding_percentage)
		) ENGINE=InnoDB DEFAULT CHARSET=UTF8";

    $createAmazonTableQueryResult = runQuery($createYoutubeTableQuery, $conn);
    // printArr($createAmazonTableQueryResult);exit;

    if (noError($createAmazonTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createAmazonTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}
function createYoutubeVideoClaimReportTable3($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $US_HOLDING_PERCENTAGE = US_HOLDING_PERCENTAGE;
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`approx_daily_views` varchar(50) DEFAULT NULL,
		`asset_id` varchar(50)   DEFAULT NULL,
		`asset_type` varchar(250)  DEFAULT NULL,
		`status` varchar(250) DEFAULT NULL,
		`metadata_origination` varchar(250) DEFAULT NULL,
		`custom_id` varchar(250)  DEFAULT NULL,
		`season` varchar(250) DEFAULT NULL,
		`episode_title` varchar(250) DEFAULT NULL,
		`tms` varchar(100)  DEFAULT NULL,
		`active_claims` varchar(100) DEFAULT NULL,
		`constituent_asset_id` varchar(100)  DEFAULT NULL,
		`active_reference_id` varchar(100)   DEFAULT NULL,
		`inactive_reference_id` varchar(100) DEFAULT NULL,
		`match_policy` varchar(50) DEFAULT NULL,
		`is_merged` varchar(50) DEFAULT NULL,
		`conflicting_owner` varchar(100) DEFAULT NULL,
		`ownership` varchar(100) DEFAULT NULL,
		`asset_label` varchar(50) DEFAULT NULL,
		`conflicting_country_code` varchar(50) DEFAULT NULL,
		`content_owner` varchar(100) DEFAULT NULL,
        `holding_percentage` decimal(10,2) DEFAULT {$US_HOLDING_PERCENTAGE},
         PRIMARY KEY (id),
         INDEX `holding_percentage` (holding_percentage),
         INDEX j (asset_id,asset_label,content_owner)
         ) ENGINE=InnoDB DEFAULT CHARSET=UTF8";

    $createAmazonTableQueryResult = runQuery($createYoutubeTableQuery, $conn);

    // printArr($createAmazonTableQueryResult);exit;

    if (noError($createAmazonTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createAmazonTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}

function createYoutubeRedMusicReportTable3($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $US_HOLDING_PERCENTAGE = US_HOLDING_PERCENTAGE;
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`approx_daily_views` varchar(50) DEFAULT NULL,
		`asset_id` varchar(50)   DEFAULT NULL,
		`asset_type` varchar(250)  DEFAULT NULL,
		`status` varchar(250) DEFAULT NULL,
		`metadata_origination` varchar(250) DEFAULT NULL,
		`custom_id` varchar(250)  DEFAULT NULL,
        `ISRC` varchar(50) DEFAULT NULL,
        `UPC` varchar(50) DEFAULT NULL,
        `GRid` varchar(50) DEFAULT NULL,
        `artist` varchar(50) DEFAULT NULL,
		`season` varchar(250) DEFAULT NULL,
		`asset_title` varchar(250) DEFAULT NULL,
		`album` varchar(100)  DEFAULT NULL,
		`label` varchar(100) DEFAULT NULL,
		`constituent_asset_id` varchar(100)  DEFAULT NULL,
		`active_reference_id` varchar(100)   DEFAULT NULL,
		`inactive_reference_id` varchar(100) DEFAULT NULL,
		`match_policy` varchar(50) DEFAULT NULL,
		`is_merged` varchar(50) DEFAULT NULL,
		`ownership` varchar(100) DEFAULT NULL,
	 	`conflicting_country_code` varchar(50) DEFAULT NULL,
         `embedded_asset_id` varchar(50) DEFAULT NULL,
         `asset_label` varchar(50) DEFAULT NULL,
         `active_claims` varchar(50) DEFAULT NULL,
		`content_owner` varchar(100) DEFAULT NULL,
        `holding_percentage` decimal(10,2) DEFAULT {$US_HOLDING_PERCENTAGE},
         PRIMARY KEY (id),
         INDEX `holding_percentage` (holding_percentage),
         INDEX j (asset_id,asset_label,content_owner)
         ) ENGINE=InnoDB DEFAULT CHARSET=UTF8";

    $createAmazonTableQueryResult = runQuery($createYoutubeTableQuery, $conn);
    @unlink("polo.txt");
    file_put_contents("polo.txt", $createYoutubeTableQuery);
    @chmod("polo.txt", 0777);
    // printArr($createAmazonTableQueryResult);exit;

    if (noError($createAmazonTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createAmazonTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}
function insertYoutubeVideoClaimReportInfo($filePath, $tableName, $conn)
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
			IGNORE 1 ROWS (`adjustmentType`,`day`,`country`, `videoID`, `channelID`, `assetID`, `assetChannelID`, `assetType`, `cutsomID`, `contentType`, `policy`, `claimType`, `claimOrigin`, `ownedViews`, `youtubeRevenueSplitAuction`, `youtubeRevenueSplitReserved`, `youtubeRevenueSplitPartnerSoldYoutubeServed`, `youtubeRevenueSplitPartnerSoldPartnerServed`, `youtubeRevenueSplit`, `partnerRevenueAuction`, `partnerRevenueReserved`, `partnerRevenuePartnerSoldYouTubeServed`, `partnerRevenuePartnerSoldPartnerServed`,`partnerRevenue`);";
            $insertTableQueryResult = runQuery($insertTableQuery, $conn);

            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //    $a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }
            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insertYoutubeVideoClaimReportInfo($filePath, $tableName, $conn);
            } else {
                return setErrorStack($returnArr, -1, $res, null);
            }
        } else {
            return setErrorStack($returnArr, -1, $res, null);
        }
    } else {
        return setErrorStack($returnArr, -1, $res, null);
    }

    /* if (noError($insertTableQueryResult)) {
return setErrorStack($returnArr, -1, $res, null);
} else {
return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
} */
}

function insertYoutubeVideoClaimReportInfo2($filePath, $tableName, $conn)
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
			IGNORE 1 ROWS (`claim_id`,`claim_status`, `claim_status_detail`, `claim_origin`, `claim_type`, `asset_id`, `video_id`, `uploader`, `channel_id`, `channel_display_name`, `video_title`, `view`, `matching_duration`, `longest_match`, `content_type`, `reference_video_id`, `reference_id`, `claim_policy_id`, `asset_policy_id`, `claim_policy_monetize`, `claim_policy_track`, `claim_policy_block`, `asset_policy_monetize`, `asset_policy_track`, `asset_policy_block`, `claim_created_date`, `video_upload_date`, `custom_id`, `video_duration_sec`, `asset_title`, `asset_labels`, `tms`, `director`, `studio`, `season`, `episode_number`, `episode_title`, `release_date`, `hfa_song_code`, `isrc`, `grid`, `artist`, `album`, `record_label`, `upc`, `iswc`, `writers`);";

            $insertTableQueryResult = runQuery($insertTableQuery, $conn);

            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //    $a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {

                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }
            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insertYoutubeVideoClaimReportInfo2($filePath, $tableName, $conn);
            } else {
                return setErrorStack($returnArr, -1, $res, null);
            }
        } else {
            return setErrorStack($returnArr, -1, $res, null);
        }
    } else {
        return setErrorStack($returnArr, -1, $res, null);
    }

    /* if (noError($insertTableQueryResult)) {
return setErrorStack($returnArr, -1, $res, null);
} else {
return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
} */
}

function insertYoutubeRedMusicVideoReportInfo3($filePath, $tableName, $conn)
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
								IGNORE 1 ROWS (`approx_daily_views`,`asset_id`, `asset_type`, `status`, `metadata_origination`, `custom_id`, `isrc`, `grid`, `upc`, `artist`, `asset_title`, `album`, `label`, `constituent_asset_id`, `active_reference_id`, `inactive_reference_id`, `match_policy`, `is_merged`, `ownership`, `conflicting_country_code`, `embedded_asset_id`, `asset_label`, `active_claims`);";

            @unlink("polo.txt");
            file_put_contents("polo.txt", $insertTableQuery);
            @chmod("polo.txt", 0777);

            $insertTableQueryResult = runQuery($insertTableQuery, $conn);

            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //    $a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }
            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insertYoutubeRedMusicVideoReportInfo3($filePath, $tableName, $conn);
            } else {
                return setErrorStack($returnArr, -1, $res, null);
            }
        } else {
            return setErrorStack($returnArr, -1, $res, null);
        }
    } else {
        return setErrorStack($returnArr, -1, $res, null);
    }

    /* if (noError($insertTableQueryResult)) {
return setErrorStack($returnArr, -1, $res, null);
} else {
return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
} */
}

///////////
function insertYoutubeVideoClaimReportInfo3($filePath, $tableName, $conn)
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
								IGNORE 1 ROWS (`approx_daily_views`,`asset_id`, `asset_type`, `status`, `metadata_origination`, `custom_id`, `season`, `episode_title`, `tms`, `active_claims`, `constituent_asset_id`, `active_reference_id`, `inactive_reference_id`, `match_policy`, `is_merged`, `conflicting_owner`, `ownership`, `asset_label`, `conflicting_country_code`);";

            @unlink("polo.txt");
            file_put_contents("polo.txt", $insertTableQuery);
            @chmod("polo.txt", 0777);

            $insertTableQueryResult = runQuery($insertTableQuery, $conn);

            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //    $a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }
            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insertYoutubeVideoClaimReportInfo3($filePath, $tableName, $conn);
            } else {
                return setErrorStack($returnArr, -1, $res, null);
            }
        } else {
            return setErrorStack($returnArr, -1, $res, null);
        }
    } else {
        return setErrorStack($returnArr, -1, $res, null);
    }

    /* if (noError($insertTableQueryResult)) {
return setErrorStack($returnArr, -1, $res, null);
} else {
return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
} */
}

//red musci
function insertYoutubeRedMusicVideoReportInfo($filePath, $tableName, $conn)
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
            IGNORE 1 ROWS (`adjustmentType`,`day`,`country`, `videoID`, `channelID`, `assetID`, `assetChannelID`, `assetType`, `cutsomID`, `ISRC`, `UPC`, `GRid`, `contentType`, `policy`, `claimType`, `claimOrigin`, `ownedViews`, `youtubeRevenueSplitAuction`, `youtubeRevenueSplitReserved`, `youtubeRevenueSplitPartnerSoldYoutubeServed`, `youtubeRevenueSplitPartnerSoldPartnerServed`, `youtubeRevenueSplit`, `partnerRevenueAuction`, `partnerRevenueReserved`, `partnerRevenuePartnerSoldYouTubeServed`, `partnerRevenuePartnerSoldPartnerServed`,`partnerRevenue`);";

            @unlink("polo.txt");
            file_put_contents("polo.txt", $insertTableQuery);
            @chmod("polo.txt", 0777);

            $insertTableQueryResult = runQuery($insertTableQuery, $conn);

            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //    $a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }
            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insertYoutubeRedMusicVideoReportInfo($filePath, $tableName, $conn);
            } else {
                return setErrorStack($returnArr, -1, $res, null);
            }
        } else {
            return setErrorStack($returnArr, -1, $res, null);
        }
    } else {
        return setErrorStack($returnArr, -1, $res, null);
    }

    /* if (noError($insertTableQueryResult)) {
return setErrorStack($returnArr, -1, $res, null);
} else {
return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
} */
}

//activation start
function createActivationYoutubeReportTablev2($tableName, $conn)
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

function autoAssignContentOwnerChannelCOMapStep1($tableName, $conn)
{
    $returnArr = array();
    $res = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }

    //looping through array passed to create another array of where clauses
    //$whereClause = "assetChannelID=t1.assetChannelID";
    $whereClause = '';

    $a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
    $a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
    $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
    //$a3 = runQuery("SET sql_log_bin = 0;", $conn);


    $result_des = array_map('strrev', explode('_', strrev($tableName)));
    $nd_type = getNDTYPESFORCOMAPPING($result_des[2]);


    //content_owner
    // update on the basis of contentType='Partner-provided' with channel_co_maping-> partner_provided
    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}      inner join  	channel_co_maping on {$tableName}.assetChannelID = channel_co_maping.assetChannelID  SET {$tableName}.content_owner = channel_co_maping.partner_provided , {$tableName}.Label = channel_co_maping.Label ,autoassign_steps='1'  where ({$tableName}.content_owner='' || {$tableName}.content_owner is NULL) and {$tableName}.contentType='Partner-provided' ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

    // update on the basis of contentType='UGC' with channel_co_maping-> ugc
 

    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}      inner join  	channel_co_maping on {$tableName}.assetChannelID = channel_co_maping.assetChannelID  SET {$tableName}.content_owner = channel_co_maping.ugc  ,{$tableName}.Label = channel_co_maping.Label  ,{$tableName}.autoassign_steps='1' where ({$tableName}.content_owner='' || {$tableName}.content_owner is NULL) and {$tableName}.contentType !='Partner-provided' ";
    // file_put_contents(''.$tableName . date("ymd") . '.txt', $autoAssignChannelCOMapQuery, FILE_APPEND);
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

    //////////// updating holding_percentage 2022-05-14 ////////////////////////////
    // 
     // and partnerRevenue>0
    $client_youtube_sharesQuery = "UPDATE {$tableName} inner join channel_co_maping on {$tableName}.assetChannelID = channel_co_maping.assetChannelID  SET {$tableName}.holding_percentage = channel_co_maping.client_youtube_shares   where  channel_co_maping.CMS ='{$nd_type}' and country='US' ";
    
     file_put_contents('autoassign_shares_'.$tableName . date("ymd") . '.txt', $client_youtube_sharesQuery, FILE_APPEND);

    $client_youtube_sharesQueryResult = runQuery($client_youtube_sharesQuery, $conn);
    if (!noError($client_youtube_sharesQueryResult)) {
        return setErrorStack($returnArr, 3, $client_youtube_sharesQueryResult, null);
    }
    

    ////////////////end o updateing label //////////////////////
    $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
    $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
    //$a3 = runQuery("SET sql_log_bin = 1;", $conn);
    if (noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }
}

function autoAssignContentOwnerChannelCOMapStep2($tableName, $conn)
{
    $returnArr = array();
    $res = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }

    //looping through array passed to create another array of where clauses
    //$whereClause = "assetChannelID=t1.assetChannelID";
    $whereClause = '';

    $a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
    $a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
    $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
    //$a3 = runQuery("SET sql_log_bin = 0;", $conn);
    $tableNameReport1 = "";

    $findme = 'youtuberedmusic_video_report';
    $pos = strpos($tableName, $findme);

    if ($pos === false) {
        $table_exp = explode('youtube_video_claim_report2_', $tableName);
        $tableNameReport1 = "youtube_video_claim_report_" . $table_exp[1];

    } else {
        $table_exp = explode('youtuberedmusic_video_report2_redmusic_', $tableName);
        $tableNameReport1 = "youtuberedmusic_video_report_redmusic_" . $table_exp[1];

    }

    $tableView = "view_" . $tableName;
    //content_owner
    //self upate update on the basis of contentType='Partner-provided' with channel_co_maping-> partner_provided

     
    //drop view if exist. view sample : view_youtube_video_claim_report2_nd1_2020_12
    $autoAssignChannelCOMapQuery3 = "DROP VIEW IF EXISTS {$tableView} ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery3, $conn);

    //create view
    $autoAssignChannelCOMapQuery4 = "CREATE VIEW {$tableView} AS SELECT asset_id,count(asset_id) as assetCount,asset_labels,asset_title,content_owner,content_type FROM {$tableName} GROUP by asset_id ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery4, $conn);

    @unlink("polo_step2_vew.txt");
    file_put_contents("polo_step2_vew.txt", $autoAssignChannelCOMapQuery4);
    @chmod("polo_step2_vew.txt", 0777);
    
    //update orginal table  youtube_video_claim_report_ on asset id
    //{$tableNameReport1}.content_owner = {$tableView}.content_owner,
    $autoAssignChannelCOMapQuery5 = "UPDATE {$tableNameReport1}      inner join  	{$tableView} on {$tableNameReport1}.assetID = {$tableView}.asset_id  SET  {$tableNameReport1}.video_title = {$tableView}.asset_title    ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery5, $conn);

    @unlink("polo_step2.txt");
    file_put_contents("polo_step2.txt", $autoAssignChannelCOMapQuery5);
    @chmod("polo_step2.txt", 0777);
    //update orginal table  youtube_video_claim_report_ on label
    //{$tableNameReport1}.content_owner = {$tableView}.content_owner,
 
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

        //drop view after use
    $autoAssignChannelCOMapQuery3 = "DROP VIEW IF EXISTS {$tableView} ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery3, $conn);

        
    $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
    $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
    //$a3 = runQuery("SET sql_log_bin = 1;", $conn);
    if (noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }
}

function autoAssignContentOwnerChannelCOMapStep3($tableName, $conn)
{$returnArr = array();
    $res = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }

    //looping through array passed to create another array of where clauses
    //$whereClause = "assetChannelID=t1.assetChannelID";
    $whereClause = '';

    $a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
    $a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
    $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
    //$a3 = runQuery("SET sql_log_bin = 0;", $conn);
    $tableNameReport1 = "";

    // $mystring = $tableName;
    $findme = 'youtuberedmusic_video_report';
    $pos = strpos($tableName, $findme);

    if ($pos === false) {
        $table_exp = explode('youtube_video_claim_report3_', $tableName);
        $tableNameReport1 = "youtube_video_claim_report_" . $table_exp[1];

    } else {
        $table_exp = explode('youtuberedmusic_video_report3_redmusic_', $tableName);
        $tableNameReport1 = "youtuberedmusic_video_report_redmusic_" . $table_exp[1];

    }
    $CMS = "";
    $findme = 'nd1';
    $pos = strpos($tableNameReport1, $findme);
    if ($pos === false) {

    } else {
        $CMS = "ND1";
    }

    $findme = 'nd2';
    $pos = strpos($tableNameReport1, $findme);
    if ($pos === false) {

    } else {
        $CMS = "ND2";
    }

    $findme = 'ndkids';
    $pos = strpos($tableNameReport1, $findme);
    if ($pos === false) {

    } else {
        $CMS = "ND Kids";
    }

    $findme = 'redmusic';
    $pos = strpos($tableNameReport1, $findme);
    if ($pos === false) {

    } else {
        $CMS = "ND Music";
    }

    //ceating temp table on the basis of asset_id and label 2021-05-29
    $tableView = "t_pp_" . $tableName;
    $tableView2 = "t_ugc_" . $tableName;

    /////////////////////===================Now PP ===========================

    //drop TABLE if exist. TABLE sample : view_youtube_video_claim_report_nd1_2020_12
    $autoAssignChannelCOMapQuery3 = "DROP TABLE IF EXISTS {$tableView} ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery3, $conn);

    // create   table for PP

    $autoAssignChannelCOMapQuery = "CREATE TABLE {$tableView} AS SELECT r1.contentType, r1.assetID, COUNT(r1.videoID) AS cnt_video_id , cm.ugc, cm.partner_provided, cm.Label FROM {$tableNameReport1} r1 inner JOIN {$tableName} r3 on r1.assetID = r3.asset_id inner JOIN channel_co_maping cm on r3.asset_label = cm.Label WHERE r1.contentType = 'Partner-provided' AND r1.content_owner IS NULL and cm.CMS='{$CMS}' GROUP BY r1.assetID   ";

    @unlink("ppCreateTableStep3.txt");
    file_put_contents("ppCreateTableStep3.txt", $autoAssignChannelCOMapQuery);
    @chmod("ppCreateTableStep3.txt", 0777);

    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

    addIndexTempTable($tableView, $conn, 'partner_provided');
    addIndexTempTable($tableView, $conn, 'contentType');
    addIndexTempTable($tableView, $conn, 'Label');
    addIndexTempTable($tableView, $conn, 'asset_id');
    //update $tableName  on view
    // now update on asset id
    $autoAssignChannelCOMapQuery = "UPDATE {$tableNameReport1}      inner join {$tableView}  ON  {$tableNameReport1}.assetID = {$tableView}.assetID      SET {$tableNameReport1}.content_owner = {$tableView}.partner_provided,{$tableNameReport1}.autoassign_steps='3' , {$tableNameReport1}.Label={$tableView}.Label  where {$tableNameReport1}.contentType = 'Partner-provided' and  ({$tableNameReport1}.content_owner='' || {$tableNameReport1}.content_owner is NULL) ";

    @unlink("ppUpdateTableStep3.txt");
    file_put_contents("ppUpdateTableStep3.txt", $autoAssignChannelCOMapQuery);
    @chmod("ppUpdateTableStep3.txt", 0777);

    // assetid mapping
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);

    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

/////////////////////===================end PP ===========================

/////////////////////===================Now UGC ===========================
    // create   view for UGC
    $autoAssignChannelCOMapQuery3 = "DROP TABLE IF EXISTS {$tableView2} ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery3, $conn);

    // Working fine query if only UGC contentType

    $autoAssignChannelCOMapQuery = "CREATE TABLE {$tableView2} AS SELECT r1.contentType, r1.assetID, COUNT(r1.videoID) AS cnt_video_id, r1.videoID, cm.ugc, cm.partner_provided, cm.Label FROM {$tableNameReport1} r1 inner JOIN {$tableName} r3 on r1.assetID = r3.asset_id inner JOIN channel_co_maping cm on r3.asset_label = cm.Label WHERE r1.contentType != 'Partner-provided' AND r1.content_owner IS NULL  and cm.CMS='{$CMS}' GROUP BY r1.assetID   ";

    @unlink("ugcCreateTableStep3.txt");
    file_put_contents("ugcCreateTableStep3.txt", $autoAssignChannelCOMapQuery);
    @chmod("ugcCreateTableStep3.txt", 0777);

    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

    addIndexTempTable($tableView2, $conn, 'partner_provided');
    addIndexTempTable($tableView2, $conn, 'contentType');
    addIndexTempTable($tableView2, $conn, 'Label');
    addIndexTempTable($tableView2, $conn, 'asset_id');

    //update on assett id
    $autoAssignChannelCOMapQuery = "UPDATE {$tableNameReport1}      inner join  	{$tableView2} on  {$tableNameReport1}.assetID = {$tableView2}.assetID   SET {$tableNameReport1}.content_owner = {$tableView2}.ugc ,{$tableNameReport1}.autoassign_steps='3'  , {$tableNameReport1}.Label={$tableView2}.Label  where   {$tableNameReport1}.contentType != 'Partner-provided'  and  ({$tableNameReport1}.content_owner='' || {$tableNameReport1}.content_owner is NULL) ";

    @unlink("ugcUpdateTableStep3.txt");
    file_put_contents("ugcUpdateTableStep3.txt", $autoAssignChannelCOMapQuery);
    @chmod("ugcUpdateTableStep3.txt", 0777);

    // assetid mapping commented 23rd April
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);

    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

////////////////////===================end UGC ============================

    //drop TABLE if exist. TABLE sample : view_youtube_video_claim_report_nd1_2020_12
    $autoAssignChannelCOMapQuery3 = "DROP TABLE IF EXISTS {$tableView} ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery3, $conn);

    // create   view for UGC
    $autoAssignChannelCOMapQuery3 = "DROP TABLE IF EXISTS {$tableView2} ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery3, $conn);

        
    //end===============================   2021-05-29 ============================
 
    $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
    $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
    //$a3 = runQuery("SET sql_log_bin = 1;", $conn);
    if (noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

     

}

function createActivationYoutubeClaimReportTablev2($tableName, $conn)
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

function generateActicationYoutubeLabelEngineReportv2($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,witholding)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(partnerRevenue),8),0),
						0,
						0
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    

    if (noError($updateQueryResult)) {

        $result = array_map('strrev', explode('_', strrev($sourcetable)));
        if($result[2]=="redmusic"){
            $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
            set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudio')),
            a.amt_payable=ROUND((JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudio'))*a.total_amt_recd)/100,8),
            a.final_payable= ROUND(((a.total_amt_recd-a.witholding)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudio'))/100),8)
            where b.client_username =a.content_owner and b.`status` =1 and b.client_type_details!=''";
        } else {
            $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
            set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube')),
            a.amt_payable=ROUND((JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube'))*a.total_amt_recd)/100,8),
            a.final_payable= ROUND(((a.total_amt_recd-a.witholding)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube'))/100),8)
            where b.client_username =a.content_owner and b.`status` =1 and b.client_type_details!=''";
        }
      

        @unlink("polo_ACT_update.txt");
        file_put_contents("polo_ACT_update.txt", $updateQuery);
        @chmod("polo_ACT_update.txt", 0777);

        $updateQueryResult = runQuery($updateQuery, $conn);
        if (noError($updateQueryResult)) {
            return setErrorStack($returnArr, -1, $res, null);
        } else {
            return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
        }

        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
    }
}
function generateActicationYoutubeRedMusicReportv2($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,witholding)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(partnerRevenue),8),0),
						Coalesce(ROUND(SUM(CASE WHEN country='US' THEN partnerRevenue END),8),0),
						Coalesce(ROUND((SUM(CASE WHEN country='US' THEN partnerRevenue END)*30/100),8),0)
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    if (noError($updateQueryResult)) {

           //new code 
    $result_des = array_map('strrev', explode('_', strrev($desinationtable)));
    $nd_type = getNDTypes($result_des[2]);

        // update us payout withholding
        $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
						a.witholding = ROUND(((a.us_payout)*JSON_UNQUOTE(JSON_EXTRACT(b.client_youtube_shares, '$.{$nd_type}'))/100),8),
                        where b.client_username =a.content_owner   and b.`status` =1 and b.client_youtube_shares!=''";

        @unlink("polo_ACT_update1.txt");
        file_put_contents("polo_ACT_update1.txt", $updateQuery);
        @chmod("polo_ACT_update1.txt", 0777);

      //  $updateQueryResult = runQuery($updateQuery, $conn);


        //end update us payout withholding
        //end new code


        $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudio')),
						a.amt_payable=ROUND((JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudio'))*a.total_amt_recd)/100,8),
						a.final_payable= ROUND(((a.total_amt_recd-a.witholding)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudio'))/100),8)
                        where b.client_username =a.content_owner   and b.`status` =1 and b.client_type_details!=''";


 

        @unlink("polo_ACT_update.txt");
        file_put_contents("polo_ACT_update.txt", $updateQuery);
        @chmod("polo_ACT_update.txt", 0777);

        $updateQueryResult = runQuery($updateQuery, $conn);


        if (noError($updateQueryResult)) {
            return setErrorStack($returnArr, -1, $res, null);
        } else {
            return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
        }

        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
    }
}

function generateActicationYoutubeclaimReportv2($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,witholding)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(partnerRevenue),8),0),
						Coalesce(ROUND(SUM(CASE WHEN country='US' THEN partnerRevenue END),8),0),
						Coalesce(ROUND((SUM(CASE WHEN country='US' THEN partnerRevenue END)*30/100),8),0)
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    if (noError($updateQueryResult)) {
        $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube')),
						a.amt_payable=ROUND((JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube'))*a.total_amt_recd)/100,8),
						a.final_payable= ROUND(((a.total_amt_recd-a.witholding)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube'))/100),8)
                        where b.client_username =a.content_owner   and b.`status` =1 and b.client_type_details!=''";


 

        @unlink("polo_ACT_update.txt");
        file_put_contents("polo_ACT_update.txt", $updateQuery);
        @chmod("polo_ACT_update.txt", 0777);

        $updateQueryResult = runQuery($updateQuery, $conn);


        if (noError($updateQueryResult)) {
            return setErrorStack($returnArr, -1, $res, null);
        } else {
            return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
        }

        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
    }
}
function generateActication_youtube_ecommerce_paid_features_Reportv3($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();
   
     

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,holding_percentage)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(earnings),8),0),
						Coalesce(ROUND(SUM(CASE WHEN country='US' THEN earnings END),8),0),
                        Coalesce(ROUND(AVG(holding_percentage),8),0)
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    if (noError($updateQueryResult)) {

        //and r.holding_percentage > 0  
        $updateQuery1 = "UPDATE {$desinationtable}  m JOIN(     SELECT         content_owner,
        AVG(holding_percentage) AS holding_percentage     FROM     {$sourcetable}  r where r.country='US'    GROUP BY         content_owner ) r ON     m.content_owner = r.content_owner SET     m.holding_percentage = r.holding_percentage";

        @unlink("polo_hodling_update1.txt");
        file_put_contents("polo_hodling_update1.txt", $updateQuery1);
        @chmod("polo_hodling_update1.txt", 0777);

        $updateQueryResult1 = runQuery($updateQuery1, $conn);


        //new code 
    $result_des = array_map('strrev', explode('_', strrev($desinationtable)));
    $nd_type = getNDTypes($result_des[2]);

        $result = array_map('strrev', explode('_', strrev($sourcetable)));

        // update us current  data
      
 
        if($result[2]=="redmusic"){
         $updateQuery1 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.gst_percentage = b.gst_per,
                      
                        a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudio')),
                        a.witholding = ROUND(((a.us_payout) * a.holding_percentage/100),8),
                        a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
                        a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8),
                        a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
                        where b.client_username =a.content_owner   and b.`status` =1";
        } else {
            $updateQuery1 = "UPDATE  {$desinationtable} a,crep_cms_clients b
            set 
            a.gst_percentage = b.gst_per,
          
            a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube')),
            a.witholding = ROUND(((a.us_payout) * a.holding_percentage/100),8),
            a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
            a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8),
            a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
            where b.client_username =a.content_owner   and b.`status` =1";
        }
        @unlink("polo_ACT_update1.txt");
        file_put_contents("polo_ACT_update1.txt", $updateQuery1);
        @chmod("polo_ACT_update1.txt", 0777);

        $updateQueryResult1 = runQuery($updateQuery1, $conn);

   
        $updateQuery2 = "UPDATE  {$desinationtable} a,crep_cms_clients b
        set 
        a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
        a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8)
        where b.client_username =a.content_owner   and b.`status` =1 ";

        @unlink("polo_ACT_update2.txt");
        file_put_contents("polo_ACT_update2.txt", $updateQuery2);
        @chmod("polo_ACT_update2.txt", 0777);

        $updateQueryResult2 = runQuery($updateQuery2, $conn);

        $updateQuery3 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
                        where b.client_username =a.content_owner   and b.`status` =1 ";

        @unlink("polo_ACT_update3.txt");
        file_put_contents("polo_ACT_update3.txt", $updateQuery3);
        @chmod("polo_ACT_update3.txt", 0777);

        $updateQueryResult3 = runQuery($updateQuery3, $conn);

        //end update us payout withholding
        //end new code

       


        if (noError($updateQueryResult1)) {
            return setErrorStack($returnArr, -1, $res, null);
        } else {
            return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
        }

        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
    }
}
function generateActication_youtube_red_music_video_finance_Reportv3($sourcetable, $desinationtable, $conn)
{

  
    $res = array();
    $returnArr = array();
    $extraArg = array();
   
    

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,holding_percentage)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(partnerRevenue),8),0),
						Coalesce(ROUND(SUM(CASE WHEN country='US' THEN partnerRevenue END),8),0),
                        Coalesce(ROUND(AVG(holding_percentage),8),0)
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    if (noError($updateQueryResult)) {
// and r.holding_percentage > 0
        $updateQuery1 = "UPDATE {$desinationtable}  m JOIN(     SELECT         content_owner,
        AVG(holding_percentage) AS holding_percentage     FROM     {$sourcetable}  r where r.country='US'     GROUP BY         content_owner ) r ON     m.content_owner = r.content_owner SET     m.holding_percentage = r.holding_percentage";

        @unlink("polo_hodling_update1.txt");
        file_put_contents("polo_hodling_update1.txt", $updateQuery1);
        @chmod("polo_hodling_update1.txt", 0777);

        $updateQueryResult1 = runQuery($updateQuery1, $conn);
        
        //new code 
    $result_des = array_map('strrev', explode('_', strrev($desinationtable)));
    $nd_type = getNDTypes($result_des[2]);

        $result = array_map('strrev', explode('_', strrev($sourcetable)));

        // update us current  data
      
 
        if($result[2]=="redmusic"){
         $updateQuery1 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.gst_percentage = b.gst_per,
                        
                        a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudioRed')),
                        a.witholding = ROUND(((a.us_payout) * a.holding_percentage/100),8),
                        a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
                        a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8),
                        a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
                        where b.client_username =a.content_owner   and b.`status` =1 ";
        } else {
            $updateQuery1 = "UPDATE  {$desinationtable} a,crep_cms_clients b
            set 
            a.gst_percentage = b.gst_per,
             
            a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeRed')),
            a.witholding = ROUND(((a.us_payout) * a.holding_percentage/100),8),
            a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
            a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8),
            a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
            where b.client_username =a.content_owner   and b.`status` =1 ";
        }
        @unlink("polo_ACT_update1.txt");
        file_put_contents("polo_ACT_update1.txt", $updateQuery1);
        @chmod("polo_ACT_update1.txt", 0777);

        $updateQueryResult1 = runQuery($updateQuery1, $conn);

   
        $updateQuery2 = "UPDATE  {$desinationtable} a,crep_cms_clients b
        set 
        a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
        a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8)
        where b.client_username =a.content_owner   and b.`status` =1 ";

        @unlink("polo_ACT_update2.txt");
        file_put_contents("polo_ACT_update2.txt", $updateQuery2);
        @chmod("polo_ACT_update2.txt", 0777);

        $updateQueryResult2 = runQuery($updateQuery2, $conn);

        $updateQuery3 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
                        where b.client_username =a.content_owner   and b.`status` =1  ";

        @unlink("polo_ACT_update3.txt");
        file_put_contents("polo_ACT_update3.txt", $updateQuery3);
        @chmod("polo_ACT_update3.txt", 0777);

        $updateQueryResult3 = runQuery($updateQuery3, $conn);

        //end update us payout withholding
        //end new code

       


        if (noError($updateQueryResult1)) {
            return setErrorStack($returnArr, -1, $res, null);
        } else {
            return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
        }

        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
    }
}

function generateActicationYoutubeRedMusicReportv3($sourcetable, $desinationtable, $conn)
{

   
    $res = array();
    $returnArr = array();
    $extraArg = array();
   
    

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(partnerRevenue),8),0),
						Coalesce(ROUND(SUM(CASE WHEN country='US' THEN partnerRevenue END),8),0)
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    if (noError($updateQueryResult)) {
        //new code 
    $result_des = array_map('strrev', explode('_', strrev($desinationtable)));
    $nd_type = getNDTypes($result_des[2]);

        // update us current  data
      
        $updateQuery1 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.gst_percentage = b.gst_per,
                        a.holding_percentage = JSON_UNQUOTE(JSON_EXTRACT(b.client_youtube_shares, '$.{$nd_type}')),
                        a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudio')),
						a.witholding = ROUND(((a.us_payout)*JSON_UNQUOTE(JSON_EXTRACT(b.client_youtube_shares, '$.{$nd_type}'))/100),8),
                        a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
                        a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8),
                        a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
                        where b.client_username =a.content_owner   and b.`status` =1 and b.client_youtube_shares!=''";

        @unlink("polo_ACT_update1.txt");
        file_put_contents("polo_ACT_update1.txt", $updateQuery1);
        @chmod("polo_ACT_update1.txt", 0777);

        $updateQueryResult1 = runQuery($updateQuery1, $conn);

   
        $updateQuery2 = "UPDATE  {$desinationtable} a,crep_cms_clients b
        set 
        a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
        a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8)
        where b.client_username =a.content_owner   and b.`status` =1 and b.client_youtube_shares!=''";

        @unlink("polo_ACT_update2.txt");
        file_put_contents("polo_ACT_update2.txt", $updateQuery2);
        @chmod("polo_ACT_update2.txt", 0777);

        $updateQueryResult2 = runQuery($updateQuery2, $conn);

        $updateQuery3 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
                        where b.client_username =a.content_owner   and b.`status` =1 and b.client_youtube_shares!=''";

        @unlink("polo_ACT_update3.txt");
        file_put_contents("polo_ACT_update3.txt", $updateQuery3);
        @chmod("polo_ACT_update3.txt", 0777);

        $updateQueryResult3 = runQuery($updateQuery3, $conn);

        //end update us payout withholding
        //end new code

       


        if (noError($updateQueryResult1)) {
            return setErrorStack($returnArr, -1, $res, null);
        } else {
            return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
        }

        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
    }
}


function generateActicationYoutubeLabelEngineReportv3($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,witholding)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(partnerRevenue),8),0),
						0,
						0
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

     
    if (noError($updateQueryResult)) {
        //new code 
    $result_des = array_map('strrev', explode('_', strrev($desinationtable)));
    $nd_type = getNDTypes($result_des[2]);

        $result = array_map('strrev', explode('_', strrev($sourcetable)));

        // update us current  data
      
 
        if($result[2]=="redmusic"){
         $updateQuery1 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.gst_percentage = b.gst_per,
                        a.holding_percentage = JSON_UNQUOTE(JSON_EXTRACT(b.client_youtube_shares, '$.{$nd_type}')),
                        a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudio')),
						a.witholding = ROUND(((a.us_payout)*JSON_UNQUOTE(JSON_EXTRACT(b.client_youtube_shares, '$.{$nd_type}'))/100),8),
                        a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
                        a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8),
                        a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
                        where b.client_username =a.content_owner   and b.`status` =1 and b.client_youtube_shares!=''";
        } else {
            $updateQuery1 = "UPDATE  {$desinationtable} a,crep_cms_clients b
            set 
            a.gst_percentage = b.gst_per,
            a.holding_percentage = JSON_UNQUOTE(JSON_EXTRACT(b.client_youtube_shares, '$.{$nd_type}')),
            a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube')),
            a.witholding = ROUND(((a.us_payout)*JSON_UNQUOTE(JSON_EXTRACT(b.client_youtube_shares, '$.{$nd_type}'))/100),8),
            a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
            a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8),
            a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
            where b.client_username =a.content_owner   and b.`status` =1 and b.client_youtube_shares!=''";
        }
        @unlink("polo_ACT_update1.txt");
        file_put_contents("polo_ACT_update1.txt", $updateQuery1);
        @chmod("polo_ACT_update1.txt", 0777);

        $updateQueryResult1 = runQuery($updateQuery1, $conn);

   
        $updateQuery2 = "UPDATE  {$desinationtable} a,crep_cms_clients b
        set 
        a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
        a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8)
        where b.client_username =a.content_owner   and b.`status` =1 and b.client_youtube_shares!=''";

        @unlink("polo_ACT_update2.txt");
        file_put_contents("polo_ACT_update2.txt", $updateQuery2);
        @chmod("polo_ACT_update2.txt", 0777);

        $updateQueryResult2 = runQuery($updateQuery2, $conn);

        $updateQuery3 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
                        where b.client_username =a.content_owner   and b.`status` =1 and b.client_youtube_shares!=''";

        @unlink("polo_ACT_update3.txt");
        file_put_contents("polo_ACT_update3.txt", $updateQuery3);
        @chmod("polo_ACT_update3.txt", 0777);

        $updateQueryResult3 = runQuery($updateQuery3, $conn);

        //end update us payout withholding
        //end new code

       


        if (noError($updateQueryResult1)) {
            return setErrorStack($returnArr, -1, $res, null);
        } else {
            return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
        }

        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
    }
}


function generateActicationwithholdingPercentageReportv4($sourcetable, $desinationtable, $conn)
{
 
 
}

function generateActicationCommonReportv4($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();
   
    

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,holding_percentage)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(partnerRevenue),8),0),
						Coalesce(ROUND(SUM(CASE WHEN country='US' THEN partnerRevenue END),8),0),
                        Coalesce(ROUND(AVG(holding_percentage),8),0)
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    if (noError($updateQueryResult)) {
        //and r.holding_percentage > 0  
        $updateQuery1 = "UPDATE {$desinationtable}  m JOIN(     SELECT         content_owner,
        AVG(holding_percentage) AS holding_percentage     FROM     {$sourcetable}  r where r.country='US'    GROUP BY         content_owner ) r ON     m.content_owner = r.content_owner SET     m.holding_percentage = r.holding_percentage";

        @unlink("polo_hodling_update1.txt");
        file_put_contents("polo_hodling_update1.txt", $updateQuery1);
        @chmod("polo_hodling_update1.txt", 0777);

        $updateQueryResult1 = runQuery($updateQuery1, $conn);

        //new code 
    $result_des = array_map('strrev', explode('_', strrev($desinationtable)));
    $nd_type = getNDTypes($result_des[2]);

        // update us current  data
        
      //  a.holding_percentage = JSON_UNQUOTE(JSON_EXTRACT(b.client_youtube_shares, '$.{$nd_type}')),
        $updateQuery1 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.gst_percentage = b.gst_per,
                        a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube')),
						a.witholding = ROUND(((a.us_payout) * a.holding_percentage/100),8),
                        a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
                        a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8),
                        a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
                        where b.client_username =a.content_owner   and b.`status` =1 ";

        @unlink("polo_ACT_update1.txt");
        file_put_contents("polo_ACT_update1.txt", $updateQuery1);
        @chmod("polo_ACT_update1.txt", 0777);

        $updateQueryResult1 = runQuery($updateQuery1, $conn);

   
        $updateQuery2 = "UPDATE  {$desinationtable} a,crep_cms_clients b
        set 
        a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
        a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8)
        where b.client_username =a.content_owner   and b.`status` =1 ";

        @unlink("polo_ACT_update2.txt");
        file_put_contents("polo_ACT_update2.txt", $updateQuery2);
        @chmod("polo_ACT_update2.txt", 0777);

        $updateQueryResult2 = runQuery($updateQuery2, $conn);

        $updateQuery3 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
                        where b.client_username =a.content_owner   and b.`status` =1 ";

        @unlink("polo_ACT_update3.txt");
        file_put_contents("polo_ACT_update3.txt", $updateQuery3);
        @chmod("polo_ACT_update3.txt", 0777);

        $updateQueryResult3 = runQuery($updateQuery3, $conn);

        //end update us payout withholding
        //end new code

       


        if (noError($updateQueryResult1)) {
            return setErrorStack($returnArr, -1, $res, null);
        } else {
            return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
        }

        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
    }
}
function generateActicationCommonReportv3($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();
   
    

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(partnerRevenue),8),0),
						Coalesce(ROUND(SUM(CASE WHEN country='US' THEN partnerRevenue END),8),0)
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    if (noError($updateQueryResult)) {
        //new code 
    $result_des = array_map('strrev', explode('_', strrev($desinationtable)));
    $nd_type = getNDTypes($result_des[2]);

        // update us current  data
      
        $updateQuery1 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.gst_percentage = b.gst_per,
                        a.holding_percentage = JSON_UNQUOTE(JSON_EXTRACT(b.client_youtube_shares, '$.{$nd_type}')),
                        a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube')),
						a.witholding = ROUND(((a.us_payout)*JSON_UNQUOTE(JSON_EXTRACT(b.client_youtube_shares, '$.{$nd_type}'))/100),8),
                        a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
                        a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8),
                        a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
                        where b.client_username =a.content_owner   and b.`status` =1 and b.client_youtube_shares!=''";

        @unlink("polo_ACT_update1.txt");
        file_put_contents("polo_ACT_update1.txt", $updateQuery1);
        @chmod("polo_ACT_update1.txt", 0777);

        $updateQueryResult1 = runQuery($updateQuery1, $conn);

   
        $updateQuery2 = "UPDATE  {$desinationtable} a,crep_cms_clients b
        set 
        a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
        a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8)
        where b.client_username =a.content_owner   and b.`status` =1 and b.client_youtube_shares!=''";

        @unlink("polo_ACT_update2.txt");
        file_put_contents("polo_ACT_update2.txt", $updateQuery2);
        @chmod("polo_ACT_update2.txt", 0777);

        $updateQueryResult2 = runQuery($updateQuery2, $conn);

        $updateQuery3 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
                        where b.client_username =a.content_owner   and b.`status` =1 and b.client_youtube_shares!=''";

        @unlink("polo_ACT_update3.txt");
        file_put_contents("polo_ACT_update3.txt", $updateQuery3);
        @chmod("polo_ACT_update3.txt", 0777);

        $updateQueryResult3 = runQuery($updateQuery3, $conn);

        //end update us payout withholding
        //end new code

       


        if (noError($updateQueryResult1)) {
            return setErrorStack($returnArr, -1, $res, null);
        } else {
            return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
        }

        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
    }
}
function generateActicationYoutubeclaimReportv3($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();
   
    

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(partnerRevenue),8),0),
						Coalesce(ROUND(SUM(CASE WHEN country='US' THEN partnerRevenue END),8),0)
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    if (noError($updateQueryResult)) {
        //new code 
    $result_des = array_map('strrev', explode('_', strrev($desinationtable)));
    $nd_type = getNDTypes($result_des[2]);

        // update us current  data
      
        $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.gst_percentage = b.gst_per,
                        a.holding_percentage = JSON_UNQUOTE(JSON_EXTRACT(b.client_youtube_shares, '$.{$nd_type}')),
                        a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube')),
						a.witholding = ROUND(((a.us_payout)*JSON_UNQUOTE(JSON_EXTRACT(b.client_youtube_shares, '$.{$nd_type}'))/100),8),
                        a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
                        a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8),
                        a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
                        where b.client_username =a.content_owner   and b.`status` =1 and b.client_youtube_shares!=''";

        @unlink("polo_ACT_update1.txt");
        file_put_contents("polo_ACT_update1.txt", $updateQuery);
        @chmod("polo_ACT_update1.txt", 0777);

        $updateQueryResult = runQuery($updateQuery, $conn);


        //end update us payout withholding
        //end new code

       /*  $updateQuery = "UPDATE  {$desinationtable} a 
		                set 
						a.amt_payable=ROUND((a.shares * a.total_amt_recd)/100,8),
						a.final_payable= ROUND(((a.total_amt_recd - a.witholding) * a.shares / 100),8),
                        a.final_payable_with_gst= ROUND(a.final_payable + (final_payable * gst_percentage / 100),8)
                         ";

        @unlink("polo_ACT_update.txt");
        file_put_contents("polo_ACT_update.txt", $updateQuery);
        @chmod("polo_ACT_update.txt", 0777);
 */
      //  $updateQueryResult = runQuery($updateQuery, $conn);


        if (noError($updateQueryResult)) {
            return setErrorStack($returnArr, -1, $res, null);
        } else {
            return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
        }

        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
    }
}
function generateActication_youtube_red_music_video_finance_Reportv2($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO
					{$desinationtable} (content_owner, total_amt_recd,us_payout,witholding)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(partnerRevenue),8),0),
						Coalesce(ROUND(SUM(CASE WHEN country='US' THEN partnerRevenue END),8),0),
						Coalesce(ROUND((SUM(CASE WHEN country='US' THEN partnerRevenue END)*30/100),8),0)
                    FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    $updateQueryResult = runQuery($updateQuery, $conn);

    if (noError($updateQueryResult)) {

        $result = array_map('strrev', explode('_', strrev($sourcetable)));

           //new code 
           $result_des = array_map('strrev', explode('_', strrev($desinationtable)));   
        $nd_type = getNDTypes($result_des[2]);

        // update us payout withholding
        $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
						a.witholding = ROUND(((a.us_payout)*JSON_UNQUOTE(JSON_EXTRACT(b.client_youtube_shares, '$.{$nd_type}'))/100),8),
                        where b.client_username =a.content_owner   and b.`status` =1 and b.client_youtube_shares!=''";

        @unlink("polo_ACT_update1.txt");
        file_put_contents("polo_ACT_update1.txt", $updateQuery);
        @chmod("polo_ACT_update1.txt", 0777);

      //  $updateQueryResult = runQuery($updateQuery, $conn);


        //end update us payout withholding
        //end new code


        if($result[2]=="redmusic"){
            $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
            set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudioRed')),
            a.amt_payable=ROUND((JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudioRed'))*a.total_amt_recd)/100,8),
            a.final_payable= ROUND(((a.total_amt_recd-a.witholding)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudioRed'))/100),8)
            where b.client_username =a.content_owner and b.`status` =1 and b.client_type_details!=''";

        } else {
            $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
            set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeRed')),
            a.amt_payable=ROUND((JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeRed'))*a.total_amt_recd)/100,8),
            a.final_payable= ROUND(((a.total_amt_recd-a.witholding)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeRed'))/100),8)
            where b.client_username =a.content_owner and b.`status` =1 and b.client_type_details!=''";

        }
       
        @unlink("polo_ACT_update.txt");
        file_put_contents("polo_ACT_update.txt", $updateQuery);
        @chmod("polo_ACT_update.txt", 0777);

        $updateQueryResult = runQuery($updateQuery, $conn);
        if (noError($updateQueryResult)) {
            return setErrorStack($returnArr, -1, $res, null);
        } else {
            return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
        }

        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
    }
}

function generateActication_youtube_ecommerce_paid_features_Reportv2($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO
					{$desinationtable} (content_owner, total_amt_recd,us_payout,witholding)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(earnings),8),0),
						Coalesce(ROUND(SUM(CASE WHEN country='US' THEN earnings END),8),0),
						Coalesce(ROUND((SUM(CASE WHEN country='US' THEN earnings END)*30/100),8),0)

                    FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    $updateQueryResult = runQuery($updateQuery, $conn);

    if (noError($updateQueryResult)) {
        
        $result = array_map('strrev', explode('_', strrev($sourcetable)));

   //new code 
   $result_des = array_map('strrev', explode('_', strrev($desinationtable)));   
   $nd_type = getNDTypes($result_des[2]);

       // update us payout withholding
       $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
                       set 
                       a.witholding = ROUND(((a.us_payout)*JSON_UNQUOTE(JSON_EXTRACT(b.client_youtube_shares, '$.{$nd_type}'))/100),8),
                       where b.client_username =a.content_owner   and b.`status` =1 and b.client_youtube_shares!=''";

       @unlink("polo_ACT_update1.txt");
       file_put_contents("polo_ACT_update1.txt", $updateQuery);
       @chmod("polo_ACT_update1.txt", 0777);

      // $updateQueryResult = runQuery($updateQuery, $conn);


       //end update us payout withholding
       //end new code


        if($result[2]=="redmusic"){

            $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
            set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudio')),
            a.amt_payable=ROUND((JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudio'))*a.total_amt_recd)/100,8),
            a.final_payable= ROUND(((a.total_amt_recd-a.witholding)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeAudio'))/100),8)
            where b.client_username =a.content_owner and b.`status` =1 and b.client_type_details!=''";

        } else {
            $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
            set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube')),
            a.amt_payable=ROUND((JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube'))*a.total_amt_recd)/100,8),
            a.final_payable= ROUND(((a.total_amt_recd-a.witholding)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube'))/100),8)
            where b.client_username =a.content_owner and b.`status` =1 and b.client_type_details!=''";

        }
       
        @unlink("polo_ACT_upd.txt");
        file_put_contents("polo_ACT_upd.txt", $updateQuery);
        @chmod("polo_ACT_upd.txt", 0777);

        $updateQueryResult = runQuery($updateQuery, $conn);
        if (noError($updateQueryResult)) {
            return setErrorStack($returnArr, -1, $res, null);
        } else {
            return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
        }

        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
    }
}

function addIndexTempTable($tableName, $conn, $additionalField = '')
{
    $returnArr = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " {$tableName} to create index be empty", null);
    }

    $res = array();

    $truncateTableQuery = "ALTER TABLE {$tableName} ADD INDEX( `videoID`)";
    $truncateTableQueryResult = runQuery($truncateTableQuery, $conn);

    $truncateTableQuery = "ALTER TABLE {$tableName} ADD INDEX( `assetID`)";
    $truncateTableQueryResult = runQuery($truncateTableQuery, $conn);

    if ($additionalField != '') {
        $truncateTableQuery = "ALTER TABLE {$tableName} ADD INDEX( `" . $additionalField . "`)";
        $truncateTableQueryResult = runQuery($truncateTableQuery, $conn);
    }

    if (noError($truncateTableQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $truncateTableQueryResult["errMsg"], null);
    }
}

function addIndexTempTable3($tableName, $conn, $additionalField = '')
{
    $returnArr = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " {$tableName} to create index be empty", null);
    }

    $res = array();

    if ($additionalField != '') {
        $truncateTableQuery = "ALTER TABLE {$tableName} ADD INDEX( `" . $additionalField . "`)";
        $truncateTableQueryResult = runQuery($truncateTableQuery, $conn);
    }

    if (noError($truncateTableQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $truncateTableQueryResult["errMsg"], null);
    }
}

function autoAssignContentOwnerChannelCOMapStep3_redmusic($tableName, $conn)
{$returnArr = array();
    $res = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }

    //looping through array passed to create another array of where clauses
    //$whereClause = "assetChannelID=t1.assetChannelID";
    $whereClause = '';

    $a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
    $a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
    $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
    //$a3 = runQuery("SET sql_log_bin = 0;", $conn);
    $tableNameReport1 = "";
    $table_exp = explode('youtuberedmusic_video_report3_redmusic_', $tableName);

    $tableNameReport1 = "youtuberedmusic_video_report_redmusic_" . $table_exp[1];
    $tableView = "view_pp_" . $tableName;
    $tableView2 = "view_ugc_" . $tableName;
    //content_owner
    // self update on the basis of  label with channel_co_maping-> Label

/*     // They said that we should consider this as partner_provided only as asset lable file does not have ugc records.
$autoAssignChannelCOMapQuery = "UPDATE {$tableName}      inner join      channel_co_maping on {$tableName}.asset_label = channel_co_maping.Label  SET {$tableName}.content_owner = channel_co_maping.partner_provided ";
$autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);

if (!noError($autoAssignChannelCOMapQueryResult)) {
return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
} */

    /*  @unlink("polo.txt");
    file_put_contents("polo.txt", $autoAssignChannelCOMapQuery);
    @chmod("polo.txt",0777);
     */
    //update orginal table  youtube_video_claim_report_ on label
    //$autoAssignChannelCOMapQuery = "UPDATE {$tableNameReport1}      inner join      {$tableName} on {$tableNameReport1}.Label = {$tableName}.asset_label  SET {$tableNameReport1}.content_owner = {$tableName}.content_owner,{$tableNameReport1}.video_title = {$tableName}.episode_title    where ({$tableNameReport1}.content_owner='' || {$tableNameReport1}.content_owner is NULL)   ";

    // $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);

    /*
    // They said that we should consider this as partner_provided only as asset asset_id count is 1 and consider UGC count more than 1.

    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}      inner join      channel_co_maping on {$tableName}.asset_label = channel_co_maping.Label  SET {$tableName}.content_owner = channel_co_maping.partner_provided ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);

    if (!noError($autoAssignChannelCOMapQueryResult)) {
    return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    } */

    //drop view if exist. view sample : view_youtube_video_claim_report_nd1_2020_12
    $autoAssignChannelCOMapQuery3 = "DROP TABLE IF EXISTS {$tableView} ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery3, $conn);

    // create   view for PP
    $autoAssignChannelCOMapQuery = "CREATE TABLE {$tableView} AS SELECT r1.contentType, r1.assetID, COUNT(r1.videoID) AS cnt_video_id, r1.videoID, cm.ugc, cm.partner_provided, cm.Label FROM {$tableNameReport1} r1 inner JOIN {$tableName} r3 on r1.assetID = r3.asset_id inner JOIN channel_co_maping cm on r3.asset_label = cm.Label WHERE r1.contentType = 'Partner-provided' AND r1.content_owner IS NULL GROUP BY r1.assetID, r1.videoID  ";

    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

    addIndexTempTable($tableView, $conn);

    //update $tableName  on view
    $autoAssignChannelCOMapQuery = "UPDATE {$tableNameReport1}      inner join {$tableView}  ON  {$tableNameReport1}.videoID = {$tableView}.videoID      SET {$tableNameReport1}.content_owner = {$tableView}.partner_provided where {$tableNameReport1}.contentType = 'Partner-provided' and  ({$tableNameReport1}.content_owner='' || {$tableNameReport1}.content_owner is NULL) ";

    @unlink("polo.txt");
    file_put_contents("polo.txt", $autoAssignChannelCOMapQuery);
    @chmod("polo.txt", 0777);

    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);

    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

    // create   view for UGC
    $autoAssignChannelCOMapQuery3 = "DROP TABLE IF EXISTS {$tableView2} ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery3, $conn);

    $autoAssignChannelCOMapQuery = "CREATE TABLE {$tableView2} AS SELECT r1.contentType, r1.assetID, COUNT(r1.videoID) AS cnt_video_id, r1.videoID, cm.ugc, cm.partner_provided, cm.Label FROM {$tableNameReport1} r1 inner JOIN {$tableName} r3 on r1.assetID = r3.asset_id inner JOIN channel_co_maping cm on r3.asset_label = cm.Label WHERE r1.contentType = 'UGC' AND r1.content_owner IS NULL GROUP BY r1.assetID, r1.videoID  ";

    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

    addIndexTempTable($tableView2, $conn);

    //update $tableName  on view
    $autoAssignChannelCOMapQuery = "UPDATE {$tableNameReport1}      inner join  	{$tableView2} on  {$tableNameReport1}.videoID = {$tableView2}.videoID   SET {$tableNameReport1}.content_owner = {$tableView2}.ugc  where   {$tableNameReport1}.contentType = 'UGC'  and  ({$tableNameReport1}.content_owner='' || {$tableNameReport1}.content_owner is NULL) ";

    @unlink("polo1.txt");
    file_put_contents("polo1.txt", $autoAssignChannelCOMapQuery);
    @chmod("polo1.txt", 0777);

    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);

    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }
 
    /*  $autoAssignChannelCOMapQuery3 = "DROP VIEW IF EXISTS {$tableView} ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery3, $conn);
     */
    $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
    $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
    //$a3 = runQuery("SET sql_log_bin = 1;", $conn);
    if (noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

}
function getNDTypes($nd_types='nd1'){

    $CMS_TYPE = "";
    if ($nd_types == "nd1") {
        $CMS_TYPE = "ND1";
    }
    if ($nd_types == "nd2") {
        $CMS_TYPE = "ND2";
    }
    if ($nd_types == "ndkids") {
        $CMS_TYPE = "ND_Kids";
    }
    if ($nd_types == "redmusic") {
        $CMS_TYPE = "ND_Music";
    }
    return $CMS_TYPE;
}
function getNDTYPESFORCOMAPPING($nd_types='nd1'){
    $CMS_TYPE = "";
    if ($nd_types == "nd1") {
        $CMS_TYPE = "ND1";
    }
    if ($nd_types == "nd2") {
        $CMS_TYPE = "ND2";
    }
    if ($nd_types == "ndkids") {
        $CMS_TYPE = "nd kids";
    }
    if ($nd_types == "redmusic") {
        $CMS_TYPE = "ND Music";
    }
    return $CMS_TYPE;
}


/// 2022-06-18


function create_non_cgm_main_report_Tablev4($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $US_HOLDING_PERCENTAGE = US_HOLDING_PERCENTAGE;
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`Adjustment_Type` varchar(50) DEFAULT NULL,
        `Day` varchar(10)   DEFAULT NULL,
		`Country` varchar(5)   DEFAULT NULL,
		`Video_ID` varchar(50)  DEFAULT NULL,
		`Channel_ID` varchar(50) DEFAULT NULL,
		`Asset_ID` varchar(50) DEFAULT NULL,
        `Asset_Label` varchar(150) DEFAULT NULL,
		`Asset_Channel_ID` varchar(50)  DEFAULT NULL,
		`Asset_Type` varchar(50) DEFAULT NULL,
        `Custom_ID` varchar(50) DEFAULT NULL,
		`ISRC` varchar(50) DEFAULT NULL,
        `ISWC` varchar(50) DEFAULT NULL,
        `HFA_Song_Code` varchar(50) DEFAULT NULL,
		`Content_Type` varchar(50)  DEFAULT NULL,
		`Policy` varchar(50) DEFAULT NULL,
		`Claim_Type` varchar(50)  DEFAULT NULL,
        `Claim_Origin` varchar(50)  DEFAULT NULL,
		`Music_Claim_Type` varchar(50)   DEFAULT NULL,
        `Composition_Right_Type` varchar(50)   DEFAULT NULL,
        `Ownership_Percentage` varchar(50)   DEFAULT NULL,
		`Owned_Views` int(10) DEFAULT NULL,
		`YouTube_Revenue_Split_Auction` decimal(18,8) DEFAULT NULL,
		`YouTube_Revenue_Split_Reserved` decimal(18,8) DEFAULT NULL,
		`YouTube_Revenue_Split_Partner_Sold_YouTube_Served` decimal(18,8) DEFAULT NULL,
		`YouTube_Revenue_Split_Partner_Sold_Partner_Served` decimal(18,8) DEFAULT NULL,
        `YouTube_Revenue_Split` decimal(18,8) DEFAULT NULL,
		`Partner_Revenue_Auction` decimal(18,8) DEFAULT NULL,
		`Partner_Revenue_Reserved` decimal(18,8) DEFAULT NULL,
		`Partner_Revenue_Partner_Sold_YouTube_Served` decimal(18,8) DEFAULT NULL,
		`Partner_Revenue_Partner_Sold_Partner_Served` decimal(18,8) DEFAULT NULL,
		`partnerRevenue` decimal(18,8) DEFAULT NULL,
        `content_owner` varchar(50) DEFAULT NULL,
	    `autoassign_steps` varchar(10) DEFAULT NULL,
        `holding_percentage` decimal(10,2) DEFAULT {$US_HOLDING_PERCENTAGE},
		 PRIMARY KEY (id),
         INDEX i (Asset_Channel_ID),
         INDEX Content_Type (Content_Type),
         INDEX Channel_ID (Channel_ID),
         INDEX Asset_ID (Asset_ID),
         INDEX `Asset_Label` (Asset_Label),
         INDEX `Video_ID` (Video_ID),
         INDEX partnerRevenue (partnerRevenue),
         INDEX `holding_percentage` (holding_percentage),
         INDEX `content_owner` (content_owner)
		 ) ENGINE=InnoDB DEFAULT CHARSET=UTF8";

        @unlink("polo_create_cgm.txt");
        file_put_contents("polo_create_cgm.txt", $createYoutubeTableQuery);
        @chmod("polo_create_cgm.txt", 0777);

    $createAmazonTableQueryResult = runQuery($createYoutubeTableQuery, $conn);
    // printArr($createAmazonTableQueryResult);exit;

    if (noError($createAmazonTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createAmazonTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}

function insert_non_cgm_main_reportv4($filePath, $tableName, $conn)
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
			IGNORE 1 ROWS (`Adjustment_Type`,`Day`,`Country`, `Video_ID`, `Channel_ID`, `Asset_ID`, `Asset_Label`, `Asset_Channel_ID`, `Asset_Type`, `Custom_ID`, `ISRC`, `ISWC`, `HFA_Song_Code`, `Content_Type`, `Policy`, `Claim_Type`, `Claim_Origin`, `Music_Claim_Type`, `Composition_Right_Type`, `Ownership_Percentage`, `Owned_Views`, `YouTube_Revenue_Split_Auction`, `YouTube_Revenue_Split_Reserved`,`YouTube_Revenue_Split_Partner_Sold_YouTube_Served`, `YouTube_Revenue_Split_Partner_Sold_Partner_Served`, `YouTube_Revenue_Split`, `Partner_Revenue_Auction`, `Partner_Revenue_Reserved`, `Partner_Revenue_Partner_Sold_YouTube_Served`, `Partner_Revenue_Partner_Sold_Partner_Served`, `partnerRevenue`);";


            @unlink("polo_create_cmg.txt");
            file_put_contents("polo_create_cmg.txt", $insertTableQuery);
            @chmod("polo_create_cmg.txt", 0777);


            $insertTableQueryResult = runQuery($insertTableQuery, $conn);

            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //    $a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }
            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insert_non_cgm_main_reportv4($filePath, $tableName, $conn);
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


function autoAssignContentOwnernon_cgm_mainv4($tableName, $conn)
{
    $returnArr = array();
    $res = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }

    //looping through array passed to create another array of where clauses
    //$whereClause = "assetChannelID=t1.assetChannelID";
    $whereClause = '';

    $a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
    $a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
    $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
    //$a3 = runQuery("SET sql_log_bin = 0;", $conn);


  

    //content_owner
    // update on the basis of contentType='Partner-provided' with channel_co_maping-> partner_provided
    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}      inner join  	channel_co_maping on {$tableName}.Asset_Label = channel_co_maping.Label  SET {$tableName}.content_owner = channel_co_maping.partner_provided    where ({$tableName}.content_owner='' || {$tableName}.content_owner is NULL)   ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }
    //DEFAULT_CONTENT_OWNER_NONCMG
    $DEFAULT_CONTENT_OWNER_NONCMG = DEFAULT_CONTENT_OWNER_NONCMG;
    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}       SET {$tableName}.content_owner = '{$DEFAULT_CONTENT_OWNER_NONCMG}'    where ({$tableName}.content_owner='' || {$tableName}.content_owner is NULL)   ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

    
     
 
}


 

//// red-susbcription



function create_non_cgm_redsubscription_report_Tablev4($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $US_HOLDING_PERCENTAGE = US_HOLDING_PERCENTAGE;
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`Adjustment_Type` varchar(50) DEFAULT NULL,
        `Country` varchar(5)   DEFAULT NULL,
        `Day` varchar(10)   DEFAULT NULL,
		`Video_ID` varchar(50)  DEFAULT NULL,
		`Video_Channel_ID` varchar(50) DEFAULT NULL,
		`Asset_ID` varchar(50) DEFAULT NULL,
        `Asset_Title` varchar(50) DEFAULT NULL,
        `Asset_Label` varchar(150) DEFAULT NULL,
		`Asset_Channel_ID` varchar(50)  DEFAULT NULL,
		`Claiming_Asset_Type` varchar(50) DEFAULT NULL,
        `Custom_ID` varchar(50) DEFAULT NULL,
		`ISRC` varchar(50) DEFAULT NULL,
        `ISWC` varchar(50) DEFAULT NULL,
        `HFA_Song_Code` varchar(50) DEFAULT NULL,
        `Writers` varchar(50) DEFAULT NULL,
		`Composition_Right_Type` varchar(50)  DEFAULT NULL,
		`Ownership_Percentage` varchar(50) DEFAULT NULL,
		`Claim_Quality` varchar(50)  DEFAULT NULL,
        `Revshare_Rate_Type` varchar(50)  DEFAULT NULL,
		`Offer` varchar(50)   DEFAULT NULL,
        `Owned_Views` int(10) DEFAULT NULL,
		`Monetized_Views___Audio` decimal(18,8) DEFAULT NULL,
		`Monetized_Views___Audio_Visual` decimal(18,8) DEFAULT NULL,
		`Monetized_Views` decimal(18,8) DEFAULT NULL,
		`YouTube_Revenue_Split` decimal(18,8) DEFAULT NULL,
        `Partner_Revenue___Pro_Rata` decimal(18,8) DEFAULT NULL,
		`Partner_Revenue___Per_Sub_Min` decimal(18,8) DEFAULT NULL,
		`partnerRevenue` decimal(18,8) DEFAULT NULL,
        `content_owner` varchar(50) DEFAULT NULL,
	    `autoassign_steps` varchar(10) DEFAULT NULL,
        `holding_percentage` decimal(10,2) DEFAULT {$US_HOLDING_PERCENTAGE},
		 PRIMARY KEY (id),
         INDEX i (Asset_Channel_ID),
         INDEX Video_Channel_ID (Video_Channel_ID),
         INDEX Asset_ID (Asset_ID),
         INDEX `Asset_Label` (Asset_Label),
         INDEX `Video_ID` (Video_ID),
         INDEX partnerRevenue (partnerRevenue),
         INDEX `holding_percentage` (holding_percentage),
         INDEX `content_owner` (content_owner)
		 ) ENGINE=InnoDB DEFAULT CHARSET=UTF8";

        @unlink("polo_create_cgm.txt");
        file_put_contents("polo_create_cgm.txt", $createYoutubeTableQuery);
        @chmod("polo_create_cgm.txt", 0777);

    $createAmazonTableQueryResult = runQuery($createYoutubeTableQuery, $conn);
    // printArr($createAmazonTableQueryResult);exit;

    if (noError($createAmazonTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createAmazonTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}

function insert_non_cgm_redsubscription_reportv4($filePath, $tableName, $conn)
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
			IGNORE 1 ROWS (`Adjustment_Type`,`Country`,`Day`, `Video_ID`, `Video_Channel_ID`, `Asset_ID`, `Asset_Title`, `Asset_Label`, `Claiming_Asset_Type`, `Custom_ID`, `ISRC`, `ISWC`, `HFA_Song_Code`, `Writers`, `Composition_Right_Type`, `Ownership_Percentage`, `Claim_Quality`, `Revshare_Rate_Type`, `Offer`, `Owned_Views`, `Monetized_Views___Audio`, `Monetized_Views___Audio_Visual`, `Monetized_Views`,`YouTube_Revenue_Split`, `Partner_Revenue___Pro_Rata`, `Partner_Revenue___Per_Sub_Min`, `partnerRevenue`);";


            @unlink("polo_create_cmg.txt");
            file_put_contents("polo_create_cmg.txt", $insertTableQuery);
            @chmod("polo_create_cmg.txt", 0777);


            $insertTableQueryResult = runQuery($insertTableQuery, $conn);

            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //    $a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }
            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insert_non_cgm_redsubscription_reportv4($filePath, $tableName, $conn);
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


function autoAssignContentOwnernon_cgm_redsubscriptionv4($tableName, $conn)
{
    $returnArr = array();
    $res = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }

    //looping through array passed to create another array of where clauses
    //$whereClause = "assetChannelID=t1.assetChannelID";
    $whereClause = '';

    $a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
    $a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
    $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
    //$a3 = runQuery("SET sql_log_bin = 0;", $conn);


  

    //content_owner
    // update on the basis of contentType='Partner-provided' with channel_co_maping-> partner_provided
    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}      inner join  	channel_co_maping on {$tableName}.Asset_Label = channel_co_maping.Label  SET {$tableName}.content_owner = channel_co_maping.partner_provided    where ({$tableName}.content_owner='' || {$tableName}.content_owner is NULL)   ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }
    //DEFAULT_CONTENT_OWNER_NONCMG
    $DEFAULT_CONTENT_OWNER_NONCMG = DEFAULT_CONTENT_OWNER_NONCMG;
    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}       SET {$tableName}.content_owner = '{$DEFAULT_CONTENT_OWNER_NONCMG}'    where ({$tableName}.content_owner='' || {$tableName}.content_owner is NULL)   ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

    
    

     
     
}



//activation start
function createActivationnon_cgm_redsubscription_v4($tableName, $conn)
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
							  `status` ENUM('active', 'inactive') DEFAULT 'active',
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

// YouTube WithHolding Percentage


function create_youTube_withholding_percentage_report_v5($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $US_HOLDING_PERCENTAGE = US_HOLDING_PERCENTAGE;
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`AdSense_Earnings_Month` varchar(50) DEFAULT NULL,
        `Channel_ID` varchar(50)   DEFAULT NULL,
        `Revenue_Source` text   DEFAULT NULL,
        `Local_Currency` varchar(10)   DEFAULT NULL,
		`Total_Channel_Revenue` varchar(50)  DEFAULT NULL,
		`US_Sourced_Revenue` varchar(50) DEFAULT NULL,
		`Tax_Withholding_Rate` varchar(50) DEFAULT NULL,
        `Tax_Withheld_Amount` varchar(50) DEFAULT NULL,
        `content_owner` varchar(50) DEFAULT NULL,
	    `autoassign_steps` varchar(10) DEFAULT NULL,
         PRIMARY KEY (id),
         INDEX i (Channel_ID),
         INDEX Tax_Withholding_Rate (Tax_Withholding_Rate),
         INDEX `content_owner` (content_owner)
		 ) ENGINE=InnoDB DEFAULT CHARSET=UTF8";

        @unlink("polo_create_whp.txt");
        file_put_contents("polo_create_whp.txt", $createYoutubeTableQuery);
        @chmod("polo_create_whp.txt", 0777);

    $createAmazonTableQueryResult = runQuery($createYoutubeTableQuery, $conn);
    // printArr($createAmazonTableQueryResult);exit;

    
    if (noError($createAmazonTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createAmazonTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}


function insert_youtube_whp_reportv4($filePath, $tableName, $conn)
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
			IGNORE 1 ROWS (`AdSense_Earnings_Month`,`Channel_ID`,`Revenue_Source`,`Local_Currency`, `Total_Channel_Revenue`, `US_Sourced_Revenue`, `Tax_Withholding_Rate`, `Tax_Withheld_Amount`);";


            @unlink("polo_create_cmg.txt");
            file_put_contents("polo_create_cmg.txt", $insertTableQuery);
            @chmod("polo_create_cmg.txt", 0777);


            $insertTableQueryResult = runQuery($insertTableQuery, $conn);

            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //    $a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }
            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insert_youtube_whp_reportv4($filePath, $tableName, $conn);
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

//dated 2023-02-16
function autoAssign_ContentOwner_youtube_whp_v4($tableName, $conn)
{
    //return false;
    $returnArr = array();
    $res = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }

    //looping through array passed to create another array of where clauses
    //$whereClause = "assetChannelID=t1.assetChannelID";
 
    $a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
    $a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
    $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
    //$a3 = runQuery("SET sql_log_bin = 0;", $conn);


  
    
    $tableView = "t_whp_" . $tableName;
 
    //content_owner
    //self upate update on the basis of contentType='Partner-provided' with channel_co_maping-> partner_provided

     
    //drop TABLE if exist. TABLE sample : view_youtube_video_claim_report_nd1_2020_12
    $tableView_query = "DROP TABLE IF EXISTS {$tableView} ";
    $tableView_queryResult = runQuery($tableView_query, $conn);

    $explode_table = array_reverse(explode('_', $tableName));
    
    $source_table = "youtube_video_claim_report_".$explode_table[2]."_".$explode_table[1]."_".$explode_table[0];

    //create view from youtube_video_claim_report_nd2_2022_02
     $tableView_query = "CREATE TABLE {$tableView} AS SELECT channelID, assetChannelID, content_owner FROM {$source_table} GROUP by channelID ";

    @unlink("t_whp.txt");
    file_put_contents("t_whp.txt", $tableView_query);
    @chmod("t_whp.txt", 0777);
 
    $tableView_queryResult = runQuery($tableView_query, $conn);
    if (!noError($tableView_queryResult)) {
        return setErrorStack($returnArr, 3, $tableView_queryResult, null);
    }

    addIndexTempTable($tableView, $conn, 'content_owner');
    addIndexTempTable($tableView, $conn, 'channelID');
    addIndexTempTable($tableView, $conn, 'assetChannelID');

    //update orginal table  youtube_video_claim_report_ on asset id
    //{$tableNameReport1}.content_owner = {$tableView}.content_owner,
    $autoAssignChannelCOMapQuery5 = "UPDATE {$tableName}      inner join  	{$tableView} on {$tableName}.Channel_ID = {$tableView}.channelID  SET  {$tableName}.content_owner = {$tableView}.content_owner    ";
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery5, $conn);

    @unlink("polo_step2.txt");
    file_put_contents("polo_step2.txt", $autoAssignChannelCOMapQuery5);
    @chmod("polo_step2.txt", 0777);
    //update orginal table  youtube_video_claim_report_ on label
    //{$tableNameReport1}.content_owner = {$tableView}.content_owner,
 
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

    $autoAssignChannelCOMapQuery5 = "UPDATE {$source_table}  a, {$tableName} b    SET  a.holding_percentage_actual = b.Tax_Withholding_Rate    where a.channelID = b.Channel_ID and a.content_owner = b.content_owner and a.country ='US'";
     $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery5, $conn);

    @unlink("polo_step21.txt");
    file_put_contents("polo_step21.txt", $autoAssignChannelCOMapQuery5);
    @chmod("polo_step21.txt", 0777);
     
    // if (!noError($autoAssignChannelCOMapQueryResult)) {
    //     return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    // }

        //drop view after use
        $tableView_query = "DROP TABLE IF EXISTS {$tableView} ";
        $tableView_queryResult = runQuery($tableView_query, $conn);
    
    if (!noError($tableView_queryResult)) {
        return setErrorStack($returnArr, 3, $tableView_queryResult, null);
    }
     
     
}



//end YouTube WithHolding Percentage


function addActualHoldingPercentageColumn($tableName, $conn)
{
	$returnArr = array();

	if (empty($tableName)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Tablename to create cannot be empty", null);
	}

	$res = array();

	$truncateTableQuery = "ALTER TABLE {$tableName} ADD COLUMN  `holding_percentage_actual` VARCHAR(50) NOT NULL,ADD INDEX (holding_percentage_actual)";

    @unlink("polo_holding_percentage_actual.txt");
    file_put_contents("polo_holding_percentage_actual.txt", $truncateTableQuery);
    @chmod("polo_holding_percentage_actual.txt", 0777);

	$truncateTableQueryResult = runQuery($truncateTableQuery, $conn); 
    
	if (noError($truncateTableQueryResult)) {
		return setErrorStack($returnArr, -1, $res, null);
	} else {
		return setErrorStack($returnArr, 3, $truncateTableQueryResult["errMsg"], null);
	}
}