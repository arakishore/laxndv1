<?php

function createYoutubeRedClaimReportTable($tableName, $conn)
{ 
	$returnArr = array();
	if (empty($tableName)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Tablename to create cannot be empty", null);
	}
	$createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`claim_id` varchar(15) NOT NULL,
	`claim_status` varchar(10) NOT NULL,
	`claim_status_detail` text NOT NULL,
	`claim_origin` varchar(50) NOT NULL,
	`claim_type` varchar(50)  NOT NULL,
	`asset_id` varchar(50) NOT NULL,
	`video_id` varchar(50) NOT NULL,
	`uploader` varchar(50) NOT NULL,
	`channel_id` varchar(50) NOT NULL,
	`channel_display_name` varchar(150) NOT NULL,
	`video_title` varchar(255) CHARACTER SET UTF8 NOT NULL,
	`view` int(11) NOT NULL,
	`matching_duration` int(11) NOT NULL,
	`longest_match` int(11) NOT NULL,
	`content_type` varchar(50) NOT NULL,
	`reference_video_id` varchar(50) NOT NULL,
	`reference_id` varchar(50) NOT NULL,
	`claim_policy_id` varchar(50) NOT NULL,
	`asset_policy_id` varchar(50) NOT NULL,
	`claim_policy_monetize` text NOT NULL,
	`claim_policy_track` text NOT NULL,
	`claim_policy_block` text NOT NULL,
	`asset_policy_monetize` varchar(50) NOT NULL,
	`asset_policy_track` varchar(50) NOT NULL,
	`asset_policy_block` varchar(50) NOT NULL,
	`claim_created_date` date NOT NULL,
	`video_upload_date` date NOT NULL,
	`custom_id` varchar(50) NOT NULL,
	`video_duration_sec` int(11) NOT NULL,
	`asset_title` text NOT NULL,
	`asset_labels` varchar(50) NOT NULL,
	`tms` varchar(50) NOT NULL,
	`director` varchar(50) NOT NULL,
	`studio` varchar(50) NOT NULL,
	`season` varchar(50) NOT NULL,
	`episode_number` varchar(50) NOT NULL,
	`episode_title` text NOT NULL,
	`release_date` date NOT NULL,
	`hfa_song_code` varchar(50) NOT NULL,
	`isrc` varchar(50) NOT NULL,
	`grid` varchar(50) NOT NULL,
	`artist` varchar(50) NOT NULL,
	`album` varchar(50) NOT NULL,
	`record_label` varchar(50) NOT NULL,
	`upc` varchar(50) NOT NULL,
	`iswc` varchar(50) NOT NULL,
	`writers` varchar(50) NOT NULL,
		PRIMARY KEY (id),
		INDEX j (asset_id,video_id,uploader,channel_id,channel_display_name,video_title,content_type)
	) ENGINE=InnoDB DEFAULT CHARSET=UTF8";
	$createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);
  // printArr($createYoutubeTableQueryResult);exit;
	
    if (noError($createYoutubeTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}
function insertReportRedVideoInfo($filePath, $tableName, $conn)
{
	$returnArr = array();
	if (empty($tableName)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Tablename to create cannot be empty", null);
	}
	$res = array();
	$currentFile = '';
	if($filePath){
	    $files =  explode(',',$filePath);  
		if(count($files)>0){ 
			$currentFile= $files[0];
			$a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
			$a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
			$a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
			//$a3 = runQuery("SET sql_log_bin = 0;", $conn);
			$insertTableQuery =  "LOAD DATA INFILE '{$currentFile}'
								INTO TABLE {$tableName} 
								FIELDS TERMINATED BY ',' 
								ENCLOSED BY '\"' 
								LINES TERMINATED BY '\\n'
								IGNORE 1 ROWS (`claim_id`,`claim_status`, `claim_status_detail`, `claim_origin`, `claim_type`, `asset_id`, `video_id`, `uploader`, `channel_id`, `channel_display_name`, `video_title`, `view`, `matching_duration`, `longest_match`, `content_type`, `reference_video_id`, `reference_id`, `claim_policy_id`, `asset_policy_id`, `claim_policy_monetize`, `claim_policy_track`, `claim_policy_block`, `asset_policy_monetize`, `asset_policy_track`, `asset_policy_block`, `claim_created_date`, `video_upload_date`, `custom_id`, `video_duration_sec`, `asset_title`, `asset_labels`, `tms`, `director`, `studio`, `season`, `episode_number`, `episode_title`, `release_date`, `hfa_song_code`, `isrc`, `grid`, `artist`, `album`, `record_label`, `upc`, `iswc`, `writers`);";
			$insertTableQueryResult = runQuery($insertTableQuery, $conn);
			$a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
			$a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
		//	$a3 = runQuery("SET sql_log_bin = 1;", $conn);
			if (!noError($insertTableQueryResult)) {  
				return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
			}
			$arr = array_shift($files); 
			if(is_array($files)){
				$filePath= implode(',',$files);
			}  
		    if($filePath){
			    return insertReportVideoInfo($filePath, $tableName, $conn);
			}else{
			    return setErrorStack($returnArr, -1, $res, null);
			}
		}else{  
			return setErrorStack($returnArr, -1, $res, null);
		}
	}else{  
		return setErrorStack($returnArr, -1, $res, null);
	}
	
	/* if (noError($insertTableQueryResult)) {
		return setErrorStack($returnArr, -1, $res, null);
	} else {
		return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
	} */
}

?>
