<?php
function getMonthlyRateInfo(
	$fieldSearchArr=null,
	$fieldsStr="",
	$dateField=null,
	$conn,
	$offset=null,
	$resultsPerPage=10,
	$orderBy="rate_id"
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

	$getMonthlyRateInfoQuery = "SELECT {$fieldsStr} FROM monthly_exchange_rate";
	if (!empty($whereClause))
		$getMonthlyRateInfoQuery .= " WHERE {$whereClause}";
	$getMonthlyRateInfoQuery .= " ORDER BY ".$orderBy;
	if ($offset!==null)
		$getMonthlyRateInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";
	$getMonthlyRateInfoQueryResult = runQuery($getMonthlyRateInfoQuery, $conn);
	if (!noError($getMonthlyRateInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $getMonthlyRateInfoQueryResult["errMsg"], null);
	}

	/* This function negotiates that an distributor_id must be fetched from the database. All distributor info is keyed by the distributor's distributor_id
	*  However, in case an distributor_id is not desired, like in the case of fetching counts, a default distributor_id of "-9999" will be used
	*/
	while ($row = mysqli_fetch_assoc($getMonthlyRateInfoQueryResult["dbResource"])) {
		if (!isset($row["rate_id"]))
			$row["rate_id"] = "-9999";
			
		$res[$row["rate_id"]] = $row;
	}
	
	return setErrorStack($returnArr, -1, $res, null);
}

function deleteMonthlyRate($rateSearchArr, $conn)
{
	$returnArr = array();
	$whereClause = "";

	if (empty($rateSearchArr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to search cannot be empty", null);
	}
	
	//looping through array passed to create another array of where clauses
	foreach ($rateSearchArr as $colName=>$searchVal) {
		if(!empty($whereClause))
			$whereClause .= " AND ";
		$whereClause .= "{$colName} = {$searchVal}";
	}

	$deleteMonthlyRateInfoQuery = "DELETE FROM monthly_exchange_rate";
	if(!empty($whereClause))
		$deleteMonthlyRateInfoQuery .= " WHERE {$whereClause}";
	
	$deleteMonthlyRateInfoQueryResult = runQuery($deleteMonthlyRateInfoQuery, $conn);
	if (!noError($deleteMonthlyRateInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $deleteMonthlyRateInfoQueryResult["errMsg"], null);
	}

	return setErrorStack($returnArr, -1, getErrMsg(-1), null);

}

function createMonthlyRate($rateArr, $fieldsStr, $conn)
{
	$res = array();
	$returnArr = array();

	if (empty($rateArr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to insert cannot be empty", null);
	}
	if (empty($fieldsStr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Fields String cannot be empty", null);
	}

	$allValuesStr = "";	

	$insertMonthlyRateQuery = "INSERT INTO monthly_exchange_rate ({$fieldsStr}) VALUES ";
	foreach ($rateArr as $rateId=>$rateDetails) {
		if (!empty($valuesStr)) {
			$allValuesStr .= ",";
		}
		$valuesStr = "";
		foreach ($rateDetails as $colName=>$value) {
			$valuesStr .= $value.",";
		}
		$valuesStr = rtrim($valuesStr, ",");
		$allValuesStr .= "({$valuesStr})";
	}
	$insertMonthlyRateQuery .= $allValuesStr;
	
	$insertMonthlyRateQueryResult = runQuery($insertMonthlyRateQuery, $conn);
	if (noError($insertMonthlyRateQueryResult)) {
		return setErrorStack($returnArr, -1, $res, null);
	} else {
		return setErrorStack($returnArr, 3, $insertMonthlyRateQueryResult["errMsg"], null);
	}
}

function updateMonthlyRateInfo($arrToUpdate=null, $fieldSearchArr=null, $conn)
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

	$updateDistributorQuery = "UPDATE monthly_exchange_rate SET {$setClause} WHERE {$whereClause}";
	$updateDistributorQueryResult = runQuery($updateDistributorQuery, $conn);
	if (noError($updateDistributorQueryResult)) {
		return setErrorStack($returnArr, -1, $res, null);
	} else {
		return setErrorStack($returnArr, 3, $updateDistributorQueryResult["errMsg"], null);
	}
}
?>