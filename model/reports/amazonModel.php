<?php

function createAmazonReportTable($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $createAmazonTableQuery = "CREATE TABLE {$tableName}  (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`seasonID` varchar(25) DEFAULT NULL,
	`ASIN` varchar(20) DEFAULT NULL,
	`titleName` varchar(512) DEFAULT NULL,
	`seriesName` varchar(512) DEFAULT NULL,
	`seasonName` varchar(50)  DEFAULT NULL,
	`consumptionType` varchar(20) DEFAULT NULL,
	`quality` varchar(10) DEFAULT NULL,
	`unitPrice` decimal(18,8) DEFAULT NULL,
	`quantity` int(11) DEFAULT NULL,
	`isRefund` varchar(5) DEFAULT NULL,
	`durationStreamed` decimal(18,8) DEFAULT NULL,
	`paidAdImpressions` varchar(50) DEFAULT NULL,
	`adRevenue` varchar(50) DEFAULT NULL,
	`royaltyRate` decimal(18,8) DEFAULT NULL,
	`royaltyAmount` decimal(18,8) DEFAULT NULL,
	`royaltyCurrency` varchar(10) DEFAULT NULL,
	`exchangeRate` decimal(18,8) DEFAULT NULL,
	`revINRwithoutHolding` decimal(18,8) DEFAULT NULL,
	`withHolding` decimal(18,8) DEFAULT NULL,
	`amountReceived` decimal(18,8) DEFAULT NULL,
	`clientShare` varchar(10) DEFAULT NULL,
	`payable` decimal(18,8) DEFAULT NULL,
	`region` varchar(50) DEFAULT NULL,
	`territory` varchar(20) DEFAULT NULL,
	`periodStart` date DEFAULT NULL,
	`periodEnd` date DEFAULT NULL,
	`type` varchar(20) DEFAULT NULL,
	`cERPercentile` varchar(20) DEFAULT NULL,
    `content_owner` varchar(150) DEFAULT NULL,
	  PRIMARY KEY (id)
	) ENGINE=InnoDB DEFAULT CHARSET=UTF8";
    $createAmazonTableQueryResult = runQuery($createAmazonTableQuery, $conn);
    // printArr($createAmazonTableQueryResult);exit;

    if (noError($createAmazonTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createAmazonTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}
function createAmazonPaymentReportTable($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $createAmazonTableQuery = "CREATE TABLE {$tableName}  (
	`id` int(11) NOT NULL AUTO_INCREMENT,
    `payment_date` varchar(25) DEFAULT NULL,
	`sales_period_start_date` varchar(25) DEFAULT NULL,
	`sales_period_end_date` varchar(20) DEFAULT NULL,
    `status` varchar(20) DEFAULT NULL,
	`accrued_royalty` varchar(50) DEFAULT NULL,
	`accrued_royalty_currency` varchar(10) DEFAULT NULL,
	`tax_withholding` varchar(50)  DEFAULT NULL,
	`holding_percentage` varchar(50) DEFAULT NULL,
	`tax_withholding_currency` varchar(10) DEFAULT NULL,
	`adjustments` varchar(50) DEFAULT NULL,
	`adjustments_currency` varchar(20) DEFAULT NULL,
	`net_earnings` varchar(50) DEFAULT NULL,
	`net_earnings_currency` varchar(10) DEFAULT NULL,
	`fx_rate` varchar(50) DEFAULT NULL,
	`payment` varchar(50) DEFAULT NULL,
	`payment_currency` varchar(10) DEFAULT NULL,
	  PRIMARY KEY (id)
	) ENGINE=InnoDB DEFAULT CHARSET=UTF8";
    $createAmazonTableQueryResult = runQuery($createAmazonTableQuery, $conn);
    // printArr($createAmazonTableQueryResult);exit;

    if (noError($createAmazonTableQueryResult)) {
        $returnArr = setErrorStack($returnArr, -1, $createAmazonTableQueryResult);
    } else {
        $returnArr = setErrorStack($returnArr, 3, null);
    }

    return $returnArr;
}
function insertReportAmazonVideoInfo($filePath, $tableName, $conn)
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
								IGNORE 1 ROWS (`seasonID`,`ASIN`, `titleName`, `seriesName`, `seasonName`, `consumptionType`, `quality`, `unitPrice`, `quantity`, `isRefund`, `durationStreamed`, `paidAdImpressions`, `adRevenue`, `royaltyRate`, `royaltyAmount`, `royaltyCurrency`, `region`, `territory`, `periodStart`, `periodEnd`, `type`, `cERPercentile`);";
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
                return insertReportAmazonVideoInfo($filePath, $tableName, $conn);
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
function insertReportAmazonVideoPInfoPayment($filePath, $tableName, $conn)
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
								IGNORE 1 ROWS (`payment_date`,`status`, `sales_period_start_date`, `sales_period_end_date`,  `accrued_royalty`, `accrued_royalty_currency`, `tax_withholding`,   `tax_withholding_currency`, `adjustments`, `adjustments_currency`, `net_earnings`, `net_earnings_currency`, `fx_rate`, `payment`, `payment_currency`);";
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
                return insertReportAmazonVideoPInfoPayment($filePath, $tableName, $conn);
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
////////////////////////////////////Auto Assign helpers////////////////////////////////////////
function autoAssignContentOwnerCOMap($tableName = "", $contentType = "", $conn)
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

   /*  $getmappingquery = 'SELECT session_id,assin,partner_provided FROM channel_co_maping_amazon order by id asc';
    $getmappingqueryresult = runQuery($getmappingquery, $conn);
    if (!noError($getmappingqueryresult)) {
        return setErrorStack($returnArr, 3, $getmappingqueryresult["errMsg"], null);
    } */
   
    //content_owner
    //echo $updatecasestring;exit;
    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}      inner join  channel_co_maping_amazon on {$tableName}.seasonID = channel_co_maping_amazon.session_id  SET {$tableName}.content_owner = channel_co_maping_amazon.partner_provided    where ({$tableName}.content_owner='' || {$tableName}.content_owner is NULL) ";
    
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }
//    printArr($numbofupdate);exit;

    $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
    $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
    //$a3 = runQuery("SET sql_log_bin = 1;", $conn);
    if (noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }
}

function autoCalculateWithHolding($tableName = "", $conn)
{
   
    $returnArr = array();
    $res = array();

    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $tableNameReport = "";
    $table_exp = explode('amazon_video_payment_report_',$tableName);
    
    $tableNameReport = "amazon_video_report_". $table_exp[1];
    //looping through array passed to create another array of where clauses
    //$whereClause = "assetChannelID=t1.assetChannelID";
    $whereClause = '';

    $a1 = runQuery("SET GLOBAL sql_mode = '';", $conn);
    $a1 = runQuery("SET UNIQUE_CHECKS = 0;", $conn);
    $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 0;", $conn);
    //$a3 = runQuery("SET sql_log_bin = 0;", $conn);
    
    $autoAssignChannelCOMapQuery = "UPDATE {$tableName} set holding_percentage = tax_withholding / accrued_royalty      ";
         
    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    
    $getPaymentgquery = "SELECT * FROM {$tableName} " ;
    
    $getPaymentgqueryresult = runQuery($getPaymentgquery, $conn);
    if (!noError($getPaymentgqueryresult)) {
        return setErrorStack($returnArr, 3, $getPaymentgqueryresult["errMsg"], null);
    }
   
    $allco=[];
   
	while($row = mysqli_fetch_assoc($getPaymentgqueryresult["dbResource"])){
       
         //updateing exchange rate
         $fx_rate = $row['fx_rate'];
        
         $fx_rate = ($fx_rate<=0) ? 1 : $fx_rate;
        $autoAssignChannelCOMapQuery = "UPDATE {$tableNameReport} set exchangeRate =  ".$fx_rate." where royaltyCurrency='".$row['accrued_royalty_currency']."'      ";
       
         $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);

  //updateing revINRwithoutHolding  
         $autoAssignChannelCOMapQuery = "UPDATE {$tableNameReport} set revINRwithoutHolding = royaltyAmount * exchangeRate where royaltyCurrency='".$row['accrued_royalty_currency']."'";
        
         $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);

       

//updateing withHolding  
        $holding_percentage = $row['holding_percentage'];
        $autoAssignChannelCOMapQuery = "UPDATE {$tableNameReport} set withHolding = (revINRwithoutHolding * ". $holding_percentage." )    where royaltyCurrency='".$row['accrued_royalty_currency']."'      ";
        
        $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
      
        //updateing amountReceived 
        $autoAssignChannelCOMapQuery = "UPDATE {$tableNameReport} set amountReceived = revINRwithoutHolding - withHolding where royaltyCurrency='".$row['accrued_royalty_currency']."'      ";
        
         $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);

     
            $updateQuery = "UPDATE  {$tableNameReport} a,crep_cms_clients b
            set a.clientShare = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueAmazon')),
            a.payable=(JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueAmazon'))*a.amountReceived)/100 
            where b.client_username =a.content_owner";
            $updateQueryResult = runQuery($updateQuery, $conn);

     }
     
   
    
//    printArr($numbofupdate);exit;

    $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
    $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
    //$a3 = runQuery("SET sql_log_bin = 1;", $conn);
    
}