<?php


function getClientsInfo_email_v5(
    $fieldSearchArr = null,
    $fieldsStr = "",
    $dateField = null,
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $orderBy = "client_username"
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

    if ($dateField !== null) {
        if (!isset($dateField["fromDate"]) || empty($dateField["fromDate"])) {
            return setErrorStack($returnArr, 7, "Date Field missing from date", null);
        }
        if (!isset($dateField["toDate"]) || empty($dateField["toDate"])) {
            return setErrorStack($returnArr, 7, "Date Field missing to date", null);
        }
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "inception_date BETWEEN '{$dateField["fromDate"]}' AND '{$dateField["toDate"]}'";
    }

    if (empty($fieldsStr)) {
        $fieldsStr = "*";
    }

    $getClientInfoQuery = "SELECT  {$fieldsStr},client_id FROM crep_cms_clients";
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
        if (!isset($row["email"])) {
            $row["email"] = "anonymous";
        }

        $res[strtolower(trim($row["email"]))] = $row;
    }

    return setErrorStack($returnArr, -1, $res, null);
}

function getClientsInfo_email(
    $fieldSearchArr = null,
    $fieldsStr = "",
    $dateField = null,
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $orderBy = "client_username"
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

    if ($dateField !== null) {
        if (!isset($dateField["fromDate"]) || empty($dateField["fromDate"])) {
            return setErrorStack($returnArr, 7, "Date Field missing from date", null);
        }
        if (!isset($dateField["toDate"]) || empty($dateField["toDate"])) {
            return setErrorStack($returnArr, 7, "Date Field missing to date", null);
        }
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "inception_date BETWEEN '{$dateField["fromDate"]}' AND '{$dateField["toDate"]}'";
    }

    if (empty($fieldsStr)) {
        $fieldsStr = "*";
    }

    $getClientInfoQuery = "SELECT  {$fieldsStr},client_id FROM crep_cms_clients";
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
        if (!isset($row["email"])) {
            $row["email"] = "anonymous";
        }

        $res[trim($row["email"])] = $row;
    }

    return setErrorStack($returnArr, -1, $res, null);
}

function getClientsInfo(
    $fieldSearchArr = null,
    $fieldsStr = "",
    $dateField = null,
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $orderBy = "client_username"
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";

    //looping through array passed to create another array of where clauses

    foreach ($fieldSearchArr as $colName => $searchVal) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "{$colName} like '%{$searchVal}%'";
    }

    if ($dateField !== null) {
        if (!isset($dateField["fromDate"]) || empty($dateField["fromDate"])) {
            return setErrorStack($returnArr, 7, "Date Field missing from date", null);
        }
        if (!isset($dateField["toDate"]) || empty($dateField["toDate"])) {
            return setErrorStack($returnArr, 7, "Date Field missing to date", null);
        }
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "inception_date BETWEEN '{$dateField["fromDate"]}' AND '{$dateField["toDate"]}'";
    }

    if (empty($fieldsStr)) {
        $fieldsStr = "*";
    }

    $getClientInfoQuery = "SELECT  {$fieldsStr},client_id FROM crep_cms_clients";
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
        if (!isset($row["client_id"])) {
            $row["client_id"] = "anonymous";
        }

        $res[$row["client_id"]] = $row;
    }

    return setErrorStack($returnArr, -1, $res, null);
}
function getClientsInfo_showinfo(
    $fieldSearchArr = null,
    $fieldsStr = "",
    $dateField = null,
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $orderBy = "client_username"
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";

    //looping through array passed to create another array of where clauses

    foreach ($fieldSearchArr as $colName => $searchVal) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "{$colName} like '{$searchVal}'";
    }

    if ($dateField !== null) {
        if (!isset($dateField["fromDate"]) || empty($dateField["fromDate"])) {
            return setErrorStack($returnArr, 7, "Date Field missing from date", null);
        }
        if (!isset($dateField["toDate"]) || empty($dateField["toDate"])) {
            return setErrorStack($returnArr, 7, "Date Field missing to date", null);
        }
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "inception_date BETWEEN '{$dateField["fromDate"]}' AND '{$dateField["toDate"]}'";
    }

    if (empty($fieldsStr)) {
        $fieldsStr = "*";
    }

    $getClientInfoQuery = "SELECT  {$fieldsStr},client_id FROM crep_cms_clients";
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
        if (!isset($row["client_id"])) {
            $row["client_id"] = "anonymous";
        }

        $res[$row["client_id"]] = $row;
    }

    return setErrorStack($returnArr, -1, $res, null);
}
function getClientsInfo_org(
    $fieldSearchArr = null,
    $fieldsStr = "",
    $dateField = null,
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $orderBy = "client_username"
) {
    $res = array();
    $returnArr = array();
    $whereClause = "";

    //looping through array passed to create another array of where clauses

    foreach ($fieldSearchArr as $colName => $searchVal) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "{$colName} like '%{$searchVal}%'";
    }

    if ($dateField !== null) {
        if (!isset($dateField["fromDate"]) || empty($dateField["fromDate"])) {
            return setErrorStack($returnArr, 7, "Date Field missing from date", null);
        }
        if (!isset($dateField["toDate"]) || empty($dateField["toDate"])) {
            return setErrorStack($returnArr, 7, "Date Field missing to date", null);
        }
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "inception_date BETWEEN '{$dateField["fromDate"]}' AND '{$dateField["toDate"]}'";
    }

    if (empty($fieldsStr)) {
        $fieldsStr = "*";
    }

    $getClientInfoQuery = "SELECT  {$fieldsStr} FROM crep_cms_clients";
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
        if (!isset($row["client_id"])) {
            $row["client_id"] = "anonymous";
        }

        $res[$row["client_id"]] = $row;
    }

    return setErrorStack($returnArr, -1, $res, null);
}
function deleteClientInfo($clientSearchArr, $conn)
{
    $returnArr = array();
    $whereClause = "";

    if (empty($clientSearchArr)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Array to search cannot be empty", null);
    }

    //looping through array passed to create another array of where clauses
    foreach ($clientSearchArr as $colName => $searchVal) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "{$colName} = {$searchVal}";
    }

    $deleteClientInfoQuery = "DELETE FROM crep_cms_clients";
    if (!empty($whereClause)) {
        $deleteClientInfoQuery .= " WHERE {$whereClause}";
    }

    $deleteClientInfoQueryResult = runQuery($deleteClientInfoQuery, $conn);
    if (!noError($deleteClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $deleteClientInfoQueryResult["errMsg"], null);
    }

    return setErrorStack($returnArr, -1, getErrMsg(-1), null);

}

function createClient($clientArr, $fieldsStr, $conn)
{
    $res = array();
    $returnArr = array();

    if (empty($clientArr)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Array to insert cannot be empty", null);
    }
    if (empty($fieldsStr)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Fields String cannot be empty", null);
    }

    $allValuesStr = "";
    $insertClientQuery = "INSERT INTO crep_cms_clients ({$fieldsStr}) VALUES ";
    foreach ($clientArr as $userName => $clientDetails) {
        if (!empty($valuesStr)) {
            $allValuesStr .= ",";
        }
        $valuesStr = "";
        foreach ($clientDetails as $colName => $value) {
            $valuesStr .= $value . ",";
        }
        $valuesStr = rtrim($valuesStr, ",");
        $allValuesStr .= "({$valuesStr})";
    }
    $insertClientQuery .= $allValuesStr;

    @unlink("polo.txt");
    file_put_contents("polo.txt", $insertClientQuery);
    @chmod("polo.txt", 0777);

    $insertClientQueryResult = runQuery($insertClientQuery, $conn);

    @unlink("polo_error.txt");
    file_put_contents("polo_error.txt", $insertClientQueryResult);
    @chmod("polo_error.txt", 0777);

    if (noError($insertClientQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $insertClientQueryResult["errMsg"], null);
    }
}

function updateClientInfo($arrToUpdate = null, $fieldSearchArr = null, $conn)
{
    $res = array();
    $returnArr = array();

    if (empty($arrToUpdate)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Array to update cannot be empty", null);
    }
    if (empty($fieldSearchArr)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Array to search cannot be empty", null);
    }

    $arrToUpdate['updated_on'] = date("Y-m-d h:m:s");
    $setClause = "";
    $whereClause = "";

    //looping through array passed to create another array of where clauses
    foreach ($fieldSearchArr as $colName => $searchVal) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "{$colName} = '{$searchVal}'";
    }

    //looping through array passed to create another array of where clauses
    foreach ($arrToUpdate as $colName => $setVal) {
        if (!empty($setClause)) {
            $setClause .= ", ";
        }

        $setClause .= "{$colName} = '{$setVal}'";
    }
    $updateClientQuery = "UPDATE crep_cms_clients SET {$setClause} WHERE {$whereClause}";

    @unlink("polo.txt");
    file_put_contents("polo.txt", $updateClientQuery);
    @chmod("polo.txt", 0777);

    $updateClientQueryResult = runQuery($updateClientQuery, $conn);

    if (noError($updateClientQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateClientQueryResult["errMsg"], null);
    }
}
function getAvilableActivateReports($type, $client, $conn)
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

        foreach ($row as $k => $v) {

            $check = "SELECT EXISTS(SELECT * from $v WHERE content_owner='{$client}'  and `status`='active' ) as available";

            $checkresult = runQuery($check, $conn);

            $test = mysqli_fetch_assoc($checkresult["dbResource"]);
            if ($test['available'] == 1) {
                $a = explode($type . '_report_', $v);
                $tt = str_replace('_', '-', $a[1]);

                $res[] = $tt;
            }
        }

    }

    return setErrorStack($returnArr, -1, $res, null);
}
function getAvilableActivateReportsYoutubev2($type, $client, $conn)
{
    $res = array();
    $returnArr = array();

    //echo "<br>check ".
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
        foreach ($row as $k => $v) {

            //echo "<br>  ".
            $check = "SELECT EXISTS(SELECT * from $v WHERE content_owner='{$client}'  and `status`='active' ) as available";

            $checkresult = runQuery($check, $conn);

            $test = mysqli_fetch_assoc($checkresult["dbResource"]);

            if ($test['available'] == 1) {
                //    print_r($v);
                $result = array_map('strrev', explode('_', strrev($v)));
                //    $a = explode($type.'',$v);
                //    $b = explode('_',$a[1]);
                $c = $result[1] . "-" . $result[0];

                $tt = $c;

                $res[] = $tt;
            }
        }

    }
    $res = array_unique($res);
    return setErrorStack($returnArr, -1, $res, null);
}

function getAvilableActivateReportsFinanceV2($type, $client, $conn)
{
    $res = array();
    $returnArr = array();

    //echo "<br>check ".
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
        foreach ($row as $k => $v) {

            //    echo "<br>check ".$type .
            $check = "SELECT EXISTS(SELECT * from $v WHERE content_owner='{$client}'  and `status`='active' ) as available";

            $checkresult = runQuery($check, $conn);

            $test = mysqli_fetch_assoc($checkresult["dbResource"]);

            if ($test['available'] == 1) {

                $result = array_map('strrev', explode('_', strrev($v)));

                //    $a = explode($type.'',$v);

                //    $b = explode('_',$a[1]);

                $c = $result[1] . "-" . $result[0];

                $tt = $c;

                $res[] = $tt;
            }
        }

    }
    $res = array_unique($res);
    return setErrorStack($returnArr, -1, $res, null);
}

function updateClientInfoMapping($arrToUpdate = null, $fieldSearchArr = null, $conn)
{
    $res = array();
    $returnArr = array();

    if (empty($arrToUpdate)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Array to update cannot be empty", null);
    }
    if (empty($fieldSearchArr)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Array to search cannot be empty", null);
    }

    $arrToUpdate['updated_on'] = date("Y-m-d h:m:s");
    $setClause = "";
    $whereClause = "";

    //looping through array passed to create another array of where clauses
    foreach ($fieldSearchArr as $colName => $searchVal) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "{$colName} = '{$searchVal}'";
    }

    //looping through array passed to create another array of where clauses
    foreach ($arrToUpdate as $colName => $setVal) {
        if (!empty($setClause)) {
            $setClause .= ", ";
        }

        $setClause .= "{$colName} = '{$setVal}'";
    }
    $updateClientQuery = "UPDATE channel_co_maping_amazon SET {$setClause} WHERE {$whereClause}";
    //file_put_contents("clientModel.txt",$updateClientQuery);

    $updateClientQueryResult = runQuery($updateClientQuery, $conn);
    if (noError($updateClientQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateClientQueryResult["errMsg"], null);
    }
}

function createClientMapping($clientArr, $fieldsStr, $conn)
{
    $res = array();
    $returnArr = array();

    if (empty($clientArr)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Array to insert cannot be empty", null);
    }
    if (empty($fieldsStr)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Fields String cannot be empty", null);
    }

    $allValuesStr = "";
    $insertClientQuery = "INSERT INTO channel_co_maping_amazon ({$fieldsStr}) VALUES ";
    foreach ($clientArr as $userName => $clientDetails) {
        if (!empty($valuesStr)) {
            $allValuesStr .= ",";
        }
        $valuesStr = "";
        foreach ($clientDetails as $colName => $value) {
            $valuesStr .= $value . ",";
        }
        $valuesStr = rtrim($valuesStr, ",");
        $allValuesStr .= "({$valuesStr})";
    }
    $insertClientQuery .= $allValuesStr;

    //    @unlink("clientModel.txt");
    //  file_put_contents("clientModel2.txt",$insertClientQuery);

    $insertClientQueryResult = runQuery($insertClientQuery, $conn);
    if (noError($insertClientQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $insertClientQueryResult["errMsg"], null);
    }
}

function deleteClientInfoAmazon($clientSearchArr, $conn)
{
    $returnArr = array();
    $whereClause = "";

    if (empty($clientSearchArr)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Array to search cannot be empty", null);
    }

    //looping through array passed to create another array of where clauses
    foreach ($clientSearchArr as $colName => $searchVal) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "{$colName} = {$searchVal}";
    }

    $deleteClientInfoQuery = "DELETE FROM channel_co_maping_amazon";
    if (!empty($whereClause)) {
        $deleteClientInfoQuery .= " WHERE {$whereClause}";
    }

    //  @unlink("clientModel.txt");
    file_put_contents("clientModel3.txt", $deleteClientInfoQuery);

    $deleteClientInfoQueryResult = runQuery($deleteClientInfoQuery, $conn);
    if (!noError($deleteClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $deleteClientInfoQueryResult["errMsg"], null);
    }

    return setErrorStack($returnArr, -1, getErrMsg(-1), null);

}

function updateClientInfoMappingYoutube($arrToUpdate = null, $fieldSearchArr = null, $conn)
{
    $res = array();
    $returnArr = array();

    if (empty($arrToUpdate)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Array to update cannot be empty", null);
    }
    if (empty($fieldSearchArr)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Array to search cannot be empty", null);
    }

    $setClause = "";
    $whereClause = "";

    //looping through array passed to create another array of where clauses
    foreach ($fieldSearchArr as $colName => $searchVal) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "{$colName} = '{$searchVal}'";
    }

    //looping through array passed to create another array of where clauses
    foreach ($arrToUpdate as $colName => $setVal) {
        if (!empty($setClause)) {
            $setClause .= ", ";
        }

        $setClause .= "{$colName} = '{$setVal}'";
    }
    $updateClientQuery = "UPDATE channel_co_maping SET {$setClause} WHERE {$whereClause}";
    file_put_contents("polo.txt", $updateClientQuery);

    $updateClientQueryResult = runQuery($updateClientQuery, $conn);
    if (noError($updateClientQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $updateClientQueryResult["errMsg"], null);
    }
}

function createClientMappingYoutube($clientArr, $fieldsStr, $conn)
{
    $res = array();
    $returnArr = array();

    if (empty($clientArr)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Array to insert cannot be empty", null);
    }
    if (empty($fieldsStr)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Fields String cannot be empty", null);
    }

    $allValuesStr = "";
    $insertClientQuery = "INSERT INTO channel_co_maping ({$fieldsStr}) VALUES ";
    foreach ($clientArr as $userName => $clientDetails) {
        if (!empty($valuesStr)) {
            $allValuesStr .= ",";
        }
        $valuesStr = "";
        foreach ($clientDetails as $colName => $value) {
            $valuesStr .= $value . ",";
        }
        $valuesStr = rtrim($valuesStr, ",");
        $allValuesStr .= "({$valuesStr})";
    }
    $insertClientQuery .= $allValuesStr;

/*     @unlink("polo.txt");
file_put_contents("polo.txt", $insertClientQuery);
@chmod("polo.txt",0777);
 */

    $insertClientQueryResult = runQuery($insertClientQuery, $conn);
    if (noError($insertClientQueryResult)) {
        return setErrorStack($returnArr, -1, $res, null);
    } else {
        return setErrorStack($returnArr, 3, $insertClientQueryResult["errMsg"], null);
    }
}

function getClientsInfoYoutube(
    $fieldSearchArr = null,
    $fieldsStr = "",
    $dateField = null,
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $orderBy = "id"
) {
    $res = array();
    $returnArr = array();
    $whereClause = "( cc.client_username = ccm.partner_provided or cc.client_username = ccm.ugc)";

    //looping through array passed to create another array of where clauses

    foreach ($fieldSearchArr as $colName => $searchVal) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }
        if($searchVal!=""){
            $searchVal = trim($searchVal);
            $whereClause .= "{$colName} like '%{$searchVal}%'";
        }
       
    }

    if ($dateField !== null) {
        if (!isset($dateField["fromDate"]) || empty($dateField["fromDate"])) {
            return setErrorStack($returnArr, 7, "Date Field missing from date", null);
        }
        if (!isset($dateField["toDate"]) || empty($dateField["toDate"])) {
            return setErrorStack($returnArr, 7, "Date Field missing to date", null);
        }
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= " inception_date BETWEEN '{$dateField["fromDate"]}' AND '{$dateField["toDate"]}'";
    }

    if (empty($fieldsStr)) {
        $fieldsStr = "*";
    }

    $getClientInfoQuery = "SELECT distinct {$fieldsStr} , client_id FROM crep_cms_clients cc, 	channel_co_maping ccm ";
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
        if (!isset($row["client_id"])) {
            $row["client_id"] = "anonymous";
        }

        $res[] = $row;
    }

    return setErrorStack($returnArr, -1, $res, null);
}

function getClientsInfoYoutubeDownload(
    $fieldSearchArr = null,
    $fieldsStr = "",
    $dateField = null,
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $orderBy = "id"
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

    $getClientInfoQuery = "SELECT distinct {$fieldsStr}  FROM channel_co_maping ccm ";
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
function getClientsInfoYoutube_org(
    $fieldSearchArr = null,
    $fieldsStr = "",
    $dateField = null,
    $conn,
    $offset = null,
    $resultsPerPage = 10,
    $orderBy = "id"
) {
    $res = array();
    $returnArr = array();
    $whereClause = "( cc.client_username = ccm.partner_provided or cc.client_username = ccm.ugc)";

    //looping through array passed to create another array of where clauses

    foreach ($fieldSearchArr as $colName => $searchVal) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "{$colName} = '{$searchVal}'";
    }

    if ($dateField !== null) {
        if (!isset($dateField["fromDate"]) || empty($dateField["fromDate"])) {
            return setErrorStack($returnArr, 7, "Date Field missing from date", null);
        }
        if (!isset($dateField["toDate"]) || empty($dateField["toDate"])) {
            return setErrorStack($returnArr, 7, "Date Field missing to date", null);
        }
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "inception_date BETWEEN '{$dateField["fromDate"]}' AND '{$dateField["toDate"]}'";
    }

    if (empty($fieldsStr)) {
        $fieldsStr = "*";
    }

    $getClientInfoQuery = "SELECT distinct {$fieldsStr} FROM crep_cms_clients cc, 	channel_co_maping ccm ";
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
        if (!isset($row["client_id"])) {
            $row["client_id"] = "anonymous";
        }

        $res[] = $row;
    }

    return setErrorStack($returnArr, -1, $res, null);
}
function deleteClientInfoYoutubeComapping($clientSearchArr, $conn)
{
    $returnArr = array();
    $whereClause = "";

    if (empty($clientSearchArr)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Array to search cannot be empty", null);
    }

    //looping through array passed to create another array of where clauses
    foreach ($clientSearchArr as $colName => $searchVal) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "{$colName} = {$searchVal}";
    }

    $deleteClientInfoQuery = "DELETE FROM channel_co_maping";
    if (!empty($whereClause)) {
        $deleteClientInfoQuery .= " WHERE {$whereClause}";
    }

    //  @unlink("clientModel.txt");
    file_put_contents("clientModel3.txt", $deleteClientInfoQuery);

    $deleteClientInfoQueryResult = runQuery($deleteClientInfoQuery, $conn);
    if (!noError($deleteClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $deleteClientInfoQueryResult["errMsg"], null);
    }

    return setErrorStack($returnArr, -1, getErrMsg(-1), null);

}

function deleteActivityLog($clientSearchArr, $conn)
{
    $returnArr = array();
    $whereClause = "";

    if (empty($clientSearchArr)) {
        return setErrorStack($returnArr, 4, getErrMsg(4) . " Array to search cannot be empty", null);
    }

    //looping through array passed to create another array of where clauses
    foreach ($clientSearchArr as $colName => $searchVal) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ";
        }

        $whereClause .= "{$colName} = {$searchVal}";
    }

    $deleteClientInfoQuery = "DELETE FROM 	activity_downlaod_report";
    if (!empty($whereClause)) {
        $deleteClientInfoQuery .= " WHERE {$whereClause}";
    }

    //  @unlink("clientModel.txt");

    $deleteClientInfoQueryResult = runQuery($deleteClientInfoQuery, $conn);
    if (!noError($deleteClientInfoQueryResult)) {
        return setErrorStack($returnArr, 3, $deleteClientInfoQueryResult["errMsg"], null);
    }

    return setErrorStack($returnArr, -1, getErrMsg(-1), null);

}

function getAvilableActivateReportsMain_non_cmgv2($type, $client, $conn)
{
    $res = array();
    $returnArr = array();

    //echo "<br>check ".
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
        foreach ($row as $k => $v) {

            //echo "<br>  ".
            $check = "SELECT EXISTS(SELECT * from $v WHERE content_owner='{$client}'  limit 0,1 ) as available";

            $checkresult = runQuery($check, $conn);

            $test = mysqli_fetch_assoc($checkresult["dbResource"]);

            if ($test['available'] == 1) {
                //    print_r($v);
                $result = array_map('strrev', explode('_', strrev($v)));
                //    $a = explode($type.'',$v);
                //    $b = explode('_',$a[1]);
                $c = $result[1] . "-" . $result[0];

                $tt = $c;

                $res[] = $tt;
            }
        }

    }
    $res = array_unique($res);
    return setErrorStack($returnArr, -1, $res, null);
}

function getActivationReportSummaryNONCMGv2($type, $clientSearchArr, $client, $conn)
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

        foreach ($row as $k => $v) {

            $check = "SELECT sum(partnerRevenue) as partnerRevenue  from $v WHERE content_owner='{$client}' ";

            $checkresult = runQuery($check, $conn);

            $resultQyeryscheck = mysqli_num_rows($checkresult["dbResource"]);
            if ($resultQyeryscheck > 0) {
                $test = mysqli_fetch_assoc($checkresult["dbResource"]);
                $res[] = $test;
            }

        }

    }
    $res_final['partnerRevenue'] = 0;

    foreach ($res as $k => $value) {

        $res_final['total_amt_recd'] = $res_final['partnerRevenue'] + $value['partnerRevenue'];

    }

    return setErrorStack($returnArr, -1, $res_final, null);
}

function getPublisherReportv2(
    $selectedDate, $conn

) {
    $res = array();
    $returnArr = array();
   
    $table = 'report_publishing_main_non_cmg_' . $selectedDate;

	    $getClientInfoQuery = "SELECT 'Main' as typename,  Asset_Label,content_owner , sum(partnerRevenue) as partnerRevenue FROM {$table}  GROUP by content_owner ";
 
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

    // now for table 2
    $activatetableName = 'report_publishing_redsubscription_non_cmg_' . $selectedDate;
    $tableArr = checkTableExist($activatetableName, $conn);
    if ($tableArr['errMsg'] == '1') {

         $getClientInfoQuery = "SELECT 'Red subscription' as typename, Asset_Label,content_owner , sum(partnerRevenue) as partnerRevenue FROM {$activatetableName}  GROUP by content_owner ";

        //echo $getClientInfoQuery;
        $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
        while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {

            $res[] = $row;
        }
    }

    return setErrorStack($returnArr, -1, $res, null);
}
