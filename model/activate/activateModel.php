<?php

function getActivationReport(
    $table,
    $fieldSearchArr = null,
    $fieldsStr = "",
    $dateField = null,
    $conn,
    $offset = null,
    $resultsPerPage = 100,
    $orderBy = "content_owner"
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";

    //looping through array passed to create another array of where clauses

    foreach ($fieldSearchArr as $colName => $searchVal) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "{$colName} = '{$searchVal}'";
    }

    if (empty($fieldsStr)) {
        $fieldsStr = "*";
    }

    $getClientInfoQuery = "SELECT {$fieldsStr} FROM {$table}";
    if (!empty($whereClause)) {
        $getClientInfoQuery .= " WHERE {$whereClause}";
    }

    $getClientInfoQuery .= " ORDER BY " . $orderBy;
    if ($offset !== null) {
        $getClientInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }
 
    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used
     */
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {

        $res[] = $row;
    }

    return setErrorStack($returnArr, -1, $res, null);
}

function generateActicationReport($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO
					{$desinationtable} (content_owner, total_amt_recd,us_payout,witholding)
					SELECT
						content_owner,
						ROUND(SUM(partnerRevenue),2),
						ROUND(SUM(CASE WHEN country='US' THEN partnerRevenue END),2),
						ROUND((SUM(CASE WHEN country='US' THEN partnerRevenue END)*30/100),2)
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";

 

    $updateQueryResult = runQuery($updateQuery, $conn);
    if (noError($updateQueryResult)) {
        $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube')),
						a.amt_payable=ROUND((JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube'))*a.total_amt_recd)/100,2),
						a.final_payable= ROUND(((a.total_amt_recd-a.witholding)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube'))/100),2)
                        where b.client_username =a.content_owner";
                        
                      
                        @unlink("polo_ACT_update.txt");
                        file_put_contents("polo_ACT_update.txt", $updateQuery);
                        @chmod("polo_ACT_update.txt", 0777);
                        
        $updateQueryResult = runQuery($updateQuery, $conn);
        if (noError($updateQueryResult)) {
            return setErrorStack($returnArr, -1, $res, null);
        } else {
            return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
        }

        //Update Content owner in claim report
        // $tb = explode('youtube_finance_report_',$sourcetable);
        // $claimtable= 'youtube_video_report_'.$tb[1];

        // $autoAssignChannelCOMapQuery = "UPDATE {$claimtable} t1, {$sourcetable} t2 SET t1.content_owner =t2.content_owner WHERE t1.video_id=t2.videoID";
        // $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
        //  if (noError($autoAssignChannelCOMapQueryResult)) {
        //     return setErrorStack($returnArr, -1, $res, null);
        // }else{
        //     return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult["errMsg"], null);
        // }

        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
    }
}
function generateActicationReportRed($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO
					{$desinationtable} (content_owner, total_amt_recd,us_payout,witholding)
					SELECT
						content_owner,
						ROUND(SUM(partnerRevenue),2),
						ROUND(SUM(CASE WHEN country='US' THEN partnerRevenue END),2),
						ROUND((SUM(CASE WHEN country='US' THEN partnerRevenue END)*30/100),2)
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);
    if (noError($updateQueryResult)) {
        // $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
        //                 set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeRed')),
        //                 a.amt_payable=(JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube'))*a.total_amt_recd)/100,
        //                 a.final_payable= ROUND(((a.total_amt_recd-a.witholding)*a.shares/100),2)
        //                 where b.client_username =a.content_owner";
        $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube')),
						a.amt_payable=(JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube'))*a.total_amt_recd)/100,
						a.final_payable= ROUND(((a.total_amt_recd-a.witholding)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube'))/100),2)
						where b.client_username =a.content_owner";
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

function generateAudioActicationReport($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO
					{$desinationtable} (content_owner, total_amt_recd,us_payout,witholding)
					SELECT
						content_owner,
						ROUND(SUM(partnerRevenue),2),
						ROUND(SUM(CASE WHEN country='US' THEN partnerRevenue END),2),
						ROUND((SUM(CASE WHEN country='US' THEN partnerRevenue END)*30/100),2)
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";

@unlink("polo_ACT.txt");
file_put_contents("polo_ACT.txt", $updateQuery);
@chmod("polo_ACT.txt", 0777);


    $updateQueryResult = runQuery($updateQuery, $conn);
    if (noError($updateQueryResult)) {
        $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube')),
						a.amt_payable=ROUND((JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube'))*a.total_amt_recd)/100,2),
						a.final_payable= ROUND(((a.total_amt_recd-a.witholding)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube'))/100),2)
						where b.client_username =a.content_owner";
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
function updateStatus($table, $status, $ids, $conn)
{
    $res = array();
    $returnArr = array();
    $updateQuery = "UPDATE {$table} SET status = '{$status}' WHERE id IN({$ids})";
    $updateQueryResult = runQuery($updateQuery, $conn);
    if (noError($updateQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
    }
}



//amazon
function createActivationAmazonReportTable($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
	$createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
								`id` int(11) NOT NULL AUTO_INCREMENT,
								`content_owner` varchar(150)  NOT NULL,
								`total_amt_recd` varchar(50)  NOT NULL,
								`witholding` varchar(50)  DEFAULT NULL,
								`amt_recd` varchar(50)  DEFAULT NULL,
								`shares` varchar(50)  DEFAULT NULL,
								`amt_payable` varchar(50)  DEFAULT NULL,
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

function generateActicationReportAmazon($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO
					{$desinationtable} (content_owner, total_amt_recd,witholding,amt_recd,amt_payable)
					SELECT
						content_owner,
						ROUND(SUM(revINRwithoutHolding),2),
						ROUND(SUM(withHolding),2),
						ROUND(SUM(amountReceived),2),
						ROUND(SUM(payable),2)
                    FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
                    
                               
    $updateQueryResult = runQuery($updateQuery, $conn);
    if (noError($updateQueryResult)) {
        // $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
        //                 set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutubeRed')),
        //                 a.amt_payable=(JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueShareYoutube'))*a.total_amt_recd)/100,
        //                 a.final_payable= ROUND(((a.total_amt_recd-a.witholding)*a.shares/100),2)
        //                 where b.client_username =a.content_owner";
        $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueAmazon')),
						 
						a.final_payable= ROUND(((a.total_amt_recd-a.witholding)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueAmazon'))/100),2)
						where b.client_username =a.content_owner";
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
function getActivationReportSummaryv2_backup_before_coding_dynamic_gst_holding($type,$clientSearchArr,$client,$conn)
{
	$res = array();
	$returnArr = array();

	 
	$getClientInfoQuery = "SHOW TABLES LIKE '$type'";

	$getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
	if (!noError($getClientInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
	}

	/* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
	*  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used
	*/

    $getshare = "SELECT    gst_per  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $gst_per = $getshareresdata['gst_per'];
    }


	while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888
        
		foreach($row as $k=>$v){
				 
   	$check = "SELECT sum(total_amt_recd) as total_amt_recd,sum(us_payout) as us_payout,sum(witholding) as witholding,sum(final_payable) as final_payable , sum(amt_payable) as amt_payable , sum(shares) as shares  from $v WHERE content_owner='{$client}' and `status`='Active'";
				 
				$checkresult = runQuery($check, $conn);
				 
				$test = mysqli_fetch_assoc($checkresult["dbResource"]);
                $res[] = $test;
				 
		}  
		
    }
    $res_final['total_amt_recd'] = 0;
    $res_final['us_payout'] = 0;
    $res_final['witholding'] = 0;
    $res_final['final_payable'] = 0;
    $res_final['amt_payable'] = 0;
    $res_final['shares'] = 0;
    $res_final['gst_per'] = $gst_per;
    $shares = 0;
    foreach($res as $k=>$value){
        
        if($value['shares'] > 0){
            $shares = $value['shares'];
        }
        $res_final['total_amt_recd'] =   $res_final['total_amt_recd'] + $value['total_amt_recd'];
        $res_final['us_payout'] =   $res_final['us_payout']  + $value['us_payout'];
        $res_final['witholding'] =   $res_final['witholding']  + $value['witholding'];
        $res_final['final_payable'] =   $res_final['final_payable'] + $value['final_payable'];
        $res_final['shares'] =     (int) $shares;
        $res_final['amt_payable'] =  $res_final['amt_payable'] + $value['amt_payable'];
    }
 

    
	return setErrorStack($returnArr, -1, $res_final, null);
}

function getActivationReportSummaryv2($type,$clientSearchArr,$client,$conn)
{
	$res = array();
	$returnArr = array();

	 
	$getClientInfoQuery = "SHOW TABLES LIKE '$type'";

	$getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
	if (!noError($getClientInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
	}

	/* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
	*  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used
	*/

 

	while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888
        
		foreach($row as $k=>$v){
				 
           	$check = "SELECT total_amt_recd as total_amt_recd,us_payout as us_payout,witholding as witholding,final_payable as final_payable , amt_payable as amt_payable , shares as shares,holding_percentage as holding_percentage ,gst_percentage as gst_percentage ,final_payable_with_gst as final_payable_with_gst  from $v WHERE content_owner='{$client}' and `status`='Active'";
				 
				$checkresult = runQuery($check, $conn);
             
               $resultQyeryscheck = mysqli_num_rows($checkresult["dbResource"]);
                if($resultQyeryscheck > 0){
				$test = mysqli_fetch_assoc($checkresult["dbResource"]);
                $res[] = $test;
                }
				 
		}  
		
    }
    $res_final['total_amt_recd'] = 0;
    $res_final['us_payout'] = 0;
    $res_final['witholding'] = 0;
    $res_final['final_payable'] = 0;
    $res_final['amt_payable'] = 0;
    $res_final['shares'] = 0;
    $res_final['gst_percentage'] = 0;
    $res_final['holding_percentage'] = 0;
    $res_final['final_payable_with_gst'] = 0;
   $counter=0;
    foreach($res as $k=>$value){
        $counter = $counter+1;
     //   $holding_percentage = (!empty($value['holding_percentage'])) ? $value['holding_percentage'] : 0;
    //    $gst_percentage = (!empty($value['gst_percentage'])) ? $value['gst_percentage'] : 0;
    //    $shares = (!empty($value['shares'])) ? $value['shares'] : 0;
        
        $res_final['total_amt_recd'] =   $res_final['total_amt_recd'] + $value['total_amt_recd'];
        $res_final['us_payout'] =   $res_final['us_payout']  + $value['us_payout'];
        $res_final['witholding'] =   $res_final['witholding']  + $value['witholding'];
        $res_final['final_payable'] =   $res_final['final_payable'] + $value['final_payable'];
         $res_final['amt_payable'] =  $res_final['amt_payable'] + $value['amt_payable'];
        $res_final['final_payable_with_gst'] =  $res_final['final_payable_with_gst'] + $value['final_payable_with_gst'];
        $res_final['holding_percentage'] =  $res_final['holding_percentage'] + $value['holding_percentage'];
        $res_final['gst_percentage'] =  $res_final['gst_percentage'] + $value['gst_percentage'];
        $res_final['shares'] =  $res_final['shares'] + $value['shares'];
        
    }
     
    if($counter > 0){
        $res_final['gst_percentage'] =      $res_final['gst_percentage'] / $counter ;
        $res_final['shares'] =      $res_final['shares'] / $counter ;
        $res_final['holding_percentage'] =      $res_final['holding_percentage'] / $counter ;
        
    }
   
	return setErrorStack($returnArr, -1, $res_final, null);
}
function getActivationReportv2(
    $table,
    $fieldSearchArr = null,
    $fieldsStr = "",
    $dateField = null,
    $conn,
    $offset = null,
    $resultsPerPage = 100,
    $orderBy = "content_owner"
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";

    //looping through array passed to create another array of where clauses

    foreach ($fieldSearchArr as $colName => $searchVal) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "{$colName} = '{$searchVal}'";
    }

    if (empty($fieldsStr)) {
        $fieldsStr = "*";
    }

    $getClientInfoQuery = "SELECT {$fieldsStr} FROM {$table} as main";
    if (!empty($whereClause)) {
        $getClientInfoQuery .= " WHERE {$whereClause}";
    }

    $getClientInfoQuery .= " ORDER BY " . $orderBy;
    if ($offset !== null) {
        $getClientInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }
//echo $getClientInfoQuery;
    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used
     */
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {

        $res[] = $row;
    }

    return setErrorStack($returnArr, -1, $res, null);
}


////////////////youtube red music finance --

function getyoutube_red_music_video_finance_activationSummaryv2($type,$clientSearchArr,$client,$conn)
{
	$res = array();
	$returnArr = array();

	 
	   $getClientInfoQuery = "SHOW TABLES LIKE '$type%'";

	$getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
	if (!noError($getClientInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
	}

	/* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
	*  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used
	*/
	while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888
        
		foreach($row as $k=>$v){
				 
	  	$check = "SELECT sum(total_amt_recd) as total_amt_recd,sum(us_payout) as us_payout,sum(witholding) as witholding,sum(final_payable) as final_payable ,sum(shares) as shares ,sum(amt_payable) as amt_payable   from $v WHERE content_owner='{$client}' and `status`='Active'";
				 
				$checkresult = runQuery($check, $conn);
				 
				$test = mysqli_fetch_assoc($checkresult["dbResource"]);
                $res[] = $test;
				 
		}  
		
    }
  

    $res_final['total_amt_recd'] = 0;
    $res_final['us_payout'] = 0;
    $res_final['witholding'] = 0;
    $res_final['final_payable'] = 0;
    $res_final['amt_payable'] = 0;
    $res_final['shares'] = 0;

    foreach($res as $k=>$value){
        $res_final['total_amt_recd'] =   $res_final['total_amt_recd'] + $value['total_amt_recd'];
        $res_final['us_payout'] =   $res_final['us_payout']  + $value['us_payout'];
        $res_final['witholding'] =   $res_final['witholding']  + $value['witholding'];
        $res_final['final_payable'] =   $res_final['final_payable'] + $value['final_payable'];
        $res_final['shares'] =  $res_final['shares'] + $value['shares'];
        $res_final['amt_payable'] =  $res_final['amt_payable'] + $value['amt_payable'];
    }
 
	return setErrorStack($returnArr, -1, $res_final, null);
}


//-----youtube ecom paid features 

function get_youtube_ecommerce_paid_features_activation_Summarryv2($type,$clientSearchArr,$client,$conn)
{
	$res = array();
	$returnArr = array();

	 
	   $getClientInfoQuery = "SHOW TABLES LIKE '$type%'";

	$getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
	if (!noError($getClientInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
	}

	/* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
	*  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used
	*/
	while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888
        
		foreach($row as $k=>$v){
				 
	  	$check = "SELECT sum(total_amt_recd) as total_amt_recd,sum(us_payout) as us_payout,sum(witholding) as witholding,sum(final_payable) as final_payable ,sum(shares) as shares ,sum(amt_payable) as amt_payable   from $v WHERE content_owner='{$client}' and `status`='Active'";
				 
				$checkresult = runQuery($check, $conn);
				 
				$test = mysqli_fetch_assoc($checkresult["dbResource"]);
                $res[] = $test;
				 
		}  
		
    }
    $res_final['total_amt_recd'] = 0;
    $res_final['us_payout'] = 0;
    $res_final['witholding'] = 0;
    $res_final['final_payable'] = 0;
    $res_final['amt_payable'] = 0;
    $res_final['shares'] = 0;

    foreach($res as $k=>$value){
        $res_final['total_amt_recd'] =   $res_final['total_amt_recd'] + $value['total_amt_recd'];
        $res_final['us_payout'] =   $res_final['us_payout']  + $value['us_payout'];
        $res_final['witholding'] =   $res_final['witholding']  + $value['witholding'];
        $res_final['final_payable'] =   $res_final['final_payable'] + $value['final_payable'];
        $res_final['shares'] =  $res_final['shares'] + $value['shares'];
        $res_final['amt_payable'] =  $res_final['amt_payable'] + $value['amt_payable'];
    }
 
	return setErrorStack($returnArr, -1, $res_final, null);
}


function getActivationReportwhm_v10($conn,$type='',$client='',$uspayout=0,$uspayment_holding_amount=0)
{
	$res = array();
	$returnArr = array();

	 

	$getClientInfoQuery = "SHOW TABLES LIKE '$type'";

	$getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
	if (!noError($getClientInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
	}

	/* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
	*  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used
	*/
    $client = strtolower($client);

	while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888
        
		foreach($row as $k=>$v){
				 
           	$check = "select SUM(old_amt) as old_amt, SUM(new_amt) as new_amt from ( SELECT SUM(partnerRevenue), holding_percentage_actual, ROUND((SUM(partnerRevenue) * holding_percentage)/100,8) as old_amt ,ROUND((SUM(partnerRevenue) * holding_percentage_actual)/100,8) as new_amt FROM $v where LOWER(content_owner)='{$client}' and country='US' GROUP by channelID ) as new_table";
				 
				$checkresult = runQuery($check, $conn);
             
               $resultQyeryscheck = mysqli_num_rows($checkresult["dbResource"]);
                if($resultQyeryscheck > 0){
				$test = mysqli_fetch_assoc($checkresult["dbResource"]);
                $res[] = $test;
                }
				 
		}  
		
    }
    $res_final['new_amt'] = 0;
    $res_final['old_amt'] = 0;
   $counter=0;
    foreach($res as $k=>$value){
        $res_final['old_amt'] =   $res_final['old_amt'] + $value['old_amt'];
        $res_final['new_amt'] =   $res_final['new_amt'] + $value['new_amt'];
        
    }
     
    
   
	return setErrorStack($returnArr, -1, $res_final, null);
}


function getWhpReport_v10($conn,$tablename='',$client='',$uspayout=0,$uspayment_holding_amount=0)
{
	$res = array();
	$returnArr = array();

	 
    $client = strtolower($client);

    $check = "select SUM(US_Sourced_Revenue) as US_Sourced_Revenue, avg(Tax_Withholding_Rate) as Tax_Withholding_Rate,sum(Tax_Withheld_Amount)  as Tax_Withheld_Amount    FROM $tablename where LOWER(content_owner)='{$client}'  ";
				 
    $getClientInfoQueryResult = runQuery($check, $conn);
     if (!noError($getClientInfoQueryResult)) {
         return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
     }

     /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used
     */
     while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {

         $res[] = $row;
     }

     $res_final['US_Sourced_Revenue'] = 0;
    $res_final['Tax_Withholding_Rate'] = 0;
    $res_final['Tax_Withheld_Amount'] = 0;
 

    foreach($res as $k=>$value){
        $res_final['US_Sourced_Revenue'] =   $res_final['US_Sourced_Revenue'] + $value['US_Sourced_Revenue'];
        $res_final['Tax_Withholding_Rate'] =   $res_final['Tax_Withholding_Rate']  + $value['Tax_Withholding_Rate'];
        $res_final['Tax_Withheld_Amount'] =   $res_final['Tax_Withheld_Amount']  + $value['Tax_Withheld_Amount'];
       
    }

     return setErrorStack($returnArr, -1, $res_final, null);
                 
    
 
}
//end