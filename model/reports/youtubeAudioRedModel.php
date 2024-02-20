<?php

function createYoutubeAudioRedReportTable($tableName, $conn)
{
	$returnArr = array();
	if (empty($tableName)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Tablename to create cannot be empty", null);
	}
	 $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							--   `adjustmentType` varchar(50)  NOT NULL,
							  `country` varchar(5)   NOT NULL,
							  `day` timestamp NOT NULL,
							  `videoID` varchar(50)  NOT NULL,
							  `videoChannelID` varchar(50)  NOT NULL,
							  `assetID` varchar(50) NOT NULL,
							  `assetChannelID` varchar(50) NOT NULL,
							  `assetTitle` TEXT NOT NULL,
							  `assetLabels` varchar(100) NOT NULL,
							  `assetType` varchar(25) NOT NULL,
							  `cutsomID` varchar(50) NOT NULL,
							  `isrc` varchar(50) NOT NULL,
							  `upc` varchar(50) NOT NULL,
							  `grid` varchar(50)  NULL,
							  `artist` varchar(250) NOT NULL,
							  `album` varchar(250) NOT NULL,
							  `label` varchar(50) NOT NULL,
							  `claimType` varchar(50)  NOT NULL,
							  `contentType` varchar(50)  NOT NULL,
							  `offer` varchar(250)  NOT NULL,
							  `ownedViews` int(10) NOT NULL,
							  `monetizedViewsAudio` int(10) NOT NULL,
							  `monetizedViewsAudioVisual` int(10) NOT NULL,
							  `monetizedViews` int(10) NOT NULL,
							  `youtubeRevenueSplit` decimal(18,8) NOT NULL,
							  `partnerRevenueProData` decimal(18,8) NOT NULL,
							  `partnerRevenuePerSubMin` decimal(18,8) NOT NULL,
							  `partnerRevenue` decimal(18,8) NOT NULL,
							   PRIMARY KEY (id),
							   INDEX contentType (contentType),
							   INDEX assetID (assetID),
							   INDEX partnerRevenue (partnerRevenue),
							   INDEX videoID (videoID)
							)";

	
	$createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);
 
	
    if (noError($createYoutubeTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}
// function createActivationYoutubeReportTable($tableName, $conn)
// {
// 	$returnArr = array();
// 	if (empty($tableName)) {
// 		return setErrorStack($returnArr, 4, getErrMsg(4)." Tablename to create cannot be empty", null);
// 	}
// 	 $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
// 							  `id` int(11) NOT NULL AUTO_INCREMENT,
// 							  `content_owner` varchar(50)  NOT NULL,
// 							  `total_amt_recd` varchar(50)  NOT NULL,
// 							  `shares` varchar(50)  DEFAULT NULL,
// 							  `amt_payable` varchar(50)  DEFAULT NULL,
// 							  `us_payout` varchar(50)  DEFAULT NULL,
// 							  `witholding` varchar(50)  DEFAULT NULL,
// 							  `final_payable` varchar(50)  DEFAULT NULL,
// 							  `status` ENUM('active', 'inactive') DEFAULT 'inactive',
// 							   PRIMARY KEY (id),
// 							   INDEX i (content_owner)
// 							)";

	
// 	$createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);
 
	
//     if (noError($createYoutubeTableQueryResult)) {
//         $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
//     } else {
//         $returnArr = setErrorStack($returnArr, 3, null);
//     }

//     return $returnArr;
// }
// function truncateReportTable($tableName, $conn)
// {
// 	$returnArr = array();

// 	if (empty($tableName)) {
// 		return setErrorStack($returnArr, 4, getErrMsg(4)." Tablename to create cannot be empty", null);
// 	}

// 	$res = array();

// 	$truncateTableQuery = "TRUNCATE TABLE {$tableName}";
// 	$truncateTableQueryResult = runQuery($truncateTableQuery, $conn);
    
// 	if (noError($truncateTableQueryResult)) {
// 		return setErrorStack($returnArr, -1, $res, null);
// 	} else {
// 		return setErrorStack($returnArr, 3, $truncateTableQueryResult["errMsg"], null);
// 	}
// }

// function addContentOwnerColumn($tableName, $conn)
// {
// 	$returnArr = array();

// 	if (empty($tableName)) {
// 		return setErrorStack($returnArr, 4, getErrMsg(4)." Tablename to create cannot be empty", null);
// 	}

// 	$res = array();

// 	$truncateTableQuery = "ALTER TABLE {$tableName} ADD COLUMN  `content_owner` VARCHAR(50) NOT NULL,ADD INDEX (content_owner),ADD INDEX compositeforred (assetID,content_owner)";
// 	$truncateTableQueryResult = runQuery($truncateTableQuery, $conn); 
    
// 	if (noError($truncateTableQueryResult)) {
// 		return setErrorStack($returnArr, -1, $res, null);
// 	} else {
// 		return setErrorStack($returnArr, 3, $truncateTableQueryResult["errMsg"], null);
// 	}
// }
// function addReportDateColumn($tableName, $conn)
// {
// 	$returnArr = array();

// 	if (empty($tableName)) {
// 		return setErrorStack($returnArr, 4, getErrMsg(4)." Tablename to create cannot be empty", null);
// 	}

// 	$res = array();

// 	$truncateTableQuery = "ALTER TABLE {$tableName} ADD COLUMN  `report_date` DATE NULL DEFAULT NULL,ADD INDEX (report_date)";
// 	$truncateTableQueryResult = runQuery($truncateTableQuery, $conn); 
    
// 	if (noError($truncateTableQueryResult)) {
// 		return setErrorStack($returnArr, -1, $res, null);
// 	} else {
// 		return setErrorStack($returnArr, 3, $truncateTableQueryResult["errMsg"], null);
// 	}
// }
function insertYTAudioRedReportInfo($filePath, $tableName, $conn)
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
		//	rerun:
			$currentFile= $files[0];
			$a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
			$a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
			$a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
		//	$a3 = runQuery("SET sql_log_bin = 0;", $conn);
		//	$a3 = runQuery("LOCK TABLES {$tableName} WRITE;", $conn);
			$insertTableQuery =  "LOAD DATA INFILE '{$currentFile}'
								INTO TABLE {$tableName} 
								FIELDS TERMINATED BY ',' 
								ENCLOSED BY '\"' 
								LINES TERMINATED BY '\\n'
								IGNORE 1 ROWS;";
			$insertTableQueryResult = runQuery($insertTableQuery, $conn);
		//	$a3 = runQuery("UNLOCK TABLES;", $conn);
			$a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
			$a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
		//	$a3 = runQuery("SET sql_log_bin = 1;", $conn);
		
			if (!noError($insertTableQueryResult)) {  
			//	goto rerun;
				return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
			}
			$arr = array_shift($files); 
			if(is_array($files)){
				$filePath= implode(',',$files);
			}  
		    if($filePath){
			    return insertReportInfo($filePath, $tableName, $conn);
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

////////////////////////////////////Auto Assign helpers////////////////////////////////////////
function autoAssignChannelCOMapforYTAudioRed($tableName="", $fieldSearchArr=null, $contentType="", $conn)
{
	$returnArr = array();
	$res = array();

	if (empty($tableName)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Tablename to create cannot be empty", null);
	}

	if (is_null($fieldSearchArr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Field Search array cannot be empty", null);
	}

	$allowedContentTypes = array("partner_provided", "ugc");
	if (empty($contentType) || !in_array(strtolower($contentType), $allowedContentTypes)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Content Type cannot be empty", null);
	}

	//looping through array passed to create another array of where clauses
	//$whereClause = "assetChannelID=t1.assetChannelID";
	$whereClause = '';
	foreach ($fieldSearchArr as $colName=>$searchVal) {
		if(!empty($whereClause))
			$whereClause .= " AND ";
		$whereClause .= "{$colName} = '{$searchVal}'";
	}
	$a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
	$a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
	$a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
	//$a3 = runQuery("SET sql_log_bin = 0;", $conn);

	$getmappingquery = 'SELECT assetChannelID,partner_provided,ugc FROM channel_co_maping order by id asc';
	$getmappingqueryresult = runQuery($getmappingquery, $conn);
	if (!noError($getmappingqueryresult)) {
		return setErrorStack($returnArr, 3, $getmappingqueryresult["errMsg"], null);
	}
	$allco=[];
	$bulkupdatecasestring ='';
	 
	while($row = mysqli_fetch_assoc($getmappingqueryresult["dbResource"])){
		$allco[$row['assetChannelID']] = [
			'partner_provided'=>$row['partner_provided'],
			'ugc'=>$row['ugc']
			];
		 	
		//$bulkupdatecasestring.=" when assetChannelID='{$row['assetChannelID']}' and {$whereClause} then '{$allco[$row['assetChannelID']][$contentType]}'";	
	 }
//echo $updatecasestring;exit;
	 $i=1;$j=1;
	 $numbofupdate = [];
	 $totalco=count($allco); 
	foreach($allco as $k=>$y){
		$bulkupdatecasestring.=" when (assetChannelID='{$k}') and {$whereClause} then '{$y[$contentType]}'";
		
		if($i==100 || $totalco==$j)
		{
			echo $j.'-';
			//$numbofupdate[]=$bulkupdatecasestring;
			$autoAssignChannelCOMapQuery = "UPDATE {$tableName}  SET content_owner = case  {$bulkupdatecasestring} end where content_owner=''"; 
			$autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn); 
			if (!noError($autoAssignChannelCOMapQueryResult)) {
				return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
			}
			$i=1;
			$bulkupdatecasestring='';
		}else{
			$i++;
		}
		$j++;
		
	}
//	printArr($numbofupdate);exit;
		  
	//	 $autoAssignChannelCOMapQuery = "UPDATE {$tableName}  SET content_owner = case when assetChannelID='{$k}' and {$whereClause} then '{$y[$contentType]}' else '' end where content_owner=''"; 
	//	$autoAssignChannelCOMapQuery = "UPDATE {$tableName}  SET content_owner = case  {$bulkupdatecasestring} end where content_owner=''"; 
		//echo $autoAssignChannelCOMapQuery;exit;
		// $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn); 
		// if (!noError($autoAssignChannelCOMapQueryResult)) {
		// 	return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
		// }
	// }
	 
	//printArr($allco);
 	//  exit;
	// 	$autoAssignChannelCOMapQuery = "UPDATE {$tableName} t1 SET t1.content_owner = (SELECT {$contentType} from channel_co_maping where {$whereClause}  limit $limit,100) where t1.content_owner=''"; 
 
	 
 	// 	 $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn); 
		// if (noError($autoAssignChannelCOMapQueryResult)) {
		//    continue;
		// }else{
		//    break;
		//    return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
		// }
		
	 
 
	//$autoAssignChannelCOMapQuery = "UPDATE channel_co_maping t1, {$tableName} t2 SET t2.content_owner =t1.{$contentType} WHERE {$whereClause}"; 
    //    $autoAssignChannelCOMapQuery = "UPDATE channel_co_maping t1 INNER JOIN {$tableName} t2 ON  {$whereClause}  SET t2.content_owner =t1.{$contentType}";
	//$autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
	$a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
	$a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
	//$a3 = runQuery("SET sql_log_bin = 1;", $conn);
	if (noError($autoAssignChannelCOMapQueryResult)) {
		return setErrorStack($returnArr, -1, $res, null);
	} else {
		return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
	}
}
 


// function getChannelMapping(
// 	$fieldSearchArr=null,
// 	$fieldsStr="",
// 	$dateField=null,
// 	$conn,
// 	$offset=null,
// 	$resultsPerPage=10
// )
// {
// 	$res = array();
// 	$returnArr = array();
// 	$whereClause = "";
	
// 	//looping through array passed to create another array of where clauses

// 	foreach ($fieldSearchArr as $colName=>$searchVal) {
// 		if(!empty($whereClause))
// 			$whereClause .= " AND ";
// 		$whereClause .= "{$colName} = '{$searchVal}'";
// 	}

	 

// 	if (empty($fieldsStr))
// 		$fieldsStr = "*";
// 	$getClientInfoQuery = "SELECT {$fieldsStr} FROM channel_co_maping";
// 	if (!empty($whereClause))
// 		$getClientInfoQuery .= " WHERE {$whereClause}";
// 	$getClientInfoQuery .= " ORDER BY id asc";
// 	if ($offset!==null)
// 		$getClientInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";

// 	$getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
// 	if (!noError($getClientInfoQueryResult)) {
// 		return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
// 	}

// 	/* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
// 	*  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used
// 	*/
// 	while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
// 		$res[] = $row;
// 	}
	
// 	return setErrorStack($returnArr, -1, $res, null);
// }

////////////////////////////////////Auto Assign helpers////////////////////////////////////////
?>
