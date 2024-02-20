<?php

function exportRevenueReportv2(
    $table,
    $params,
    $conn

) {
    $res = array();
    $returnArr = array();

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    //@unlink("/var/lib/mysql-files/forDJ/{$table}.csv");
    $file_path_root = '/var/lib/mysql-files/forDJ/';
 //   $file_path_root = 'D:/';
    $csv_file_name = "AssetCoMAPRevenue_" . $params['nd'] . "_" . $params['selectedDate'];
    $sql_sub = "";

    if ($params['onlyunassigned'] == "1") {
        $sql_sub = "content_owner is NULL";
        $csv_file_name = "pending" . $csv_file_name;
    } else {
        if (isset($params['contentOwner']) && $params['contentOwner'] != "") {
            $sql_sub = "content_owner ='{$params['contentOwner']}' ";
            $csv_file_name = $csv_file_name . "_" . $params['contentOwner'];
        } else {
            $sql_sub = "content_owner IS NOT NULL";
        }

    }

    if ($params['type_cate'] == "report_audio") {

        if ($params['nd'] == "applemusic") {
            $sqlTableQuery = "SELECT   'Storefront Name' , ' Apple Identifier' , ' Membership Type' , ' Quantity' , ' Net Royalty' , ' Net Royalty Total' , ' USD' , ' Partner Share' , ' ISRC' , ' Item Title' , ' Item Artist' , ' Item Type' , ' Media Type' , ' Vendor Identifier' , ' Offline Indicator' , 'Label','Grid'
            UNION ALL SELECT  StorefrontName,AppleIdentifier,MembershipType,Quantity,NetRoyalty,NetRoyaltyTotal,sum(USD) as USD,sum(PartnerShare) as PartnerShare ,ISRC,ItemTitle,ItemArtist,ItemType,MediaType,VendorIdentifier,OfflineIndicator,Label,Grid   FROM {$table}   where " . $sql_sub . "  group by {$table}.ISRC INTO OUTFILE '{$file_path_root}{$csv_file_name}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";

        }
        if ($params['nd'] == "itune") {
            $sqlTableQuery = "SELECT   'Start Date','End Date','UPC','ISRC/ISBN','Vendor Identifier','Quantity','Partner Share','Extended Partner Share','Partner Share Currency','USD','Label Share','Sales or Returns','Apple Identifier','Artist/Show/Developer/Author','Title','Label/Studio/Network/Developer/Publisher','Grid','Product Type Identifier','ISAN/Other Identifier','Country Of Sale','Pre-order Flag','Promo Code','Customer Price','Customer Currency'
            UNION ALL SELECT tartDate,EndDate,UPC,ISRC_ISBN,VendorIdentifier,Quantity,PartnerShare,ExtendedPartnerShare,PartnerShareCurrency,sum(USD) as USD,sum(LabelShare) as LabelShare,SalesorReturns,AppleIdentifier,Artist_Show_Developer_Author,Title,Label,Grid,ProductTypeIdentifier,ISAN_OtherIdentifier,CountryOfSale,Pre_orderFlag,PromoCode,CustomerPrice,CustomerCurrency   FROM {$table}   where " . $sql_sub . "  group by {$table}.ISRC_ISBN INTO OUTFILE '{$file_path_root}{$csv_file_name}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";
        }
        if ($params['nd'] == "gaana") {
            $sqlTableQuery = "SELECT   'Sub vendor Name','Free Playouts','Paid Playouts','Total Playouts','Free playout revenue','Paid playout revenue','Total Revenue','Final Payables'
            UNION ALL SELECT  Sub_vendor_Name  , sum(Free_Playouts) as Free_Playouts , sum(Paid_Playouts) as Paid_Playouts  , sum(Total_Playouts) as Total_Playouts , sum(free_playout_revenue) as free_playout_revenue , sum(paid_playout_revenue) as paid_playout_revenue ,  sum(Total_Revenue) as Total_Revenue   FROM {$table}   where " . $sql_sub . "  group by {$table}.Sub_vendor_Name INTO OUTFILE '{$file_path_root}{$csv_file_name}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";
        }

        if ($params['nd'] == "saavan") {
            $sqlTableQuery = "SELECT   'TrackName','ISRC','Language','Label','Ad-Supported Streams','Subscription Streams','Jio-Trial Streams','Total Streams','Ad Supported Revenue','Subscription Revenue','Total Revenue','Final Payables'
            UNION ALL SELECT TrackName, ISRC, Language, Label, sum(Ad_Supported_Streams) as Ad_Supported_Streams, sum(Ad_Supported_Revenue) as Ad_Supported_Revenue, sum(Subscription_Streams) as Subscription_Streams, sum(Subscription_Revenue) as Subscription_Revenue , sum(Jio_Trial_Streams) as Jio_Trial_Streams , sum(Total_Streams) as Total_Streams , sum(Total_Revenue) as Total_Revenue   FROM {$table}   where " . $sql_sub . "  group by {$table}.ISRC INTO OUTFILE '{$file_path_root}{$csv_file_name}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";
        }

        if ($params['nd'] == "spotify") {
            $sqlTableQuery = "SELECT   'Country','Product','URI','UPC','EAN','ISRC','Track_name','Artist_name','Composer_name','Album_name','Quantity','Label','Payable_USD'
           UNION ALL SELECT Country,Product,URI,UPC,EAN,ISRC,Track_name,Artist_name,Composer_name,Album_name,Quantity,Label,sum(Payable_USD) as Payable_USD
   FROM {$table}   where " . $sql_sub . "  group by {$table}.ISRC INTO OUTFILE '{$file_path_root}{$csv_file_name}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";
        }
    } elseif ($params['type_cate'] == "youtube_ecommerce_paid_features_report") {

        $sqlTableQuery = "SELECT   'partnerRevenue','assetID','channel_name','channelID',  'content_owner','Label'
        UNION ALL SELECT SUM(earnings) as partnerRevenue,assetID,channel_name,channel_id, content_owner,Label  FROM {$table}   where " . $sql_sub . "  group by {$table}.channel_id INTO OUTFILE '{$file_path_root}{$csv_file_name}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";

    } elseif ($params['type_cate'] == "youtube_red_music_video_finance_report") {

        $sqlTableQuery = "SELECT   'partnerRevenue','assetID','channelID','cutsomID' ,'content_owner','Label'
        UNION ALL SELECT sum(partnerRevenue),assetID,channelID,cutsomID,content_owner,Label  FROM {$table}   where " . $sql_sub . "  group by {$table}.assetID INTO OUTFILE '{$file_path_root}{$csv_file_name}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";

    } elseif ($params['type_cate'] == "youtube_labelengine_report") {

        $sqlTableQuery = "SELECT   'contentType','assetID','partnerRevenue','content_owner'
        UNION ALL SELECT contentType,assetID,sum(partnerRevenue),content_owner  FROM {$table}   where " . $sql_sub . "  group by {$table}.assetID INTO OUTFILE '{$file_path_root}{$csv_file_name}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";

    } else {

        if ($params['nd'] == "nd1") {
            $sqlTableQuery = "SELECT   'partnerRevenue','assetID','channelID','assetChannelID','cutsomID' ,'content_owner','Label'
            UNION ALL SELECT sum(partnerRevenue),assetID,channelID,assetChannelID,cutsomID,content_owner,Label  FROM {$table}   where " . $sql_sub . "  group by {$table}.assetID INTO OUTFILE '{$file_path_root}{$csv_file_name}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";
        }
        if ($params['nd'] == "nd2") {
            $sqlTableQuery = "SELECT   'partnerRevenue','assetID','channelID','assetChannelID','cutsomID' ,'content_owner','Label'
            UNION ALL SELECT sum(partnerRevenue),assetID,channelID,assetChannelID,cutsomID,content_owner,Label  FROM {$table}   where " . $sql_sub . "  group by {$table}.assetID INTO OUTFILE '{$file_path_root}{$csv_file_name}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";
        }
        if ($params['nd'] == "ndkids") {
            $sqlTableQuery = "SELECT   'partnerRevenue','assetID','channelID','assetChannelID','cutsomID' ,'content_owner','Label'
            UNION ALL SELECT sum(partnerRevenue),assetID,channelID,assetChannelID,cutsomID,content_owner,Label  FROM {$table}   where " . $sql_sub . "  group by {$table}.assetID INTO OUTFILE '{$file_path_root}{$csv_file_name}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";
        }
        if ($params['nd'] == "redmusic") {
            $sqlTableQuery = "SELECT   'partnerRevenue','assetID','channelID','assetChannelID','cutsomID' ,'content_owner','Label'
            UNION ALL SELECT sum(partnerRevenue),assetID,channelID,assetChannelID,cutsomID,content_owner,Label  FROM {$table}   where " . $sql_sub . "  group by {$table}.assetID INTO OUTFILE '{$file_path_root}{$csv_file_name}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";
        }

    }

    $csv_file_name = $csv_file_name . "_" . time();

    @unlink("polo_export_query.txt");
    file_put_contents("polo_export_query.txt", $sqlTableQuery);
    @chmod("polo_export_query.txt", 0777);
    //   $sqlTableQuery = "select * from activity_reports";
    $sqlTableQueryResult = runQuery($sqlTableQuery, $conn);

    if (noError($sqlTableQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $sqlTableQueryResult["errMsg"], null);
    }

}

function assignupdateContentOwnerv2($table, $params, $conn)
{
    /*
    $params['contentOwner'] = $contentOwner;
    $params['cmstype'] = $nd;
    $params['selectedDate'] = $selectedDate;
    $params['asset_id'] = $asset_id;
    $params['channel_id'] = $channel_id;
    $params['content_type'] = $content_type;
    $params['type_cate'] = $type_cate;
    $params['year'] = $year;
    $params['month'] = $month;
     */

    $res = array();
    $returnArr = array();
    $sql_sub = "";
    if ($params['type_cate'] == "report_audio") {
        if ($params['nd'] == "applemusic") {
            $sql_sub = $sql_sub . " and Label = '{$params['asset_id']}'";
        }
        if ($params['nd'] == "itune") {
            $sql_sub = $sql_sub . " and Label = '{$params['asset_id']}'";
        }
        if ($params['nd'] == "gaana") {
            $sql_sub = $sql_sub . " and Label = '{$params['asset_id']}'";
        }
        if ($params['nd'] == "saavan") {
            $sql_sub = $sql_sub . " and Label = '{$params['asset_id']}'";
        }

        if ($params['nd'] == "spotify") {
            $sql_sub = $sql_sub . " and Label = '{$params['asset_id']}'";
        }

    } else {
        if ($params['channel_id'] != "") {
            $sql_sub = " and channelID='{$params['channel_id']}'";
        }
        if ($params['asset_id'] != "") {
            $sql_sub = $sql_sub . " and assetID='{$params['asset_id']}'";
        }

        if ($params['content_type'] != "") {
            if ($params['content_type'] == "Partner-provided") {
                $sql_sub = $sql_sub . " and contentType ='{$params['content_type']}'";
            } else {
                $sql_sub = $sql_sub . " and contentType != '{$params['content_type']}'";
            }

        }
    }

    if ($params['onlyunassigned'] == 1) {
        $sql_sub = $sql_sub . " and content_owner is Null ";
    }

    if ($params['contentOwner'] != "") {
        $updateQuery = "UPDATE {$table} SET content_owner = '{$params['contentOwner']}' WHERE 1=1    " . $sql_sub;

        @unlink("polo_assign_update.txt");
        file_put_contents("polo_assign_update.txt", $updateQuery);
        @chmod("polo_assign_update.txt", 0777);

        //die();
        $updateQueryResult = runQuery($updateQuery, $conn);
        if (noError($updateQueryResult)) {
            return setErrorStack($returnArr, -1, $res, null);
        } else {
            return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
        }
    }

}

function ExportRevenueVideoCountv3($tablename,
    $params,
    $conn) {
    $gettotalcounts = 0;
    $res = array();
    $returnArr = array();

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    //get share of co

    $sql_sub = "";
    if ($params['contentOwner'] != "") {

        $sql_sub = " where content_owner='{$params['contentOwner']}'";
    }

 


    if ($params['type_cate'] == "youtube_video_claim_report_nd") {
        $gettotalcountquery = "select count(DISTINCT videoID) as totalcount from {$params['table_name']} " . $sql_sub;
    }

    if ($params['type_cate'] == "youtuberedmusic_video_report") {
        $gettotalcountquery = "select count(DISTINCT videoID) as totalcount from {$params['table_name']} " . $sql_sub;
    }

    if ($params['type_cate'] == "youtube_red_music_video_finance_report") {
        $gettotalcountquery = "select count(DISTINCT videoID) as totalcount from {$params['table_name']} " . $sql_sub;
    }

    if ($params['type_cate'] == "youtube_ecommerce_paid_features_report") {
        $gettotalcountquery = "select count(DISTINCT video_id) as totalcount from {$params['table_name']} " . $sql_sub;
    }

    if ($params['type_cate'] == "youtube_labelengine_report") {
        $gettotalcountquery = "select count(DISTINCT assetID) as totalcount from {$params['table_name']} " . $sql_sub;
    }

   



    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}

function ExportRevenueVideoDetailv3($tablename,
    $params,
    $conn,
    $offset = null,
    $resultsPerPage = 10
) {

    $res = array();
    $returnArr = array();

    $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    $US_HOLDING_PERCENTAGE = US_HOLDING_PERCENTAGE;

    $row = [];

    $sql_sub = "";
    if ($params['contentOwner'] != "") {

        $sql_sub = "  where  maintbl.content_owner='{$params['contentOwner']}'";
    }
    if ($params['cmstype'] == "nd1") {
        $CMS_TYPE = "ND1";
    }
    if ($params['cmstype'] == "nd2") {
        $CMS_TYPE = "ND2";
    }
    if ($params['cmstype'] == "ndkids") {
        $CMS_TYPE = "ND Kids";
    }
    if ($params['cmstype'] == "redmusic") {
        $CMS_TYPE = "ND Music";
    }

  

    if ($params['type_cate'] == "youtube_video_claim_report_nd") {
       
        $youtubereport = "SELECT maintbl.videoID,maintbl.assetID ,  maintbl.contentType,maintbl.content_owner,maintbl.assetType, Coalesce(sum(partnerRevenue),0) as partnerRevenue , Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT ,
        	Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END) * 30 / 100),0) as WITHHOLDING  , ( SELECT cm.Label FROM `channel_co_maping` cm where (maintbl.content_owner = cm.partner_provided or maintbl.content_owner = cm.ugc) and cm.CMS='{$CMS_TYPE}' limit 1 ) as Label
         
        from {$params['table_name']} maintbl  
        

        " . $sql_sub . " group by maintbl.videoID ";

        $sql_co_mapping = "select client_username ,  CASE WHEN JSON_VALID(client_type_details) THEN JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) ELSE null END as rev_share  from crep_cms_clients ";

    }

    if ($params['type_cate'] == "youtuberedmusic_video_report") {
        $youtubereport = "SELECT maintbl.videoID,maintbl.assetID ,  maintbl.contentType,maintbl.content_owner,maintbl.assetType, Coalesce(sum(partnerRevenue),0) as partnerRevenue , Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT ,
        	Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END) * 30 / 100),0) as WITHHOLDING    , ( SELECT cm.Label FROM `channel_co_maping` cm where (maintbl.content_owner = cm.partner_provided or maintbl.content_owner = cm.ugc)   and cm.CMS='{$CMS_TYPE}' limit 1 ) as Label
        
        from  {$params['table_name']} maintbl  
         

        " . $sql_sub . " group by maintbl.videoID ";

        $sql_co_mapping = "select client_username ,  CASE WHEN JSON_VALID(client_type_details) THEN JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutubeAudio')) ELSE null END as rev_share  from crep_cms_clients ";
    }

    if ($params['type_cate'] == "youtube_red_music_video_finance_report") {
        $youtubereport = "SELECT maintbl.videoID,maintbl.assetID ,  maintbl.contentType,maintbl.content_owner, Coalesce(sum(partnerRevenue),0) as partnerRevenue , Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT ,
        	Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END) * 30 / 100),0) as WITHHOLDING   , ( SELECT cm.Label FROM `channel_co_maping` cm where (maintbl.content_owner = cm.partner_provided or maintbl.content_owner = cm.ugc)   and cm.CMS='{$CMS_TYPE}' limit 1 ) as Label
       
        from  {$params['table_name']} maintbl  
       

        " . $sql_sub . " group by maintbl.videoID ";

        $sql_co_mapping = "select client_username ,  CASE WHEN JSON_VALID(client_type_details) THEN JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) ELSE null END as rev_share  from crep_cms_clients ";

    }
    if ($params['type_cate'] == "youtube_ecommerce_paid_features_report") {
        $youtubereport = "SELECT maintbl.video_id as videoID ,maintbl.assetID ,maintbl.content_owner, Coalesce(sum(earnings),0) as partnerRevenue , Coalesce(SUM(CASE WHEN country='US' THEN earnings END),0) as USPAYOUT ,
        	Coalesce((SUM(CASE WHEN country='US' THEN earnings END) * 30 / 100),0) as WITHHOLDING   , ( SELECT cm.Label FROM `channel_co_maping` cm where (maintbl.content_owner = cm.partner_provided or maintbl.content_owner = cm.ugc)   and cm.CMS='{$CMS_TYPE}' limit 1 ) as Label
       
        from  {$params['table_name']} maintbl  
       

        " . $sql_sub . " group by maintbl.video_id ";

        $sql_co_mapping = "select client_username ,  CASE WHEN JSON_VALID(client_type_details) THEN JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) ELSE null END as rev_share  from crep_cms_clients ";

    }

    if ($params['type_cate'] == "youtube_labelengine_report") {
        $youtubereport = "SELECT  maintbl.assetID ,maintbl.content_owner, Coalesce(sum(partnerRevenue),0) as partnerRevenue , 0 as USPAYOUT ,
        	0 as WITHHOLDING   , ( SELECT cm.Label FROM `channel_co_maping` cm where (maintbl.content_owner = cm.partner_provided or maintbl.content_owner = cm.ugc)   and cm.CMS='{$CMS_TYPE}' limit 1 ) as Label
       
        from  {$params['table_name']} maintbl  
       
        " . $sql_sub . " group by maintbl.assetID ";

        $sql_co_mapping = "select client_username ,  CASE WHEN JSON_VALID(client_type_details) THEN JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutubeAudio')) ELSE null END as rev_share  from crep_cms_clients ";

    }
//revenueShareYoutube
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    //$youtubereport .=  " LIMIT 0, 100";

    @unlink("polo_export_query.txt");
    file_put_contents("polo_export_query.txt", $youtubereport);
    @chmod("polo_export_query.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];

    $rev_share = 0;

    // @unlink("polo_export_query_share.txt");
    //file_put_contents("polo_export_query_share.txt", $sql_co_mapping);
    //@chmod("polo_export_query_share.txt", 0777);

    $channel_co_maping_result = runQuery($sql_co_mapping, $conn);
    while ($row3 = mysqli_fetch_assoc($channel_co_maping_result["dbResource"])) {
        $cdata[strtolower($row3['client_username'])] = (int) $row3['rev_share'];

    }
    @unlink("polo_export_query_share.txt");
    file_put_contents("polo_export_query_share.txt", json_encode($channel_co_maping_result));
    @chmod("polo_export_query_share.txt", 0777);

    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {

        $rev_share = isset($cdata[strtolower($row2['content_owner'])]) ? $cdata[strtolower($row2['content_owner'])] : 0;

        $res['data'][] = [
            'content_owner' => isset($row2['content_owner']) ? $row2['content_owner'] : 'N/A',
            'videoID' => isset($row2['videoID']) ? $row2['videoID'] : 'N/A',
            'assetID' => isset($row2['assetID']) ? $row2['assetID'] : 'N/A',
            'Label' => isset($row2['Label']) ? $row2['Label'] : 'N/A',
            'assetType' => isset($row2['assetType']) ? $row2['assetType'] : 'N/A',
            //  'content_type' => isset($row2['contentType']) ? $row2['contentType'] : 'N/A',
            'rev_share' => $rev_share,
            'youtube_payout' => $row2['partnerRevenue'] + 0,
            'USPAYOUT' => $row2['USPAYOUT'] + 0,
            'US_HOLDING_PERCENTAGE' => $US_HOLDING_PERCENTAGE + 0,
            'WITHHOLDING' => $row2['WITHHOLDING'] + 0,
            'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0,

        ];
    }

    // $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);

}
//end
