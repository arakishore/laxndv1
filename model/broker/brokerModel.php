<?php
function getBrokersInfo(
	$fieldSearchArr=null,
	$fieldsStr="",
	$dateField=null,
	$conn,
	$offset=null,
	$resultsPerPage=10,
	$orderBy="broker_name"
)
{
	$res = array();
	$returnArr = array();
	$whereClause = "";
	
	//looping through array passed to create another array of where clauses
	foreach ($fieldSearchArr as $colName=>$searchVal) {
		if(!empty($whereClause))
			$whereClause .= " AND ";
		$whereClause .= "{$colName} = '{$searchVal}'";
	}

	if ($dateField!==null) {
		if ( !isset($dateField["fromDate"]) || empty($dateField["fromDate"]) ) {
			return setErrorStack($returnArr, 7, "Date Field missing from date", null);
		}
		if ( !isset($dateField["toDate"]) || empty($dateField["toDate"]) ) {
			return setErrorStack($returnArr, 7, "Date Field missing to date", null);
		}
		if(!empty($whereClause))
			$whereClause .= " AND ";
		$whereClause .= "DATE(created_on) BETWEEN '{$dateField["fromDate"]}' AND '{$dateField["toDate"]}'";
	}

	if (empty($fieldsStr))
		$fieldsStr = "*";

	$getBrokerInfoQuery = "SELECT {$fieldsStr} FROM crep_cms_broker";
	if (!empty($whereClause))
		$getBrokerInfoQuery .= " WHERE {$whereClause}";
	$getBrokerInfoQuery .= " ORDER BY ".$orderBy;
	if ($offset!==null)
		$getBrokerInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";
	$getBrokerInfoQueryResult = runQuery($getBrokerInfoQuery, $conn);
	if (!noError($getBrokerInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $getBrokerInfoQueryResult["errMsg"], null);
	}

	/* This function negotiates that an broker_id must be fetched from the database. All broker info is keyed by the broker's broker_id
	*  However, in case an broker_id is not desired, like in the case of fetching counts, a default broker_id of "-9999" will be used
	*/
	while ($row = mysqli_fetch_assoc($getBrokerInfoQueryResult["dbResource"])) {
		if (!isset($row["broker_id"]))
			$row["broker_id"] = "-9999";
			
		$res[$row["broker_id"]] = $row;
	}
	
	return setErrorStack($returnArr, -1, $res, null);
}

function deleteBroker($brokerSearchArr, $conn)
{
	$returnArr = array();
	$whereClause = "";

	if (empty($brokerSearchArr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to search cannot be empty", null);
	}
	
	//looping through array passed to create another array of where clauses
	foreach ($brokerSearchArr as $colName=>$searchVal) {
		if(!empty($whereClause))
			$whereClause .= " AND ";
		$whereClause .= "{$colName} = {$searchVal}";
	}

	$deleteBrokerInfoQuery = "DELETE FROM crep_cms_broker";
	if(!empty($whereClause))
		$deleteBrokerInfoQuery .= " WHERE {$whereClause}";
	
	$deleteBrokerInfoQueryResult = runQuery($deleteBrokerInfoQuery, $conn);
	if (!noError($deleteBrokerInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $deleteBrokerInfoQueryResult["errMsg"], null);
	}

	return setErrorStack($returnArr, -1, getErrMsg(-1), null);

}

function createBroker($brokerArr, $fieldsStr, $conn)
{
	$res = array();
	$returnArr = array();

	if (empty($brokerArr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to insert cannot be empty", null);
	}
	if (empty($fieldsStr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Fields String cannot be empty", null);
	}

	$allValuesStr = "";	
	$insertBrokerQuery = "INSERT INTO crep_cms_broker ({$fieldsStr}) VALUES ";
	foreach ($brokerArr as $brokerId=>$brokerDetails) {
		if (!empty($valuesStr)) {
			$allValuesStr .= ",";
		}
		$valuesStr = "";
		foreach ($brokerDetails as $colName=>$value) {
			$valuesStr .= $value.",";
		}
		$valuesStr = rtrim($valuesStr, ",");
		$allValuesStr .= "({$valuesStr})";
	}
	$insertBrokerQuery .= $allValuesStr;
	
	$insertBrokerQueryResult = runQuery($insertBrokerQuery, $conn);
	if (noError($insertBrokerQueryResult)) {
		return setErrorStack($returnArr, -1, $res, null);
	} else {
		return setErrorStack($returnArr, 3, $insertBrokerQueryResult["errMsg"], null);
	}
}

function updateBrokerInfo($arrToUpdate=null, $fieldSearchArr=null, $conn)
{
	$res = array();
	$returnArr = array();

	if (empty($arrToUpdate)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to update cannot be empty", null);
	}
	if (empty($fieldSearchArr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to search cannot be empty", null);
	}
	
	$arrToUpdate['updated_on'] = date("Y-m-d h:m:s");
	$setClause = "";
	$whereClause = "";
	
	//looping through array passed to create another array of where clauses
	foreach ($fieldSearchArr as $colName=>$searchVal) {
		if(!empty($whereClause))
			$whereClause .= " AND ";
		$whereClause .= "{$colName} = '{$searchVal}'";
	}
	
	//looping through array passed to create another array of where clauses
	foreach ($arrToUpdate as $colName=>$setVal) {
		if(!empty($setClause))
			$setClause .= ", ";
		$setClause .= "{$colName} = '{$setVal}'";
	}

	$updateBrokerQuery = "UPDATE crep_cms_broker SET {$setClause} WHERE {$whereClause}";
	$updateBrokerQueryResult = runQuery($updateBrokerQuery, $conn);
	if (noError($updateBrokerQueryResult)) {
		return setErrorStack($returnArr, -1, $res, null);
	} else {
		return setErrorStack($returnArr, 3, $updateBrokerQueryResult["errMsg"], null);
	}
}
?>

