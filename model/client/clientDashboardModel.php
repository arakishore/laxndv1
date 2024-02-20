<?php

function getClientsYoutubeFinanceReport(
    $table,
    $table2,
    $conn,
    $offset = null,
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

    $gettotalcountquery = "SELECT count(id) as totalcount FROM $table where content_owner = '" . $client . "'";
    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

    $getClientInfoQuery = "SELECT videoID,channelID,partnerRevenue FROM $table where content_owner = '" . $client . "' order by partnerRevenue desc";

    if ($offset !== null) {
        $getClientInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

       
   
    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube , gst_per FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }
    $chennelids = [];
    $videoids = [];
    $rawdata = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        $videoids[] = "'" . $row['videoID'] . "'";
        $chennelids[] = "'" . $row['channelID'] . "'";
        $rowdata[] = $row;
    }

    $videoids = implode(',', $videoids);
    $chennelids = implode(',', $chennelids);
    $row = [];

    $youtubereport = "SELECT  video_id,channel_id,video_title,channel_display_name  FROM $table2 where video_id in($videoids) and channel_id in($chennelids) ";

    if (!empty($search)) {
        $youtubereport .= ' and video_title like "' . $search . '%"  ';
    }
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
        $cdata[$row2['video_id']] = $row2['video_title'];
        $cdata[$row2['channel_id']] = $row2['channel_display_name'];

    }

    foreach ($rowdata as $r) {

        $res['data'][] = [
            'video_title' => isset($cdata[$r['videoID']]) ? $cdata[$r['videoID']] : 'N/A',
            'channel_name' => isset($cdata[$r['channelID']]) ? $cdata[$r['channelID']] : 'N/A',
            'youtube_payout' => $r['partnerRevenue'],
            'finalpayable' => (($r['partnerRevenue'] * $rev_share) / 100),
        ];

    }
    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}

function getClientsYoutubeRedFinanceReport(
    $table,
    $table2,
    $conn,
    $offset = null,
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

    $gettotalcountquery = "SELECT count(id) as totalcount FROM $table where content_owner = '" . $client . "'";
    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

    $getClientInfoQuery = "SELECT videoID,channelID,partnerRevenue FROM $table where content_owner = '" . $client . "' order by partnerRevenue desc";

    if ($offset !== null) {
        $getClientInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube , gst_per  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }
    $chennelids = [];
    $videoids = [];
    $rawdata = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        $videoids[] = "'" . $row['videoID'] . "'";
        $chennelids[] = "'" . 'UC' . $row['channelID'] . "'";
        $rowdata[] = $row;
    }

    $videoids = implode(',', $videoids);
    $chennelids = implode(',', $chennelids);
    $row = [];

    $youtubereport = "SELECT  video_id,channel_id,video_title,channel_display_name  FROM $table2 where video_id in($videoids) and channel_id in($chennelids) ";

    if (!empty($search)) {
        $youtubereport .= ' and video_title like "' . $search . '%"  ';
    }
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
        $cdata[$row2['video_id']] = $row2['video_title'];
        $cdata[$row2['channel_id']] = $row2['channel_display_name'];

    }

    foreach ($rowdata as $r) {

        $res['data'][] = [
            'video_title' => isset($cdata[$r['videoID']]) ? $cdata[$r['videoID']] : 'N/A',
            'channel_name' => isset($cdata[$r['channelID']]) ? $cdata[$r['channelID']] : 'N/A',
            'youtube_payout' => $r['partnerRevenue'],
            'finalpayable' => (($r['partnerRevenue'] * $rev_share) / 100),
        ];

    }
    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}
function ExportClientsYoutubeFinanceReport(
    $table,
    $table2,
    $conn,
    $offset = null,
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

    $gettotalcountquery = "SELECT count(id) as totalcount FROM $table where content_owner = '" . $client . "'";
    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

    $getClientInfoQuery = "SELECT videoID,channelID,partnerRevenue,assetID FROM $table where content_owner = '" . $client . "'  order by partnerRevenue desc";

    if ($offset !== null) {
        $getClientInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube , gst_per  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per =0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }
    $chennelids = [];
    $videoids = [];
    $rawdata = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        $videoids[] = "'" . $row['videoID'] . "'";
        $chennelids[] = "'" . $row['channelID'] . "'";
        $rowdata[] = $row;
    }

    $videoids = implode(',', $videoids);
    $chennelids = implode(',', $chennelids);
    $row = [];

    $youtubereport = "SELECT  video_id,channel_id,video_title,channel_display_name,asset_id  FROM $table2 where video_id in($videoids) and channel_id in($chennelids)  ";
    if (!empty($search)) {
        $youtubereport .= ' and video_title like "' . $search . '%"  ';
    }
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
        $cdata[$row2['video_id']] = $row2['video_title'];
        $cdata[$row2['channel_id']] = $row2['channel_display_name'];
        $cdata[$row2['asset_id']] = $row2['asset_id'];
    }

    $rowNum = 2;
    foreach ($rowdata as $r) {

        $res['data'][] = [
            'video_title' => isset($cdata[$r['videoID']]) ? $cdata[$r['videoID']] : 'N/A',
            'channel_name' => isset($cdata[$r['channelID']]) ? $cdata[$r['channelID']] : 'N/A',
            'assetId' => isset($cdata[$r['assetID']]) ? $cdata[$r['assetID']] : 'N/A',
            'youtube_payout' => $r['partnerRevenue'],
            'rev_share' => $rev_share,
            'finalpayable' => ($r['partnerRevenue'] * $rev_share / 100),
            
        ];
        $rowNum++;
    }
    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}
function ExportClientsYoutubeRedFinanceReport(
    $table,
    $table2,
    $conn,
    $offset = null,
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

    $gettotalcountquery = "SELECT count(id) as totalcount FROM $table where content_owner = '" . $client . "'";
    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

    $getClientInfoQuery = "SELECT videoID,channelID,partnerRevenue,assetID FROM $table where content_owner = '" . $client . "'  order by partnerRevenue desc";

    if ($offset !== null) {
        $getClientInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube ,gst_per FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }
    $chennelids = [];
    $videoids = [];
    $rawdata = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        $videoids[] = "'" . $row['videoID'] . "'";
        $chennelids[] = "'" . 'UC' . $row['channelID'] . "'";
        $rowdata[] = $row;
    }

    $videoids = implode(',', $videoids);
    $chennelids = implode(',', $chennelids);
    $row = [];

    $youtubereport = "SELECT  video_id,channel_id,video_title,channel_display_name,asset_id  FROM $table2 where video_id in($videoids) and channel_id in($chennelids)  ";
    if (!empty($search)) {
        $youtubereport .= ' and video_title like "' . $search . '%"  ';
    }
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
        $cdata[$row2['video_id']] = $row2['video_title'];
        $cdata[$row2['channel_id']] = $row2['channel_display_name'];
        $cdata[$row2['asset_id']] = $row2['asset_id'];
    }

    $rowNum = 2;
    foreach ($rowdata as $r) {

        $res['data'][] = [
            'video_title' => isset($cdata[$r['videoID']]) ? $cdata[$r['videoID']] : 'N/A',
            'channel_name' => isset($cdata[$r['channelID']]) ? $cdata[$r['channelID']] : 'N/A',
            'assetId' => isset($cdata[$r['assetID']]) ? $cdata[$r['assetID']] : 'N/A',
            'youtube_payout' => $r['partnerRevenue'],
            'rev_share' => $rev_share,
            'finalpayable' => ($r['partnerRevenue'] * $rev_share / 100),
            // 'finalpayable'=>"=D{$rowNum}*E{$rowNum}/100",
        ];
        $rowNum++;
    }
    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}
function RevenueChartClientsYoutubeFinanceReport(
    $table,
    $table2,
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

    $getAllrevenueQuery = "SELECT DATE(adjustmentType) as adjustmentType,SUM(partnerRevenue) as partnerRevenue,SUM(if(contentType='UGC',partnerRevenue,0)) as UGC,SUM(if(contentType='Partner-provided',partnerRevenue,0)) as Partnerprovided FROM $table where content_owner = '" . $client . "' ";
    if ($channelid !== null) {
        $getAllrevenueQuery .= " AND channelID = '{$channelid}'";
    }

    $getAllrevenueQuery .= "  group by adjustmentType ORDER by adjustmentType";

    if ($offset !== null) {
        $getAllrevenueQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    $getAllrevenueQueryResult = runQuery($getAllrevenueQuery, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        $d = explode('-', $row['adjustmentType']);
        $year = (int) $d[0];
        $month = (int) $d[1] - 1;
        $day = (int) $d[2];
        $res[] = array('c' => array(
            array('v' => "Date($year, $month, $day)"),
            array('v' => $row['partnerRevenue']),
            array('v' => $row['Partnerprovided']),
            array('v' => $row['UGC']),
        ));
    }

    return setErrorStack($returnArr, -1, $res, null);
}

function RevenueChartClientsYoutubeRedFinanceReport(
    $table,
    $table2,
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

    $getAllrevenueQuery = "SELECT DATE(day) as day,SUM(partnerRevenue) as partnerRevenue,SUM(if(contentType='UGC',partnerRevenue,0)) as UGC,SUM(if(contentType='Partner-provided',partnerRevenue,0)) as Partnerprovided FROM $table where content_owner = '" . $client . "' ";
    if ($channelid !== null) {
        $getAllrevenueQuery .= " AND channelID = '{$channelid}'";
    }

    $getAllrevenueQuery .= "  group by day ORDER by day";

    if ($offset !== null) {
        $getAllrevenueQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    $getAllrevenueQueryResult = runQuery($getAllrevenueQuery, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        $d = explode('-', $row['day']);
        $year = (int) $d[0];
        $month = (int) $d[1] - 1;
        $day = (int) $d[2];
        $res[] = array('c' => array(
            array('v' => "Date($year, $month, $day)"),
            array('v' => $row['partnerRevenue']),
            array('v' => $row['Partnerprovided']),
            array('v' => $row['UGC']),
        ));
    }

    return setErrorStack($returnArr, -1, $res, null);
}

function ViewsChartClientsYoutubeFinanceReport(
    $table,
    $table2,
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

    $getAllrevenueQuery = "SELECT DATE(adjustmentType) as adjustmentType,SUM(ownedViews) as views,SUM(if(contentType='UGC',ownedViews,0)) as UGC,SUM(if(contentType='Partner-provided',ownedViews,0)) as Partnerprovided FROM $table where content_owner = '" . $client . "' ";
    if ($channelid !== null) {
        $getAllrevenueQuery .= " AND channelID = '{$channelid}'";
    }

    $getAllrevenueQuery .= "  group by adjustmentType ORDER by adjustmentType";

    if ($offset !== null) {
        $getAllrevenueQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    $getAllrevenueQueryResult = runQuery($getAllrevenueQuery, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        $d = explode('-', $row['adjustmentType']);
        $year = (int) $d[0];
        $month = (int) $d[1] - 1;
        $day = (int) $d[2];
        $res[] = array('c' => array(
            array('v' => "Date($year, $month, $day)"),
            array('v' => $row['views']),
            array('v' => $row['Partnerprovided']),
            array('v' => $row['UGC']),
        ));
    }

    return setErrorStack($returnArr, -1, $res, null);
}

function ViewsChartClientsYoutubeRedFinanceReport(
    $table,
    $table2,
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

    $getAllrevenueQuery = "SELECT DATE(day) as day,SUM(ownedViews) as views,SUM(if(contentType='UGC',ownedViews,0)) as UGC,SUM(if(contentType='Partner-provided',ownedViews,0)) as Partnerprovided FROM $table where content_owner = '" . $client . "' ";
    if ($channelid !== null) {
        $getAllrevenueQuery .= " AND channelID = '{$channelid}'";
    }

    $getAllrevenueQuery .= "  group by day ORDER by day";

    if ($offset !== null) {
        $getAllrevenueQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    $getAllrevenueQueryResult = runQuery($getAllrevenueQuery, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        $d = explode('-', $row['day']);
        $year = (int) $d[0];
        $month = (int) $d[1] - 1;
        $day = (int) $d[2];
        $res[] = array('c' => array(
            array('v' => "Date($year, $month, $day)"),
            array('v' => $row['views']),
            array('v' => $row['Partnerprovided']),
            array('v' => $row['UGC']),
        ));
    }

    return setErrorStack($returnArr, -1, $res, null);
}
function getClientsYoutubeFinanceReportCount(
    $table,
    $table2,
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
    if ($client == null) {
        $client = $_SESSION['client'];
    }
    $getClientInfoQuery = "SELECT * FROM $table where content_owner = '" . $client . "' order by partnerRevenue desc";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube ,gst_per FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }
    $chennelids = [];
    $videoids = [];
    $rawdata = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        $videoids[] = "'" . $row['videoID'] . "'";
        $chennelids[] = "'" . $row['channelID'] . "'";
        $rowdata[] = $row;
    }
    $videoids = implode(',', $videoids);
    $chennelids = implode(',', $chennelids);
    $row = [];

    $youtubereport = "SELECT  count(*) as total  FROM $table2 where video_id in($videoids) and channel_id in($chennelids) ";
    if (!empty($search)) {
        $youtubereport .= ' and video_title like "' . $search . '%"  ';
    }
    $youtubereportresult = runQuery($youtubereport, $conn);

    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        $res[0] = $row2;

    }

    return setErrorStack($returnArr, -1, $res, null);
}
function getChannelsofthereport(
    $table,
    $conn,
    $search = '',
    $client = null
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";

    $getClientInfoQuery = "SELECT DISTINCT channelID FROM $table where content_owner = '" . $client . "' ";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {

        $res[] = $row;
    }

    return setErrorStack($returnArr, -1, $res, null);
}
// function getClientsYoutubeFinanceReportSum(
//     $table,
//     $table2,
//     $conn,
//     $search = ''
// )
// {
//     $res = array();
//     $returnArr = array();
//     $whereClause = "";

//     $getClientInfoQueryResult = runQuery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))", $conn);
//     if (!noError($getClientInfoQueryResult)) {
//         return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
//     }

//     $getClientInfoQuery = "SELECT sum(partnerRevenue) as payout FROM $table where content_owner = '".$_SESSION['client']."' order by partnerRevenue desc";

//     $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
//     if (!noError($getClientInfoQueryResult)) {
//         return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
//     }

//      //get share of co
//     $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube  FROM crep_cms_clients where client_username = '".$_SESSION['client']."'";
//     $getshareres = runQuery($getshare, $conn);
//     $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
//     $rev_share = 0;
//     if(!empty($getshareresdata)){
//         $rev_share = (int) $getshareresdata['revenueShareYoutube'];
//     }
//     while ($row2 = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
//             $res['youtube_payout_sum'] = $row2['payout'];
//             $res['final_sum'] = $res['youtube_payout_sum']*$rev_share/100;
//     }
//     return setErrorStack($returnArr, -1, $res, null);
// }

function getClientsYoutubeClaimReportv2_singletable(
    $table,
    $table2,
    $conn,
    $offset = null,
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

    $gettotalcountquery = "SELECT count(id) as totalcount FROM $table where content_owner = '" . $client . "'";
    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube ,gst_per FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }

    $row = [];

    $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '" . $client . "' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        $youtubereport .= ' and video_title like "' . $search . '%"  ';
    }
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;

        $res['data'][] = [
            'video_title' => isset($row2['video_title']) ? $row2['video_title'] : 'N/A',

            'youtube_payout' => $row2['partnerRevenue'],
            'finalpayable' => (($row2['partnerRevenue'] * $rev_share) / 100),
        ];
    }

    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}
function getClientsYoutubeClaimReportv2(
    $type,

    $conn,
    $offset = null,
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

    $check_query_total1 = "select  sum(totalcount)   as totalcount  from 	";
    $union_final_query_sql = "select  *   from 	 ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;
            // $explode_table = explode("youtube_video_claim_report_",$v);
            //     $explode_table_1 = explode("_",$explode_table[1]);
            //     $table_name = $explode_table_1[0];

            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];

            if (!empty($search)) {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}' and  channelID like '" . $search . "%'  ";
                $union_final_query[] = " ( SELECT videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, sum(partnerRevenue) as partnerRevenue,'{$table_name}'  as tablename FROM $v where  content_owner = '{$client}' and  channelID like '" . $search . "%'   group by videoID )  ";
            } else {

                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                $union_final_query[] = " ( SELECT    videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, sum(partnerRevenue) as partnerRevenue ,'{$table_name}'  as tablename FROM $v where  content_owner = '{$client}'   group by videoID )  ";
            }
        }

    }

    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube , gst_per  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }


    

    $row = [];

    $table2 = $all_table[0];

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc, tablename desc";
    //file_put_contents("tablequery.txt",$youtubereport);
    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    // echo $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
        $finalpayable = (($row2['partnerRevenue'] * $rev_share) / 100);
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);
        $res['data'][] = [
            'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
            'video_id' => isset($row2['videoID']) ? $row2['videoID'] : 'N/A',
            'video_title' => isset($row2['video_title']) ? $row2['video_title'] : 'N/A',
            'youtube_payout' => $row2['partnerRevenue'],
            'share' => $rev_share,
            'finalpayable' => $finalpayable,
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,
            'tablename' => isset($row2['tablename']) ? $row2['tablename'] : 'N/A',
        ];
    }

    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}
function getClientsYoutubeClaimReportv3(
    $type,

    $conn,
    $offset = null,
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

    $check_query_total1 = "select  sum(totalcount)   as totalcount  from 	";
    $union_final_query_sql = "select  *   from 	 ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    $nd_type= "";
    $year_mnoth = "";
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;
            // $explode_table = explode("youtube_video_claim_report_",$v);
            //     $explode_table_1 = explode("_",$explode_table[1]);
            //     $table_name = $explode_table_1[0];

            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];
            $year_mnoth = $result[1]."_".$result[0];
            $nd_type = $result[2];

              //get share of co
              $rev_share = 30;
              $gst_percentage = 0;
              $holding_percentage = 0;
              $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	youtube_video_claim_activation_report_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
          
              $getshareres = runQuery($getshare, $conn);
              if (!noError($getshareres)) {
                //  @unlink("polo_export_getshare.txt");
                $ddd = "Error_in_query: ".PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                @chmod("polo_export_getshare.txt", 0777);

                // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
            }   
            else {
              $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
      
                  if (!empty($getshareresdata)) {
                  
                      $rev_share = $getshareresdata['shares'];
                      $gst_percentage = $getshareresdata['gst_percentage'];
                     // $holding_percentage = $getshareresdata['holding_percentage'];
                  }
          }

           


            if (!empty($search)) {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}' and  channelID like '" . $search . "%'  ";
                $union_final_query[] = " ( SELECT videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, sum(partnerRevenue) as partnerRevenue,'{$table_name}'  as tablename ,  {$rev_share} as rev_share, {$gst_percentage} as gst_percentage,   holding_percentage FROM $v where  content_owner = '{$client}' and  channelID like '" . $search . "%'   group by videoID )  ";
            } else {

                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                $union_final_query[] = " ( SELECT    videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, sum(partnerRevenue) as partnerRevenue ,'{$table_name}'  as tablename , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage,  holding_percentage FROM $v where  content_owner = '{$client}'   group by videoID )  ";

                
            }
        }

    }

    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";



    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

   /*  //get share of co
    $rev_share = 0;
    $gst_per = 0;
    $holding_percentage =0;
    $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	youtube_video_claim_activation_report_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
  
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
  
    if (!empty($getshareresdata)) {
       
        $rev_share = $getshareresdata['shares'];
        $gst_per = $getshareresdata['gst_percentage'];
        $holding_percentage = $getshareresdata['holding_percentage'];
    } */


    

    $row = [];

    $table2 = $all_table[0];

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc, tablename desc";
    //file_put_contents("tablequery.txt",$youtubereport);
    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    @unlink("polodetail_query.txt");
    file_put_contents("polodetail_query.txt", $youtubereport);
    @chmod("polodetail_query.txt", 0777);

    // echo $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;
        
        $finalpayable = (($row2['partnerRevenue'] * $rev_share) / 100);
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);
        $res['data'][] = [
            'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
            'video_id' => isset($row2['videoID']) ? $row2['videoID'] : 'N/A',
            'video_title' => isset($row2['video_title']) ? $row2['video_title'] : 'N/A',
            'youtube_payout' => $row2['partnerRevenue'],
            'holding_percentage' => $row2['holding_percentage'],
            'share' => $rev_share,
            'finalpayable' => $finalpayable,
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,
            'tablename' => isset($row2['tablename']) ? $row2['tablename'] : 'N/A',
        ];
    }

    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}
function getChannelsofthereportAmazon(
    $table,
    $conn,
    $search = '',
    $client = null
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";

    $getClientInfoQuery = "SELECT DISTINCT seasonID,titleName FROM $table where content_owner = '" . $client . "' ";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {

        $res[] = $row;
    }

    return setErrorStack($returnArr, -1, $res, null);
}
function getClientsAmazonReportCount(
    $table,
    $table2,
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
    if ($client == null) {
        $client = $_SESSION['client'];
    }
    $getClientInfoQuery = "SELECT * FROM $table where content_owner = '" . $client . "' order by amountReceived desc";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueAmazon')) as  revenueAmazon ,gst_per FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueAmazon'];
        $gst_per = $getshareresdata['gst_per'];
    }
    $chennelids = [];
    $videoids = [];
    $rawdata = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        $videoids[] = "'" . $row['seasonID'] . "'";

        $rowdata[] = $row;
    }
    $videoids = implode(',', $videoids);
    $chennelids = implode(',', $chennelids);
    $row = [];

    $youtubereport = "SELECT  count(*) as total  FROM $table2 where seasonID in($videoids)   ";
    if (!empty($search)) {
        $youtubereport .= ' and titleName like "' . $search . '%"  ';
    }
    $youtubereportresult = runQuery($youtubereport, $conn);

    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        $res[0] = $row2;

    }

    return setErrorStack($returnArr, -1, $res, null);
}

function getClientsAmazonReport(
    $table,
    $table2,
    $conn,
    $offset = null,
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

    $gettotalcountquery = "SELECT count(id) as totalcount FROM $table where content_owner = '" . $client . "'";
    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

    $getClientInfoQuery = "SELECT titleName,seasonID,royaltyAmount, royaltyCurrency, exchangeRate, revINRwithoutHolding, withHolding, amountReceived,clientShare,payable  FROM $table where content_owner = '" . $client . "'";
    if (!empty($search)) {
        $getClientInfoQuery .= ' and titleName like "' . $search . '%"  ';
    }
    $getClientInfoQuery .= " order by payable desc";
    if ($offset !== null) {
        $getClientInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueAmazon')) as  revenueAmazon ,gst_per FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueAmazon'];
        $gst_per = $getshareresdata['gst_per'];
    }
    $chennelids = [];
    $videoids = [];
    $rawdata = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        $videoids[] = "'" . $row['seasonID'] . "'";

        $rowdata[] = $row;
    }

    $videoids = implode(',', $videoids);
    $chennelids = implode(',', $chennelids);
    $row = [];

    $youtubereport = "SELECT  titleName,seasonID, royaltyAmount, royaltyCurrency, exchangeRate, revINRwithoutHolding, withHolding, amountReceived,clientShare,payable   FROM $table2 where   content_owner = '" . $client . "' ";
    if (!empty($search)) {
        $youtubereport .= ' and titleName like "' . $search . '%"  ';
    }

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;

        $cdata[$row2['seasonID']] = $row2['seasonID'];

        $cdata[$row2['titleName']] = $row2['titleName'];
        $cdata[$row2['royaltyAmount']] = $row2['royaltyAmount'];
        $cdata[$row2['royaltyCurrency']] = $row2['royaltyCurrency'];
        $cdata[$row2['exchangeRate']] = $row2['exchangeRate'];
        $cdata[$row2['revINRwithoutHolding']] = $row2['revINRwithoutHolding'];
        $cdata[$row2['withHolding']] = $row2['withHolding'];
        $cdata[$row2['amountReceived']] = $row2['amountReceived'];
        $cdata[$row2['clientShare']] = $row2['clientShare'];
        $cdata[$row2['payable']] = $row2['payable'];

    }

    foreach ($rowdata as $r) {

        $res['data'][] = [
            'seasonID' => isset($cdata[$r['seasonID']]) ? $cdata[$r['seasonID']] : 'N/A',

            'titleName' => isset($cdata[$r['titleName']]) ? $cdata[$r['titleName']] : 'N/A',
            'royaltyAmount' => number_format($r['royaltyAmount'], 2),
            'royaltyCurrency' => isset($cdata[$r['royaltyCurrency']]) ? $cdata[$r['royaltyCurrency']] : 'N/A',
            'exchangeRate' => isset($cdata[$r['exchangeRate']]) ? $cdata[$r['exchangeRate']] : 'N/A',
            'revINRwithoutHolding' => isset($cdata[$r['revINRwithoutHolding']]) ? $cdata[$r['revINRwithoutHolding']] : 'N/A',
            'withHolding' => isset($cdata[$r['withHolding']]) ? $cdata[$r['withHolding']] : 'N/A',
            'amountReceived' => isset($cdata[$r['amountReceived']]) ? $cdata[$r['amountReceived']] : 'N/A',
            'clientShare' => isset($cdata[$r['clientShare']]) ? $cdata[$r['clientShare']] : 'N/A',

            'finalpayable' => (($r['payable'])),
        ];

    }
    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}

function RevenueChartClientsAmazonReport(
    $table,
    $table2,
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

    $getAllrevenueQuery = "SELECT titleName,SUM(payable) as partnerRevenue  FROM $table where content_owner = '" . $client . "' ";
    if ($channelid !== null) {
        $getAllrevenueQuery .= " AND seasonID = '{$channelid}'";
    }

    $getAllrevenueQuery .= "  group by titleName ORDER by titleName";

    if ($offset !== null) {
        $getAllrevenueQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    $getAllrevenueQueryResult = runQuery($getAllrevenueQuery, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res[] = array('Title', 'Amt (INR)');

    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {

        $res[] = array($row['titleName'], (float) $row['partnerRevenue']);

    }

    return setErrorStack($returnArr, -1, $res, null);
}

function ViewsChartClientsAmazonReport(
    $table,
    $table2,
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

    $getAllrevenueQuery = "SELECT titleName ,count(durationStreamed) as views FROM $table where content_owner = '" . $client . "' ";
    if ($channelid !== null) {
        $getAllrevenueQuery .= " AND seasonID = '{$channelid}'";
    }

    $getAllrevenueQuery .= "  group by titleName ORDER by titleName";

    if ($offset !== null) {
        $getAllrevenueQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    $getAllrevenueQueryResult = runQuery($getAllrevenueQuery, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res[] = array('Title', 'Duration');
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {

        $res[] = array($row['titleName'], (float) $row['views']);
    }

    return setErrorStack($returnArr, -1, $res, null);
}

function ExportClientsAmazonReport(
    $table,
    $table2,
    $conn,
    $offset = null,
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

    $gettotalcountquery = "SELECT count(id) as totalcount FROM $table where content_owner = '" . $client . "'";
    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

    $getClientInfoQuery = "SELECT seasonID, titleName,royaltyAmount, royaltyCurrency, exchangeRate, revINRwithoutHolding, withHolding, amountReceived,clientShare,payable  FROM $table where content_owner = '" . $client . "' order by payable desc";

    if ($offset !== null) {
        $getClientInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueAmazon')) as  revenueAmazon,gst_per  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueAmazon'];
        $gst_per = $getshareresdata['gst_per'];
    }
    $chennelids = [];
    $videoids = [];
    $rawdata = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        $videoids[] = "'" . $row['seasonID'] . "'";

        $rowdata[] = $row;
    }

    $videoids = implode(',', $videoids);
    $chennelids = implode(',', $chennelids);
    $row = [];

    $youtubereport = "SELECT  titleName,seasonID, royaltyAmount, royaltyCurrency, exchangeRate, revINRwithoutHolding, withHolding, amountReceived,clientShare,payable   FROM $table2 where seasonID in($videoids) ";
    if (!empty($search)) {
        $youtubereport .= ' and titleName like "' . $search . '%"  ';
    }
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;

        $cdata[$row2['seasonID']] = $row2['seasonID'];

        $cdata[$row2['titleName']] = $row2['titleName'];
        $cdata[$row2['royaltyAmount']] = $row2['royaltyAmount'];
        $cdata[$row2['royaltyCurrency']] = $row2['royaltyCurrency'];
        $cdata[$row2['exchangeRate']] = $row2['exchangeRate'];
        $cdata[$row2['revINRwithoutHolding']] = $row2['revINRwithoutHolding'];
        $cdata[$row2['withHolding']] = $row2['withHolding'];
        $cdata[$row2['amountReceived']] = $row2['amountReceived'];
        $cdata[$row2['clientShare']] = $row2['clientShare'];
        $cdata[$row2['payable']] = $row2['payable'];

    }

    foreach ($rowdata as $r) {

        $res['data'][] = [
            'seasonID' => isset($cdata[$r['seasonID']]) ? $cdata[$r['seasonID']] : 'N/A',

            'titleName' => isset($cdata[$r['titleName']]) ? $cdata[$r['titleName']] : 'N/A',
            'royaltyAmount' => number_format($r['royaltyAmount'], 2),
            'royaltyCurrency' => isset($cdata[$r['royaltyCurrency']]) ? $cdata[$r['royaltyCurrency']] : 'N/A',
            'exchangeRate' => isset($cdata[$r['exchangeRate']]) ? $cdata[$r['exchangeRate']] : 'N/A',
            'revINRwithoutHolding' => isset($cdata[$r['revINRwithoutHolding']]) ? $cdata[$r['revINRwithoutHolding']] : 'N/A',
            'withHolding' => isset($cdata[$r['withHolding']]) ? $cdata[$r['withHolding']] : 'N/A',
            'amountReceived' => isset($cdata[$r['amountReceived']]) ? $cdata[$r['amountReceived']] : 'N/A',
            'clientShare' => isset($cdata[$r['clientShare']]) ? $cdata[$r['clientShare']] : 'N/A',

            'finalpayable' => (($r['payable'])),
        ];

    }
    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);

}
function getClientsYoutubeFinanceReportCountv2(
    $table,
    $table2,
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
    if ($client == null) {
        $client = $_SESSION['client'];
    }
    $getClientInfoQuery = "SELECT * FROM $table where content_owner = '" . $client . "' order by partnerRevenue desc";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube ,gst_per FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }
    $chennelids = [];
    $videoids = [];
    $rawdata = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        $videoids[] = "'" . $row['videoID'] . "'";
        $chennelids[] = "'" . $row['channelID'] . "'";
        $rowdata[] = $row;
    }
    $videoids = implode(',', $videoids);
    $chennelids = implode(',', $chennelids);
    $row = [];

    $youtubereport = "SELECT  count(*) as total  FROM $table2 where video_id in($videoids) and channel_id in($chennelids) ";
    if (!empty($search)) {
        $youtubereport .= ' and video_title like "' . $search . '%"  ';
    }
    $youtubereportresult = runQuery($youtubereport, $conn);

    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        $res[0] = $row2;

    }

    return setErrorStack($returnArr, -1, $res, null);
}

function ViewsChartClientsYoutubeFinanceReportv2(
    $table,
    $table2,
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
                $union_final_query[] = " ( SELECT DATE(day) as day,SUM(ownedViews) as views,SUM(if(contentType='UGC',ownedViews,0)) as UGC,SUM(if(contentType='Partner-provided',ownedViews,0)) as Partnerprovided FROM $v where content_owner = '" . $client . "'    group by day ORDER by day )  ";
            } else {
                $union_final_query[] = " ( SELECT DATE(day) as day,SUM(ownedViews) as views,SUM(if(contentType='UGC',ownedViews,0)) as UGC,SUM(if(contentType='Partner-provided',ownedViews,0)) as Partnerprovided FROM $v where content_owner = '" . $client . "'    group by day ORDER by day )  ";
            }

        }

    }

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by day asc";

    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    @unlink("polochart_view.txt");
    file_put_contents("polochart_view.txt", $youtubereport);
    @chmod("polochart_view.txt", 0777);

    $getAllrevenueQueryResult = runQuery($youtubereport, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        //$d= explode('-',$row['day']);

        if ($row['day'] != "") {
            $d = explode('-', $row['day']);
            $year = (int) $d[0];
            $month = (int) $d[1] - 1;
            $day = (int) $d[2];

            $res[] = array('c' => array(
                array('v' => "Date($year, $month, $day)"),
                array('v' => $row['views']),
                array('v' => $row['Partnerprovided']),
                array('v' => $row['UGC']),
            ));
        }

    }

    return setErrorStack($returnArr, -1, $res, null);
}

function RevenueChartClientsYoutubeFinanceReportv2(
    $table,
    $table2,
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
                $union_final_query[] = " ( SELECT DATE(day) as day,SUM(partnerRevenue) as partnerRevenue,SUM(if(contentType='UGC',partnerRevenue,0)) as UGC,SUM(if(contentType='Partner-provided',partnerRevenue,0)) as Partnerprovided FROM $v where content_owner = '" . $client . "'   group by day ORDER by day  )  ";
            } else {
                $union_final_query[] = " ( SELECT DATE(day) as day,SUM(partnerRevenue) as partnerRevenue,SUM(if(contentType='UGC',partnerRevenue,0)) as UGC,SUM(if(contentType='Partner-provided',partnerRevenue,0)) as Partnerprovided FROM $v where content_owner = '" . $client . "'   group by day ORDER by day  )  ";
            }

        }

    }

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by day asc";

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
        if ($row['day'] != "") {
            $d = explode('-', $row['day']);
            $year = (int) $d[0];
            $month = (int) $d[1] - 1;
            $day = (int) $d[2];

            $res[] = array('c' => array(
                array('v' => "Date($year, $month, $day)"),
                array('v' => $row['partnerRevenue']),
                array('v' => $row['Partnerprovided']),
                array('v' => $row['UGC']),
            ));
        }

    }

    return setErrorStack($returnArr, -1, $res, null);
}
function ExportClientsYoutubeFinanceReportv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $gettotalcounts = 0;
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

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube,gst_per  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
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
    $union_final_query_sql = "select  *   from 	( ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $explode_table = explode("youtube_video_claim_report_", $v);
            $explode_table_1 = explode("_", $explode_table[1]);
            $table_name = $explode_table_1[0];

            $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
            $union_final_query[] = " ( SELECT video_title,assetID,assetChannelID,partnerRevenue ,'{$table_name}' as table_name FROM $v where  content_owner = '{$client}' ) ";
        }

    }

    $check_query_total_new = implode(" union all ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube ,gst_per FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }

    $row = [];

    $table2 = $all_table[0];

    $check_query_list_new = implode(" union all ", $union_final_query);
    $youtubereport = $union_final_query_sql . $check_query_list_new . " )  as list_all order by partnerRevenue desc";

    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        $youtubereport .= ' and video_title like "' . $search . '%"  ';
    }
    // echo "<br>sql :::::  ". $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;

        $res['data'][] = [
            'video_title' => isset($row2['video_title']) ? $row2['video_title'] : 'N/A',
            'assetID' => isset($row2['assetID']) ? $row2['assetID'] : 'N/A',
            'assetChannelID' => isset($row2['assetChannelID']) ? $row2['assetChannelID'] : 'N/A',
            'youtube_payout' => $row2['partnerRevenue'],
            'rev_share' => $rev_share,
            'finalpayable' => (($row2['partnerRevenue'] * $rev_share) / 100),

            'table_name' => isset($row2['table_name']) ? $row2['table_name'] : 'N/A',
        ];
    }

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}

//added new function for count only on 15-09-2021 by kishore

function ExportClientsYoutubeClaimReportCountv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $gettotalcounts = 0;
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

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube ,gst_per FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
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
   
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];

            $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
            
        }

    }

    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

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
function ExportClientsYoutubeClaimReportv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $gettotalcounts = 0;
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

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube ,gst_per FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
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

            $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
            $union_final_query[] = " ( SELECT videoID,channelID,contentType,video_title,assetID,assetChannelID,Coalesce(sum(partnerRevenue),0) as partnerRevenue, Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
			Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END)*30/100),0) as WITHHOLDING ,'{$table_name}' as table_name,   ( SELECT Channel FROM `channel_co_maping` ccm  where ccm.assetChannelID={$v}.assetChannelID limit 1 ) as channel_name,  ( SELECT Channel FROM `channel_co_maping` ccm  where  ccm.Label={$v}.Label limit 1 ) as channel_name2  FROM $v where  content_owner = '{$client}'   group by videoID  ) ";
        }

    }

    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube,gst_per  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per =0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }

    $row = [];

    $table2 = $all_table[0];

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc";

    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    // echo "<br>sql :::::  ". $youtubereport;
    // echo "<br>sql :::::  ". $youtubereport;
    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $youtubereport);
    @chmod("polo_export.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;

        $channel_name2 = isset($row2['channel_name2']) ? $row2['channel_name2'] : 'N/A';

        $res['data'][] = [
            'channel_name' => isset($row2['channel_name']) ? $row2['channel_name'] : $channel_name2,
            'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
            'videoID' => isset($row2['videoID']) ? $row2['videoID'] : 'N/A',
            'video_title' => isset($row2['video_title']) ? $row2['video_title'] : 'N/A',
            'contentType' => isset($row2['contentType']) ? $row2['contentType'] : 'N/A',
            'assetID' => isset($row2['assetID']) ? $row2['assetID'] : 'N/A',
            'assetChannelID' => isset($row2['assetChannelID']) ? $row2['assetChannelID'] : 'N/A',
            'youtube_payout' => $row2['partnerRevenue'] * 1,
            'rev_share' => $rev_share * 1,
            'USPAYOUT' => $row2['USPAYOUT'] * 1,
            'WITHHOLDING' => $row2['WITHHOLDING'] * 1,
            'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) * 1,
            'table_name' => isset($row2['table_name']) ? $row2['table_name'] : 'N/A',

        ];
    }

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}



function ExportClientsYoutubeClaimReportCountv3(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $downlaodType = 'normal'
   
) {
    
    $gettotalcounts = 0;
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

   /*  //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
    } */

    $getClientInfoQuery = "SHOW TABLES LIKE '$type'";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */

    $union_final_query_sql = "SELECT COUNT('') as totalcount from  	 ";
    
    $union_final_query = [];
 

 
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

      
        // youtube_video_claim_report2_nd
        $other_table_temp1 = "youtube_video_claim_report2";

        foreach ($row as $k => $v) {
           

            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];
            $other_tables = $other_table_temp1."_".$result[2]."_".$result[1]."_".$result[0];

            $year_mnoth = $result[1]."_".$result[0];
            $nd_type = $result[2];

             //get share of co
              
                $rev_share = 30;
                $gst_percentage = 0;
                $holding_percentage = 0;
                $nd_types = array('nd1','nd2','ndkids');
                if(in_array($nd_type,$nd_types)){

                    $getshare = "SELECT  shares , gst_percentage    FROM youtube_video_claim_activation_report_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
            
                    //  @unlink("polo_export_getshare.txt");
                      $ddd = $getshare.PHP_EOL;
                      file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                      @chmod("polo_export_getshare.txt", 0777);
      
                      
                      $getshareres = runQuery($getshare, $conn);
      
                      if (!noError($getshareres)) {
                          //  @unlink("polo_export_getshare.txt");
                          $ddd = "ExportClientsYoutubeClaimReportv3 Error_in_query: ".$client.PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                          file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                          @chmod("polo_export_getshare.txt", 0777);
          
                          // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
                      }    else {
                          $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
                  
                          if (!empty($getshareresdata)) {
                          
                              $rev_share = $getshareresdata['shares'];
                              $gst_percentage = $getshareresdata['gst_percentage'];
                            //  $holding_percentage = $getshareresdata['holding_percentage'];
                          }
                      }
                }
           
                
                if($downlaodType == "withholding"){
                    
                    $union_final_query[] = " ( SELECT   {$v}.country,{$v}.videoID,{$v}.channelID,{$v}.video_title,{$v}.assetID , {$v}.assetChannelID, {$v}.contentType,{$v}.content_owner,{$v}.Label, Coalesce(sum(partnerRevenue),0) as partnerRevenue , Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
                    Coalesce( ( SUM(CASE WHEN country='US' THEN partnerRevenue END) * {$v}.holding_percentage / 100) , 0) as WITHHOLDING , '{$table_name}'  as tablename ,  ( SELECT Channel FROM `channel_co_maping` ccm  where ccm.assetChannelID={$v}.assetChannelID limit 1 ) as channel_name   ,
                    ( SELECT record_label FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as record_label, ( SELECT asset_title FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as  asset_title, ( SELECT channel_display_name FROM `{$other_tables}`    where  {$other_tables}.video_id = {$v}.videoID limit 1  ) as  channel_name2 ,
                    ( SELECT video_title FROM `{$other_tables}`    where  {$other_tables}.video_id = {$v}.videoID limit 1  ) as  video_title2  , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage,  {$v}.holding_percentage
                    FROM $v
                    where  {$v}.content_owner = '{$client}'   group by {$v}.assetChannelID ,{$v}.holding_percentage  )  ";
                } else {
                  
                    $union_final_query[] = " ( SELECT   {$v}.country,{$v}.videoID,{$v}.channelID,{$v}.video_title,{$v}.assetID , {$v}.assetChannelID, {$v}.contentType,{$v}.content_owner,{$v}.Label, Coalesce(sum(partnerRevenue),0) as partnerRevenue , Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
                    Coalesce( ( SUM(CASE WHEN country='US' THEN partnerRevenue END) * {$v}.holding_percentage / 100) , 0) as WITHHOLDING , '{$table_name}'  as tablename ,  ( SELECT Channel FROM `channel_co_maping` ccm  where ccm.assetChannelID={$v}.assetChannelID limit 1 ) as channel_name   ,
                    ( SELECT record_label FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as record_label, ( SELECT asset_title FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as  asset_title, ( SELECT channel_display_name FROM `{$other_tables}`    where  {$other_tables}.video_id = {$v}.videoID limit 1  ) as  channel_name2 ,
                    ( SELECT video_title FROM `{$other_tables}`    where  {$other_tables}.video_id = {$v}.videoID limit 1  ) as  video_title2  , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage,  {$v}.holding_percentage
                    FROM $v
                    where  {$v}.content_owner = '{$client}'   group by {$v}.videoID , {$v}.holding_percentage )  ";
                }
            
        }


    }


    $row = [];
 
    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as final_total ";

 

    // echo "<br>sql :::::  ". $youtubereport;
    // echo "<br>sql :::::  ". $youtubereport;
    @unlink("polo_export_count.txt");
    file_put_contents("polo_export_count.txt", $youtubereport);
    @chmod("polo_export_count.txt", 0777);

    $gettotalcountquery = runQuery($youtubereport, $conn);

    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

 
    

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}
function ExportClientsYoutubeClaimReportv3(
    $type,
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = Null,
    $downlaodType = 'normal'
) {
    $gettotalcounts = 0;
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

   /*  //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
    } */

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

    $all_assetChannelID_holding = [];
    $all_holding = [];
    $all_assetChannelID = [];
/* 
    $sql_channel_co_maping = "SELECT  assetChannelID,client_youtube_shares,CMS  FROM 	channel_co_maping where partner_provided='{$client}' ";
      
      $query_channel_co_maping = runQuery($sql_channel_co_maping, $conn);

      if (!noError($query_channel_co_maping)) {
        while ($row = mysqli_fetch_assoc($query_channel_co_maping["dbResource"])) {
            $all_assetChannelID_holding[$row['assetChannelID']] = $row['client_youtube_shares'];
            $all_holding[] = $row['client_youtube_shares'];
            $all_assetChannelID[] = $row['assetChannelID'];
        }
      }
       */
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

      
        // youtube_video_claim_report2_nd
        $other_table_temp1 = "youtube_video_claim_report2";

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];
            $other_tables = $other_table_temp1."_".$result[2]."_".$result[1]."_".$result[0];

            $year_mnoth = $result[1]."_".$result[0];
            $nd_type = $result[2];

             //get share of co
              
                $rev_share = 30;
                $gst_percentage = 0;
                $holding_percentage = 0;
                $nd_types = array('nd1','nd2','ndkids');
                if(in_array($nd_type,$nd_types)){

                    $getshare = "SELECT  shares , gst_percentage    FROM youtube_video_claim_activation_report_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
            
                    //  @unlink("polo_export_getshare.txt");
                      $ddd = $getshare.PHP_EOL;
                      file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                      @chmod("polo_export_getshare.txt", 0777);
      
                      
                      $getshareres = runQuery($getshare, $conn);
      
                      if (!noError($getshareres)) {
                          //  @unlink("polo_export_getshare.txt");
                          $ddd = "ExportClientsYoutubeClaimReportv3 Error_in_query: ".$client.PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                          file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                          @chmod("polo_export_getshare.txt", 0777);
          
                          // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
                      }    else {
                          $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
                  
                          if (!empty($getshareresdata)) {
                          
                              $rev_share = $getshareresdata['shares'];
                              $gst_percentage = $getshareresdata['gst_percentage'];
                            //  $holding_percentage = $getshareresdata['holding_percentage'];
                          }
                      }
                }
           
                
                
/* 
            if (!empty($search)) {
                $check_query_total[] = " select count(distinct id) as totalcount from   $v WHERE {$v}.content_owner='{$client}' and  video_title like '" . $search . "%'  group by videoID ";
                $union_final_query[] = " ( SELECT {$v}.videoID,{$v}.channelID,{$v}.video_title,{$v}.assetID , {$v}.assetChannelID, {$v}.contentType,{$v}.content_owner,{$v}.Label, Coalesce(sum(partnerRevenue),0) as partnerRevenue,Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
				Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END) * holding_percentage / 100),0) as WITHHOLDING , '{$table_name}'  as tablename,  ( SELECT Channel FROM `channel_co_maping` ccm  where ccm.assetChannelID={$v}.assetChannelID limit 1 ) as channel_name ,
                ( SELECT record_label FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as record_label, ( SELECT asset_title FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as  asset_title, ( SELECT channel_display_name FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as  channel_name2  ( SELECT video_title FROM `{$other_tables}`    where  {$other_tables}.video_id = {$v}.videoID limit 1  ) as  video_title2  , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage,  holding_percentage   FROM $v

                where  {$v}.content_owner = '{$client}' and  video_title like '" . $search . "%'   group by videoID )  ";
            } else {

                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE {$v}.content_owner='{$client}'  group by assetID ";
                $union_final_query[] = " ( SELECT   {$v}.videoID,{$v}.channelID,{$v}.video_title,{$v}.assetID , {$v}.assetChannelID, {$v}.contentType,{$v}.content_owner,{$v}.Label, Coalesce(sum(partnerRevenue),0) as partnerRevenue , Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
				Coalesce( ( SUM(CASE WHEN country='US' THEN partnerRevenue END) * {$v}.holding_percentage / 100) , 0) as WITHHOLDING , '{$table_name}'  as tablename ,  ( SELECT Channel FROM `channel_co_maping` ccm  where ccm.assetChannelID={$v}.assetChannelID limit 1 ) as channel_name   ,
                ( SELECT record_label FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as record_label, ( SELECT asset_title FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as  asset_title, ( SELECT channel_display_name FROM `{$other_tables}`    where  {$other_tables}.video_id = {$v}.videoID limit 1  ) as  channel_name2 ,
                ( SELECT video_title FROM `{$other_tables}`    where  {$other_tables}.video_id = {$v}.videoID limit 1  ) as  video_title2  , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage,  {$v}.holding_percentage
                FROM $v
                where  {$v}.content_owner = '{$client}'   group by {$v}.videoID )  ";
            } */

            if($downlaodType == "withholding"){
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE {$v}.content_owner='{$client}'  group by assetID ";
                $union_final_query[] = " ( SELECT   {$v}.assetChannelID,{$v}.content_owner,{$v}.Label, Coalesce(sum(partnerRevenue),0) as partnerRevenue , Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
                Coalesce( ( SUM(CASE WHEN country='US' THEN partnerRevenue END) * {$v}.holding_percentage / 100) , 0) as WITHHOLDING , '{$table_name}'  as tablename , 
                 {$rev_share} as rev_share, {$gst_percentage} as gst_percentage,  {$v}.holding_percentage
                FROM $v
                where  {$v}.content_owner = '{$client}'   group by {$v}.assetChannelID ,{$v}.holding_percentage  )  ";
            } else {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE {$v}.content_owner='{$client}'  group by assetID ";
                $union_final_query[] = " ( SELECT   {$v}.country,{$v}.videoID,{$v}.channelID,{$v}.video_title,{$v}.assetID , {$v}.assetChannelID, {$v}.contentType,{$v}.content_owner,{$v}.Label, Coalesce(sum(partnerRevenue),0) as partnerRevenue , Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
                Coalesce( ( SUM(CASE WHEN country='US' THEN partnerRevenue END) * {$v}.holding_percentage / 100) , 0) as WITHHOLDING , '{$table_name}'  as tablename ,  ( SELECT Channel FROM `channel_co_maping` ccm  where ccm.assetChannelID={$v}.assetChannelID limit 1 ) as channel_name   ,
                ( SELECT record_label FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as record_label, ( SELECT asset_title FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as  asset_title, ( SELECT channel_display_name FROM `{$other_tables}`    where  {$other_tables}.video_id = {$v}.videoID limit 1  ) as  channel_name2 ,
                ( SELECT video_title FROM `{$other_tables}`    where  {$other_tables}.video_id = {$v}.videoID limit 1  ) as  video_title2  , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage,  {$v}.holding_percentage
                FROM $v
                where  {$v}.content_owner = '{$client}'   group by {$v}.videoID ,{$v}.holding_percentage )  ";
            }
            
        }


    }


    $row = [];
 
    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc";

    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    // echo "<br>sql :::::  ". $youtubereport;
    // echo "<br>sql :::::  ". $youtubereport;
    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $youtubereport);
    @chmod("polo_export.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;

      
         
       $channel_name = isset($row2['channel_name']) ? $row2['channel_name'] : 'N/A';
       // $content_type = isset($row2['content_type']) ? $row2['content_type'] : 'N/A';
       $channel_name_temp = isset($row2['channel_name2']) ? $row2['channel_name2'] : $channel_name;
       $video_title = isset($row2['video_title']) ? $row2['video_title'] : 'N/A';
       $asset_title = isset($row2['asset_title']) ? $row2['asset_title'] : 'N/A';

       $video_title_temp = isset($row2['video_title2']) ? $row2['video_title2'] : $video_title;
       $rev_share = $row2['rev_share'] + 0;
       $gst_per  = $row2['gst_percentage'] + 0;
       $finalpayable = ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0;
       $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

   //    $headtitle = ['channel-Name', 'channel-ID', 'video-ID', 'Video title','Content-Type','Label','asset_title', 'AssetId', 'assetChannelID', 'Youtube payout', 'RevShare', 'USPAYOUT','Holding-Perc', 'WITHHOLDING', 'Final Payable','GST','Final Payable-GST', 'Channel'];

            if($downlaodType == "withholding"){
                $res['data'][] = [
                    'assetChannelID' => isset($row2['assetChannelID']) ? $row2['assetChannelID'] : 'N/A',
                    'youtube_payout' => $row2['partnerRevenue'] + 0,
                    'rev_share' => $rev_share + 0,
                    'USPAYOUT' => $row2['USPAYOUT'] + 0,
                    'holding_percentage' => ($row2['USPAYOUT'] > 0) ? $row2['holding_percentage'] + 0 : 0,
                    'WITHHOLDING' => $row2['WITHHOLDING'] + 0,
                    'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0,
                    'gst_per' => $gst_per,
                    'finalpayable_gst' => $final_payable_wth_gst,
                    'table_name' => isset($row2['tablename']) ? $row2['tablename'] : 'N/A',
                ];  
            } else {
                $res['data'][] = [
                    'country' =>isset($row2['country']) ? $row2['country'] : 'N/A',
                    'channel_name' => str_replace('=', '   ', $channel_name_temp),
                    'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
                    'videoID' => isset($row2['videoID']) ? $row2['videoID'] : 'N/A',
                    'video_title' => str_replace('=', '   ', $video_title_temp),
                    'content_type' => isset($row2['contentType']) ? $row2['contentType'] : 'N/A',
                    'Label' => isset($row2['Label']) ? $row2['Label'] : 'N/A',
                    'asset_title' => str_replace('=', '   ', $asset_title),
                    'assetID' => isset($row2['assetID']) ? $row2['assetID'] : 'N/A',
                    'assetChannelID' => isset($row2['assetChannelID']) ? $row2['assetChannelID'] : 'N/A',
                    'youtube_payout' => $row2['partnerRevenue'] + 0,
                    'rev_share' => $rev_share + 0,
                    'USPAYOUT' => $row2['USPAYOUT'] + 0,
                  //  'holding_percentage' => ($row2['USPAYOUT'] > 0) ? $row2['holding_percentage'] + 0 : 0,
                    'WITHHOLDING' => $row2['WITHHOLDING'] + 0,
                    'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0,
                    'gst_per' => $gst_per,
                    'finalpayable_gst' => $final_payable_wth_gst,
                    'table_name' => isset($row2['tablename']) ? $row2['tablename'] : 'N/A',
                ];   
            }

        
    }
    
  
   // $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}


function getChannelsofthereportv2(
    $table,
    $conn,
    $search = '',
    $client = null
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";

    return setErrorStack($returnArr, 3, '', null); //need to delete this , it is added bcz it takingg too much time to load

    $getClientInfoQuery = "SELECT DISTINCT channelID FROM $table where content_owner = '" . $client . "' ";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {

        $res[] = $row;
    }

    return setErrorStack($returnArr, -1, $res, null);
}
function getChannelsofthereportv3(
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

            if (in_array("ecommerce", $result)) {
                $union_final_query[] = " ( SELECT  DISTINCT   channel_id  as channelID ,'{$table_name}'  as tablename FROM $v where  content_owner = '{$client}' ) ";
            } else {
                $union_final_query[] = " ( SELECT  DISTINCT   channelID  ,'{$table_name}'  as tablename FROM $v where  content_owner = '{$client}' ) ";
            }

        }

    }

    $check_query_list_new = implode(" union  ", $union_final_query);
    $getClientInfoQuery = $union_final_query_sql . $check_query_list_new . " )  as list_all order by channelID desc";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {

        $res[] = $row;
    }

    return setErrorStack($returnArr, -1, $res, null);
}

function ExportClientsYoutubeRedMusicUnAssignedv2(
    $table,

    $conn,
    $offset = null,
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
//SELECT T1.assetID,label,youtube_video_claim_report3_nd1_2020_12.asset_label,youtube_video_claim_report3_nd1_2020_12.asset_id FROM `youtube_video_claim_report_nd1_2020_12` as T1  INNER JOIN youtube_video_claim_report3_nd1_2020_12 ON T1.assetID=youtube_video_claim_report3_nd1_2020_12.asset_id  where T1.content_owner IS NULL  group by T1.assetID INTO OUTFILE '/var/lib/mysql-files/youtube_video_claim_report/missinglablesinv2.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n'
    //$sql = "INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/missinglablesinv2.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n'";
    @unlink("/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv");

    $insertTableQuery = "SELECT 'id' , 'adjustmentType' , 'day' , 'country' , 'videoID' , 'channelID' , 'assetID' , 'assetChannelID' , 'assetType' , 'cutsomID' ,'ISRC' ,'UPC' ,'GRid' , 'contentType' , 'policy' , 'claimType' , 'claimOrigin' , 'ownedViews' , 'youtubeRevenueSplitAuction' , 'youtubeRevenueSplitReserved' ,'youtubeRevenueSplitPartnerSoldYoutubeServed' , 'youtubeRevenueSplitPartnerSoldPartnerServed' , 'youtubeRevenueSplit' ,'partnerRevenueAuction' , 'partnerRevenueReserved' , 'partnerRevenuePartnerSoldYouTubeServed' ,'partnerRevenuePartnerSoldPartnerServed' , 'partnerRevenue' , 'content_owner' , 'Label' , 'video_title'
	UNION ALL SELECT  `id` , `adjustmentType` , `day` , `country` , `videoID` , `channelID` , `assetID` , `assetChannelID` , `assetType` , `cutsomID` ,`ISRC` ,`UPC` ,`GRid` , `contentType` , `policy` , `claimType` , `claimOrigin` , `ownedViews` , `youtubeRevenueSplitAuction` , `youtubeRevenueSplitReserved` ,`youtubeRevenueSplitPartnerSoldYoutubeServed` , `youtubeRevenueSplitPartnerSoldPartnerServed` , `youtubeRevenueSplit` ,`partnerRevenueAuction` , `partnerRevenueReserved` , `partnerRevenuePartnerSoldYouTubeServed` ,`partnerRevenuePartnerSoldPartnerServed` , `partnerRevenue` , COALESCE(content_owner, '')  , `Label` , `video_title` FROM {$table}   where ({$table}.content_owner IS NULL  or {$table}.content_owner ='' )  group by {$table}.assetID INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $insertTableQuery);
    @chmod("polo_export.txt", 0777);

    $insertTableQueryResult = runQuery($insertTableQuery, $conn);
/*

$gettotalcountquery = "SELECT count(id) as totalcount FROM $table where   (content_owner IS NULL  || content_owner ='')";
$gettotalcountquery = runQuery($gettotalcountquery, $conn);
if (!noError($gettotalcountquery)) {
return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
}
$gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
$gettotalcounts = $gettotalcounts['totalcount'];

$youtubereport = "SELECT  video_id,channel_id,video_title,asset_id ,Label  FROM $table   where (content_owner IS NULL  || content_owner ='')   ";

$youtubereportresult = runQuery($youtubereport, $conn);
$cdata  = [];
while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {

$res['data'][] =  [
'video_id'=>$row2['video_id'],
'channel_id'=>$row2['channel_id'],
'asset_id'=>$row2['asset_id'],
'video_title'=>$row2['video_title'],
'Label'=>$row2['Label'],

];

}

$res['total']=$gettotalcounts;

return setErrorStack($returnArr, -1, $res, null);
 */
}



function ExportwithholdingUnAssignedv5(
    $table,

    $conn,
    $offset = null,
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
//SELECT T1.assetID,label,youtube_video_claim_report3_nd1_2020_12.asset_label,youtube_video_claim_report3_nd1_2020_12.asset_id FROM `youtube_video_claim_report_nd1_2020_12` as T1  INNER JOIN youtube_video_claim_report3_nd1_2020_12 ON T1.assetID=youtube_video_claim_report3_nd1_2020_12.asset_id  where T1.content_owner IS NULL  group by T1.assetID INTO OUTFILE '/var/lib/mysql-files/youtube_video_claim_report/missinglablesinv2.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n'
    //$sql = "INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/missinglablesinv2.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n'";
    //@unlink("/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv");

    //@chmod("/var/lib/mysql-files/nonassignedcontentowners", 0777);

    $insertTableQuery = "SELECT 'id' , 'AdSense_Earnings_Month' , 'Channel_ID','Revenue Source' , 'Local_Currency' , 'Total_Channel_Revenue' , 'US_Sourced_Revenue' , 'Tax_Withholding_Rate' , 'Tax_Withheld_Amount' , 'content_owner'
	UNION ALL SELECT `id` , `AdSense_Earnings_Month` , `Channel_ID` , `Revenue_Source` , `Local_Currency` , `Total_Channel_Revenue` , `US_Sourced_Revenue` , `Tax_Withholding_Rate` , `Tax_Withheld_Amount` , `content_owner` FROM {$table}   where ({$table}.content_owner IS NULL  or {$table}.content_owner ='' ) group by {$table}.assetID INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $insertTableQuery);
    @chmod("polo_export.txt", 0777);

    $insertTableQueryResult = runQuery($insertTableQuery, $conn);

    die();
    /*
$gettotalcountquery = "SELECT count(id) as totalcount FROM $table where   (content_owner IS NULL  || content_owner ='')";
$gettotalcountquery = runQuery($gettotalcountquery, $conn);
if (!noError($gettotalcountquery)) {
return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
}
$gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
$gettotalcounts = $gettotalcounts['totalcount'];

$youtubereport = "SELECT  video_id,channel_id,video_title,asset_id ,Label  FROM $table   where (content_owner IS NULL  || content_owner ='')   ";

$youtubereportresult = runQuery($youtubereport, $conn);
$cdata  = [];
while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {

$res['data'][] =  [
'video_id'=>$row2['video_id'],
'channel_id'=>$row2['channel_id'],
'asset_id'=>$row2['asset_id'],
'video_title'=>$row2['video_title'],
'Label'=>$row2['Label'],

];

}

$res['total']=$gettotalcounts;

return setErrorStack($returnArr, -1, $res, null);
 */
}

function ExportClientsYoutubeUnAssignedv2(
    $table,

    $conn,
    $offset = null,
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
//SELECT T1.assetID,label,youtube_video_claim_report3_nd1_2020_12.asset_label,youtube_video_claim_report3_nd1_2020_12.asset_id FROM `youtube_video_claim_report_nd1_2020_12` as T1  INNER JOIN youtube_video_claim_report3_nd1_2020_12 ON T1.assetID=youtube_video_claim_report3_nd1_2020_12.asset_id  where T1.content_owner IS NULL  group by T1.assetID INTO OUTFILE '/var/lib/mysql-files/youtube_video_claim_report/missinglablesinv2.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n'
    //$sql = "INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/missinglablesinv2.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n'";
    //@unlink("/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv");

    //@chmod("/var/lib/mysql-files/nonassignedcontentowners", 0777);

    $insertTableQuery = "SELECT 'id' , 'adjustmentType' , 'day' , 'country' , 'videoID' , 'channelID' , 'assetID' , 'assetChannelID' , 'assetType' , 'cutsomID' , 'contentType' , 'policy' , 'claimType' , 'claimOrigin' , 'ownedViews' , 'youtubeRevenueSplitAuction' , 'youtubeRevenueSplitReserved' ,'youtubeRevenueSplitPartnerSoldYoutubeServed' , 'youtubeRevenueSplitPartnerSoldPartnerServed' , 'youtubeRevenueSplit' ,'partnerRevenueAuction' , 'partnerRevenueReserved' , 'partnerRevenuePartnerSoldYouTubeServed' ,'partnerRevenuePartnerSoldPartnerServed' , 'partnerRevenue' , 'content_owner' , 'Label' , 'video_title','holding_percentage'
	UNION ALL SELECT `id` , `adjustmentType` , `day` , `country` , `videoID` , `channelID` , `assetID` , `assetChannelID` , `assetType` , `cutsomID` , `contentType` , `policy` , `claimType` , `claimOrigin` , `ownedViews` , `youtubeRevenueSplitAuction` , `youtubeRevenueSplitReserved` ,`youtubeRevenueSplitPartnerSoldYoutubeServed` , `youtubeRevenueSplitPartnerSoldPartnerServed` , `youtubeRevenueSplit` ,`partnerRevenueAuction` , `partnerRevenueReserved` , `partnerRevenuePartnerSoldYouTubeServed` ,`partnerRevenuePartnerSoldPartnerServed` , `partnerRevenue` , COALESCE(content_owner, '')  , COALESCE(Label, '')   , `video_title` ,holding_percentage FROM {$table}   where ({$table}.content_owner IS NULL  or {$table}.content_owner ='' ) group by {$table}.assetID INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $insertTableQuery);
    @chmod("polo_export.txt", 0777);

    $insertTableQueryResult = runQuery($insertTableQuery, $conn);

    die();
    /*
$gettotalcountquery = "SELECT count(id) as totalcount FROM $table where   (content_owner IS NULL  || content_owner ='')";
$gettotalcountquery = runQuery($gettotalcountquery, $conn);
if (!noError($gettotalcountquery)) {
return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
}
$gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
$gettotalcounts = $gettotalcounts['totalcount'];

$youtubereport = "SELECT  video_id,channel_id,video_title,asset_id ,Label  FROM $table   where (content_owner IS NULL  || content_owner ='')   ";

$youtubereportresult = runQuery($youtubereport, $conn);
$cdata  = [];
while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {

$res['data'][] =  [
'video_id'=>$row2['video_id'],
'channel_id'=>$row2['channel_id'],
'asset_id'=>$row2['asset_id'],
'video_title'=>$row2['video_title'],
'Label'=>$row2['Label'],

];

}

$res['total']=$gettotalcounts;

return setErrorStack($returnArr, -1, $res, null);
 */
}


function ExportActivateCommonReportv3(
    $table_type_name,
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $gettotalcounts = 0;
    $res = array();
    $returnArr = array();
    $whereClause = "";

    $gettotalcountquery = "select  count('') as totalcount  from {$table_type_name}	";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

    $row = [];
    //$youtubereport = "SELECT   main.*,gst_per from {$table_type_name} as main , crep_cms_clients ccc WHERE main.content_owner=ccc.client_username";
    $youtubereport = "SELECT   * from {$table_type_name}";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        $youtubereport .= ' and video_title like "' . $search . '%"  ';
    }
   
    // echo "<br>sql :::::  ". $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
          //  $final_payable = $row2["final_payable"];
          //  $gst_percentage = $row2["gst_percentage"];
         //   $final_payable_wth_gst = $final_payable + ($final_payable * $gst_percentage /100);
          //  $final_payable_wth_gst = number_format($final_payable_wth_gst,2,'.','');
          
        $res['data'][] = [
            'content_owner' => isset($row2['content_owner']) ? $row2['content_owner'] : 'N/A',
            'total_amt_recd' => isset($row2['total_amt_recd']) ? $row2['total_amt_recd'] : 'N/A',
            'shares' => isset($row2['shares']) ? $row2['shares'] : 'N/A',
            'amt_payable' => isset($row2['amt_payable']) ? $row2['amt_payable'] : 'N/A',
            'us_payout' => isset($row2['us_payout']) ? $row2['us_payout'] : 'N/A',
            'holding_percentage' => isset($row2['holding_percentage']) ? $row2['holding_percentage'] : '0',
            'witholding' => isset($row2['witholding']) ? $row2['witholding'] : 'N/A',
            'final_payable' => isset($row2['final_payable']) ? $row2['final_payable'] : 'N/A',
            'gst_percentage' => isset($row2['gst_percentage']) ? $row2['gst_percentage'] : '0',
            'final_payable_with_gst' => isset($row2['final_payable_with_gst']) ? $row2['final_payable_with_gst'] : '0',
            'status' => isset($row2['status']) ? $row2['status'] : 'N/A',
        ];
    }

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}


function ExportActivateCommonReportv10(
    $table_type_name,
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $gettotalcounts = 0;
    $res = array();
    $returnArr = array();
    $whereClause = "";

    $gettotalcountquery = "select  count('') as totalcount  from {$table_type_name}	";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

    $row = [];
    //$youtubereport = "SELECT   main.*,gst_per from {$table_type_name} as main , crep_cms_clients ccc WHERE main.content_owner=ccc.client_username";
    $youtubereport = "SELECT   * from {$table_type_name}";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        $youtubereport .= ' and video_title like "' . $search . '%"  ';
    }
   
    // echo "<br>sql :::::  ". $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
          //  $final_payable = $row2["final_payable"];
          //  $gst_percentage = $row2["gst_percentage"];
         //   $final_payable_wth_gst = $final_payable + ($final_payable * $gst_percentage /100);
          //  $final_payable_wth_gst = number_format($final_payable_wth_gst,2,'.','');
          
        $res['data'][] = [
            'content_owner' => isset($row2['content_owner']) ? $row2['content_owner'] : 'N/A',
            'total_amt_recd' => isset($row2['total_amt_recd']) ? $row2['total_amt_recd'] : 'N/A',
            'shares' => isset($row2['shares']) ? $row2['shares'] : 'N/A',
            'amt_payable' => isset($row2['amt_payable']) ? $row2['amt_payable'] : 'N/A',
            'us_payout' => isset($row2['us_payout']) ? $row2['us_payout'] : 'N/A',
            'us_payout_youtube' => '',
            'holding_percentage' => isset($row2['holding_percentage']) ? $row2['holding_percentage'] : '0',
            'witholding' => isset($row2['witholding']) ? $row2['witholding'] : 'N/A',
            'witholding_youtube' => '',
            'final_payable' => isset($row2['final_payable']) ? $row2['final_payable'] : 'N/A',
            'gst_percentage' => isset($row2['gst_percentage']) ? $row2['gst_percentage'] : '0',
            'final_payable_with_gst' => isset($row2['final_payable_with_gst']) ? $row2['final_payable_with_gst'] : '0',
            'status' => isset($row2['status']) ? $row2['status'] : 'N/A',
        ];
    }

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}


function ExportWhpreportv10(
    $table_type_name,
    $conn,
    $client = null,
    $offset = null,
    $resultsPerPage = 10
    
) {
    $gettotalcounts = 0;
    $res = array();
    $returnArr = array();
    $whereClause = "";

    $gettotalcountquery = "select  count('') as totalcount  from {$table_type_name}	";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

    $row = [];
    //$youtubereport = "SELECT   main.*,gst_per from {$table_type_name} as main , crep_cms_clients ccc WHERE main.content_owner=ccc.client_username";
    $youtubereport = "SELECT   * from {$table_type_name} where content_owner ='{$client }'";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

  
    // echo "<br>sql :::::  ". $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
          //  $final_payable = $row2["final_payable"];
          //  $gst_percentage = $row2["gst_percentage"];
         //   $final_payable_wth_gst = $final_payable + ($final_payable * $gst_percentage /100);
          //  $final_payable_wth_gst = number_format($final_payable_wth_gst,2,'.','');
          
        $res['data'][] = [
            
            'AdSense_Earnings_Month' => isset($row2['AdSense_Earnings_Month']) ? $row2['AdSense_Earnings_Month'] : 'N/A',
            'Channel_ID' => isset($row2['Channel_ID']) ? $row2['Channel_ID'] : 'N/A',
            'Revenue_Source' => isset($row2['Revenue_Source']) ? $row2['Revenue_Source'] : 'N/A',
            'Local_Currency' => isset($row2['Local_Currency']) ? $row2['Local_Currency'] : 'N/A',
            'Total_Channel_Revenue' => isset($row2['Total_Channel_Revenue']) ? $row2['Total_Channel_Revenue'] : 'N/A',
            'US_Sourced_Revenue' => isset($row2['US_Sourced_Revenue']) ? $row2['US_Sourced_Revenue'] : 'N/A',
            'Tax_Withholding_Rate' => isset($row2['Tax_Withholding_Rate']) ? $row2['Tax_Withholding_Rate'] : 'N/A',
            'Tax_Withheld_Amount' => isset($row2['Tax_Withheld_Amount']) ? $row2['Tax_Withheld_Amount'] : 'N/A',
            'content_owner' => isset($row2['content_owner']) ? $row2['content_owner'] : 'N/A',
            
        ];
    }

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}
function ExportActivateYoutubeFinanceReportv2(
    $table_type_name,
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $gettotalcounts = 0;
    $res = array();
    $returnArr = array();
    $whereClause = "";

    $gettotalcountquery = "select  count('') as totalcount  from {$table_type_name}	";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

    $row = [];
    //$youtubereport = "SELECT   main.*,gst_per from {$table_type_name} as main , crep_cms_clients ccc WHERE main.content_owner=ccc.client_username";
    $youtubereport = "SELECT   * from {$table_type_name}";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        $youtubereport .= ' and video_title like "' . $search . '%"  ';
    }
   
    // echo "<br>sql :::::  ". $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
            $final_payable = $row2["final_payable"];
            $gst_percentage = $row2["gst_percentage"];
            $final_payable_wth_gst = $final_payable + ($final_payable * $gst_percentage /100);
            $final_payable_wth_gst = number_format($final_payable_wth_gst,2,'.','');
        $res['data'][] = [
            'content_owner' => isset($row2['content_owner']) ? $row2['content_owner'] : 'N/A',
            'total_amt_recd' => isset($row2['total_amt_recd']) ? $row2['total_amt_recd'] : 'N/A',
            'shares' => isset($row2['shares']) ? $row2['shares'] : 'N/A',
            'amt_payable' => isset($row2['amt_payable']) ? $row2['amt_payable'] : 'N/A',
            'us_payout' => isset($row2['us_payout']) ? $row2['us_payout'] : 'N/A',
            'holding_percentage' => isset($row2['holding_percentage']) ? $row2['holding_percentage'] : '0',
            'witholding' => isset($row2['witholding']) ? $row2['witholding'] : 'N/A',
            'final_payable' => isset($row2['final_payable']) ? $row2['final_payable'] : 'N/A',
            'gst_percentage' => isset($row2['gst_percentage']) ? $row2['gst_percentage'] : '0',
            'final_payable_with_gst' => isset($final_payable_wth_gst) ? $final_payable_wth_gst : 'N/A',
            'status' => isset($row2['status']) ? $row2['status'] : 'N/A',
        ];
    }

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}

function ExportActivate_youtube_ecommerce_paid_featuresv2(
    $table_type_name,
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $gettotalcounts = 0;
    $res = array();
    $returnArr = array();
    $whereClause = "";

    $gettotalcountquery = "select  count('') as totalcount  from {$table_type_name}	";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

    $row = [];
  //  $youtubereport = "SELECT   * from {$table_type_name}";
    $youtubereport = "SELECT   main.*,gst_per from {$table_type_name} as main , crep_cms_clients ccc WHERE main.content_owner=ccc.client_username";
    
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    // echo "<br>sql :::::  ". $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
        $final_payable = $row2["final_payable"];
        $gst_per = $row2["gst_per"];
        $final_payable_wth_gst = $final_payable + ($final_payable * $gst_per /100);

        $res['data'][] = [
            'content_owner' => isset($row2['content_owner']) ? $row2['content_owner'] : 'N/A',
            'total_amt_recd' => isset($row2['total_amt_recd']) ? $row2['total_amt_recd'] : 'N/A',
            'shares' => isset($row2['shares']) ? $row2['shares'] : 'N/A',
            'amt_payable' => isset($row2['amt_payable']) ? $row2['amt_payable'] : 'N/A',
            'us_payout' => isset($row2['us_payout']) ? $row2['us_payout'] : 'N/A',
            'witholding' => isset($row2['witholding']) ? $row2['witholding'] : 'N/A',
            'final_payable' => isset($row2['final_payable']) ? $row2['final_payable'] : 'N/A',
            'gst_per' => isset($row2['gst_per']) ? $row2['gst_per'] : '0',
            'final_payable_with_gst' => isset($final_payable_wth_gst) ? $final_payable_wth_gst : 'N/A',
            'status' => isset($row2['status']) ? $row2['status'] : 'N/A',
        ];
    }

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}

function ExportActivate_youtube_red_music_video_financev2(
    $table_type_name,
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $gettotalcounts = 0;
    $res = array();
    $returnArr = array();
    $whereClause = "";

    $gettotalcountquery = "select  count('') as totalcount  from {$table_type_name}	";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

    $row = [];
    //$youtubereport = "SELECT   * from {$table_type_name}";
    $youtubereport = "SELECT   main.*,gst_per from {$table_type_name} as main , crep_cms_clients ccc WHERE main.content_owner=ccc.client_username";
    

    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    // echo "<br>sql :::::  ". $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
        $final_payable = $row2["final_payable"];
        $gst_per = $row2["gst_per"];
        $final_payable_wth_gst = $final_payable + ($final_payable * $gst_per /100);

        $res['data'][] = [
            'content_owner' => isset($row2['content_owner']) ? $row2['content_owner'] : 'N/A',
            'total_amt_recd' => isset($row2['total_amt_recd']) ? $row2['total_amt_recd'] : 'N/A',
            'shares' => isset($row2['shares']) ? $row2['shares'] : 'N/A',
            'amt_payable' => isset($row2['amt_payable']) ? $row2['amt_payable'] : 'N/A',
            'us_payout' => isset($row2['us_payout']) ? $row2['us_payout'] : 'N/A',
            'witholding' => isset($row2['witholding']) ? $row2['witholding'] : 'N/A',
            'final_payable' => isset($row2['final_payable']) ? $row2['final_payable'] : 'N/A',
            'gst_per' => isset($row2['gst_per']) ? $row2['gst_per'] : '0',
            'final_payable_with_gst' => isset($final_payable_wth_gst) ? $final_payable_wth_gst : 'N/A',
            'status' => isset($row2['status']) ? $row2['status'] : 'N/A',
        ];
    }

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}

function ExportActivate_report_audio_activationv2(
    $table_type_name,
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $gettotalcounts = 0;
    $res = array();
    $returnArr = array();
    $whereClause = "";

    $gettotalcountquery = "select  count('') as totalcount  from {$table_type_name}	";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

    $row = [];
//    $youtubereport = "SELECT   * from {$table_type_name}";
    $youtubereport = "SELECT   main.*,gst_per from {$table_type_name} as main , crep_cms_clients ccc WHERE main.content_owner=ccc.client_username";
    

    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    @unlink("polo_ExportActivate.txt");
    file_put_contents("polo_ExportActivate.txt", $youtubereport);
    @chmod("polo_ExportActivate.txt", 0777);

    // echo "<br>sql :::::  ". $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
        $final_payable = $row2["final_payable"];
        $gst_per = $row2["gst_per"];
        $final_payable_wth_gst = $final_payable + ($final_payable * $gst_per /100);

        $res['data'][] = [
            'content_owner' => isset($row2['content_owner']) ? $row2['content_owner'] : 'N/A',
            'total_amt_recd' => isset($row2['total_amt_recd']) ? $row2['total_amt_recd'] : 'N/A',
            'shares' => isset($row2['shares']) ? $row2['shares'] : 'N/A',
            'amt_payable' => isset($row2['amt_payable']) ? $row2['amt_payable'] : 'N/A',
            'us_payout' => isset($row2['us_payout']) ? $row2['us_payout'] : 'N/A',
            'witholding' => isset($row2['witholding']) ? $row2['witholding'] : 'N/A',
            'final_payable' => isset($row2['final_payable']) ? $row2['final_payable'] : 'N/A',
            'gst_per' => isset($row2['gst_per']) ? $row2['gst_per'] : '0',
            'final_payable_with_gst' => isset($final_payable_wth_gst) ? $final_payable_wth_gst : 'N/A',
            'status' => isset($row2['status']) ? $row2['status'] : 'N/A',
        ];
    }

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}
function Exportyoutube_red_music_video_finance_reportv2(
    $table,

    $conn,
    $offset = null,
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
//SELECT T1.assetID,label,youtube_video_claim_report3_nd1_2020_12.asset_label,youtube_video_claim_report3_nd1_2020_12.asset_id FROM `youtube_video_claim_report_nd1_2020_12` as T1  INNER JOIN youtube_video_claim_report3_nd1_2020_12 ON T1.assetID=youtube_video_claim_report3_nd1_2020_12.asset_id  where T1.content_owner IS NULL  group by T1.assetID INTO OUTFILE '/var/lib/mysql-files/youtube_video_claim_report/missinglablesinv2.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n'
    //$sql = "INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/missinglablesinv2.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n'";
    //@unlink("/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv");

/*    
 $insertTableQuery = "SELECT   'adjustmentType', 'country', 'day', 'videoID', 'channelID', 'assetID',   'album', 'Label', 'claimType', 'contentType', 'ownedViews', 'MonetizedViewsAudio', 'MonetizedViewsAudioVisual', 'MonetizedViews', 'youtubeRevenueSplit', 'partnerRevenueProRata', 'partnerRevenuePerSubMin', 'partnerRevenue', 'content_owner' 
	UNION ALL SELECT `adjustmentType`, `country`, `day`, `videoID`, `channelID`, `assetID`,    `album`, `Label`, `claimType`, `contentType`, `ownedViews`, `MonetizedViewsAudio`, `MonetizedViewsAudioVisual`, `MonetizedViews`, `youtubeRevenueSplit`, `partnerRevenueProRata`, `partnerRevenuePerSubMin`, `partnerRevenue`, `content_owner`   FROM {$table}   where {$table}.content_owner IS NULL  group by {$table}.assetID INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";

    "adjustmentType","country","day","videoID","channelID","assetID","assetChannelID","asset_title","asset_labels","assetType","album","Label","claimType","contentType","ownedViews","MonetizedViewsAudio","MonetizedViewsAudioVisual","MonetizedViews","youtubeRevenueSplit","partnerRevenueProRata","partnerRevenuePerSubMin","partnerRevenue","content_owner"
 */

    $result = array_map('strrev', explode('_', strrev($table)));
    $nd_type = trim($result[2]);

        if($nd_type=="redmusic"){
            $insertTableQuery = "SELECT   'adjustmentType', 'country', 'day', 'videoID', 'channelID', 'assetID',   'album', 'Label', 'claimType', 'contentType', 'ownedViews', 'MonetizedViewsAudio', 'MonetizedViewsAudioVisual', 'MonetizedViews', 'youtubeRevenueSplit', 'partnerRevenueProRata', 'partnerRevenuePerSubMin', 'partnerRevenue', 'content_owner' 
            UNION ALL SELECT `adjustmentType`, `country`, `day`, `videoID`, `channelID`, `assetID`,    `album`, `Label`, `claimType`, `contentType`, `ownedViews`, `MonetizedViewsAudio`, `MonetizedViewsAudioVisual`, `MonetizedViews`, `youtubeRevenueSplit`, `partnerRevenueProRata`, `partnerRevenuePerSubMin`, `partnerRevenue`, COALESCE(content_owner, '')    FROM {$table}   where ({$table}.content_owner IS NULL  or {$table}.content_owner ='' )  group by {$table}.assetID INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";
        
        
            
        } else {
            $insertTableQuery = "SELECT   'id','adjustmentType','day','country','videoID','cutsomID','contentType','videoTitle','videoDuration','username','uploader','channelDisplayName','channelID','claimType','claimOrigin','multipleClaims','assetID','policy','ownedViews','youtubeRevenueSplit','partnerRevenue','content_owner','last_content_owner','Label'
            UNION ALL SELECT `id`,`adjustmentType`,`day`,`country`,`videoID`,`cutsomID`,`contentType`,`videoTitle`,`videoDuration`,`username`,`uploader`,`channelDisplayName`,`channelID`,`claimType`,`claimOrigin`,`multipleClaims`,`assetID`,`policy`,`ownedViews`,`youtubeRevenueSplit`,`partnerRevenue`,COALESCE(content_owner, '') ,`last_content_owner`,`Label`   FROM {$table}   where ({$table}.content_owner IS NULL  or {$table}.content_owner ='' )  group by {$table}.assetID INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";   
        }


        // @unlink("polo_export_ndtype.txt");
        // file_put_contents("polo_export_ndtype.txt", $nd_type);
        // @chmod("polo_export_ndtype.txt", 0777);
    
    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $insertTableQuery);
    @chmod("polo_export.txt", 0777);

    $insertTableQueryResult = runQuery($insertTableQuery, $conn);

    /*
    $gettotalcountquery = "SELECT count(id) as totalcount FROM $table where   (content_owner IS NULL  || content_owner ='')";
    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
    return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

    $youtubereport = "SELECT  video_id,channel_id,video_title,asset_id ,Label  FROM $table   where (content_owner IS NULL  || content_owner ='')   ";

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata  = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {

    $res['data'][] =  [
    'video_id'=>$row2['video_id'],
    'channel_id'=>$row2['channel_id'],
    'asset_id'=>$row2['asset_id'],
    'video_title'=>$row2['video_title'],
    'Label'=>$row2['Label'],

    ];

    }

    $res['total']=$gettotalcounts; */

    return setErrorStack($returnArr, -1, $res, null);
}
//=============red music ----

function getyoutube_red_music_video_finance_reportv3(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {$res = array();
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
            // $explode_table = explode("youtube_video_claim_report_",$v);
            //     $explode_table_1 = explode("_",$explode_table[1]);
            //     $table_name = $explode_table_1[0];

            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];
            $year_mnoth = $result[1]."_".$result[0];
            $nd_type = $result[2];

            //get share of co
            $rev_share = 30;
            $gst_percentage = 0;
            $holding_percentage = 0;
            $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	youtube_red_music_finance_activation_report_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
        
            $getshareres = runQuery($getshare, $conn);
            $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
        
            if (!empty($getshareresdata)) {
            
                $rev_share = $getshareresdata['shares'];
                $gst_percentage = $getshareresdata['gst_percentage'];
                $holding_percentage = $getshareresdata['holding_percentage'];
            }
            $findme = 'redmusic';
            $pos = strpos($v, $findme);

            if ($pos === false) {
                if (!empty($search)) {
                    $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}' and  channelID like '" . $search . "%'  ";
                    $union_final_query[] = " ( SELECT videoID,channelID,videoTitle,assetID,contentType,content_owner,Label, sum(partnerRevenue) as partnerRevenue,'{$table_name}'  as tablename , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage   FROM $v where  content_owner = '{$client}' and  channelID like '" . $search . "%'   group by videoID )  ";
                } else {
    
                    $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                    $union_final_query[] = " ( SELECT    videoID,channelID,videoTitle,assetID,contentType,content_owner,Label, sum(partnerRevenue) as partnerRevenue,'{$table_name}'  as tablename , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage   FROM $v where  content_owner = '{$client}'   group by videoID )  ";
                }
            } else {
                if (!empty($search)) {
                    $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}' and  channelID like '" . $search . "%'  ";
                    $union_final_query[] = " ( SELECT videoID,channelID,asset_title as videoTitle,assetID,contentType,content_owner,Label, sum(partnerRevenue) as partnerRevenue,'{$table_name}'  as tablename , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage   FROM $v where  content_owner = '{$client}' and  channelID like '" . $search . "%'   group by videoID )  ";
                } else {
    
                    $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                    $union_final_query[] = " ( SELECT    videoID,channelID,asset_title as videoTitle,assetID,contentType,content_owner,Label, sum(partnerRevenue) as partnerRevenue,'{$table_name}'  as tablename , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage   FROM $v where  content_owner = '{$client}'   group by videoID )  ";
                }
            }    
            
        }

    }

    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

    

    $row = [];
 
    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc, tablename desc";
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
        
        $finalpayable = (($row2['partnerRevenue'] * $rev_share) / 100);
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

        $res['data'][] = [
            'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
            'video_id' => isset($row2['videoID']) ? $row2['videoID'] : 'N/A',
            'video_title' => isset($row2['videoTitle']) ? $row2['videoTitle'] : 'N/A',
            'youtube_payout' => $row2['partnerRevenue'],
            'share' => $rev_share,
            'finalpayable' => (($row2['partnerRevenue'] * $rev_share) / 100),
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,
            'tablename' => isset($row2['tablename']) ? $row2['tablename'] : 'N/A',
        ];
    }

    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}
function getyoutube_red_music_video_finance_reportv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {$res = array();
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
            // $explode_table = explode("youtube_video_claim_report_",$v);
            //     $explode_table_1 = explode("_",$explode_table[1]);
            //     $table_name = $explode_table_1[0];

            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];

            if (!empty($search)) {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}' and  channelID like '" . $search . "%'  ";
                $union_final_query[] = " ( SELECT videoID,channelID,videoTitle,assetID,contentType,content_owner,Label, sum(partnerRevenue) as partnerRevenue,'{$table_name}'  as tablename FROM $v where  content_owner = '{$client}' and  channelID like '" . $search . "%'   group by videoID )  ";
            } else {

                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                $union_final_query[] = " ( SELECT    videoID,channelID,videoTitle,assetID,contentType,content_owner,Label, sum(partnerRevenue) as partnerRevenue,'{$table_name}'  as tablename FROM $v where  content_owner = '{$client}'   group by videoID )  ";
            }
        }

    }

    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube,gst_per   FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }

    $row = [];

    $table2 = $all_table[0];

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc, tablename desc";
    //file_put_contents("tablequery.txt",$youtubereport);
    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    // echo $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
        $finalpayable = (($row2['partnerRevenue'] * $rev_share) / 100);
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);
        $res['data'][] = [
            'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
            'video_id' => isset($row2['videoID']) ? $row2['videoID'] : 'N/A',
            'video_title' => isset($row2['videoTitle']) ? $row2['videoTitle'] : 'N/A',
            'youtube_payout' => $row2['partnerRevenue'],
            'finalpayable' => (($row2['partnerRevenue'] * $rev_share) / 100),
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,
            'tablename' => isset($row2['tablename']) ? $row2['tablename'] : 'N/A',
        ];
    }

    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}
function RevenueChartyoutuberedmusicvideofinancev2(
    $table,
    $table2,
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

    $getAllrevenueQuery = "SELECT DATE(day) as day,SUM(partnerRevenue) as partnerRevenue,SUM(if(contentType='UGC',partnerRevenue,0)) as UGC,SUM(if(contentType='Partner-provided',partnerRevenue,0)) as Partnerprovided FROM $table where content_owner = '" . $client . "' ";

    if ($channelid !== null) {
        $getAllrevenueQuery .= " AND channelID = '{$channelid}'";
    }

    $getAllrevenueQuery .= "  group by day ORDER by day";

    if ($offset !== null) {
        $getAllrevenueQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    $getAllrevenueQueryResult = runQuery($getAllrevenueQuery, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {

        //   $year =  (int) substr($row['day'],0,4);
        //   $month =(int)  substr($row['day'],4,2);
        //   $day = (int) substr($row['day'],6,2);

        $d = explode('-', $row['day']);
        $year = (int) $d[0];
        $month = (int) $d[1] - 1;
        $day = (int) $d[2];

        $res[] = array('c' => array(
            array('v' => "Date($year, $month, $day)"),
            array('v' => $row['partnerRevenue']),
            array('v' => $row['Partnerprovided']),
            array('v' => $row['UGC']),
        ));
    }

    return setErrorStack($returnArr, -1, $res, null);
}
function ViewsChartyoutuberedmusicvideofinancev2(
    $table,
    $table2,
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

    $getAllrevenueQuery = "SELECT DATE(day) as day,SUM(ownedViews) as views,SUM(if(contentType='UGC',ownedViews,0)) as UGC,SUM(if(contentType='Partner-provided',ownedViews,0)) as Partnerprovided FROM $table where content_owner = '" . $client . "' ";
    if ($channelid !== null) {
        $getAllrevenueQuery .= " AND channelID = '{$channelid}'";
    }

    $getAllrevenueQuery .= "  group by day ORDER by day";

    if ($offset !== null) {
        $getAllrevenueQuery .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    $getAllrevenueQueryResult = runQuery($getAllrevenueQuery, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        //$d= explode('-',$row['day']);
        $d = explode('-', $row['day']);
        $year = (int) $d[0];
        $month = (int) $d[1] - 1;
        $day = (int) $d[2];

        // $year =  (int) substr($row['day'],0,4);
        //   $month =(int)  substr($row['day'],4,2);
        //      $day = (int) substr($row['day'],6,2);

        $res[] = array('c' => array(
            array('v' => "Date($year, $month, $day)"),
            array('v' => $row['views']),
            array('v' => $row['Partnerprovided']),
            array('v' => $row['UGC']),
        ));
    }

    return setErrorStack($returnArr, -1, $res, null);
}
function ExportClientsYoutuberedMusicre_finance_reportCountv3(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $downlaodType='normal'
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "SELECT COUNT('') as totalcount from 	 ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];

    $flag_redmusic = 0;
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
                $gst_percentage = 0;
                $holding_percentage = 0;
                $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	youtube_red_music_finance_activation_report_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
            
                
                  
                  $getshareres = runQuery($getshare, $conn);
  
                  if (!noError($getshareres)) {
                      //  @unlink("polo_export_getshare.txt");
                      $ddd = "ExportClientsYoutuberedMusicre_finance_reportv3 Error_in_query: ".$client.PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                      file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                      @chmod("polo_export_getshare.txt", 0777);
      
                      // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
                  }   
                  else {
                    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
            
                        if (!empty($getshareresdata)) {
                        
                            $rev_share = $getshareresdata['shares'];
                            $gst_percentage = $getshareresdata['gst_percentage'];
                            $holding_percentage = $getshareresdata['holding_percentage'];
                        }
                }

                 

            $findme = 'redmusic';
            $pos = strpos($v, $findme);
            if($downlaodType == "withholding"){
                if ($pos === false) {
                
                    $union_final_query[] = " ( SELECT country,contentType,videoID, channelID, assetID,videoTitle,channelDisplayName, Coalesce(sum(partnerRevenue),0) as partnerRevenue, Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
                    Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END)* holding_percentage/100),0) as WITHHOLDING  ,'{$table_name}' as table_name , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, holding_percentage   FROM $v where  content_owner = '{$client}'   group by {$v}.channelID ,{$v}.holding_percentage )   ";
    
                } else {
                    $flag_redmusic =1;
                  
                    $union_final_query[] = " ( SELECT country,contentType,videoID,channelID, assetID,asset_title,asset_labels, Coalesce(sum(partnerRevenue),0) as partnerRevenue, Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
                    Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END)* holding_percentage/100),0) as WITHHOLDING  ,'{$table_name}' as table_name , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, holding_percentage  FROM $v where  content_owner = '{$client}'  group by assetChannelID ,{$v}.holding_percentage  )   ";
                }

            } else {
                if ($pos === false) {
                
                    $union_final_query[] = " ( SELECT country,contentType,videoID, channelID, assetID,videoTitle,channelDisplayName, Coalesce(sum(partnerRevenue),0) as partnerRevenue, Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
                    Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END)* holding_percentage/100),0) as WITHHOLDING  ,'{$table_name}' as table_name , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, holding_percentage   FROM $v where  content_owner = '{$client}'  group by {$v}.videoID  )   ";
    
                } else {
                    $flag_redmusic =1;
                  
                    $union_final_query[] = " ( SELECT country,contentType,videoID,channelID, assetID,asset_title,asset_labels, Coalesce(sum(partnerRevenue),0) as partnerRevenue, Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
                    Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END)* holding_percentage/100),0) as WITHHOLDING  ,'{$table_name}' as table_name , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, holding_percentage  FROM $v where  content_owner = '{$client}'  group by {$v}.videoID )   ";
                }
            }
         

        }

    }
 
  
   

    $row = [];
 

    $check_query_list_new = implode(" union  ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc";

  

    // echo "<br>sql :::::  ". $youtubereport;
    @unlink("polo_export_count.txt");
    file_put_contents("polo_export_count.txt", $youtubereport);
    @chmod("polo_export_count.txt", 0777);

    $gettotalcountquery = runQuery($youtubereport, $conn);

    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];
 
   

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}
function ExportClientsYoutuberedMusicre_finance_reportv3(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $downlaodType = 'normal'
) {
    $gettotalcounts = 0;
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

    $flag_redmusic = 0;
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
                $gst_percentage = 0;
                $holding_percentage = 0;
                $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	youtube_red_music_finance_activation_report_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
            
                
                  
                  $getshareres = runQuery($getshare, $conn);
  
                  if (!noError($getshareres)) {
                      //  @unlink("polo_export_getshare.txt");
                      $ddd = "ExportClientsYoutuberedMusicre_finance_reportv3 Error_in_query: ".$client.PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                      file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                      @chmod("polo_export_getshare.txt", 0777);
      
                      // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
                  }   
                  else {
                    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
            
                        if (!empty($getshareresdata)) {
                        
                            $rev_share = $getshareresdata['shares'];
                            $gst_percentage = $getshareresdata['gst_percentage'];
                            $holding_percentage = $getshareresdata['holding_percentage'];
                        }
                }

                 

            $findme = 'redmusic';
            $pos = strpos($v, $findme);

            if($downlaodType == "withholding"){

                if ($pos === false) {
                
                    $union_final_query[] = " ( SELECT country,contentType,videoID, channelID, assetID,videoTitle,channelDisplayName, Coalesce(sum(partnerRevenue),0) as partnerRevenue, Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
                    Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END)* holding_percentage/100),0) as WITHHOLDING  ,'{$table_name}' as table_name , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, holding_percentage   FROM $v where  content_owner = '{$client}'  group   by {$v}.channelID ,{$v}.holding_percentage)   ";
    
                } else {
                    $flag_redmusic =1;
                  
                    $union_final_query[] = " ( SELECT assetChannelID,country,contentType,videoID,channelID, assetID,asset_title,asset_labels, Coalesce(sum(partnerRevenue),0) as partnerRevenue, Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
                    Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END)* holding_percentage/100),0) as WITHHOLDING  ,'{$table_name}' as table_name , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, holding_percentage  FROM $v where  content_owner = '{$client}'  group   by {$v}.assetChannelID ,{$v}.holding_percentage)   ";
                }
                
            } else {
                if ($pos === false) {
                
                    $union_final_query[] = " ( SELECT country,contentType,videoID, channelID, assetID,videoTitle,channelDisplayName, Coalesce(sum(partnerRevenue),0) as partnerRevenue, Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
                    Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END)* holding_percentage/100),0) as WITHHOLDING  ,'{$table_name}' as table_name , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, holding_percentage   FROM $v where  content_owner = '{$client}'  group   by {$v}.videoID,{$v}.holding_percentage,{$v}.channelID  )   ";
    
                } else {
                    $flag_redmusic =1;
                  
                    $union_final_query[] = " ( SELECT assetChannelID, country,contentType,videoID,channelID, assetID,asset_title,asset_labels, Coalesce(sum(partnerRevenue),0) as partnerRevenue, Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
                    Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END)* holding_percentage/100),0) as WITHHOLDING  ,'{$table_name}' as table_name , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, holding_percentage  FROM $v where  content_owner = '{$client}'  group by {$v}.videoID,{$v}.holding_percentage,{$v}.assetChannelID )   ";
                }
            }
           

        }

    }
 
  
   

    $row = [];
 

    $check_query_list_new = implode(" union  ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc";

 
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    // echo "<br>sql :::::  ". $youtubereport;
    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $youtubereport);
    @chmod("polo_export.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    if($flag_redmusic==1){

        while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
            
            $rev_share = $row2['rev_share'] + 0;
            $gst_per  = $row2['gst_percentage'] + 0;

            $finalpayable =  ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0;
            $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

            if($downlaodType == "withholding"){
                $res['data'][] = [
                    'assetChannelID' => isset($row2['assetChannelID']) ? $row2['assetChannelID'] : 'N/A',
                    'youtube_payout' => $row2['partnerRevenue'] + 0,
                    'rev_share' => $rev_share + 0,
                    'USPAYOUT' => $row2['USPAYOUT'] + 0,
                    'holding_percentage' => ($row2['USPAYOUT'] > 0) ? $row2['holding_percentage'] + 0 : 0,
                    'WITHHOLDING' => $row2['WITHHOLDING'] + 0,
                    'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0,
                    'gst_per' => $gst_per,
                    'finalpayable_gst' => $final_payable_wth_gst,
                    'table_name' => isset($row2['tablename']) ? $row2['tablename'] : 'N/A',
                ];  
            } else {
                $res['data'][] = [
                    'country' => isset($row2['country']) ? $row2['country'] : 'N/A',
                    'contentType' => isset($row2['contentType']) ? $row2['contentType'] : 'N/A',
                     'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
                     'videoID' => isset($row2['videoID']) ? $row2['videoID'] : 'N/A',
                     'assetID' => isset($row2['assetID']) ? $row2['assetID'] : 'N/A',
                    //  'asset_title' => isset($row2['asset_title']) ? substr(trim($row2['asset_title']),0,1) : 'N/A',
                      'asset_labels' => isset($row2['asset_labels']) ? $row2['asset_labels'] : 'N/A',
                     'youtube_payout' => $row2['partnerRevenue'] + 0,
                     'rev_share' => $rev_share + 0,
                    'USPAYOUT' => $row2['USPAYOUT'] + 0,
                     // 'holding_percentage' => $row2['holding_percentage'] + 0,
                     'WITHHOLDING' => $row2['WITHHOLDING'] + 0,
                     'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0,
                     'gst_per' => $gst_per,
                     'finalpayable_gst' => $final_payable_wth_gst,
                     'table_name' => isset($row2['table_name']) ? $row2['table_name'] : 'N/A',
                    
                ];

            }

           
        }

    } else {

      
        while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
            // $row2['payout']=number_format($r['partnerRevenue'],2);
            // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
            //country,contentType,channelID,videoID
            $rev_share = $row2['rev_share'] + 0;
            $gst_per  = $row2['gst_percentage'] + 0;

            $finalpayable =  ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0;
            $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

            if($downlaodType == "withholding"){
                $res['data'][] = [
                    'assetChannelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
                    'youtube_payout' => $row2['partnerRevenue'] + 0,
                    'rev_share' => $rev_share + 0,
                    'USPAYOUT' => $row2['USPAYOUT'] + 0,
                    'holding_percentage' => ($row2['USPAYOUT'] > 0) ? $row2['holding_percentage'] + 0 : 0,
                    'WITHHOLDING' => $row2['WITHHOLDING'] + 0,
                    'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0,
                    'gst_per' => $gst_per,
                    'finalpayable_gst' => $final_payable_wth_gst,
                    'table_name' => isset($row2['tablename']) ? $row2['tablename'] : 'N/A',
                ];  
            } else {
                $res['data'][] = [
                    'country' => isset($row2['country']) ? $row2['country'] : 'N/A',
                    'contentType' => isset($row2['contentType']) ? $row2['contentType'] : 'N/A',
                    'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
                    'videoID' => isset($row2['videoID']) ? $row2['videoID'] : 'N/A',
                    'videoTitle' => isset($row2['videoTitle']) ? $row2['videoTitle'] : 'N/A',
                    'assetID' => isset($row2['assetID']) ? $row2['assetID'] : 'N/A',
                    'youtube_payout' => $row2['partnerRevenue'] + 0,
                    'rev_share' => $rev_share + 0,
                    'USPAYOUT' => $row2['USPAYOUT'] + 0,
                //    'holding_percentage' => $row2['holding_percentage'] + 0,
                    'WITHHOLDING' => $row2['WITHHOLDING'] + 0,
                    'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0,
                    'gst_per' => $gst_per,
                    'finalpayable_gst' => $final_payable_wth_gst,
                    'table_name' => isset($row2['table_name']) ? $row2['table_name'] : 'N/A',
                    
                ];
            }
           
        }

    }
    

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}
function ExportClientsYoutuberedMusicre_finance_reportv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $gettotalcounts = 0;
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

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
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

    $flag_redmusic = 0;
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];

            $findme = 'redmusic';
            $pos = strpos($v, $findme);

            if ($pos === false) {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                $union_final_query[] = " ( SELECT country,contentType,videoID, channelID, assetID,videoTitle,channelDisplayName, Coalesce(sum(partnerRevenue),0) as partnerRevenue, Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
                Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END)*30/100),0) as WITHHOLDING  ,'{$table_name}' as table_name FROM $v where  content_owner = '{$client}'  group by videoID )   ";

            } else {
                $flag_redmusic =1;
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                $union_final_query[] = " ( SELECT country,contentType,videoID,channelID, assetID,asset_title,asset_labels, Coalesce(sum(partnerRevenue),0) as partnerRevenue, Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
                Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END)*30/100),0) as WITHHOLDING  ,'{$table_name}' as table_name FROM $v where  content_owner = '{$client}'  group by videoID )   ";
            }

        }

    }
/*
 $union_final_query[] = " ( SELECT    videoID,channelID,videoTitle,assetID,contentType,content_owner,Label, sum(partnerRevenue) as partnerRevenue,'{$table_name}'  as tablename , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage   FROM $v where  content_owner = '{$client}'   group by videoID )  ";

*/
    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    // echo "<br>sql :::::  ". $youtubereport;
    @unlink("polo_export_count.txt");
    file_put_contents("polo_export_count.txt", $gettotalcountquery);
    @chmod("polo_export_count.txt", 0777);

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube ,gst_per FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }

    $row = [];

    $table2 = $all_table[0];

    $check_query_list_new = implode(" union  ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc";

    // $youtubereport = "SELECT  videoID,channelID,videoTitle,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    // echo "<br>sql :::::  ". $youtubereport;
    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $youtubereport);
    @chmod("polo_export.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    if($flag_redmusic==1){

        while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
            // $row2['payout']=number_format($r['partnerRevenue'],2);
            // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
            //country,contentType,channelID,videoID
            $finalpayable =  ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0;
            $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);


            $res['data'][] = [
                'country' => isset($row2['country']) ? $row2['country'] : 'N/A',
                'contentType' => isset($row2['contentType']) ? $row2['contentType'] : 'N/A',
                'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
                'videoID' => isset($row2['videoID']) ? $row2['videoID'] : 'N/A',
                'assetID' => isset($row2['assetID']) ? $row2['assetID'] : 'N/A',
                'asset_title' => isset($row2['asset_title']) ? $row2['asset_title'] : 'N/A',
                'asset_labels' => isset($row2['asset_labels']) ? $row2['asset_labels'] : 'N/A',
                'youtube_payout' => $row2['partnerRevenue'] + 0,
                'rev_share' => $rev_share + 0,
                'USPAYOUT' => $row2['USPAYOUT'] + 0,
                'WITHHOLDING' => $row2['WITHHOLDING'] + 0,
                'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0,
                'gst_per' => $gst_per,
                'finalpayable_gst' => $final_payable_wth_gst,
                'table_name' => isset($row2['table_name']) ? $row2['table_name'] : 'N/A',
                
            ];
        }

    } else {

        while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
            // $row2['payout']=number_format($r['partnerRevenue'],2);
            // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
            //country,contentType,channelID,videoID
            $res['data'][] = [
                'country' => isset($row2['country']) ? $row2['country'] : 'N/A',
                'contentType' => isset($row2['contentType']) ? $row2['contentType'] : 'N/A',
                'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
                'videoID' => isset($row2['videoID']) ? $row2['videoID'] : 'N/A',
                'videoTitle' => isset($row2['videoTitle']) ? $row2['videoTitle'] : 'N/A',
                'assetID' => isset($row2['assetID']) ? $row2['assetID'] : 'N/A',
                'youtube_payout' => $row2['partnerRevenue'] + 0,
                'rev_share' => $rev_share + 0,
                'USPAYOUT' => $row2['USPAYOUT'] + 0,
                'WITHHOLDING' => $row2['WITHHOLDING'] + 0,
                'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0,
                'table_name' => isset($row2['table_name']) ? $row2['table_name'] : 'N/A',
            ];
        }

    }
    

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}
///======================= youtube ecom aid features

function ExportClientsYoutubeecompaidfeaturereport_countv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $downlaodType = 'normal'
) {
    $gettotalcounts = 0;
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

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube ,gst_per FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per  =0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
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
    $union_final_query_sql = "select  *   from 	( ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $explode_table = explode("youtube_ecommerce_paid_features_report_", $v);
            $explode_table_1 = explode("_", $explode_table[1]);
            $table_name = $explode_table_1[0];

            $result = array_map('strrev', explode('_', strrev($v)));
            $year_mnoth = $result[1]."_".$result[0];
            $nd_type = $result[2];

             //get share of co
                $rev_share = 30;
                $gst_percentage = 0;
                $holding_percentage = 0;
                $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	youtube_ecom_paid_features_activation_report_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
            
                $getshareres = runQuery($getshare, $conn);
                if (!noError($getshareres)) {
                    //  @unlink("polo_export_getshare.txt");
                    $ddd = "ExportClientsYoutubeecompaidfeaturereportv2 Error_in_query: ".$client.PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                    file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                    @chmod("polo_export_getshare.txt", 0777);
    
                    // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
                }   
                else {
                  $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
          
                      if (!empty($getshareresdata)) {
                      
                          $rev_share = $getshareresdata['shares'];
                          $gst_percentage = $getshareresdata['gst_percentage'];
                          $holding_percentage = $getshareresdata['holding_percentage'];
                      }
              }

               


            $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";

            $union_final_query[] = " ( SELECT purchase_type,channel_name,country,channel_id,video_id, earnings as partnerRevenue, Coalesce((CASE WHEN country='US' THEN earnings END),0) as USPAYOUT,
			Coalesce(((CASE WHEN country='US' THEN earnings END)* holding_percentage /100),0) as WITHHOLDING ,'{$table_name}' as table_name , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, holding_percentage FROM $v where  content_owner = '{$client}' ) ";
        }

    }

    $check_query_total_new = implode(" union all ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

 
    $row = [];

    $table2 = $all_table[0];

    $check_query_list_new = implode(" union all ", $union_final_query);
    $youtubereport = $union_final_query_sql . $check_query_list_new . " )  as list_all order by partnerRevenue desc";

    // $youtubereport = "SELECT  videoID,channelID,videoTitle,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        $youtubereport .= ' and channel_id like "' . $search . '%"  ';
    }
    // echo "<br>sql :::::  ". $youtubereport;

    @unlink("polo_export_youtubeecommercepaidfeaturesv2.txt");
    file_put_contents("polo_export_youtubeecommercepaidfeaturesv2.txt", $youtubereport);
    @chmod("polo_export_youtubeecommercepaidfeaturesv2.txt", 0777);


    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        
        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;
        $finalpayable = ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0;
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

        $res['data'][] = [
            'country' => isset($row2['country']) ? $row2['country'] : 'N/A',
            'purchase_type' => isset($row2['purchase_type']) ? $row2['purchase_type'] : 'N/A',
            'channel_name' => isset($row2['channel_name']) ? $row2['channel_name'] : 'N/A',
            'channel_id' => isset($row2['channel_id']) ? $row2['channel_id'] : 'N/A',
            'video_id' => isset($row2['video_id']) ? $row2['video_id'] : 'N/A',
            'youtube_payout' => $row2['partnerRevenue'] + 0,
            'rev_share' => $rev_share + 0,
            'USPAYOUT' => $row2['USPAYOUT'] + 0,
            'holding_percentage' => $row2['holding_percentage'] + 0,
            'WITHHOLDING' => $row2['WITHHOLDING'] + 0,
            'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0,
            'table_name' => isset($row2['table_name']) ? $row2['table_name'] : 'N/A',
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,
        ];
    }

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}
function ExportClientsYoutubeecompaidfeaturereportv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $downlaodType = 'normal'
) {
    $gettotalcounts = 0;
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

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube ,gst_per FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per  =0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
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
    $union_final_query_sql = "select  *   from 	( ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $explode_table = explode("youtube_ecommerce_paid_features_report_", $v);
            $explode_table_1 = explode("_", $explode_table[1]);
            $table_name = $explode_table_1[0];

            $result = array_map('strrev', explode('_', strrev($v)));
            $year_mnoth = $result[1]."_".$result[0];
            $nd_type = $result[2];

             //get share of co
                $rev_share = 30;
                $gst_percentage = 0;
                $holding_percentage = 0;
                $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	youtube_ecom_paid_features_activation_report_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
            
                $getshareres = runQuery($getshare, $conn);
                if (!noError($getshareres)) {
                    //  @unlink("polo_export_getshare.txt");
                    $ddd = "ExportClientsYoutubeecompaidfeaturereportv2 Error_in_query: ".$client.PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                    file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                    @chmod("polo_export_getshare.txt", 0777);
    
                    // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
                }   
                else {
                  $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
          
                      if (!empty($getshareresdata)) {
                      
                          $rev_share = $getshareresdata['shares'];
                          $gst_percentage = $getshareresdata['gst_percentage'];
                          $holding_percentage = $getshareresdata['holding_percentage'];
                      }
              }

               
              if($downlaodType == "withholding"){
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";

                $union_final_query[] = " ( SELECT purchase_type,channel_name,country,channel_id,video_id, earnings as partnerRevenue, Coalesce((CASE WHEN country='US' THEN earnings END),0) as USPAYOUT,
                Coalesce(((CASE WHEN country='US' THEN earnings END)* holding_percentage /100),0) as WITHHOLDING ,'{$table_name}' as table_name , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, holding_percentage FROM $v where  content_owner = '{$client}' )  ";

              } else {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";

                $union_final_query[] = " ( SELECT purchase_type,channel_name,country,channel_id,video_id, earnings as partnerRevenue, Coalesce((CASE WHEN country='US' THEN earnings END),0) as USPAYOUT,
                Coalesce(((CASE WHEN country='US' THEN earnings END)* holding_percentage /100),0) as WITHHOLDING ,'{$table_name}' as table_name , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, holding_percentage FROM $v where  content_owner = '{$client}' ) ";
              }

           
        }

    }

    $check_query_total_new = implode(" union all ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

 
    $row = [];

    $table2 = $all_table[0];

    $check_query_list_new = implode(" union all ", $union_final_query);
    $youtubereport = $union_final_query_sql . $check_query_list_new . " )  as list_all order by partnerRevenue desc";

    // $youtubereport = "SELECT  videoID,channelID,videoTitle,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        $youtubereport .= ' and channel_id like "' . $search . '%"  ';
    }
    // echo "<br>sql :::::  ". $youtubereport;

    @unlink("polo_export_youtubeecommercepaidfeaturesv2.txt");
    file_put_contents("polo_export_youtubeecommercepaidfeaturesv2.txt", $youtubereport);
    @chmod("polo_export_youtubeecommercepaidfeaturesv2.txt", 0777);


    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        
        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;
        $finalpayable = ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0;
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);
        if($downlaodType == "withholding"){
            $res['data'][] = [
              
                'channel_id' => isset($row2['channel_id']) ? $row2['channel_id'] : 'N/A',
                'youtube_payout' => $row2['partnerRevenue'] + 0,
                'rev_share' => $rev_share + 0,
                'USPAYOUT' => $row2['USPAYOUT'] + 0,
                'holding_percentage' => ($row2['USPAYOUT'] > 0) ? $row2['holding_percentage'] + 0 : 0,
                'WITHHOLDING' => $row2['WITHHOLDING'] + 0,
                'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0,
                'gst_per' => $gst_per,
                'finalpayable_gst' => $final_payable_wth_gst,
                'table_name' => isset($row2['table_name']) ? $row2['table_name'] : 'N/A',
            ];
        } else {
            $res['data'][] = [
                'country' => isset($row2['country']) ? $row2['country'] : 'N/A',
                'purchase_type' => isset($row2['purchase_type']) ? $row2['purchase_type'] : 'N/A',
                'channel_name' => isset($row2['channel_name']) ? $row2['channel_name'] : 'N/A',
                'channel_id' => isset($row2['channel_id']) ? $row2['channel_id'] : 'N/A',
                'video_id' => isset($row2['video_id']) ? $row2['video_id'] : 'N/A',
                'youtube_payout' => $row2['partnerRevenue'] + 0,
                'rev_share' => $rev_share + 0,
                'USPAYOUT' => $row2['USPAYOUT'] + 0,
              //  'holding_percentage' => $row2['holding_percentage'] + 0,
                'WITHHOLDING' => $row2['WITHHOLDING'] + 0,
                'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0,
                'gst_per' => $gst_per,
                'finalpayable_gst' => $final_payable_wth_gst,
                'table_name' => isset($row2['table_name']) ? $row2['table_name'] : 'N/A',
            ];
        }
        
    }

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}

function getChannelIdYoutubeecommerce_paid_featuresv2(
    $table,
    $conn,
    $search = '',
    $client = null
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";

    $getClientInfoQuery = "SELECT DISTINCT channel_id as channelID FROM $table where content_owner = '" . $client . "' ";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {

        $res[] = $row;
    }

    return setErrorStack($returnArr, -1, $res, null);
}

function RevenueChartyoutube_ecompaidfeaturesv2(
    $table,
    $table2,
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

            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];

            if (!empty($channelid)) {
                $union_final_query[] = " ( SELECT DATE(dates) as day,SUM(earnings) as partnerRevenue  FROM $v where content_owner = '" . $client . "' and channel_id like '{$channelid}'  group by dates ORDER by dates  )  ";
            } else {
                $union_final_query[] = " ( SELECT DATE(dates) as day,SUM(earnings) as partnerRevenue  FROM $v where content_owner = '" . $client . "'   group by dates ORDER by dates  )  ";
            }

        }

    }

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by day asc";

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
        if ($row['day'] != "") {
            $d = explode('-', $row['day']);
            $year = (int) $d[0];
            $month = (int) $d[1] - 1;
            $day = (int) $d[2];

            $res[] = array('c' => array(
                array('v' => "Date($year, $month, $day)"),
                array('v' => $row['partnerRevenue']),

            ));
        }

    }

    return setErrorStack($returnArr, -1, $res, null);

}

function ViewsChartyoutube_ecompaidfeaturesv2(
    $table,
    $table2,
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
            $table_name = $result[2];
            if (!empty($channelid)) {
                $union_final_query[] = " ( SELECT DATE(dates) as day,count(dates) as views  FROM $v where content_owner = '" . $client . "'   group by dates ORDER by dates )  ";
            } else {
                $union_final_query[] = " ( SELECT DATE(dates) as day,count(dates) as views  FROM $v where content_owner = '" . $client . "'   group by dates ORDER by dates )  ";
            }

        }

    }

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by day asc";

    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    @unlink("polochart_view.txt");
    file_put_contents("polochart_view.txt", $youtubereport);
    @chmod("polochart_view.txt", 0777);

    $getAllrevenueQueryResult = runQuery($youtubereport, $conn);
    if (!noError($getAllrevenueQueryResult)) {
        return setErrorStack($returnArr, 3, $getAllrevenueQueryResult["errMsg"], null);
    }

    $res = [];
    while ($row = mysqli_fetch_assoc($getAllrevenueQueryResult["dbResource"])) {
        //$d= explode('-',$row['day']);

        if ($row['day'] != "") {
            $d = explode('-', $row['day']);
            $year = (int) $d[0];
            $month = (int) $d[1] - 1;
            $day = (int) $d[2];

            $res[] = array('c' => array(
                array('v' => "Date($year, $month, $day)"),
                array('v' => $row['views']),

            ));
        }

    }

    return setErrorStack($returnArr, -1, $res, null);
}

function get_youtubeecommercepaidfeatures_reportv3(
    $type,

    $conn,
    $offset = null,
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

    @unlink("polo_list_like.txt");
    file_put_contents("polo_list_like.txt", $getClientInfoQuery);
    @chmod("polo_list_like.txt", 0777);
    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */

    $check_query_total1 = "select  sum(totalcount)   as totalcount  from 	";
    $union_final_query_sql = "select  *   from 	 ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888 youtube_ecommerce_paid_features_report_nd1_2020_12

        foreach ($row as $k => $v) {

            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];
            
              //get share of co
              $year_mnoth = $result[1]."_".$result[0];
              $nd_type = $result[2];

              $rev_share = 30;
              $gst_percentage = 0;
              $holding_percentage = 0;
              $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	youtube_ecom_paid_features_activation_report_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
          
              $getshareres = runQuery($getshare, $conn);
              if (!noError($getshareres)) {
                //  @unlink("polo_export_getshare.txt");
                $ddd = "get_youtubeecommercepaidfeatures_reportv3 Error_in_query: ".PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                @chmod("polo_export_getshare.txt", 0777);

                // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
            }   
            else {
              $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
      
                  if (!empty($getshareresdata)) {
                  
                      $rev_share = $getshareresdata['shares'];
                      $gst_percentage = $getshareresdata['gst_percentage'];
                      $holding_percentage = $getshareresdata['holding_percentage'];
                  }
          }

           
              
            if (!empty($search)) {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}' and channel_id like '%{$search}%' ";
                $union_final_query[] = " ( SELECT  purchase_type,country,channel_name as videoTitle,channel_id as channelID,video_id,assetID, content_owner,Label, sum(earnings) as  partnerRevenue,'{$table_name}'  as tablename , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage FROM $v where  content_owner = '{$client}' and channel_id like '%{$search}%'  group by  channelID ) ";
            } else {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                $union_final_query[] = " ( SELECT  purchase_type,country,channel_name as videoTitle,channel_id as channelID,video_id,assetID, content_owner,Label, sum(earnings) as  partnerRevenue,'{$table_name}'  as tablename , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage FROM $v where  content_owner = '{$client}' group by  channelID ) ";
            }

        }

    }

    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube,gst_per  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }

    $row = [];

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc, tablename desc";
    //file_put_contents("tablequery.txt",$youtubereport);
    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    // echo $youtubereport;
    @unlink("polo_list.txt");
    file_put_contents("polo_list.txt", $youtubereport);
    @chmod("polo_list.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        
        
        

        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;
        
        $finalpayable = (($row2['partnerRevenue'] * $rev_share) / 100);
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);


        $res['data'][] = [
            'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
            'video_title' => isset($row2['videoTitle']) ? $row2['videoTitle'] : 'N/A',
            'youtube_payout' => $row2['partnerRevenue'],
            'share' => $rev_share,
            'finalpayable' => ($row2['partnerRevenue'] * $rev_share) / 100,
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,
            'tablename' => isset($row2['tablename']) ? $row2['tablename'] : 'N/A',
        ];
    }

    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}
//end
function get_youtubeecommercepaidfeatures_reportv2(
    $type,

    $conn,
    $offset = null,
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

    @unlink("polo_list_like.txt");
    file_put_contents("polo_list_like.txt", $getClientInfoQuery);
    @chmod("polo_list_like.txt", 0777);
    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    if (!noError($getClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $getClientInfoQueryResult["errMsg"], null);
    }

    /* This function negotiates that an email must be fetched from the database. All client info is keyed by the client's email
     *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used

     */

    $check_query_total1 = "select  sum(totalcount)   as totalcount  from 	";
    $union_final_query_sql = "select  *   from 	 ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888 youtube_ecommerce_paid_features_report_nd1_2020_12

        foreach ($row as $k => $v) {

            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];

            if (!empty($search)) {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}' and channel_id like '%{$search}%' ";
                $union_final_query[] = " ( SELECT  purchase_type,country,channel_name as videoTitle,channel_id as channelID,video_id,assetID, content_owner,Label, sum(earnings) as  partnerRevenue,'{$table_name}'  as tablename FROM $v where  content_owner = '{$client}' and channel_id like '%{$search}%'  group by  channelID ) ";
            } else {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                $union_final_query[] = " ( SELECT  purchase_type,country,channel_name as videoTitle,channel_id as channelID,video_id,assetID, content_owner,Label, sum(earnings) as  partnerRevenue,'{$table_name}'  as tablename FROM $v where  content_owner = '{$client}' group by  channelID ) ";
            }

        }

    }

    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);
    $gettotalcounts = $gettotalcounts['totalcount'];

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube,gst_per  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }

    $row = [];

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc, tablename desc";
    //file_put_contents("tablequery.txt",$youtubereport);
    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    // echo $youtubereport;
    @unlink("polo_list.txt");
    file_put_contents("polo_list.txt", $youtubereport);
    @chmod("polo_list.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
        $finalpayable = (($row2['partnerRevenue'] * $rev_share) / 100);
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

        $res['data'][] = [
            'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
            'video_title' => isset($row2['videoTitle']) ? $row2['videoTitle'] : 'N/A',
            'youtube_payout' => $row2['partnerRevenue'],
            'finalpayable' => ($row2['partnerRevenue'] * $rev_share) / 100,
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,
            'tablename' => isset($row2['tablename']) ? $row2['tablename'] : 'N/A',
        ];
    }

    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}
//end

////Youtube red-music - v3
function getClientsYoutubeRedMusicReportv3(
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
            $gst_percentage = 0;
            $holding_percentage = 0;
            $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	youtube_labelengine_activation_report_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
             
            $getshareres = runQuery($getshare, $conn);
            if (!noError($getshareres)) {
                //  @unlink("polo_export_getshare.txt");
                $ddd = "Error_in_query: ".PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                @chmod("polo_export_getshare.txt", 0777);

                // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
            }   
            else {
              $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
      
                  if (!empty($getshareresdata)) {
                  
                      $rev_share = $getshareresdata['shares'];
                      $gst_percentage = $getshareresdata['gst_percentage'];
                      $holding_percentage = $getshareresdata['holding_percentage'];
                  }
          }

           
            if (!empty($search)) {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}' and  channelID like '" . $search . "%'  ";
                $union_final_query[] = " ( SELECT    videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, sum(partnerRevenue) as partnerRevenue,'{$table_name}'  as tablename , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage   FROM $v where  content_owner = '{$client}' and  channelID like '" . $search . "%'   group by videoID )  ";
            } else {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                $union_final_query[] = " ( SELECT    videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, sum(partnerRevenue) as partnerRevenue,'{$table_name}'  as tablename , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage   FROM $v where  content_owner = '{$client}'   group by videoID )  ";
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

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube,gst_per  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per =0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }

    $row = [];

    $table2 = $all_table[0];

    $check_query_list_new = implode(" union  ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc, tablename desc";
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
        
        $finalpayable = (($row2['partnerRevenue'] * $rev_share) / 100);
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

        $res['data'][] = [
            'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
            'video_id' => isset($row2['videoID']) ? $row2['videoID'] : 'N/A',
            'video_title' => isset($row2['video_title']) ? $row2['video_title'] : 'N/A',
            'youtube_payout' => $row2['partnerRevenue'],
            'share' => $rev_share,
            'finalpayable' => (($row2['partnerRevenue'] * $rev_share) / 100),
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,

        ];
    }

    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}
////Youtube red-music - v2
function getClientsYoutubeRedMusicReportv2(
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

            if (!empty($search)) {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}' and  channelID like '" . $search . "%'  ";
                $union_final_query[] = " ( SELECT    videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, sum(partnerRevenue) as partnerRevenue,'{$table_name}'  as tablename FROM $v where  content_owner = '{$client}' and  channelID like '" . $search . "%'   group by videoID )  ";
            } else {
                $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
                $union_final_query[] = " ( SELECT    videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, sum(partnerRevenue) as partnerRevenue,'{$table_name}'  as tablename FROM $v where  content_owner = '{$client}'   group by videoID )  ";
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

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube,gst_per  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per =0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }

    $row = [];

    $table2 = $all_table[0];

    $check_query_list_new = implode(" union  ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc, tablename desc";
    //file_put_contents("tablequery.txt",$youtubereport);
    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    //echo $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;

        $finalpayable = (($row2['partnerRevenue'] * $rev_share) / 100);
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

        $res['data'][] = [
            'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
            'video_id' => isset($row2['videoID']) ? $row2['videoID'] : 'N/A',
            'video_title' => isset($row2['video_title']) ? $row2['video_title'] : 'N/A',
            'youtube_payout' => $row2['partnerRevenue'],
            'finalpayable' => (($row2['partnerRevenue'] * $rev_share) / 100),
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,

        ];
    }

    $res['total'] = $gettotalcounts;
    return setErrorStack($returnArr, -1, $res, null);
}
function ExportClientsYoutubeRedMusicReportv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $gettotalcounts = 0;
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

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube ,gst_per FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per =0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
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
    $union_final_query_sql = "select  *   from 	( ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $explode_table = explode("youtuberedmusic_video_report_redmusic_", $v);
            $explode_table_1 = explode("_", $explode_table[1]);
            $table_name = $explode_table_1[0];

            $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
            $union_final_query[] = " ( SELECT videoID, channelID, video_title,assetID,assetChannelID,ISRC,UPC, GRid, partnerRevenue ,'{$table_name}' as table_name FROM $v where  content_owner = '{$client}' ) ";
        }

    }

    $check_query_total_new = implode(" union all ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube,gst_per  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per = 0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }

    $row = [];

    $table2 = $all_table[0];

    $check_query_list_new = implode(" union all ", $union_final_query);
    $youtubereport = $union_final_query_sql . $check_query_list_new . " )  as list_all order by partnerRevenue desc";

    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        $youtubereport .= ' and video_title like "' . $search . '%"  ';
    }
    // echo "<br>sql :::::  ". $youtubereport;
    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;

        $res['data'][] = [
            'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
            'videoID' => isset($row2['videoID']) ? $row2['videoID'] : 'N/A',
            'video_title' => isset($row2['video_title']) ? $row2['video_title'] : 'N/A',
            'ISRC' => isset($row2['ISRC']) ? $row2['ISRC'] : 'N/A',
            'UPC' => isset($row2['UPC']) ? $row2['UPC'] : 'N/A',
            'GRid' => isset($row2['GRid']) ? $row2['GRid'] : 'N/A',

            'assetID' => isset($row2['assetID']) ? $row2['assetID'] : 'N/A',
            'assetChannelID' => isset($row2['assetChannelID']) ? $row2['assetChannelID'] : 'aaN/A',
            'youtube_payout' => $row2['partnerRevenue'],
            'rev_share' => $rev_share,
            'finalpayable' => (($row2['partnerRevenue'] * $rev_share) / 100),

        ];
    }
    //'Channel-id','Video Id','Video title','ISRC','UPC','GRid','AssetId','assetChannelID','Youtube payout','RevShare','Final Payable'

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}

function ExportClientsYoutubeRedMusicReport_countv3(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = '',
    $downlaodType = 'normal'
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "SELECT COUNT('') as totalcount from 	  ";
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
            $gst_percentage = 0;
            $holding_percentage = 0;
            $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	youtube_redmusic_activation_report_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
        
            $ddd = "1 ExportClientsYoutubeRedMusicReportv3 Error_in_query: ".$client.PHP_EOL. $getshare.PHP_EOL.PHP_EOL;
            file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
            @chmod("polo_export_getshare.txt", 0777);

            
            $getshareres = runQuery($getshare, $conn);
   
            if (!noError($getshareres)) {
                //  @unlink("polo_export_getshare.txt");
                $ddd = "ExportClientsYoutubeRedMusicReportv3 Error_in_query: ".$client.PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                @chmod("polo_export_getshare.txt", 0777);

                // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
            }   
            else {
              $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
      
                  if (!empty($getshareresdata)) {
                  
                      $rev_share = $getshareresdata['shares'];
                      $gst_percentage = $getshareresdata['gst_percentage'];
                      $holding_percentage = $getshareresdata['holding_percentage'];
                  }
          }

          if($downlaodType == "withholding"){

            $union_final_query[] = " ( SELECT     channelID,{$v}.video_title,{$v}.assetID , {$v}.assetChannelID, {$v}.contentType,{$v}.content_owner,{$v}.Label,{$v}.ISRC,{$v}.UPC, {$v}.GRid, Coalesce(sum(partnerRevenue),0) as partnerRevenue , Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
            Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END)* {$holding_percentage} /100),0) as WITHHOLDING , '{$table_name}'  as tablename ,  ( SELECT Channel FROM `channel_co_maping` ccm  where ccm.assetChannelID={$v}.assetChannelID limit 1 ) as channel_name   ,
            ( SELECT record_label FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as record_label, ( SELECT asset_title FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as  asset_title, ( SELECT channel_display_name FROM `{$other_tables}`    where  {$other_tables}.video_id = {$v}.videoID limit 1  ) as  channel_name2 ,
            ( SELECT video_title FROM `{$other_tables}`    where  {$other_tables}.video_id = {$v}.videoID limit 1  ) as  video_title2  , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage  
            FROM $v where  {$v}.content_owner = '{$client}'    group by {$v}.assetChannelID ,{$v}.holding_percentage)  ";

          } else {
           
            $union_final_query[] = " ( SELECT    {$v}.videoID,{$v}.channelID,{$v}.video_title,{$v}.assetID , {$v}.assetChannelID, {$v}.contentType,{$v}.content_owner,{$v}.Label,{$v}.ISRC,{$v}.UPC, {$v}.GRid, Coalesce(sum(partnerRevenue),0) as partnerRevenue , Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
            Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END)* {$holding_percentage} /100),0) as WITHHOLDING , '{$table_name}'  as tablename ,  ( SELECT Channel FROM `channel_co_maping` ccm  where ccm.assetChannelID={$v}.assetChannelID limit 1 ) as channel_name   ,
            ( SELECT record_label FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as record_label, ( SELECT asset_title FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as  asset_title, ( SELECT channel_display_name FROM `{$other_tables}`    where  {$other_tables}.video_id = {$v}.videoID limit 1  ) as  channel_name2 ,
            ( SELECT video_title FROM `{$other_tables}`    where  {$other_tables}.video_id = {$v}.videoID limit 1  ) as  video_title2  , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage  
            FROM $v where  {$v}.content_owner = '{$client}'   group by {$v}.videoID ,{$v}.holding_percentage )  ";
          }  
         
            
        }

    }
 
    $row = [];

    
    $check_query_list_new = implode(" union   ", $union_final_query);
    $gettotalcountquery = $union_final_query_sql . " ( " . $check_query_list_new . " )  as final_total";

     
    @unlink("polo_export_count.txt");
    file_put_contents("polo_export_count.txt", $gettotalcountquery);
    @chmod("polo_export_count.txt", 0777);

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

    $res['total'] = $gettotalcounts;
 
    return setErrorStack($returnArr, -1, $res, null);
}

function ExportClientsYoutubeRedMusicReportv3(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = '',
    $downlaodType = 'normal'
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "select  *   from 	  ";
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
            $gst_percentage = 0;
            $holding_percentage = 0;
            $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	youtube_redmusic_activation_report_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
        
            $ddd = "1 ExportClientsYoutubeRedMusicReportv3 Error_in_query: ".$client.PHP_EOL. $getshare.PHP_EOL.PHP_EOL;
            file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
            @chmod("polo_export_getshare.txt", 0777);

            
            $getshareres = runQuery($getshare, $conn);
   
            if (!noError($getshareres)) {
                //  @unlink("polo_export_getshare.txt");
                $ddd = "ExportClientsYoutubeRedMusicReportv3 Error_in_query: ".$client.PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                @chmod("polo_export_getshare.txt", 0777);

                // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
            }   
            else {
              $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
      
                  if (!empty($getshareresdata)) {
                  
                      $rev_share = $getshareresdata['shares'];
                      $gst_percentage = $getshareresdata['gst_percentage'];
                      $holding_percentage = $getshareresdata['holding_percentage'];
                  }
          }

          if($downlaodType == "withholding"){
           
            $union_final_query[] = " ( SELECT      {$v}.assetChannelID, {$v}.contentType,{$v}.content_owner , Coalesce(sum(partnerRevenue),0) as partnerRevenue , Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
            Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END)* {$holding_percentage} /100),0) as WITHHOLDING , '{$table_name}'  as tablename  
             , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage  
            FROM $v where  {$v}.content_owner = '{$client}'    group by {$v}.assetChannelID ,{$v}.holding_percentage )  ";

          } else {
            $union_final_query[] = " ( SELECT    {$v}.videoID,{$v}.channelID,{$v}.video_title,{$v}.assetID , {$v}.assetChannelID, {$v}.contentType,{$v}.content_owner,{$v}.Label,{$v}.ISRC,{$v}.UPC, {$v}.GRid, Coalesce(sum(partnerRevenue),0) as partnerRevenue , Coalesce(SUM(CASE WHEN country='US' THEN partnerRevenue END),0) as USPAYOUT,
            Coalesce((SUM(CASE WHEN country='US' THEN partnerRevenue END)* {$holding_percentage} /100),0) as WITHHOLDING , '{$table_name}'  as tablename ,  ( SELECT Channel FROM `channel_co_maping` ccm  where ccm.assetChannelID={$v}.assetChannelID limit 1 ) as channel_name   ,
            ( SELECT record_label FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as record_label, ( SELECT asset_title FROM `{$other_tables}`    where  {$other_tables}.asset_id = {$v}.assetID limit 1  ) as  asset_title, ( SELECT channel_display_name FROM `{$other_tables}`    where  {$other_tables}.video_id = {$v}.videoID limit 1  ) as  channel_name2 ,
            ( SELECT video_title FROM `{$other_tables}`    where  {$other_tables}.video_id = {$v}.videoID limit 1  ) as  video_title2  , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage  
            FROM $v where  {$v}.content_owner = '{$client}'    group by {$v}.videoID  ,{$v}.holding_percentage)  ";
          }
        
        }

    }
 
    $row = [];

    
    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc";

  
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        //$youtubereport .= ' and video_title like "'.$search.'%"  ';
    }
    // echo "<br>sql :::::  ". $youtubereport;

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $youtubereport);
    @chmod("polo_export.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        


        $channel_name = isset($row2['channel_name']) ? $row2['channel_name'] : 'N/A';
        $channel_name_temp = isset($row2['channel_name2']) ? $row2['channel_name2'] : $channel_name;
        $record_label = isset($row2['record_label']) ? $row2['record_label'] : 'N/A';
        $asset_title = isset($row2['asset_title']) ? $row2['asset_title'] : 'N/A';
        $Label = isset($row2['Label']) ? $row2['Label'] : 'N/A';

        $video_title = isset($row2['video_title']) ? $row2['video_title'] : 'N/A';
        $video_title_temp = isset($row2['video_title2']) ? $row2['video_title2'] : $video_title;
   

        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;

        $finalpayable = ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0;
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

        if($downlaodType == "withholding"){
            $res['data'][] = [
                'assetChannelID' => isset($row2['assetChannelID']) ? $row2['assetChannelID'] : 'N/A',
                'youtube_payout' => $row2['partnerRevenue'] + 0,
                'rev_share' => $rev_share + 0,
                'USPAYOUT' => $row2['USPAYOUT'] + 0,
                'holding_percentage' => ($row2['USPAYOUT'] > 0) ? $row2['holding_percentage'] + 0 : 0,
                'WITHHOLDING' => $row2['WITHHOLDING'] + 0,
                'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0,
                'gst_per' => $gst_per,
                'finalpayable_gst' => $final_payable_wth_gst,
                'table_name' => isset($row2['tablename']) ? $row2['tablename'] : 'N/A',
            ];  
        } else {
            $res['data'][] = [
                'channel_name' =>  str_replace('=', '   ', $channel_name_temp),
                'content_type' => isset($row2['contentType']) ? $row2['contentType'] :'N/A',
                'Label' => str_replace('=', '   ', $Label),
                'record_label' => str_replace('=', '   ', $record_label),
                'asset_title' =>  str_replace('=', '   ', $asset_title),
                'channelID' => isset($row2['channelID']) ? $row2['channelID'] : 'N/A',
                'videoID' => isset($row2['videoID']) ? $row2['videoID'] : 'N/A',
                'video_title' => str_replace('=', '   ', $video_title_temp),
                'ISRC' => isset($row2['ISRC']) ? $row2['ISRC'] : 'N/A',
                'UPC' => isset($row2['UPC']) ? $row2['UPC'] : 'N/A',
                'GRid' => isset($row2['GRid']) ? $row2['GRid'] : 'N/A',
                'assetID' => isset($row2['assetID']) ? $row2['assetID'] : 'N/A',
                'assetChannelID' => isset($row2['assetChannelID']) ? $row2['assetChannelID'] : 'N/A',
                'youtube_payout' => $row2['partnerRevenue'] + 0,
                'rev_share' => $rev_share + 0,
                'USPAYOUT' => $row2['USPAYOUT'] + 0,
              //  'holding_percentage' => $row2['holding_percentage'] + 0,
                'WITHHOLDING' => $row2['WITHHOLDING'] + 0,
                'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0,
                'gst_per' => $gst_per,
                'finalpayable_gst' => $final_payable_wth_gst,
                'table_name' => isset($row2['tablename']) ? $row2['tablename'] : 'N/A',
    
            ];
        }
       
    }
    //'Channel-id','Video Id','Video title','ISRC','UPC','GRid','AssetId','assetChannelID','Youtube payout','RevShare','Final Payable'

    // $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}

function ExportLabelengineUnAssignedv2(
    $table,

    $conn,
    $offset = null,
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
//SELECT T1.assetID,label,youtube_video_claim_report3_nd1_2020_12.asset_label,youtube_video_claim_report3_nd1_2020_12.asset_id FROM `youtube_video_claim_report_nd1_2020_12` as T1  INNER JOIN youtube_video_claim_report3_nd1_2020_12 ON T1.assetID=youtube_video_claim_report3_nd1_2020_12.asset_id  where T1.content_owner IS NULL  group by T1.assetID INTO OUTFILE '/var/lib/mysql-files/youtube_video_claim_report/missinglablesinv2.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n'
    //$sql = "INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/missinglablesinv2.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n'";
    @unlink("/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv");

    $insertTableQuery = "SELECT 'id' , 'contentType' , 'assetID' , 'partnerRevenue' , 'content_owner'
	UNION ALL SELECT  `id` , `contentType` , `assetID` , `partnerRevenue` , COALESCE(content_owner, '')  FROM {$table}   where ({$table}.content_owner IS NULL  or {$table}.content_owner ='' )  group by {$table}.assetID INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $insertTableQuery);
    @chmod("polo_export.txt", 0777);

    $insertTableQueryResult = runQuery($insertTableQuery, $conn);

}

function ExportClientsYoutubeUSReport_countv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $gettotalcounts = 0;
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
  
    $check_query_total = [];
     
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];

            $check_query_total[] = " select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
             
        }

    }

    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";

    // echo "<br>sql :::::  ". $youtubereport;
    @unlink("polo_export_count.txt");
    file_put_contents("polo_export_count.txt", $gettotalcountquery);
    @chmod("polo_export_count.txt", 0777);

    $gettotalcountquery = runQuery($gettotalcountquery, $conn);
    if (!noError($gettotalcountquery)) {
        return setErrorStack($returnArr, 3, $gettotalcountquery["errMsg"], null);
    }
    $gettotalcounts = mysqli_fetch_assoc($gettotalcountquery["dbResource"]);

    $gettotalcounts = $gettotalcounts['totalcount'];

    // echo "<br>sql :::::  ". $youtubereport;

    $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}

function ExportClientsYoutubeUSReportv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $gettotalcounts = 0;
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
            $gst_percentage = 0;
            $holding_percentage = 0;
         //   $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	youtube_video_claim_activation_report_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";

         //   $getshareres = runQuery($getshare, $conn);
           


            $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
            $union_final_query[] = " ( SELECT assetID,Coalesce(sum(partnerRevenue),0) as partnerRevenue , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage  FROM $v where  content_owner = '{$client}'   group by assetID  ) ";
        }

    }

     
    $row = [];

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc";
 
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    // echo "<br>sql :::::  ". $youtubereport;
    // echo "<br>sql :::::  ". $youtubereport;
    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $youtubereport);
    @chmod("polo_export.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
//- $row2['WITHHOLDING']

        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;

        $finalpayable = ((($row2['partnerRevenue'] ) * $rev_share) / 100) * 1;
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);


        $res['data'][] = [
            'assetID' => isset($row2['assetID']) ? $row2['assetID'] : 'N/A',
            'youtube_payout' => $row2['partnerRevenue'] * 1,
            'rev_share' => $rev_share * 1,
            'finalpayable' => ((($row2['partnerRevenue'] ) * $rev_share) / 100) * 1,
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,

        ];
    }

    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}

function ExportClientsYoutubeUSReport_mearge_v2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null
) {
    $gettotalcounts = 0;
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

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube ,gst_per  FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per =0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
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

            $check_query_total[] = "select count(distinct id) as totalcount from   $v WHERE content_owner='{$client}'  ";
            $union_final_query[] = " ( SELECT assetID,Coalesce(sum(partnerRevenue),0) as partnerRevenue FROM $v where  content_owner = '{$client}'   group by assetID  ) ";
        }

    }

    //get share of co
    $getshare = "SELECT  JSON_UNQUOTE(JSON_EXTRACT(client_type_details, '$.revenueShareYoutube')) as  revenueShareYoutube ,gst_per FROM crep_cms_clients where client_username = '" . $client . "'";
    $getshareres = runQuery($getshare, $conn);
    $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
    $rev_share = 0;
    $gst_per =0;
    if (!empty($getshareresdata)) {
        $rev_share = (int) $getshareresdata['revenueShareYoutube'];
        $gst_per = $getshareresdata['gst_per'];
    }

    $row = [];

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc";

    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    // echo "<br>sql :::::  ". $youtubereport;
    // echo "<br>sql :::::  ". $youtubereport;
    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $youtubereport);
    @chmod("polo_export.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;

        $res['data'][] = [
            //   'assetID' => isset($row2['assetID']) ? $row2['assetID'] : 'N/A',
            //    'youtube_payout' => $row2['partnerRevenue'] * 1 ,
            //    'rev_share' => $rev_share * 1 ,
            //    'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) * 1

            'channelID' => '',
            'videoID' => '',
            'video_title' => '',
            'ISRC' => '',
            'UPC' => '',
            'GRid' => '',

            'assetID' => isset($row2['assetID']) ? $row2['assetID'] : 'N/A',
            'assetChannelID' => '',
            'youtube_payout' => $row2['partnerRevenue'] + 0,
            'rev_share' => $rev_share + 0,
            'USPAYOUT' => '0',
            'WITHHOLDING' => '0',
            'finalpayable' => ((($row2['partnerRevenue'] - $row2['WITHHOLDING']) * $rev_share) / 100) + 0,

        ];
    }

    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}

function getContentActivityDownlaodJsonv2($filename='activity_downlaod_reportv2.josn')
{
    $arr['ki'] = 0;
    if (file_exists($filename)){
        $getContent = file_get_contents($filename);
        if($getContent!=""){
            $arr = json_decode($getContent, true);
        }
        
    }  
    return $arr;

}

function setContentActivityDownlaodJsonv2($filename='activity_downlaod_reportv2.josn', $keyindex=0)
{
    $getpath = file_get_contents($filename);
    $arr = json_decode($getpath, true);
     unset($arr[$keyindex]);
    file_put_contents($filename, json_encode($arr));
}
 

function get_time_minutes($ardata=array()) {
    $duration = 0;
    $minutes = 0;
    foreach($ardata as $key => $timeValue){
        $duration = $timeValue / 1000;
        $minutes =  $minutes + (floor(($duration / 60) % 60));
    }
   
   return $minutes;
}


////new report starte here  apple,itune,gaana,saavan


///export section here 


function ExportClientsAppleMusicReport_countv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = ''
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "select  *   from 	  ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $result = array_map('strrev', explode('_', strrev($v)));
            $ndType = $result[2];

            if (!empty($search)) {
                $check_query_total[] = " select  COUNT( DISTINCT ISRC) as totalcount from   $v   WHERE {$v}.content_owner='{$client}' and  {$v}.ISRC  like '" . $search . "%'   ";
                
            } else {

                $check_query_total[] = " select  COUNT( DISTINCT ISRC) as totalcount from   $v 
                WHERE {$v}.content_owner='{$client}'    ";
               
            }
        }

    }

 

     

    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";
  // echo "<br>sql :::::  ". $youtubereport;
  @unlink("polo_export_count.txt");
  file_put_contents("polo_export_count.txt", $gettotalcountquery);
  @chmod("polo_export_count.txt", 0777);
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

function ExportClientsAppleMusicReportv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = ''
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "select  *   from 	  ";
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
                $gst_percentage = 0;
                $holding_percentage = 0;
                $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	report_audio_activation_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
            
                $getshareres = runQuery($getshare, $conn);
                if (!noError($getshareres)) {
                    //  @unlink("polo_export_getshare.txt");
                    $ddd = "Error_in_query: ".PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                    file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                    @chmod("polo_export_getshare.txt", 0777);
    
                    // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
                }   
                else {
                  $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
          
                      if (!empty($getshareresdata)) {
                      
                          $rev_share = $getshareresdata['shares'];
                          $gst_percentage = $getshareresdata['gst_percentage'];
                          $holding_percentage = $getshareresdata['holding_percentage'];
                      }
              }

               

                
            if (!empty($search)) {
                
                $union_final_query[] = " ( SELECT  StorefrontName,AppleIdentifier,MembershipType,Quantity,NetRoyalty,NetRoyaltyTotal,sum(USD) as USD,sum(PartnerShare) as PartnerShare,ISRC,ItemTitle,ItemArtist,ItemType,MediaType,VendorIdentifier,OfflineIndicator,Label,Grid FinalPayable , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage
                    FROM $v

                where  {$v}.content_owner = '{$client}' and  ISRC like '" . $search . "%'   group by ISRC )  ";
            } else {

                
                $union_final_query[] = "   ( SELECT  StorefrontName,AppleIdentifier,MembershipType,Quantity,NetRoyalty,NetRoyaltyTotal,sum(USD) as USD,sum(PartnerShare) as PartnerShare ,ISRC,ItemTitle,ItemArtist,ItemType,MediaType,VendorIdentifier,OfflineIndicator,Label,Grid FinalPayable , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage
                FROM $v 
                where  {$v}.content_owner = '{$client}'   group by ISRC )  ";
            }
        }

    }

     

    $row = [];

    

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by USD desc";

    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        //$youtubereport .= ' and video_title like "'.$search.'%"  ';
    }
    // echo "<br>sql :::::  ". $youtubereport;

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $youtubereport);
    @chmod("polo_export.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;

        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;
    
        $finalpayable =  (($row2['PartnerShare'] * $rev_share) / 100) + 0;
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

        $res['data'][] = [
           
            'StorefrontName' => isset($row2['StorefrontName']) ? $row2['StorefrontName'] : 'N/A',
            'AppleIdentifier' => isset($row2['AppleIdentifier']) ? $row2['AppleIdentifier'] : 'N/A',
            'MembershipType' => isset($row2['MembershipType']) ? $row2['MembershipType'] : 'N/A',
            'Quantity' => isset($row2['Quantity']) ? $row2['Quantity'] : 'N/A',
            'NetRoyalty' => isset($row2['NetRoyalty']) ? $row2['NetRoyalty'] : 'N/A',
            'NetRoyaltyTotal' => isset($row2['NetRoyaltyTotal']) ? $row2['NetRoyaltyTotal'] : 'N/A',
            'USD' => isset($row2['USD']) ? $row2['USD'] : 'N/A',
            'PartnerShare' => isset($row2['PartnerShare']) ? $row2['PartnerShare'] : 'N/A',
            'ISRC' => isset($row2['ISRC']) ? $row2['ISRC'] : 'N/A',
            'ItemTitle' => isset($row2['ItemTitle']) ? $row2['ItemTitle'] : 'N/A',
            'ItemArtist' => isset($row2['ItemArtist']) ? $row2['ItemArtist'] : 'N/A',
            'ItemType' => isset($row2['ItemType']) ? $row2['ItemType'] : 'N/A',
            'MediaType' => isset($row2['MediaType']) ? $row2['MediaType'] : 'N/A',
            'VendorIdentifier' => isset($row2['VendorIdentifier']) ? $row2['VendorIdentifier'] : 'N/A',
            'OfflineIndicator' => isset($row2['OfflineIndicator']) ? $row2['OfflineIndicator'] : 'N/A',
            'Label' => isset($row2['Label']) ? $row2['Label'] : 'N/A',
            'Grid' => isset($row2['Grid']) ? $row2['Grid'] : 'N/A',
            'FinalPayable' => (($row2['PartnerShare'] * $rev_share) / 100) + 0,
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,

        ];
    }
    //'Channel-id','Video Id','Video title','ISRC','UPC','GRid','AssetId','assetChannelID','Youtube payout','RevShare','Final Payable'

    // $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}



function ExportClientsItuneMusicReport_countv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = ''
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "select  *   from 	  ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $result = array_map('strrev', explode('_', strrev($v)));
            $ndType = $result[2];

            if (!empty($search)) {
                $check_query_total[] = " select  COUNT( DISTINCT ISRC_ISBN) as totalcount from   $v   WHERE {$v}.content_owner='{$client}' and  {$v}.ISRC_ISBN  like '" . $search . "%'   ";
                
            } else {

                $check_query_total[] = " select  COUNT( DISTINCT ISRC_ISBN) as totalcount from   $v 
                WHERE {$v}.content_owner='{$client}'    ";
               
            }
        }

    }

 

     

    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";
  // echo "<br>sql :::::  ". $youtubereport;
  @unlink("polo_export_count.txt");
  file_put_contents("polo_export_count.txt", $gettotalcountquery);
  @chmod("polo_export_count.txt", 0777);
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



function ExportClientsItuneMusicReportv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = ''
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "select  *   from 	  ";
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
                $gst_percentage = 0;
                $holding_percentage = 0;
                $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	report_audio_activation_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
            
                $getshareres = runQuery($getshare, $conn);
                if (!noError($getshareres)) {
                    //  @unlink("polo_export_getshare.txt");
                    $ddd = "Error_in_query: ".PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                    file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                    @chmod("polo_export_getshare.txt", 0777);
    
                    // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
                }   
                else {
                  $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
          
                      if (!empty($getshareresdata)) {
                      
                          $rev_share = $getshareresdata['shares'];
                          $gst_percentage = $getshareresdata['gst_percentage'];
                          $holding_percentage = $getshareresdata['holding_percentage'];
                      }
              }

               

            if (!empty($search)) {
                
                $union_final_query[] = " ( SELECT  StartDate,EndDate,UPC,ISRC_ISBN,VendorIdentifier,Quantity,PartnerShare,ExtendedPartnerShare,PartnerShareCurrency,sum(USD) as USD,sum(LabelShare) as LabelShare,SalesorReturns,AppleIdentifier,Artist_Show_Developer_Author,Title,Label,Grid,ProductTypeIdentifier,ISAN_OtherIdentifier,CountryOfSale,Pre_orderFlag,PromoCode,CustomerPrice,CustomerCurrency  , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage 
                    FROM $v

                where  {$v}.content_owner = '{$client}' and  ISRC_ISBN like '" . $search . "%'   group by ISRC_ISBN )  ";
            } else {

                
                $union_final_query[] = "   ( SELECT  StartDate,EndDate,UPC,ISRC_ISBN,VendorIdentifier,Quantity,PartnerShare,ExtendedPartnerShare,PartnerShareCurrency,sum(USD) as USD,sum(LabelShare) as LabelShare,SalesorReturns,AppleIdentifier,Artist_Show_Developer_Author,Title,Label,Grid,ProductTypeIdentifier,ISAN_OtherIdentifier,CountryOfSale,Pre_orderFlag,PromoCode,CustomerPrice,CustomerCurrency  , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage 
                FROM $v 
                where  {$v}.content_owner = '{$client}'   group by ISRC_ISBN )  ";
            }
        }

    }

 
  

    $row = [];

    

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by USD desc";

    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        //$youtubereport .= ' and video_title like "'.$search.'%"  ';
    }
    // echo "<br>sql :::::  ". $youtubereport;

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $youtubereport);
    @chmod("polo_export.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;
        $finalpayable = (($row2['LabelShare'] * $rev_share) / 100) + 0;
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

        $res['data'][] = [
            'StartDate' => isset($row2['StartDate']) ? $row2['StartDate'] : 'N/A',
            'EndDate' => isset($row2['EndDate']) ? $row2['EndDate'] : 'N/A',
            'UPC' => isset($row2['UPC']) ? $row2['UPC'] : 'N/A',
            'ISRC_ISBN' => isset($row2['ISRC_ISBN']) ? $row2['ISRC_ISBN'] : 'N/A',
            'VendorIdentifier' => isset($row2['VendorIdentifier']) ? $row2['VendorIdentifier'] : 'N/A',
            'Quantity' => isset($row2['Quantity']) ? $row2['Quantity'] : 'N/A',
            'PartnerShare' => isset($row2['PartnerShare']) ? $row2['PartnerShare'] : 'N/A',
            'ExtendedPartnerShare' => isset($row2['ExtendedPartnerShare']) ? $row2['ExtendedPartnerShare'] : 'N/A',
            'PartnerShareCurrency' => isset($row2['PartnerShareCurrency']) ? $row2['PartnerShareCurrency'] : 'N/A',
            'USD' => isset($row2['USD']) ? $row2['USD'] : 'N/A',
            'LabelShare' => isset($row2['LabelShare']) ? $row2['LabelShare'] : 'N/A',
            'SalesorReturns' => isset($row2['SalesorReturns']) ? $row2['SalesorReturns'] : 'N/A',
            'AppleIdentifier' => isset($row2['AppleIdentifier']) ? $row2['AppleIdentifier'] : 'N/A',
             'Artist_Show_Developer_Author' => isset($row2['Artist_Show_Developer_Author']) ? $row2['Artist_Show_Developer_Author'] : 'N/A',
            'Title' => isset($row2['Title']) ? $row2['Title'] : 'N/A',
            'Label' => isset($row2['Label']) ? $row2['Label'] : 'N/A',
            'Grid' => isset($row2['Grid']) ? $row2['Grid'] : 'N/A',
            'ProductTypeIdentifier' => isset($row2['ProductTypeIdentifier']) ? $row2['ProductTypeIdentifier'] : 'N/A',
            'ISAN_OtherIdentifier' => isset($row2['ISAN_OtherIdentifier']) ? $row2['ISAN_OtherIdentifier'] : 'N/A',
            'CountryOfSale' => isset($row2['CountryOfSale']) ? $row2['CountryOfSale'] : 'N/A',
            'Pre_orderFlag' => isset($row2['Pre_orderFlag']) ? $row2['Pre_orderFlag'] : 'N/A',
            'PromoCode' => isset($row2['PromoCode']) ? $row2['PromoCode'] : 'N/A',
            'CustomerPrice' => isset($row2['CustomerPrice']) ? $row2['CustomerPrice'] : 'N/A',
            'CustomerCurrency' => isset($row2['CustomerCurrency']) ? $row2['CustomerCurrency'] : 'N/A',
            'FinalPayable' => (($row2['LabelShare'] * $rev_share) / 100) + 0,
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,
        ];
    }
    //'Channel-id','Video Id','Video title','ISRC','UPC','GRid','AssetId','assetChannelID','Youtube payout','RevShare','Final Payable'

    // $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}

 

function ExportClientsApplemusicUnAssignedv2(
    $table,

    $conn,
    $offset = null,
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
 
    //@unlink("/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv");

   // @chmod("/var/lib/mysql-files/nonassignedcontentowners", 0777);
    $insertTableQuery = "SELECT 'StorefrontName','AppleIdentifier'	,'MembershipType'	,'Quantity'	,'NetRoyalty'	,'NetRoyaltyTotal'	,'USD'	,'PartnerShare'	,'ISRC'	,'ItemTitle'	,'ItemArtist'	,'ItemType'	,'MediaType'	,'VendorIdentifier'	,'OfflineIndicator'	,'Label'	,'Grid' 
	UNION ALL SELECT 
    StorefrontName , AppleIdentifier	 , MembershipType	 , Quantity	 , NetRoyalty	 , NetRoyaltyTotal	 , USD	 , PartnerShare	 , ISRC	 , ItemTitle	 , ItemArtist	 , ItemType	 , MediaType	 , VendorIdentifier	 , OfflineIndicator	 , Label	 , Grid	 	   FROM {$table}   where ({$table}.content_owner IS NULL  or {$table}.content_owner ='' )  group by {$table}.ISRC INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $insertTableQuery);
    @chmod("polo_export.txt", 0777);

    $insertTableQueryResult = runQuery($insertTableQuery, $conn);

    die();
     
}


function ExportClientsItunemusicUnAssignedv2(
    $table,

    $conn,
    $offset = null,
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
 
   // @unlink("/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv");

  //  @chmod("/var/lib/mysql-files/nonassignedcontentowners", 0777);
  
    $insertTableQuery = "SELECT 'Start Date','End Date','UPC','ISRC/ISBN','Vendor Identifier','Quantity','Partner Share','Extended Partner Share','Partner Share Currency','USD','Label Share','Sales or Returns','Apple Identifier','Artist/Show/Developer/Author','Title','Label/Studio/Network/Developer/Publisher','Grid','Product Type Identifier','ISAN/Other Identifier','Country Of Sale','Pre-order Flag','Promo Code','Customer Price','Customer Currency' 
	UNION ALL SELECT 
    StartDate,EndDate,UPC,ISRC_ISBN,VendorIdentifier,Quantity,PartnerShare,ExtendedPartnerShare,PartnerShareCurrency,sum(USD) as USD,sum(LabelShare) as LabelShare,SalesorReturns,AppleIdentifier,Artist_Show_Developer_Author,Title,Label,Grid,ProductTypeIdentifier,ISAN_OtherIdentifier,CountryOfSale,Pre_orderFlag,PromoCode,CustomerPrice,CustomerCurrency 	   FROM {$table}   where ({$table}.content_owner IS NULL  or {$table}.content_owner ='' )  group by {$table}.ISRC_ISBN INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $insertTableQuery);
    @chmod("polo_export.txt", 0777);

    $insertTableQueryResult = runQuery($insertTableQuery, $conn);

    die();
     
}


function ExportClientsSaavanmusicUnAssignedv2(
    $table,

    $conn,
    $offset = null,
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
 
    @unlink("/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv");
    @chmod("/var/lib/mysql-files/nonassignedcontentowners", 0777);

//
 


    $insertTableQuery = "SELECT 'Sr. No.','Track Name','Album Name','Artist Name','ISRC' ,'UPC','Language','Label','Ad-Supported Streams','Subscription Streams', 'Jio-Trial Streams' ,'Total Streams'
	UNION ALL SELECT 
    srno	 , TrackName	 , AlbumName	 , ArtistName	 , ISRC	 , UPC	 , `Language`	 , Label	 , Ad_Supported_Streams	 ,  Subscription_Streams	 ,  Jio_Trial_Streams	 ,  Total_Streams  FROM {$table}   where ({$table}.content_owner IS NULL  or {$table}.content_owner ='' )  INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $insertTableQuery);
    @chmod("polo_export.txt", 0777);

    $insertTableQueryResult = runQuery($insertTableQuery, $conn);

    die();
     
}



function ExportClientsGaanamusicUnAssignedv2(
    $table,

    $conn,
    $offset = null,
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
 
    //@unlink("/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv");

    //@chmod("/var/lib/mysql-files/nonassignedcontentowners", 0777);

    $insertTableQuery = "SELECT 'Sub-vendor Name','Month','Free Playouts','Paid Playouts','Total Playouts' ,'Free Playouts Revenue','Paid Playouts  Revenue','Total Playouts  Revenue' 
	UNION ALL SELECT 
    `Sub_vendor_Name` , `Month`,`Free_Playouts`,`Paid_Playouts`,`Total_Playouts`,free_playout_revenue ,paid_playout_revenue  ,Total_Revenue   FROM {$table}   where ({$table}.content_owner IS NULL  or {$table}.content_owner ='' )  INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $insertTableQuery);
    @chmod("polo_export.txt", 0777);

    $insertTableQueryResult = runQuery($insertTableQuery, $conn);

    die();
     
}


function ExportClientsSpotifymusicUnAssignedv2(
    $table,

    $conn,
    $offset = null,
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
 
   // @unlink("/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv");

    //@chmod("/var/lib/mysql-files/nonassignedcontentowners", 0777);
    $insertTableQuery = "SELECT 'Country','Product','URI','UPC','EAN','ISRC','Track_name','Artist_name','Composer_name','Album_name','Quantity','Label','Payable_invoice','Invoice_currency','Payable_EUR','Payable_USD'
	UNION ALL SELECT 
    Country,Product,URI,UPC,EAN,ISRC,Track_name,Artist_name,Composer_name,Album_name,Quantity,Label,Payable_invoice,Invoice_currency,Payable_EUR,Payable_USD 	   FROM {$table}   where ({$table}.content_owner IS NULL  or {$table}.content_owner ='' )  group by {$table}.ISRC INTO OUTFILE '/var/lib/mysql-files/nonassignedcontentowners/{$table}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' ;";

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $insertTableQuery);
    @chmod("polo_export.txt", 0777);

    $insertTableQueryResult = runQuery($insertTableQuery, $conn);

    die();
     
}


function ExportClientsGaanaMusicReport_countv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = ''
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "select  *   from 	  ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $result = array_map('strrev', explode('_', strrev($v)));
            $ndType = $result[2];

            if (!empty($search)) {
                $check_query_total[] = " select  COUNT( DISTINCT Sub_vendor_Name) as totalcount from   $v   WHERE {$v}.content_owner='{$client}' and  {$v}.Sub_vendor_Name  like '" . $search . "%'   ";
                
            } else {

                $check_query_total[] = " select  COUNT( DISTINCT Sub_vendor_Name) as totalcount from   $v 
                WHERE {$v}.content_owner='{$client}'    ";
               
            }
        }

    }

 

     

    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";
  // echo "<br>sql :::::  ". $youtubereport;
  @unlink("polo_export_count.txt");
  file_put_contents("polo_export_count.txt", $gettotalcountquery);
  @chmod("polo_export_count.txt", 0777);
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



function ExportClientsGaanaMusicReportv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = ''
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "select  *   from 	  ";
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
                $gst_percentage = 0;
                $holding_percentage = 0;
                $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	report_audio_activation_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
            
                $getshareres = runQuery($getshare, $conn);
                if (!noError($getshareres)) {
                    //  @unlink("polo_export_getshare.txt");
                    $ddd = "Error_in_query: ".PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                    file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                    @chmod("polo_export_getshare.txt", 0777);
    
                    // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
                }   
                else {
                  $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
          
                      if (!empty($getshareresdata)) {
                      
                          $rev_share = $getshareresdata['shares'];
                          $gst_percentage = $getshareresdata['gst_percentage'];
                          $holding_percentage = $getshareresdata['holding_percentage'];
                      }
              }

               

            if (!empty($search)) {
                
                $union_final_query[] = " ( SELECT   Sub_vendor_Name  , sum(Free_Playouts) as Free_Playouts , sum(Paid_Playouts) as Paid_Playouts  , sum(Total_Playouts) as Total_Playouts , sum(free_playout_revenue) as free_playout_revenue , sum(paid_playout_revenue) as paid_playout_revenue ,  sum(Total_Revenue) as Total_Revenue  , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage
                    FROM $v

                where  {$v}.content_owner = '{$client}' and  Sub_vendor_Name like '" . $search . "%'   group by Sub_vendor_Name )  ";
            } else {

                
                $union_final_query[] = "   ( SELECT   Sub_vendor_Name  , sum(Free_Playouts) as Free_Playouts , sum(Paid_Playouts) as Paid_Playouts  , sum(Total_Playouts) as Total_Playouts , sum(free_playout_revenue) as free_playout_revenue , sum(paid_playout_revenue) as paid_playout_revenue ,  sum(Total_Revenue) as Total_Revenue  , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage
                FROM $v 
                where  {$v}.content_owner = '{$client}'   group by Sub_vendor_Name )  ";
            }
        }

    }

     
    $row = [];

    

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by Total_Revenue desc";

    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        //$youtubereport .= ' and video_title like "'.$search.'%"  ';
    }
    // echo "<br>sql :::::  ". $youtubereport;

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $youtubereport);
    @chmod("polo_export.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;

        $finalpayable =  (($row2['Total_Revenue'] * $rev_share) / 100) + 0;
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

        $res['data'][] = [
            'Sub_vendor_Name' => isset($row2['Sub_vendor_Name']) ? $row2['Sub_vendor_Name'] : 'N/A',
            'Free_Playouts' => isset($row2['Free_Playouts']) ? $row2['Free_Playouts'] : 'N/A',
            'Paid_Playouts' => isset($row2['Paid_Playouts']) ? $row2['Paid_Playouts'] : 'N/A',
            'Total_Playouts' => isset($row2['Total_Playouts']) ? $row2['Total_Playouts'] : 'N/A',
            'free_playout_revenue' => isset($row2['free_playout_revenue']) ? $row2['free_playout_revenue'] : 'N/A',
            'paid_playout_revenue' => isset($row2['paid_playout_revenue']) ? $row2['paid_playout_revenue'] : 'N/A',
            'Total_Revenue' => isset($row2['Total_Revenue']) ? $row2['Total_Revenue'] : 'N/A',
            'FinalPayable' => (($row2['Total_Revenue'] * $rev_share) / 100) + 0,
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,
        ];
    }
    //'Channel-id','Video Id','Video title','ISRC','UPC','GRid','AssetId','assetChannelID','Youtube payout','RevShare','Final Payable'

    // $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}



function ExportClientsSaavanMusicReport_countv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = ''
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "select  *   from 	  ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $result = array_map('strrev', explode('_', strrev($v)));
            $ndType = $result[2];

            if (!empty($search)) {
                $check_query_total[] = " select  COUNT( DISTINCT ISRC) as totalcount from   $v   WHERE {$v}.content_owner='{$client}' and  {$v}.ISRC  like '" . $search . "%'   ";
                
            } else {

                $check_query_total[] = " select  COUNT( DISTINCT ISRC) as totalcount from   $v 
                WHERE {$v}.content_owner='{$client}'    ";
               
            }
        }

    }

 

     

    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";
  // echo "<br>sql :::::  ". $youtubereport;
  @unlink("polo_export_count.txt");
  file_put_contents("polo_export_count.txt", $gettotalcountquery);
  @chmod("polo_export_count.txt", 0777);
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



function ExportClientsSaavanMusicReportv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = ''
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "select  *   from 	  ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $result = array_map('strrev', explode('_', strrev($v)));
            
            $year_mnoth = $result[1]."_".$result[0];
            $nd_type = $result[2];
   
             //get share of co
                $rev_share = 30;
                $gst_percentage = 0;
                $holding_percentage = 0;
                $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	report_audio_activation_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
            
                $getshareres = runQuery($getshare, $conn);
                if (!noError($getshareres)) {
                    //  @unlink("polo_export_getshare.txt");
                    $ddd = "Error_in_query: ".PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                    file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                    @chmod("polo_export_getshare.txt", 0777);
    
                    // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
                }   
                else {
                  $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
          
                      if (!empty($getshareresdata)) {
                      
                          $rev_share = $getshareresdata['shares'];
                          $gst_percentage = $getshareresdata['gst_percentage'];
                          $holding_percentage = $getshareresdata['holding_percentage'];
                      }
              }

               

                
            if (!empty($search)) {
                
                $union_final_query[] = " ( SELECT  TrackName, ISRC, Language, Label, sum(Ad_Supported_Streams) as Ad_Supported_Streams, sum(Ad_Supported_Revenue) as Ad_Supported_Revenue, sum(Subscription_Streams) as Subscription_Streams, sum(Subscription_Revenue) as Subscription_Revenue , sum(Jio_Trial_Streams) as Jio_Trial_Streams , sum(Total_Streams) as Total_Streams , sum(Total_Revenue) as Total_Revenue  , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage  
                    FROM $v

                where  {$v}.content_owner = '{$client}' and  ISRC like '" . $search . "%'   group by ISRC )  ";
            } else {

                
                $union_final_query[] = "   ( SELECT   TrackName, ISRC, Language, Label, sum(Ad_Supported_Streams) as Ad_Supported_Streams, sum(Ad_Supported_Revenue) as Ad_Supported_Revenue, sum(Subscription_Streams) as Subscription_Streams, sum(Subscription_Revenue) as Subscription_Revenue , sum(Jio_Trial_Streams) as Jio_Trial_Streams , sum(Total_Streams) as Total_Streams , sum(Total_Revenue) as Total_Revenue  , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage  
                FROM $v 
                where  {$v}.content_owner = '{$client}'   group by ISRC )  ";
            }
        }

    }

    $check_query_total_new = implode(" union   ", $check_query_total);
 
    

    $row = [];

    

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by Total_Revenue desc";

    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        //$youtubereport .= ' and video_title like "'.$search.'%"  ';
    }
    // echo "<br>sql :::::  ". $youtubereport;

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $youtubereport);
    @chmod("polo_export.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;
        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;
        $finalpayable =  (($row2['Total_Revenue'] * $rev_share) / 100) + 0;
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);

      
        $res['data'][] = [
            'TrackName' => isset($row2['TrackName']) ? $row2['TrackName'] : 'N/A',
            'ISRC' => isset($row2['ISRC']) ? $row2['ISRC'] : 'N/A',
            'Language' => isset($row2['Language']) ? $row2['Language'] : 'N/A',
            'Label' => isset($row2['Label']) ? $row2['Label'] : 'N/A',
            'Ad_Supported_Streams' => isset($row2['Ad_Supported_Streams']) ? $row2['Ad_Supported_Streams'] : 'N/A',
            'Subscription_Streams' => isset($row2['Subscription_Streams']) ? $row2['Subscription_Streams'] : 'N/A',
            'Jio_Trial_Streams' => isset($row2['Jio_Trial_Streams']) ? $row2['Jio_Trial_Streams'] : 'N/A',
            'Total_Streams' => isset($row2['Total_Streams']) ? $row2['Total_Streams'] : 'N/A',
            'Ad_Supported_Revenue' => isset($row2['Ad_Supported_Revenue']) ? $row2['Ad_Supported_Revenue'] : 'N/A',
            'Subscription_Revenue' => isset($row2['Subscription_Revenue']) ? $row2['Subscription_Revenue'] : 'N/A',
            'Total_Revenue' => isset($row2['Total_Revenue']) ? $row2['Total_Revenue'] : 'N/A',
            'FinalPayable' => (($row2['Total_Revenue'] * $rev_share) / 100) + 0,
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,
        ];
    }
    //'Channel-id','Video Id','Video title','ISRC','UPC','GRid','AssetId','assetChannelID','Youtube payout','RevShare','Final Payable'

    // $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}



function ExportClientsSpotifyMusicReport_countv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = ''
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "select  *   from 	  ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $result = array_map('strrev', explode('_', strrev($v)));
            $ndType = $result[2];

            if (!empty($search)) {
                $check_query_total[] = " select  COUNT('') as totalcount from   $v   WHERE {$v}.content_owner='{$client}' and  {$v}.Label  like '" . $search . "%'   ";
                
            } else {

                $check_query_total[] = " select  COUNT('') as totalcount from   $v 
                WHERE {$v}.content_owner='{$client}'    ";
               
            }
        }

    }

 

     

    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";
  // echo "<br>sql :::::  ". $youtubereport;
  @unlink("polo_export_count.txt");
  file_put_contents("polo_export_count.txt", $gettotalcountquery);
  @chmod("polo_export_count.txt", 0777);
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



function ExportClientsSpotifyMusicReportv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = ''
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "select  *   from 	  ";
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
             $gst_percentage = 0;
             $holding_percentage = 0;
             
             $getshare = "SELECT  shares , gst_percentage , holding_percentage   FROM 	report_audio_activation_{$nd_type}_{$year_mnoth} where content_owner = '" . $client . "'";
         
             $getshareres = runQuery($getshare, $conn);
             if (!noError($getshareres)) {
                //  @unlink("polo_export_getshare.txt");
                $ddd = "Error_in_query: ".PHP_EOL. $getshare.PHP_EOL.$getshareres["errMsg"].PHP_EOL;
                file_put_contents("polo_export_getshare.txt", $ddd,FILE_APPEND);
                @chmod("polo_export_getshare.txt", 0777);

                // return setErrorStack($returnArr, 3, $getshareres["errMsg"], null);
            }   
            else {
              $getshareresdata = mysqli_fetch_assoc($getshareres["dbResource"]);
      
                  if (!empty($getshareresdata)) {
                  
                      $rev_share = $getshareresdata['shares'];
                      $gst_percentage = $getshareresdata['gst_percentage'];
                      $holding_percentage = $getshareresdata['holding_percentage'];
                  }
          }

           

            if (!empty($search)) {
                
                $union_final_query[] = " ( SELECT  id, Country,Product,URI,UPC,EAN,ISRC,Track_name,Artist_name,Composer_name,Album_name,Quantity,Label  , sum(Payable_USD) as Total_Revenue , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage 
                    FROM $v

                where  {$v}.content_owner = '{$client}' and  ISRC like '" . $search . "%'   )  ";
            } else {

                
                $union_final_query[] = "   ( SELECT  id, Country,Product,URI,UPC,EAN,ISRC,Track_name,Artist_name,Composer_name,Album_name,Quantity,Label ,  sum(Payable_USD) as Total_Revenue  , {$rev_share} as rev_share, {$gst_percentage} as gst_percentage, {$holding_percentage} as holding_percentage 
                FROM $v 
                where  {$v}.content_owner = '{$client}'   group by id )  ";
            }
        }

    }

    

    $row = [];

    

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by Total_Revenue desc";

    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        //$youtubereport .= ' and video_title like "'.$search.'%"  ';
    }
    // echo "<br>sql :::::  ". $youtubereport;

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $youtubereport);
    @chmod("polo_export.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {
        // $row2['payout']=number_format($r['partnerRevenue'],2);
        // $row2['final_payable']=number_format($r['partnerRevenue'] * $rev_share,2) ;

        $rev_share = $row2['rev_share'] + 0;
        $gst_per  = $row2['gst_percentage'] + 0;

        $finalpayable =  (($row2['Total_Revenue'] * $rev_share) / 100) + 0;
        $final_payable_wth_gst = $finalpayable + ($finalpayable * $gst_per /100);
      

        $res['data'][] = [
            'Country' => isset($row2['Country']) ? $row2['Country'] : 'N/A',
            'Product' => isset($row2['Product']) ? $row2['Product'] : 'N/A',
            'URI' => isset($row2['URI']) ? $row2['URI'] : 'N/A',
            'UPC' => isset($row2['UPC']) ? $row2['UPC'] : 'N/A',
            'EAN' => isset($row2['EAN']) ? $row2['EAN'] : 'N/A',
            'ISRC' => isset($row2['ISRC']) ? $row2['ISRC'] : 'N/A',
            'Track_name' => isset($row2['Track_name']) ? $row2['Track_name'] : 'N/A',
            'Artist_name' => isset($row2['Artist_name']) ? $row2['Artist_name'] : 'N/A',
            'Composer_name' => isset($row2['Composer_name']) ? $row2['Composer_name'] : 'N/A',
            'Album_name' => isset($row2['Album_name']) ? $row2['Album_name'] : 'N/A',
            'Quantity' => isset($row2['Quantity']) ? $row2['Quantity'] : 'N/A',
            'Label' => isset($row2['Label']) ? $row2['Label'] : 'N/A',
            'FinalPayable' =>  $finalpayable,
            'gst_per' => $gst_per,
            'finalpayable_gst' => $final_payable_wth_gst,
        ];
    }
    //'Channel-id','Video Id','Video title','ISRC','UPC','GRid','AssetId','assetChannelID','Youtube payout','RevShare','Final Payable'

    // $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}

//non_cmg



function ExportClientReport_main_non_CMG_countv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = ''
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "select  *   from 	  ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $result = array_map('strrev', explode('_', strrev($v)));
            $ndType = $result[2];

            if (!empty($search)) {
                $check_query_total[] = " select  COUNT( '' ) as totalcount from   $v   WHERE {$v}.content_owner='{$client}'     ";
                
            } else {

                $check_query_total[] = " select  COUNT( '') as totalcount from   $v 
                WHERE {$v}.content_owner='{$client}'    ";
               
            }
        }

    }

 

     

    $check_query_total_new = implode(" union   ", $check_query_total);
    $gettotalcountquery = $check_query_total1 . " ( " . $check_query_total_new . " ) as final_total ";
  // echo "<br>sql :::::  ". $youtubereport;
  @unlink("polo_export_count.txt");
  file_put_contents("polo_export_count.txt", $gettotalcountquery);
  @chmod("polo_export_count.txt", 0777);
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



function ExportClientReport_main_non_CMGv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = ''
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "select  *   from 	  ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];

            
        
           

            if (!empty($search)) {
                
                $union_final_query[] = " ( `Country`,`Video_ID`,`Channel_ID`,`Asset_ID`,`Asset_Label`,`Asset_Channel_ID`,`Asset_Type`,`partnerRevenue`
                    FROM $v

                where  {$v}.content_owner = '{$client}'  )  ";
            } else {

                
                $union_final_query[] = "   ( SELECT  `Country`,`Video_ID`,`Channel_ID`,`Asset_ID`,`Asset_Label`,`Asset_Channel_ID`,`Asset_Type`,`partnerRevenue`
                FROM $v 
                where  {$v}.content_owner = '{$client}'   )  ";
            }
        }

    }

    

    $row = [];

    

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc";

    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        //$youtubereport .= ' and video_title like "'.$search.'%"  ';
    }
    // echo "<br>sql :::::  ". $youtubereport;

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $youtubereport);
    @chmod("polo_export.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {

         $res['data'][] = [
            'Country' => isset($row2['Country']) ? $row2['Country'] : 'N/A',
            'Video_ID' => isset($row2['Video_ID']) ? $row2['Video_ID'] : 'N/A',
            'Channel_ID' => isset($row2['Channel_ID']) ? $row2['Channel_ID'] : 'N/A',
            'Asset_ID' => isset($row2['Asset_ID']) ? $row2['Asset_ID'] : 'N/A',
            'Asset_Label' => isset($row2['Asset_Label']) ? $row2['Asset_Label'] : 'N/A',
            'Asset_Channel_ID' => isset($row2['Asset_Channel_ID']) ? $row2['Asset_Channel_ID'] : 'N/A',
            'Asset_Type' => isset($row2['Asset_Type']) ? $row2['Asset_Type'] : 'N/A',
            'Partner_Revenue' => isset($row2['partnerRevenue']) ? $row2['partnerRevenue'] : 'N/A',
            
        ];
    }
    //'Channel-id','Video Id','Video title','ISRC','UPC','GRid','AssetId','assetChannelID','Youtube payout','RevShare','Final Payable'

    // $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}

function ExportClientReport_red_subscription_non_CMGv2(
    $type,

    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $search = '',
    $client = null,
    $other_tables = ''
) {
    $gettotalcounts = 0;
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
    $union_final_query_sql = "select  *   from 	  ";
    $check_query_total = [];
    $union_final_query = [];
    $all_table = [];
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888

        foreach ($row as $k => $v) {
            $all_table[] = $v;

            $result = array_map('strrev', explode('_', strrev($v)));
            $table_name = $result[2];

            
        
           

            if (!empty($search)) {
                
                $union_final_query[] = " ( `Country`,`Video_ID`,`Video_Channel_ID`,`Asset_ID`,`Asset_Label`,`Asset_Channel_ID`,`partnerRevenue`
                    FROM $v

                where  {$v}.content_owner = '{$client}'  )  ";
            } else {

                
                $union_final_query[] = "   ( SELECT  `Country`,`Video_ID`,`Video_Channel_ID`,`Asset_ID`,`Asset_Label`,`Asset_Channel_ID`,`partnerRevenue`
                FROM $v 
                where  {$v}.content_owner = '{$client}'   )  ";
            }
        }

    }

    

    $row = [];

    

    $check_query_list_new = implode(" union   ", $union_final_query);
    $youtubereport = $union_final_query_sql . " ( " . $check_query_list_new . " )  as list_all order by partnerRevenue desc";

    // $youtubereport = "SELECT  videoID,channelID,video_title,assetChannelID,assetID,contentType,content_owner,Label, partnerRevenue  FROM $table2 where  content_owner = '".$client."' order by partnerRevenue desc";
    if ($offset !== null) {
        $youtubereport .= " LIMIT {$offset}, {$resultsPerPage}";
    }

    if (!empty($search)) {
        //$youtubereport .= ' and video_title like "'.$search.'%"  ';
    }
    // echo "<br>sql :::::  ". $youtubereport;

    @unlink("polo_export.txt");
    file_put_contents("polo_export.txt", $youtubereport);
    @chmod("polo_export.txt", 0777);

    $youtubereportresult = runQuery($youtubereport, $conn);
    $cdata = [];
    while ($row2 = mysqli_fetch_assoc($youtubereportresult["dbResource"])) {

         $res['data'][] = [
            'Country' => isset($row2['Country']) ? $row2['Country'] : 'N/A',
            'Video_ID' => isset($row2['Video_ID']) ? $row2['Video_ID'] : 'N/A',
            'Channel_ID' => isset($row2['Channel_ID']) ? $row2['Channel_ID'] : 'N/A',
            'Asset_ID' => isset($row2['Asset_ID']) ? $row2['Asset_ID'] : 'N/A',
            'Asset_Label' => isset($row2['Asset_Label']) ? $row2['Asset_Label'] : 'N/A',
            'Asset_Channel_ID' => isset($row2['Asset_Channel_ID']) ? $row2['Asset_Channel_ID'] : 'N/A',
            'Asset_Type' => isset($row2['Asset_Type']) ? $row2['Asset_Type'] : 'N/A',
            'Partner_Revenue' => isset($row2['partnerRevenue']) ? $row2['partnerRevenue'] : 'N/A',
            
        ];
    }
    //'Channel-id','Video Id','Video title','ISRC','UPC','GRid','AssetId','assetChannelID','Youtube payout','RevShare','Final Payable'

    // $res['total'] = $gettotalcounts;
    // print_r($res);
    return setErrorStack($returnArr, -1, $res, null);
}

function getRevenueStatsreport($data=array()){

    $returnArr = array();

    
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
    foreach($data as $k=>$value){
        $counter = $counter+1;
       
        $res_final['total_amt_recd'] =   $res_final['total_amt_recd'] + $value['youtube_payout'];
        $res_final['us_payout'] =   $res_final['us_payout']  + $value['USPAYOUT'];
        $res_final['witholding'] =   $res_final['witholding']  + $value['WITHHOLDING'];
        $res_final['final_payable'] =   $res_final['final_payable'] + $value['finalpayable'];
        // $res_final['amt_payable'] =  $res_final['amt_payable'] + $value['amt_payable'];
        $res_final['final_payable_with_gst'] =  $res_final['final_payable_with_gst'] + $value['finalpayable_gst'];
        $res_final['holding_percentage'] =  $res_final['holding_percentage'] + $value['holding_percentage'];
        $res_final['gst_percentage'] =  $res_final['gst_percentage'] + $value['gst_per'];
        $res_final['shares'] =  $res_final['shares'] + $value['rev_share'];
        
    }
     
    if($counter > 0){
        $res_final['gst_percentage'] =      $res_final['gst_percentage'] / $counter ;
        $res_final['shares'] =      $res_final['shares'] / $counter ;
        $res_final['holding_percentage'] =      $res_final['holding_percentage'] / $counter ;
        
    }
   
	return setErrorStack($returnArr, -1, $res_final, null);

}