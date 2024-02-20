<?php
 function create_appleMusic_report_Tablev2($tableName, $conn)
 {
     $returnArr = array();
     if (empty($tableName)) {
         return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
     }
 
     $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `StorefrontName` varchar(10)   DEFAULT NULL,
          `AppleIdentifier` varchar(50)  DEFAULT NULL,
          `MembershipType` varchar(150)  DEFAULT NULL,
          `Quantity`  int(11) DEFAULT NULL,
          `NetRoyalty` varchar(50) DEFAULT NULL,
          `NetRoyaltyTotal` varchar(50) DEFAULT NULL,
          `USD` decimal(18,9) DEFAULT NULL,
          `PartnerShare` decimal(18,9) DEFAULT NULL,
          `ISRC` varchar(50)  DEFAULT NULL,
          `ItemTitle` varchar(512)  DEFAULT NULL ,
          `ItemArtist` varchar(512)  DEFAULT NULL,
          `ItemType` int(11) DEFAULT NULL,
          `MediaType` int(11) DEFAULT NULL,
          `VendorIdentifier` varchar(50)  DEFAULT NULL,
          `OfflineIndicator` int(11) DEFAULT NULL,
          `Label` varchar(150)  DEFAULT NULL,
          `Grid` varchar(50)  DEFAULT NULL,
          `FinalPayable` decimal(18,9) DEFAULT NULL,
          `content_owner` varchar(150) DEFAULT NULL,
          `other1` varchar(150)  DEFAULT NULL,
          `other2` varchar(150)  DEFAULT NULL,
          `other3` varchar(150)  DEFAULT NULL,
          `other4` varchar(150)  DEFAULT NULL,
          `autoassign_steps` varchar(10)  DEFAULT NULL,
         
          PRIMARY KEY (id),
          INDEX `StorefrontName` (StorefrontName),
          INDEX `Quantity` (Quantity),
          INDEX USD (USD),
          INDEX PartnerShare (PartnerShare),
          INDEX `ISRC` (ISRC),
          INDEX `Label` (Label),
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

 
function insertAppleMusic_report_Table($filePath, $tableName, $conn)
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
			IGNORE 1 ROWS (`StorefrontName`,`AppleIdentifier`,`MembershipType`, `Quantity`, `NetRoyalty`, `NetRoyaltyTotal`, `USD`, `PartnerShare`, `ISRC`, `ItemTitle`, `ItemArtist`, `ItemType`, `MediaType`, `VendorIdentifier`, `OfflineIndicator`, `Label`, `Grid`);";

            @unlink("polo_import_music.txt");
            file_put_contents("polo_import_music.txt", $insertTableQuery);
            @chmod("polo_import_music.txt", 0777);


            $insertTableQueryResult = runQuery($insertTableQuery, $conn);


             

            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //    $a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }

             //inserting into logs 

            
              $result = array_map('strrev', explode('_', strrev($tableName)));
             $date_added = date("Y-m-d");
             $month = $result[0];
             $year = $result[1];
             $ndtype = $result[2];
             $query = "";//json_encode($insertTableQuery);
             $columns_name ='`StorefrontName`,`AppleIdentifier`,`MembershipType`, `Quantity`, `NetRoyalty`, `NetRoyaltyTotal`, `USD`, `PartnerShare`, `ISRC`, `ItemTitle`, `ItemArtist`, `ItemType`, `MediaType`, `VendorIdentifier`, `OfflineIndicator`, `Label`, `Grid`';
 
             $sql = "delete from activity_columnslogs where   table_name='{$tableName}'"; 
             $sqlresult = runQuery($sql, $conn);
             
             $insertQuery = "INSERT INTO activity_columnslogs (`date_added`, `month`,`year`,`query`,`columns_name`,`ndtype`,`table_name`) values('{$date_added}' , '{$month}' , '{$year}' ,    '{$query}' , '{$columns_name}','{$ndtype}','{$tableName}')";

             @unlink("polo_activity_columnslogs.txt");
             file_put_contents("polo_activity_columnslogs.txt", $insertQuery);
             @chmod("polo_activity_columnslogs.txt", 0777);
         

             $updateQueryResult = runQuery($insertQuery, $conn);  
             //end 
          
              
            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insertAppleMusic_report_Table($filePath, $tableName, $conn);
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
   

//activation start
function createActivationAppleMusic_report_Tablev2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `content_owner` varchar(150)  DEFAULT NULL,
							  `total_amt_recd` varchar(50)  DEFAULT NULL,
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

function autoAssignContentOwnerAppleCOMapStep1($tableName, $conn)
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
    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}      inner join  	channel_co_maping on {$tableName}.Label = channel_co_maping.Label  SET {$tableName}.content_owner = channel_co_maping.partner_provided   ,autoassign_steps='1'  where ({$tableName}.content_owner='' || {$tableName}.content_owner is NULL)   ";

    @unlink("polo_music_assign.txt");
    file_put_contents("polo_music_assign.txt", $autoAssignChannelCOMapQuery);
    @chmod("polo_music_assign.txt", 0777);

     

    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
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

  
function generateActicationAppleMusic_report_Tablev2($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,witholding,final_payable)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(PartnerShare),20),0),
						0,
						0,
                        0
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    

    if (noError($updateQueryResult)) {

        //$result = array_map('strrev', explode('_', strrev($sourcetable)));

        $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
        set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueAppleMusic')),
        a.amt_payable=ROUND((JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueAppleMusic'))*a.total_amt_recd)/100,20),
        a.final_payable= ROUND(((a.total_amt_recd)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueAppleMusic'))/100),20)
        where b.client_username =a.content_owner and b.`status` =1 and b.client_type_details!=''";
      

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
       
//itune here 
function create_itune_report_Tablev2($tableName, $conn)
 {
     $returnArr = array();
     if (empty($tableName)) {
         return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
     }
 
     $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `StartDate` varchar(12)  DEFAULT NULL,
          `EndDate` varchar(12) DEFAULT NULL,
          `UPC` varchar(50) DEFAULT NULL,
          `ISRC_ISBN`  varchar(50) DEFAULT NULL,
          `VendorIdentifier` varchar(50) DEFAULT NULL,
          `Quantity` int(11) DEFAULT NULL,
          `PartnerShare` varchar(50) DEFAULT NULL,
          `ExtendedPartnerShare` varchar(50) DEFAULT NULL,
          `PartnerShareCurrency` varchar(10) DEFAULT NULL,
          `USD` decimal(18,9) DEFAULT NULL,
          `LabelShare` decimal(18,9) DEFAULT NULL,
          `SalesorReturns` varchar(10) DEFAULT NULL,
          `AppleIdentifier` varchar(50) DEFAULT NULL,
          `Artist_Show_Developer_Author` varchar(150) DEFAULT NULL,
          `Title` varchar(150) DEFAULT NULL,
          `Label` varchar(150) DEFAULT NULL,
          `Grid` varchar(50) DEFAULT NULL,
          `ProductTypeIdentifier` varchar(10) DEFAULT NULL,
          `ISAN_OtherIdentifier` varchar(50) DEFAULT NULL,
          `CountryOfSale` varchar(10) DEFAULT NULL,
          `Pre_orderFlag` varchar(50) DEFAULT NULL,
          `PromoCode` varchar(50) DEFAULT NULL,
          `CustomerPrice` varchar(50) DEFAULT NULL,
          `CustomerCurrency` varchar(50) DEFAULT NULL,
          `content_owner` varchar(150) DEFAULT NULL,
          `other1` varchar(150) DEFAULT NULL,
          `other2` varchar(150) DEFAULT NULL,
          `other3` varchar(150) DEFAULT NULL,
          `other4` varchar(150) DEFAULT NULL,
          `autoassign_steps` varchar(10) DEFAULT NULL,
         
          PRIMARY KEY (id),
          INDEX `StartDate` (StartDate),
          INDEX `EndDate` (EndDate),
          INDEX `Quantity` (Quantity),
          INDEX USD (USD),
          INDEX PartnerShare (PartnerShare),
          INDEX LabelShare (LabelShare),
          INDEX `ISRC_ISBN` (ISRC_ISBN),
          INDEX `CountryOfSale` (CountryOfSale),
          INDEX `Label` (Label),
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

 
function insertItuneMusic_report_Table($filePath, $tableName, $conn)
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

         //   $sql = "SET NAMES UTF8";
         //   $aaa = runQuery($sql, $conn);

            $insertTableQuery = "LOAD DATA INFILE '{$currentFile}'
			INTO TABLE {$tableName}
			FIELDS TERMINATED BY ','
			ENCLOSED BY '\"'
			LINES TERMINATED BY '\\n'
			IGNORE 1 ROWS (`StartDate` , `EndDate` , `UPC` , `ISRC_ISBN` , `VendorIdentifier` , `Quantity` , `PartnerShare` , `ExtendedPartnerShare` , `PartnerShareCurrency` , `USD` , `LabelShare` , `SalesorReturns` , `AppleIdentifier` , `Artist_Show_Developer_Author` , `Title` , `Label` , `Grid` , `ProductTypeIdentifier` , `ISAN_OtherIdentifier` , `CountryOfSale` , `Pre_orderFlag` , `PromoCode` , `CustomerPrice` , `CustomerCurrency`);";
            $insertTableQueryResult = runQuery($insertTableQuery, $conn);

            @unlink("polo_import_music.txt");
            file_put_contents("polo_import_music.txt", $insertTableQuery);
            @chmod("polo_import_music.txt", 0777);


            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //    $a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }

             //inserting into logs 

            
             $result = array_map('strrev', explode('_', strrev($tableName)));
             $date_added = date("Y-m-d");
             $month = $result[0];
             $year = $result[1];
             $ndtype = $result[2];
             $query = "";// json_encode($insertTableQuery);
             $columns_name ='`StartDate` , `EndDate` , `UPC` , `ISRC_ISBN` , `VendorIdentifier` , `Quantity` , `PartnerShare` , `ExtendedPartnerShare` , `PartnerShareCurrency` , `USD` , `LabelShare` , `SalesorReturns` , `AppleIdentifier` , `Artist_Show_Developer_Author` , `Title` , `Label_Studio_Network_Developer_Publisher` , `Grid` , `ProductTypeIdentifier` , `ISAN_OtherIdentifier` , `CountryOfSale` , `Pre_orderFlag` , `PromoCode` , `CustomerPrice` , `CustomerCurrency`';
 
             $sql = "delete from activity_columnslogs where   table_name='{$tableName}'"; 
             $sqlresult = runQuery($sql, $conn);

             $insertQuery = "INSERT INTO activity_columnslogs (`date_added`, `month`,`year`,`query`,`columns_name`,`ndtype`,`table_name`) values('{$date_added}' , '{$month}' , '{$year}' ,    '{$query}' , '{$columns_name}','{$ndtype}','{$tableName}')";

             $updateQueryResult = runQuery($insertQuery, $conn);
             //end 

            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insertItuneMusic_report_Table($filePath, $tableName, $conn);
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
   

//activation start
function createActivationItuneMusic_report_Tablev2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `content_owner` varchar(150)  DEFAULT NULL,
							  `total_amt_recd` varchar(50)  DEFAULT NULL,
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

function autoAssignContentOwnerItuneCOMapStep1($tableName, $conn)
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
    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}      inner join  	channel_co_maping on {$tableName}.Label = channel_co_maping.Label  SET {$tableName}.content_owner = channel_co_maping.partner_provided   ,autoassign_steps='1'  where ({$tableName}.content_owner='' || {$tableName}.content_owner is NULL)   ";

    @unlink("polo_music_assign.txt");
    file_put_contents("polo_music_assign.txt", $autoAssignChannelCOMapQuery);
    @chmod("polo_music_assign.txt", 0777);

     

    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
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

  
function generateActicationItuneMusic_report_Tablev2($sourcetable, $desinationtable, $conn)
{

   
    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,witholding,final_payable)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(LabelShare),20),0),
						0,
						0,0
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    

    if (noError($updateQueryResult)) {

        //$result = array_map('strrev', explode('_', strrev($sourcetable)));

        $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
        set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueItunes')),
        a.amt_payable=ROUND((JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueItunes'))*a.total_amt_recd)/100,20),
        a.final_payable= ROUND(((a.total_amt_recd)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueItunes'))/100),20)
        where b.client_username =a.content_owner and b.`status` =1 and b.client_type_details!=''";
      

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
      


//Gaana here 
function create_gaana_report_Tablev2($tableName, $conn)
 {
     $returnArr = array();
     if (empty($tableName)) {
         return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
     }
 
     $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `Sub_vendor_Name` varchar(250)  DEFAULT NULL,
          `Month` varchar(10) DEFAULT NULL,
          `Free_Playouts` int(11) DEFAULT NULL,
          `Paid_Playouts`  int(11) DEFAULT NULL,
          `Total_Playouts` int(11) DEFAULT NULL,
          `free_playout_revenue` decimal(18,9) DEFAULT NULL,
          `paid_playout_revenue` decimal(18,9) DEFAULT NULL,
          `Total_Revenue` decimal(18,9) DEFAULT NULL,
          `content_owner` varchar(150) DEFAULT NULL,
          `other1` varchar(150) DEFAULT NULL,
          `other2` varchar(150) DEFAULT NULL,
          `other3` varchar(150) DEFAULT NULL,
          `other4` varchar(150) DEFAULT NULL,
          `autoassign_steps` varchar(10) DEFAULT NULL,
         
          PRIMARY KEY (id),
          
          INDEX `Sub_vendor_Name` (Sub_vendor_Name),
          INDEX Month (Month),
          INDEX Free_Playouts (Free_Playouts),
          INDEX `Paid_Playouts` (Paid_Playouts),
          INDEX `Total_Revenue` (Total_Revenue),
          INDEX `free_playout_revenue` (free_playout_revenue),
          INDEX `paid_playout_revenue` (paid_playout_revenue),
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

 
function insertGaanaMusic_report_Table($filePath, $tableName, $conn)
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
			IGNORE 1 ROWS (`Sub_vendor_Name`,`Month`,`Free_Playouts`, `Paid_Playouts`, `Total_Playouts`);";
            $insertTableQueryResult = runQuery($insertTableQuery, $conn);

            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //    $a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }

             //inserting into logs 

            
             $result = array_map('strrev', explode('_', strrev($tableName)));
             $date_added = date("Y-m-d");
             $month = $result[0];
             $year = $result[1];
             $ndtype = $result[2];
             $query = "";// $insertTableQuery;
             $columns_name ='`Sub_vendor_Name`,`Month`,`Free_Playouts`, `Paid_Playouts`, `Total_Playouts`';
 
             $sql = "delete from activity_columnslogs where   table_name='{$tableName}'"; 
             $sqlresult = runQuery($sql, $conn);

             $insertQuery = "INSERT INTO activity_columnslogs (`date_added`, `month`,`year`,`query`,`columns_name`,`ndtype`,`table_name`) values('{$date_added}' , '{$month}' , '{$year}' ,    '{$query}' , '{$columns_name}','{$ndtype}','{$tableName}')";

             $updateQueryResult = runQuery($insertQuery, $conn);
             //end 

            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insertGaanaMusic_report_Table($filePath, $tableName, $conn);
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
 
function autoAssignContentOwnerGaanaCOMapStep1($tableName, $conn)
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
 

    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}      inner join  	channel_co_maping on {$tableName}.Sub_vendor_Name = channel_co_maping.Label  SET {$tableName}.content_owner = channel_co_maping.partner_provided   ,autoassign_steps='1'  where ({$tableName}.content_owner='' || {$tableName}.content_owner is NULL)   ";

    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

    $result = array_map('strrev', explode('_', strrev($tableName)));
    $year = $result[1];
    $month = $result[0];
    $ndtype =  $result[2];
  
    $sql = "select * from monthly_rate_saavan_gaana_other where year='{$year}' and month='{$month}' and ndtype='{$ndtype}' ";
    $sqlResult = runQuery($sql, $conn);
    $resultscheck=mysqli_num_rows($sqlResult["dbResource"]);	
    if($resultscheck > 0 ){
        $res = mysqli_fetch_assoc($sqlResult["dbResource"]);
        $free_playout_revenue = $res['free_playout_revenue'] ;
        $paid_playout_revenue = $res['paid_playout_revenue'] ;

        $autoAssignChannelCOMapQuery = "UPDATE {$tableName}     SET paid_playout_revenue = Paid_Playouts * $paid_playout_revenue ,free_playout_revenue = Free_Playouts * $free_playout_revenue,Total_Revenue=  paid_playout_revenue + free_playout_revenue  ";
        $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
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
  

//activation start
function createActivationGaanaMusic_report_Tablev2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `content_owner` varchar(150)  DEFAULT NULL,
							  `total_amt_recd` varchar(50)  DEFAULT NULL,
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

 
function generateActicationSpotifyMusic_report_Tablev3($sourcetable, $desinationtable, $conn)
{

   
    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,witholding,final_payable)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(Payable_USD),20),0),
						0,
						0,0
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    
    
    if (noError($updateQueryResult)) {
        //new code 
    $result_des = array_map('strrev', explode('_', strrev($desinationtable)));
 
        // update us current  data
      
        $updateQuery1 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.gst_percentage = b.gst_per,
                        a.holding_percentage = 0,
                        a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueSpotify')),
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

function generateActicationGaanaMusic_report_Tablev3($sourcetable, $desinationtable, $conn)
{

   
    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,witholding)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(Total_Revenue),20),0),
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
 
        // update us current  data
      
        $updateQuery1 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.gst_percentage = b.gst_per,
                        a.holding_percentage = 0,
                        a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueGaana')),
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



//Saavan here 
function create_saavan_report_Tablev2($tableName, $conn)
 {
     $returnArr = array();
     if (empty($tableName)) {
         return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
     }
 
     $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `srno`  int(11) DEFAULT NULL,
          `TrackName` varchar(100) DEFAULT NULL,
          `AlbumName` varchar(100) DEFAULT NULL,
          `ArtistName`  varchar(100) DEFAULT NULL,
          `ISRC` varchar(50) DEFAULT NULL,
          `UPC` varchar(50) DEFAULT NULL,
          `Language` varchar(50) DEFAULT NULL,
          `Label` varchar(150) DEFAULT NULL,
          `Ad_Supported_Streams`  int(11) DEFAULT NULL,
          `Ad_Supported_Revenue` decimal(18,9) DEFAULT NULL,
          `Subscription_Streams`  int(11) DEFAULT NULL,
          `Subscription_Revenue` decimal(18,9) DEFAULT NULL,
          `Jio_Trial_Streams` int(11) DEFAULT NULL,
          `Jio_Trial_Revenue` decimal(18,9) DEFAULT NULL,
          `Total_Streams` int(11) DEFAULT NULL,
          `Total_Revenue` decimal(18,9) DEFAULT NULL,
          `content_owner` varchar(150) DEFAULT NULL,
          `other1` varchar(150) DEFAULT NULL,
          `other2` varchar(150) DEFAULT NULL,
          `other3` varchar(150) DEFAULT NULL,
          `other4` varchar(150) DEFAULT NULL,
          `autoassign_steps` varchar(10) DEFAULT NULL,
         
          PRIMARY KEY (id),
          INDEX `Ad_Supported_Streams` (Ad_Supported_Streams),
          INDEX `Ad_Supported_Revenue` (Ad_Supported_Revenue),
          INDEX `Subscription_Streams` (Subscription_Streams),
          INDEX `Subscription_Revenue` (Subscription_Revenue),
          INDEX `Total_Streams` (Total_Streams),
          INDEX `ISRC` (ISRC),
          INDEX `Label` (Label),
          INDEX `content_owner` (content_owner)
          
          ) ENGINE=InnoDB DEFAULT CHARSET=UTF8";
 

 @unlink("polo_atable_create.txt");
 file_put_contents("polo_atable_create.txt", $createYoutubeTableQuery);
 @chmod("polo_atable_create.txt", 0777);


     $createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);
 
     if (noError($createYoutubeTableQueryResult)) {
         $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
     } else {
         $returnArr = setErrorStack($returnArr, 3, null);
     }
 
     return $returnArr;
 }

 
function insertSaavanMusic_report_Table($filePath, $tableName, $conn)
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
			IGNORE 1 ROWS (`srno`,`TrackName`,`AlbumName`,  `ArtistName`, `ISRC`, `UPC`, `Language`, `Label`, `Ad_Supported_Streams`, `Subscription_Streams`, `Jio_Trial_Streams`,`Total_Streams`);";
            $insertTableQueryResult = runQuery($insertTableQuery, $conn);

            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //    $a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }

             //inserting into logs 

            
             $result = array_map('strrev', explode('_', strrev($tableName)));
             $date_added = date("Y-m-d");
             $month = $result[0];
             $year = $result[1];
             $ndtype = $result[2];
             $query = "";// $insertTableQuery;
             $columns_name ='`srno`,`TrackName`,`AlbumName`, `AlbumName`, `ArtistName`, `ISRC`, `UPC`, `Language`, `Label`, `Ad_Supported_Streams`, `Subscription_Streams`, `Jio_Trial_Streams`,`Total_Streams`';
 
             $sql = "delete from activity_columnslogs where   table_name='{$tableName}'"; 
             $sqlresult = runQuery($sql, $conn);

             $insertQuery = "INSERT INTO activity_columnslogs (`date_added`, `month`,`year`,`query`,`columns_name`,`ndtype`,`table_name`) values('{$date_added}' , '{$month}' , '{$year}' ,    '{$query}' , '{$columns_name}','{$ndtype}','{$tableName}')";

             $updateQueryResult = runQuery($insertQuery, $conn);
             //end 

            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insertSaavanMusic_report_Table($filePath, $tableName, $conn);
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
  
function autoAssignContentOwnerSaavanCOMapStep1($tableName, $conn)
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
    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}      inner join  	channel_co_maping on {$tableName}.Label = channel_co_maping.Label  SET {$tableName}.content_owner = channel_co_maping.partner_provided   ,autoassign_steps='1'  where ({$tableName}.content_owner='' || {$tableName}.content_owner is NULL)   ";

    @unlink("polo_saavan_update.txt");
    file_put_contents("polo_saavan_update.txt", $autoAssignChannelCOMapQuery);
    @chmod("polo_saavan_update.txt", 0777);


    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
    }

     
    $result = array_map('strrev', explode('_', strrev($tableName)));
    $year = $result[1];
    $month = $result[0];
    $ndtype =  $result[2];
  
    $sql = "select * from monthly_rate_saavan_gaana_other where year='{$year}' and month='{$month}' and ndtype='{$ndtype}' ";
    $sqlResult = runQuery($sql, $conn);
    $resultscheck=mysqli_num_rows($sqlResult["dbResource"]);	
    if($resultscheck > 0 ){
        $res = mysqli_fetch_assoc($sqlResult["dbResource"]);
        $Ad_Supported_Revenue = $res['Ad_Supported_Revenue'] * 1;
        $Subscription_Revenue = $res['Subscription_Revenue'] * 1;

        $autoAssignChannelCOMapQuery = "UPDATE {$tableName}     SET Ad_Supported_Revenue = Ad_Supported_Streams * $Ad_Supported_Revenue ,Subscription_Revenue = Subscription_Streams * $Subscription_Revenue,Total_Revenue=  Ad_Supported_Revenue+Subscription_Revenue  ";
        $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
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
 

//activation start
function createActivationSaavanMusic_report_Tablev2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `content_owner` varchar(150)  DEFAULT NULL,
							  `total_amt_recd` varchar(50)  DEFAULT NULL,
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

  
function generateActicationSaavanMusic_report_Tablev2($sourcetable, $desinationtable, $conn)
{

   
    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,witholding)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(Total_Revenue),20),0),
						0,
						0
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    

    if (noError($updateQueryResult)) {

        //$result = array_map('strrev', explode('_', strrev($sourcetable)));

        $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
        set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueSaavan')),
        a.amt_payable=ROUND((JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueSaavan'))*a.total_amt_recd)/100,20),
        a.final_payable= ROUND(((a.total_amt_recd)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueSaavan'))/100),20)
        where b.client_username =a.content_owner and b.`status` =1 and b.client_type_details!=''";
      

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
       
function generateActicationItuneMusic_report_Tablev3($sourcetable, $desinationtable, $conn)
{

   
    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,witholding,final_payable)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(LabelShare),20),0),
						0,
						0,0
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    if (noError($updateQueryResult)) {
        //new code 
    $result_des = array_map('strrev', explode('_', strrev($desinationtable)));
 
        // update us current  data
      
        $updateQuery1 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.gst_percentage = b.gst_per,
                        a.holding_percentage = 0,
                        a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueItunes')),
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
      

  
function generateActicationAppleMusic_report_Tablev3($sourcetable, $desinationtable, $conn)
{

    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,witholding,final_payable)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(PartnerShare),20),0),
						0,
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
 
        // update us current  data
      
        $updateQuery1 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.gst_percentage = b.gst_per,
                        a.holding_percentage = 0,
                        a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueAppleMusic')),
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
function generateActicationSaavanMusic_report_Tablev3($sourcetable, $desinationtable, $conn)
{

   
    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,witholding)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(Total_Revenue),20),0),
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
 
        // update us current  data
      
        $updateQuery1 = "UPDATE  {$desinationtable} a,crep_cms_clients b
		                set 
                        a.gst_percentage = b.gst_per,
                        a.holding_percentage = 0,
                        a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueSaavan')),
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
       


function getISRCLABELreportv3(
    $type,
    $conn,
    $search = '',
    $client = null
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '$type'";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $union_final_query_sql = "select  *   from 	( ";

    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];

            if (in_array("applemusic", $result)) {
                $union_final_query[] = " ( SELECT  DISTINCT   ISRC  as channelID ,'{$table_name}'  as tablename FROM $v where  content_owner = '{$client}' ) ";
            }else  if (in_array("itune", $result)) {
                $union_final_query[] = " ( SELECT  DISTINCT   ISRC  as channelID ,'{$table_name}'  as tablename FROM $v where  content_owner = '{$client}' ) ";
            }else  if (in_array("saavan", $result)) {
                $union_final_query[] = " ( SELECT  DISTINCT   ISRC  as channelID ,'{$table_name}'  as tablename FROM $v where  content_owner = '{$client}' ) ";
            } else  {
                $union_final_query[] = " ( SELECT  DISTINCT   Sub_vendor_Name  as channelID ,'{$table_name}'  as tablename FROM $v where  content_owner = '{$client}' ) ";
            }

        }

    }

    $check_query_list_new = implode(" union  ", $union_final_query);
    $getClientInfoQuery = $union_final_query_sql . $check_query_list_new . " )  as list_all order by channelID asc";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {

        $res[] = $row;
    }

    return setErrorStack($returnArr, -1, $res, null);
}


////Apple-music - v2
function getClientsAppleMusicReportv2(
    $type,

    $conn,
    $offset = 0,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";
    if ($client == null) {
        $client = $_SESSION['client'];
    }

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '$type'";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */

    $check_query_total1 = "select  sum(totalcount) as totalcount  from 	";
    $union_final_query_sql = "select  *   from 	 ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;
            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];
            $year_mnoth = $result[1]."_".$result[0];
            $nd_type = $result[2];

            //get share of co
            $rev_share = 30;
            $gst_per = 0;
            $holding_percentage = 0;
            $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	report_audio_activation_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
        
            $getshareres = runQuery($getshare, $conn);
            $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
        
            if (!empty($getshareresdata)) {
            
                $rev_share = $getshareresdata['shares'];
                $gst_percentage = $getshareresdata['gst_percentage'];
                $holding_percentage = $getshareresdata['holding_percentage'];
            }

            if (!empty($search)) {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}' and   ( ISRC like '" . $search . "%'   || ItemTitle like '%".$search."%' || Label like '%".$search."%') ";
                $union_final_query[] = " ( SELECT    ISRC ,ItemTitle,Label , sum(PartnerShare) as PartnerShare , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage   FROM $v where  content_owner = '{$client}' and ( ISRC like '" . $search . "%'   || ItemTitle like '%".$search."%' || Label like '%".$search."%') group by ISRC )  ";
            } else {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                $union_final_query[] = "( SELECT    ISRC ,ItemTitle,Label , sum(PartnerShare) as PartnerShare ,{$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage   FROM $v where   content_owner = '{$client}'  group by ISRC ) ";
            }

        }

    }

    $check_query_total_new = implode(" union  ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

     

    $row = [];
 

    $check_query_list_new = implode(" union  ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by USD desc";
    //file_put_contents("tablequery.txt",$youtubereport);
    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    //echo $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
       

        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;

        $finalpayable = (($row2['PartnerShare'] * $rev_share) / 100);
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

        

        $res['data'][] = [
            'ItemTitle' => isset($row2['ItemTitle']) ? $row2['ItemTitle'] : 'N/A',
            'ISRC' => isset($row2['ISRC']) ? $row2['ISRC'] : 'N/A',
            'Label' => isset($row2['Label']) ? $row2['Label'] : 'N/A',
            'USD' => $row2['PartnerShare'],
            'share' => $rev_share,
            'FinalPayable' => (($row2['PartnerShare'] * $rev_share) / 100),
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,
        ];
    }

    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}



////Apple-music - v2
function getClientsItuneMusicReportv2(
    $type,

    $conn,
    $offset = 0,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";
    if ($client == null) {
        $client = $_SESSION['client'];
    }

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '$type'";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */

    $check_query_total1 = "select  sum(totalcount) as totalcount  from 	";
    $union_final_query_sql = "select  *   from 	 ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;
            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];
            $year_mnoth = $result[1]."_".$result[0];
            $nd_type = $result[2];

            //get share of co
            $rev_share = 30;
            $gst_per = 0;
            $holding_percentage = 0;
            $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	report_audio_activation_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
        
            $getshareres = runQuery($getshare, $conn);
            $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
        
            if (!empty($getshareresdata)) {
            
                $rev_share = $getshareresdata['shares'];
                $gst_percentage = $getshareresdata['gst_percentage'];
                $holding_percentage = $getshareresdata['holding_percentage'];
            }

            if (!empty($search)) {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'    and ( ISRC_ISBN like '" . $search . "%' || Title like '%".$search."%' || Label like '%".$search."%')   ";

                $union_final_query[] = " ( SELECT  ISRC_ISBN ,Title,Label ,   sum(LabelShare) as LabelShare, {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage    FROM $v where  content_owner = '{$client}' and ( ISRC_ISBN like '" . $search . "%' || Title like '%".$search."%' || Label like '%".$search."%')  group by ISRC_ISBN )  ";
            } else {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                $union_final_query[] = " ( SELECT  ISRC_ISBN ,Title,Label ,   sum(LabelShare) as LabelShare , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage    FROM $v where  content_owner = '{$client}'  group by ISRC_ISBN ) ";
            }

        }

    }

    $check_query_total_new = implode(" union  ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

    

    $row = [];

     
    $check_query_list_new = implode(" union  ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by USD desc";
    //file_put_contents("tablequery.txt",$youtubereport);
    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

   // echo $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        
        
        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;

        $finalpayable = (($row2['LabelShare'] * $rev_share) / 100);
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

        $res['data'][] = [
            'Title' => isset($row2['Title']) ? $row2['Title'] : 'N/A',
            'ISRC' => isset($row2['ISRC_ISBN']) ? $row2['ISRC_ISBN'] : 'N/A',
            'Label' => isset($row2['Label']) ? $row2['Label'] : 'N/A',
            'USD' => $row2['LabelShare'],
            'share' => $rev_share,
            'FinalPayable' => (($row2['LabelShare'] * $rev_share) / 100),
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,
        ];
    }

    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}
 



function RevenueChartClientsAppleMusicReportv2(
    $table,
   
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $channelid = null
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";
    if ($client == null) {
        $client = $_SESSION['client'];
    }

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '$table'";

    @unlink("polochart_rev_lik.txt");
    file_put_contents("polochart_rev_lik.txt", $getClientInfoQuery);
    @chmod("polochart_rev_lik.txt", 0777);

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */
    $union_final_query_sql = "select  *   from 	 ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {

            // $explode_table = explode("youtube_video_claim_report_",$v);
            //     $explode_table_1 = explode("_",$explode_table[1]);
            //     $table_name = $explode_table_1[0];

            //     $result = array_map('strrev', explode('_', strrev($v)));
            //     $table_name = $result[2];
            // and channel_id like '{$channelid}'

            if (!empty($channelid)) {
                $union_final_query[] = " ( SELECT  ISRC   ,ItemTitle,Quantity ,Label , sum(USD) as FinalPayable FROM $v where content_owner = '" . $client . "'   group by Label ORDER by Label  )  ";
            } else {
                $union_final_query[] = " ( SELECT ISRC   ,ItemTitle,Quantity ,Label , sum(USD) as FinalPayable FROM $v where content_owner = '" . $client . "'   group by Label ORDER by Label  )  ";
            }

        }

    }

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by Label asc";

    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    @unlink("polochart_rev.txt");
    file_put_contents("polochart_rev.txt", $youtubereport);
    @chmod("polochart_rev.txt", 0777);

   // echo $youtubereport;

    $getAllrevenueQueryResult = runQuery($youtubereport, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        if ($row['Label'] != "") {
            

            $res[] = array('c' => array(
                
                array('v' => $row['Label']),
            
                array('v' => $row['FinalPayable']),
              
                
               
            ));
        }

    }

    return setErrorStack($returnArr, -1, $res, null);
}


function ViewsChartClientsAppleMusicReportv2(
    $table,
    
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $channelid
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";
    if ($client == null) {
        $client = $_SESSION['client'];
    }

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '" . trim($table) . "'";
    @unlink("polochart.txt");
    file_put_contents("polochart.txt", $getClientInfoQuery);
    @chmod("polochart.txt", 0777);
    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */
    $union_final_query_sql = "select  *   from 	 ";

    $union_final_query = [];

    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {

            // $explode_table = explode("youtube_video_claim_report_",$v);
            //     $explode_table_1 = explode("_",$explode_table[1]);
            //     $table_name = $explode_table_1[0];

            $result = array_map('strrev', explode('_', strrev($v)));
            //     $table_name = $result[2];

            if (!empty($channelid)) {
                $union_final_query[] = " ( SELECT  Label , count(Label) as FinalPayable FROM $v where content_owner = '" . $client . "'    group by Label ORDER by Label )  ";
            } else {
                $union_final_query[] = " ( SELECT  Label , count(Label) as FinalPayable FROM $v where content_owner = '" . $client . "'    group by Label ORDER by Label )  ";
            }

        }

    }

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by Label asc";

    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    @unlink("polochart_view.txt");
    file_put_contents("polochart_view.txt", $youtubereport);
    @chmod("polochart_view.txt", 0777);
    //echo $youtubereport;
    
    $getAllrevenueQueryResult = runQuery($youtubereport, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        //$d= explode('-',$row['day']);

        if ($row['Label'] != "") {
            
            $res[] = array('c' => array(
                array('v' => $row['Label']),
                array('v' => $row['FinalPayable'])
              
            ));
        }

    }

    return setErrorStack($returnArr, -1, $res, null);
}



function RevenueChartClientsItuneMusicReportv2(
    $table,
   
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $channelid = null
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";
    if ($client == null) {
        $client = $_SESSION['client'];
    }

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '$table'";

    @unlink("polochart_rev_lik.txt");
    file_put_contents("polochart_rev_lik.txt", $getClientInfoQuery);
    @chmod("polochart_rev_lik.txt", 0777);

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */
    $union_final_query_sql = "select  *   from 	 ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {

            // $explode_table = explode("youtube_video_claim_report_",$v);
            //     $explode_table_1 = explode("_",$explode_table[1]);
            //     $table_name = $explode_table_1[0];

            //     $result = array_map('strrev', explode('_', strrev($v)));
            //     $table_name = $result[2];
            // and channel_id like '{$channelid}'

            if (!empty($channelid)) {
                $union_final_query[] = " ( SELECT  Label , sum(LabelShare) as FinalPayable FROM $v where content_owner = '" . $client . "'   group by Label ORDER by Label  )  ";
            } else {
                $union_final_query[] = " ( SELECT  Label , sum(LabelShare) as FinalPayable FROM $v where content_owner = '" . $client . "'   group by Label ORDER by Label  )  ";
            }

        }

    }

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by Label asc";

    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    @unlink("polochart_rev.txt");
    file_put_contents("polochart_rev.txt", $youtubereport);
    @chmod("polochart_rev.txt", 0777);

   // echo $youtubereport;

    $getAllrevenueQueryResult = runQuery($youtubereport, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        if ($row['Label'] != "") {
            

            $res[] = array('c' => array(
                
                array('v' => $row['Label']),
            
                array('v' => $row['FinalPayable']),
              
                
               
            ));
        }

    }

    return setErrorStack($returnArr, -1, $res, null);
}


function ViewsChartClientsItuneMusicReportv2(
    $table,
    
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $channelid
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";
    if ($client == null) {
        $client = $_SESSION['client'];
    }

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '" . trim($table) . "'";
    @unlink("polochart.txt");
    file_put_contents("polochart.txt", $getClientInfoQuery);
    @chmod("polochart.txt", 0777);
    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */
    $union_final_query_sql = "select  *   from 	 ";

    $union_final_query = [];

    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {

            // $explode_table = explode("youtube_video_claim_report_",$v);
            //     $explode_table_1 = explode("_",$explode_table[1]);
            //     $table_name = $explode_table_1[0];

            $result = array_map('strrev', explode('_', strrev($v)));
            //     $table_name = $result[2];

            if (!empty($channelid)) {
                $union_final_query[] = " ( SELECT  Label , count(Label) as FinalPayable FROM $v where content_owner = '" . $client . "'    group by Label ORDER by Label )  ";
            } else {
                $union_final_query[] = " ( SELECT  Label , count(Label) as FinalPayable FROM $v where content_owner = '" . $client . "'    group by Label ORDER by Label )  ";
            }

        }

    }

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by Label asc";

    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    @unlink("polochart_view.txt");
    file_put_contents("polochart_view.txt", $youtubereport);
    @chmod("polochart_view.txt", 0777);
    //echo $youtubereport;
    
    $getAllrevenueQueryResult = runQuery($youtubereport, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        //$d= explode('-',$row['day']);

        if ($row['Label'] != "") {
            
            $res[] = array('c' => array(
                array('v' => $row['Label']),
                array('v' => $row['FinalPayable'])
              
            ));
        }

    }

    return setErrorStack($returnArr, -1, $res, null);
}

//gaana 



function RevenueChartClientsGaanaMusicReportv2(
    $table,
   
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $channelid = null
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";
    if ($client == null) {
        $client = $_SESSION['client'];
    }

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '$table'";

    @unlink("polochart_rev_lik.txt");
    file_put_contents("polochart_rev_lik.txt", $getClientInfoQuery);
    @chmod("polochart_rev_lik.txt", 0777);

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */
    $union_final_query_sql = "select  *   from 	 ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {

            // $explode_table = explode("youtube_video_claim_report_",$v);
            //     $explode_table_1 = explode("_",$explode_table[1]);
            //     $table_name = $explode_table_1[0];

            //     $result = array_map('strrev', explode('_', strrev($v)));
            //     $table_name = $result[2];
            // and channel_id like '{$channelid}'

            if (!empty($channelid)) {
                $union_final_query[] = " ( SELECT  Sub_vendor_Name , sum(Total_Revenue) as FinalPayable FROM $v where content_owner = '" . $client . "'   group by Sub_vendor_Name ORDER by Sub_vendor_Name  )  ";
            } else {
                $union_final_query[] = " ( SELECT  Sub_vendor_Name , sum(Total_Revenue) as FinalPayable FROM $v where content_owner = '" . $client . "'   group by Sub_vendor_Name ORDER by Sub_vendor_Name  )  ";
            }

        }

    }

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by Sub_vendor_Name asc";

    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    @unlink("polochart_rev.txt");
    file_put_contents("polochart_rev.txt", $youtubereport);
    @chmod("polochart_rev.txt", 0777);

   // echo $youtubereport;

    $getAllrevenueQueryResult = runQuery($youtubereport, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        if ($row['Sub_vendor_Name'] != "") {
            

            $res[] = array('c' => array(
                
                array('v' => $row['Sub_vendor_Name']),
            
                array('v' => $row['FinalPayable']),
              
                
               
            ));
        }

    }

    return setErrorStack($returnArr, -1, $res, null);
}


function ViewsChartClientsGaanaMusicReportv2(
    $table,
    
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $channelid
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";
    if ($client == null) {
        $client = $_SESSION['client'];
    }

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '" . trim($table) . "'";
    @unlink("polochart.txt");
    file_put_contents("polochart.txt", $getClientInfoQuery);
    @chmod("polochart.txt", 0777);
    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */
    $union_final_query_sql = "select  *   from 	 ";

    $union_final_query = [];

    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {

            // $explode_table = explode("youtube_video_claim_report_",$v);
            //     $explode_table_1 = explode("_",$explode_table[1]);
            //     $table_name = $explode_table_1[0];

            $result = array_map('strrev', explode('_', strrev($v)));
            //     $table_name = $result[2];

            if (!empty($channelid)) {
                $union_final_query[] = " ( SELECT  Sub_vendor_Name , sum(Total_Playouts) as FinalPayable FROM $v where content_owner = '" . $client . "'    group by Sub_vendor_Name ORDER by Sub_vendor_Name )  ";
            } else {
                $union_final_query[] = " ( SELECT  Sub_vendor_Name , sum(Total_Playouts) as FinalPayable FROM $v where content_owner = '" . $client . "'    group by Sub_vendor_Name ORDER by Sub_vendor_Name )  ";
            }

        }

    }

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by Sub_vendor_Name asc";

    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    @unlink("polochart_view.txt");
    file_put_contents("polochart_view.txt", $youtubereport);
    @chmod("polochart_view.txt", 0777);
    //echo $youtubereport;
    
    $getAllrevenueQueryResult = runQuery($youtubereport, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        //$d= explode('-',$row['day']);

        if ($row['Sub_vendor_Name'] != "") {
            
            $res[] = array('c' => array(
                array('v' => $row['Sub_vendor_Name']),
                array('v' => $row['FinalPayable'])
              
            ));
        }

    }

    return setErrorStack($returnArr, -1, $res, null);
}



////Apple-music - v2
function getClientsGaanaMusicReportv2(
    $type,

    $conn,
    $offset = 0,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";
    if ($client == null) {
        $client = $_SESSION['client'];
    }

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '$type'";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */

    $check_query_total1 = "select  sum(totalcount) as totalcount  from 	";
    $union_final_query_sql = "select  *   from 	 ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;
            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];

            $year_mnoth = $result[1]."_".$result[0];
            $nd_type = $result[2];

            //get share of co
            $rev_share = 30;
            $gst_per = 0;
            $holding_percentage = 0;
            $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	report_audio_activation_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";

            $getshareres = runQuery($getshare, $conn);
            $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);

            if (!empty($getshareresdata)) {

                $rev_share = $getshareresdata['shares'];
                $gst_percentage = $getshareresdata['gst_percentage'];
                $holding_percentage = $getshareresdata['holding_percentage'];
            }

            if (!empty($search)) {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'    and ( Sub_vendor_Name like '%" . $search . "%')   ";

                $union_final_query[] = " ( SELECT  Sub_vendor_Name  , sum(Free_Playouts) as Free_Playouts , sum(Paid_Playouts) as Paid_Playouts  , sum(Total_Playouts) as Total_Playouts , sum(free_playout_revenue) as free_playout_revenue , sum(paid_playout_revenue) as paid_playout_revenue ,  sum(Total_Revenue) as Total_Revenue ,{$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage   FROM $v where  content_owner = '{$client}' and ( Sub_vendor_Name like '%" . $search . "%' )  group by Sub_vendor_Name )  ";
            } else {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                $union_final_query[] = " ( SELECT   Sub_vendor_Name  , sum(Free_Playouts) as Free_Playouts , sum(Paid_Playouts) as Paid_Playouts  , sum(Total_Playouts) as Total_Playouts , sum(free_playout_revenue) as free_playout_revenue , sum(paid_playout_revenue) as paid_playout_revenue ,  sum(Total_Revenue) as Total_Revenue,{$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage   FROM $v where  content_owner = '{$client}'  group by Sub_vendor_Name ) ";
            }

        }

    }

    $check_query_total_new = implode(" union  ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

  

    $row = [];

    
    $check_query_list_new = implode(" union  ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by Total_Revenue desc";
    //file_put_contents("tablequery.txt",$youtubereport);
    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

   // echo $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
         
        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;

        $finalpayable = (($row2['Total_Revenue'] * $rev_share) / 100);
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

        $res['data'][] = [
            'Sub_vendor_Name' => isset($row2['Sub_vendor_Name']) ? $row2['Sub_vendor_Name'] : 'N/A',
            'Free_Playouts' => isset($row2['Free_Playouts']) ? $row2['Free_Playouts'] : 'N/A',
            'Paid_Playouts' => isset($row2['Paid_Playouts']) ? $row2['Paid_Playouts'] : 'N/A',
            'Total_Playouts' => isset($row2['Total_Playouts']) ? $row2['Total_Playouts'] : 'N/A',
            'free_playout_revenue' => isset($row2['free_playout_revenue']) ? $row2['free_playout_revenue'] : 'N/A',
            'paid_playout_revenue' => isset($row2['paid_playout_revenue']) ? $row2['paid_playout_revenue'] : 'N/A',
            'share' => $rev_share,
            'Total_Revenue' => isset($row2['Total_Revenue']) ? $row2['Total_Revenue'] : 'N/A',
            'FinalPayable' => (($row2['Total_Revenue'] * $rev_share) / 100),
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,
        ];
    }
       
    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}
 




function RevenueChartClientsSaavanMusicReportv2(
    $table,
   
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $channelid = null
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";
    if ($client == null) {
        $client = $_SESSION['client'];
    }

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '$table'";

    @unlink("polochart_rev_lik.txt");
    file_put_contents("polochart_rev_lik.txt", $getClientInfoQuery);
    @chmod("polochart_rev_lik.txt", 0777);

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */
    $union_final_query_sql = "select  *   from 	 ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {

            // $explode_table = explode("youtube_video_claim_report_",$v);
            //     $explode_table_1 = explode("_",$explode_table[1]);
            //     $table_name = $explode_table_1[0];

            //     $result = array_map('strrev', explode('_', strrev($v)));
            //     $table_name = $result[2];
            // and channel_id like '{$channelid}'

            if (!empty($channelid)) {
                $union_final_query[] = " ( SELECT  Label , sum(Total_Revenue) as FinalPayable FROM $v where content_owner = '" . $client . "'   group by Label ORDER by Label  )  ";
            } else {
                $union_final_query[] = " ( SELECT  Label , sum(Total_Revenue) as FinalPayable FROM $v where content_owner = '" . $client . "'   group by Label ORDER by Label  )  ";
            }

        }

    }

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by Label asc";

    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    @unlink("polochart_rev.txt");
    file_put_contents("polochart_rev.txt", $youtubereport);
    @chmod("polochart_rev.txt", 0777);

   // echo $youtubereport;

    $getAllrevenueQueryResult = runQuery($youtubereport, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        if ($row['Label'] != "") {
            

            $res[] = array('c' => array(
                
                array('v' => $row['Label']),
            
                array('v' => $row['FinalPayable']),
              
                
               
            ));
        }

    }

    return setErrorStack($returnArr, -1, $res, null);
}


function ViewsChartClientsSaavanMusicReportv2(
    $table,
    
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $channelid
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";
    if ($client == null) {
        $client = $_SESSION['client'];
    }

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '" . trim($table) . "'";
    @unlink("polochart.txt");
    file_put_contents("polochart.txt", $getClientInfoQuery);
    @chmod("polochart.txt", 0777);
    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */
    $union_final_query_sql = "select  *   from 	 ";

    $union_final_query = [];

    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {

            // $explode_table = explode("youtube_video_claim_report_",$v);
            //     $explode_table_1 = explode("_",$explode_table[1]);
            //     $table_name = $explode_table_1[0];

            $result = array_map('strrev', explode('_', strrev($v)));
            //     $table_name = $result[2];

            if (!empty($channelid)) {
                $union_final_query[] = " ( SELECT  Label , sum(Total_Streams) as FinalPayable FROM $v where content_owner = '" . $client . "'    group by Label ORDER by Label )  ";
            } else {
                $union_final_query[] = " ( SELECT  Label , sum(Total_Streams) as FinalPayable FROM $v where content_owner = '" . $client . "'    group by Label ORDER by Label )  ";
            }

        }

    }

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by Label asc";

    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    @unlink("polochart_view.txt");
    file_put_contents("polochart_view.txt", $youtubereport);
    @chmod("polochart_view.txt", 0777);
    //echo $youtubereport;
    
    $getAllrevenueQueryResult = runQuery($youtubereport, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        //$d= explode('-',$row['day']);

        if ($row['Label'] != "") {
            
            $res[] = array('c' => array(
                array('v' => $row['Label']),
                array('v' => $row['FinalPayable'])
              
            ));
        }

    }

    return setErrorStack($returnArr, -1, $res, null);
}



function getClientsSaanvanMusicReportv2(
    $type,

    $conn,
    $offset = 0,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";
    if ($client == null) {
        $client = $_SESSION['client'];
    }

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '$type'";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */

    $check_query_total1 = "select  sum(totalcount) as totalcount  from 	";
    $union_final_query_sql = "select  *   from 	 ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;
            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];

            $year_mnoth = $result[1]."_".$result[0];
            $nd_type = $result[2];

            //get share of co
            $rev_share = 30;
            $gst_per = 0;
            $holding_percentage = 0;
            $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	report_audio_activation_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
        
            $getshareres = runQuery($getshare, $conn);
            $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
        
            if (!empty($getshareresdata)) {
            
                $rev_share = $getshareresdata['shares'];
                $gst_percentage = $getshareresdata['gst_percentage'];
                $holding_percentage = $getshareresdata['holding_percentage'];
            }


            if (!empty($search)) {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'    and ( Label like '%" . $search . "%')   ";

                $union_final_query[] = " ( SELECT  TrackName  ,ISRC  ,Language  ,Label  , sum(Ad_Supported_Streams ) as Ad_Supported_Streams    , sum(Subscription_Streams ) as Subscription_Streams   ,sum(Jio_Trial_Streams ) as Jio_Trial_Streams  , sum(Total_Streams ) as Total_Streams    ,  sum(Jio_Trial_Revenue ) as Jio_Trial_Revenue  ,  sum(Ad_Supported_Revenue  ) as Ad_Supported_Revenue   ,  sum(Subscription_Revenue ) as Subscription_Revenue ,  sum(Total_Revenue ) as Total_Revenue ,{$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage    FROM $v where  content_owner = '{$client}' and ( Label like '%" . $search . "%' )  group by ISRC )  ";
            } else {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                $union_final_query[] = " ( SELECT   TrackName  ,ISRC  ,Language  ,Label  ,Ad_Supported_Streams   ,Subscription_Streams ,Jio_Trial_Streams ,Total_Streams  ,  sum(Jio_Trial_Revenue ) as Jio_Trial_Revenue  ,  sum(Ad_Supported_Revenue  ) as Ad_Supported_Revenue   ,  sum(Subscription_Revenue ) as Subscription_Revenue ,  sum(Total_Revenue ) as Total_Revenue ,{$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage    FROM $v where  content_owner = '{$client}'  group by ISRC ) ";
            }

        }

    }

    $check_query_total_new = implode(" union  ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

    
    $row = [];
 
    $check_query_list_new = implode(" union  ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by Total_Revenue desc";
    //file_put_contents("tablequery.txt",$youtubereport);
    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

   // echo $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
      
        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;
        

        $finalpayable = (($row2['Total_Revenue'] * $rev_share) / 100);
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);
         
        $res['data'][] = [
            'Label' => isset($row2['Label']) ? $row2['Label'] : 'N/A',
            'ISRC' => isset($row2['ISRC']) ? $row2['ISRC'] : 'N/A',
            'Language' => isset($row2['Language']) ? $row2['Language'] : 'N/A',
         
            'Ad_Supported_Streams' => isset($row2['Ad_Supported_Streams']) ? $row2['Ad_Supported_Streams'] : 'N/A',
            'Subscription_Streams' => isset($row2['Subscription_Streams']) ? $row2['Subscription_Streams'] : 'N/A',
            'Jio_Trial_Streams' => isset($row2['Jio_Trial_Streams']) ? $row2['Jio_Trial_Streams'] : 'N/A',
            'Total_Streams' => isset($row2['Total_Streams']) ? $row2['Total_Streams'] : 'N/A',
            'Ad_Supported_Revenue' => isset($row2['Ad_Supported_Revenue']) ? $row2['Ad_Supported_Revenue'] : 'N/A',
            'Subscription_Revenue' => isset($row2['Subscription_Revenue']) ? $row2['Subscription_Revenue'] : 'N/A',
           
            'Total_Revenue' => isset($row2['Total_Revenue']) ? $row2['Total_Revenue'] : 'N/A',
            'share' => $rev_share,
            'FinalPayable' => (($row2['Total_Revenue'] * $rev_share) / 100),
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,

        ];
    }
       
    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}
 
//---------------spotify search
function create_spotify_report_Tablev2($tableName, $conn)
 {
     $returnArr = array();
     if (empty($tableName)) {
         return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
     }
 

     
     $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `Country`  varchar(50) DEFAULT NULL,
          `Product` varchar(150) DEFAULT NULL,
          `URI` varchar(150) DEFAULT NULL,
          `UPC`  varchar(100) DEFAULT NULL,
          `EAN` varchar(50) DEFAULT NULL,
          `ISRC` varchar(50) DEFAULT NULL,
          `Track_name` varchar(100) DEFAULT NULL,
          `Artist_name` varchar(150) DEFAULT NULL,
          `Composer_name` varchar(100) DEFAULT NULL,
          `Album_name` varchar(50) DEFAULT NULL,
          `Quantity`  int(11) DEFAULT NULL,
          `Label` varchar(50) DEFAULT NULL,
          `Payable_invoice` decimal(18,9) DEFAULT NULL,
          `Invoice_currency` varchar(50) DEFAULT NULL,
          `Payable_EUR` decimal(18,9) DEFAULT NULL,
          `Payable_USD` decimal(18,9) DEFAULT NULL,
          `content_owner` varchar(150) DEFAULT NULL,
          `other1` varchar(150)  DEFAULT NULL,
          `other2` varchar(150)  DEFAULT NULL,
          `other3` varchar(150)  DEFAULT NULL,
          `other4` varchar(150)  DEFAULT NULL,
          `autoassign_steps` varchar(10)  DEFAULT NULL,
          PRIMARY KEY (id),
          INDEX `Country` (Country),
          INDEX `Invoice_currency` (Invoice_currency),
          INDEX `Album_name` (Album_name),
          INDEX `Payable_USD` (Payable_USD),
          INDEX `ISRC` (ISRC),
          INDEX `Label` (Label),
          INDEX `content_owner` (content_owner)
          
          ) ENGINE=InnoDB DEFAULT CHARSET=UTF8";
 

 
 
        @unlink("polo_create_music.txt");
        file_put_contents("polo_create_music.txt", $createYoutubeTableQuery);
        @chmod("polo_create_music.txt", 0777);


     $createYoutubeTableQueryResult = runQuery($createYoutubeTableQuery, $conn);
 
     if (noError($createYoutubeTableQueryResult)) {
         $returnArr = setErrorStack($returnArr, -1, $createYoutubeTableQueryResult);
     } else {
         $returnArr = setErrorStack($returnArr, 3, null);
     }
 
     return $returnArr;
 }

function insertSpotifyMusic_report_Table($filePath, $tableName, $conn)
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

         //   $sql = "SET NAMES UTF8";
         //   $aaa = runQuery($sql, $conn);

            $insertTableQuery = "LOAD DATA INFILE '{$currentFile}'
			INTO TABLE {$tableName}
			FIELDS TERMINATED BY ','
			ENCLOSED BY '\"'
			LINES TERMINATED BY '\\n'
			IGNORE 1 ROWS (`Country`,`Product`,`URI`,`UPC`,`EAN`,`ISRC`,`Track_name`,`Artist_name`,`Composer_name`,`Album_name`,`Quantity`,`Label`,`Payable_invoice`,`Invoice_currency`,`Payable_EUR`,`Payable_USD`);";
            $insertTableQueryResult = runQuery($insertTableQuery, $conn);

            @unlink("polo_import_music.txt");
            file_put_contents("polo_import_music.txt", $insertTableQuery);
            @chmod("polo_import_music.txt", 0777);


            $a1 = runQuery("SET UNIQUE_CHECKS = 1;", $conn);
            $a2 = runQuery("SET FOREIGN_KEY_CHECKS  = 1;", $conn);
            //    $a3 = runQuery("SET sql_log_bin = 1;", $conn);
            if (!noError($insertTableQueryResult)) {
                return setErrorStack($returnArr, 3, $insertTableQueryResult['query'], null);
            }

             //inserting into logs 

            
             $result = array_map('strrev', explode('_', strrev($tableName)));
             $date_added = date("Y-m-d");
             $month = $result[0];
             $year = $result[1];
             $ndtype = $result[2];
             $query = "";// json_encode($insertTableQuery);
             $columns_name ='`StartDate` , `EndDate` , `UPC` , `ISRC_ISBN` , `VendorIdentifier` , `Quantity` , `PartnerShare` , `ExtendedPartnerShare` , `PartnerShareCurrency` , `USD` , `LabelShare` , `SalesorReturns` , `AppleIdentifier` , `Artist_Show_Developer_Author` , `Title` , `Label_Studio_Network_Developer_Publisher` , `Grid` , `ProductTypeIdentifier` , `ISAN_OtherIdentifier` , `CountryOfSale` , `Pre_orderFlag` , `PromoCode` , `CustomerPrice` , `CustomerCurrency`';
 
             $sql = "delete from activity_columnslogs where   table_name='{$tableName}'"; 
             $sqlresult = runQuery($sql, $conn);

             $insertQuery = "INSERT INTO activity_columnslogs (`date_added`, `month`,`year`,`query`,`columns_name`,`ndtype`,`table_name`) values('{$date_added}' , '{$month}' , '{$year}' ,    '{$query}' , '{$columns_name}','{$ndtype}','{$tableName}')";

             $updateQueryResult = runQuery($insertQuery, $conn);
             //end 

            $arr = array_shift($files);
            if (is_array($files)) {
                $filePath = implode(',', $files);
            }
            if ($filePath) {
                return insertSpotifyMusic_report_Table($filePath, $tableName, $conn);
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
   

//activation start
function createActivationSpotifyMusic_report_Tablev2($tableName, $conn)
{
    $returnArr = array();
    if (empty($tableName)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Tablename to create cannot be empty", null);
    }
    $createYoutubeTableQuery = "CREATE TABLE {$tableName}  (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `content_owner` varchar(150)  DEFAULT NULL,
							  `total_amt_recd` varchar(50)  DEFAULT NULL,
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

function autoAssignContentOwnerSpotifyCOMapStep1($tableName, $conn)
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
    $autoAssignChannelCOMapQuery = "UPDATE {$tableName}      inner join  	channel_co_maping on {$tableName}.Label = channel_co_maping.Label  SET {$tableName}.content_owner = channel_co_maping.partner_provided   ,autoassign_steps='1'  where ({$tableName}.content_owner='' || {$tableName}.content_owner is NULL)   ";

    @unlink("polo_music_assign.txt");
    file_put_contents("polo_music_assign.txt", $autoAssignChannelCOMapQuery);
    @chmod("polo_music_assign.txt", 0777);

     

    $autoAssignChannelCOMapQueryResult = runQuery($autoAssignChannelCOMapQuery, $conn);
    if (!noError($autoAssignChannelCOMapQueryResult)) {
        return setErrorStack($returnArr, 3, $autoAssignChannelCOMapQueryResult, null);
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

  
function generateActicationSpotifyMusic_report_Tablev2($sourcetable, $desinationtable, $conn)
{

   
    $res = array();
    $returnArr = array();
    $extraArg = array();

    $updateQuery = "INSERT INTO {$desinationtable} (content_owner, total_amt_recd,us_payout,witholding,final_payable)
					SELECT
						content_owner,
						Coalesce(ROUND(SUM(Payable_USD),20),0),
						0,
						0,0
					FROM  {$sourcetable} where content_owner!='' GROUP by content_owner";
    $updateQueryResult = runQuery($updateQuery, $conn);

    @unlink("polo_ACT.txt");
    file_put_contents("polo_ACT.txt", $updateQuery);
    @chmod("polo_ACT.txt", 0777);

    

    if (noError($updateQueryResult)) {

        //$result = array_map('strrev', explode('_', strrev($sourcetable)));

        $updateQuery = "UPDATE  {$desinationtable} a,crep_cms_clients b
        set a.shares = JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueSpotify')),
        a.amt_payable=ROUND((JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueSpotify'))*a.total_amt_recd)/100,20),
        a.final_payable= ROUND(((a.total_amt_recd)*JSON_UNQUOTE(JSON_EXTRACT(b.client_type_details, '$.revenueSpotify'))/100),20)
        where b.client_username =a.content_owner and b.`status` =1 and b.client_type_details!=''";
      

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

function getClientsSpotifyMusicReportv2(
    $type,

    $conn,
    $offset = 0,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";
    if ($client == null) {
        $client = $_SESSION['client'];
    }

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '$type'";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */

    $check_query_total1 = "select  sum(totalcount) as totalcount  from 	";
    $union_final_query_sql = "select  *   from 	 ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;
            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];

  	        $year_mnoth = $result[1]."_".$result[0];
            $nd_type = $result[2];

            //get share of co
            $rev_share = 30;
            $gst_per = 0;
            $holding_percentage = 0;
            $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	report_audio_activation_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
        
            $getshareres = runQuery($getshare, $conn);
            $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
          
            if (!empty($getshareresdata)) {
            
                $rev_share = $getshareresdata['shares'];
                $gst_percentage = $getshareresdata['gst_percentage'];
                $holding_percentage = $getshareresdata['holding_percentage'];
            }

            if (!empty($search)) {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'    and ( ISRC like '" . $search . "%' || Track_name like '%".$search."%' || Label like '%".$search."%')   ";

                $union_final_query[] = " ( SELECT  ISRC ,Track_name,Label ,   sum(Payable_EUR) as USD , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage    FROM $v where  content_owner = '{$client}' and ( ISRC like '" . $search . "%' || Track_name like '%".$search."%' || Label like '%".$search."%')  group by ISRC )  ";
            } else {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                $union_final_query[] = " ( SELECT  ISRC ,Track_name,Label ,   sum(Payable_EUR) as USD ,{$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage   FROM $v where  content_owner = '{$client}'  group by ISRC ) ";
            }

        }

    }

    $check_query_total_new = implode(" union  ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

     

    $row = [];

    
    $check_query_list_new = implode(" union  ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by USD desc";
    //file_put_contents("tablequery.txt",$youtubereport);
    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

   // echo $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
   
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
       
        
        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;
        

        $finalpayable = (($row2['USD'] * $rev_share) / 100);
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

        $res['data'][] = [
            'Title' => isset($row2['Track_name']) ? $row2['Track_name'] : 'N/A',
            'ISRC' => isset($row2['ISRC']) ? $row2['ISRC'] : 'N/A',
            'Label' => isset($row2['Label']) ? $row2['Label'] : 'N/A',
            'USD' => $row2['USD'],
            'share' => $rev_share,
            'FinalPayable' => (($row2['USD'] * $rev_share) / 100),
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,
        ];
    }

    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}



function RevenueChartClientsSpotifyMusicReportv2(
    $table,
   
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $channelid = null
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";
    if ($client == null) {
        $client = $_SESSION['client'];
    }

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '$table'";

    @unlink("polochart_rev_lik.txt");
    file_put_contents("polochart_rev_lik.txt", $getClientInfoQuery);
    @chmod("polochart_rev_lik.txt", 0777);

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */
    $union_final_query_sql = "select  *   from 	 ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {

            // $explode_table = explode("youtube_video_claim_report_",$v);
            //     $explode_table_1 = explode("_",$explode_table[1]);
            //     $table_name = $explode_table_1[0];

            //     $result = array_map('strrev', explode('_', strrev($v)));
            //     $table_name = $result[2];
            // and channel_id like '{$channelid}'

            if (!empty($channelid)) {
                $union_final_query[] = " ( SELECT  Label , sum(Payable_EUR) as FinalPayable FROM $v where content_owner = '" . $client . "'   group by Label ORDER by Label  )  ";
            } else {
                $union_final_query[] = " ( SELECT  Label , sum(Payable_EUR) as FinalPayable FROM $v where content_owner = '" . $client . "'   group by Label ORDER by Label  )  ";
            }

        }

    }

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by Label asc";

    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    @unlink("polochart_rev.txt");
    file_put_contents("polochart_rev.txt", $youtubereport);
    @chmod("polochart_rev.txt", 0777);

   // echo $youtubereport;

    $getAllrevenueQueryResult = runQuery($youtubereport, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        if ($row['Label'] != "") {
            

            $res[] = array('c' => array(
                
                array('v' => $row['Label']),
            
                array('v' => $row['FinalPayable']),
              
                
               
            ));
        }

    }

    return setErrorStack($returnArr, -1, $res, null);
}


function ViewsChartClientsSpotifyMusicReportv2(
    $table,
    
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $channelid
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";
    if ($client == null) {
        $client = $_SESSION['client'];
    }

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $getClientInfoQuery = "SHOW TABLES LIKE '" . trim($table) . "'";
    @unlink("polochart.txt");
    file_put_contents("polochart.txt", $getClientInfoQuery);
    @chmod("polochart.txt", 0777);
    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */
    $union_final_query_sql = "select  *   from 	 ";

    $union_final_query = [];

    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {

            // $explode_table = explode("youtube_video_claim_report_",$v);
            //     $explode_table_1 = explode("_",$explode_table[1]);
            //     $table_name = $explode_table_1[0];

            $result = array_map('strrev', explode('_', strrev($v)));
            //     $table_name = $result[2];

            if (!empty($channelid)) {
                $union_final_query[] = " ( SELECT  Label , count(Label) as FinalPayable FROM $v where content_owner = '" . $client . "'    group by Label ORDER by Label )  ";
            } else {
                $union_final_query[] = " ( SELECT  Label , count(Label) as FinalPayable FROM $v where content_owner = '" . $client . "'    group by Label ORDER by Label )  ";
            }

        }

    }

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by Label asc";

    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    @unlink("polochart_view.txt");
    file_put_contents("polochart_view.txt", $youtubereport);
    @chmod("polochart_view.txt", 0777);
    //echo $youtubereport;
    
    $getAllrevenueQueryResult = runQuery($youtubereport, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        //$d= explode('-',$row['day']);

        if ($row['Label'] != "") {
            
            $res[] = array('c' => array(
                array('v' => $row['Label']),
                array('v' => $row['FinalPayable'])
              
            ));
        }

    }

    return setErrorStack($returnArr, -1, $res, null);
}

//end 
