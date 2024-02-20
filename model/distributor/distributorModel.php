<?php
function getDistributorsInfo(
	$fieldSearchArr=null,
	$fieldsStr="",
	$dateField=null,
	$conn,
	$offset=null,
	$resultsPerPage=10,
	$orderBy="distributor_name"
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

	$getDistributorInfoQuery = "SELECT {$fieldsStr} FROM crep_cms_distributor";
	if (!empty($whereClause))
		$getDistributorInfoQuery .= " WHERE {$whereClause}";
	$getDistributorInfoQuery .= " ORDER BY ".$orderBy;
	if ($offset!==null)
		$getDistributorInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";
	$getDistributorInfoQueryResult = runQuery($getDistributorInfoQuery, $conn);
	if (!noError($getDistributorInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $getDistributorInfoQueryResult["errMsg"], null);
	}


	/* This function negotiates that an distributor_id must be fetched from the database. All distributor info is keyed by the distributor's distributor_id
	*  However, in case an distributor_id is not desired, like in the case of fetching counts, a default distributor_id of "-9999" will be used
	*/
	while ($row = mysqli_fetch_assoc($getDistributorInfoQueryResult["dbResource"])) {
		if (!isset($row["email"]))
			$row["email"] = "-9999";
			
		// $res = $row;
		$res[$row["email"]] = $row;
	}
	
	return setErrorStack($returnArr, -1, $res, null);
}

function deleteDistributor($distributorSearchArr, $conn)
{
	$returnArr = array();
	$whereClause = "";

	if (empty($distributorSearchArr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to search cannot be empty", null);
	}
	
	//looping through array passed to create another array of where clauses
	foreach ($distributorSearchArr as $colName=>$searchVal) {
		if(!empty($whereClause))
			$whereClause .= " AND ";
		$whereClause .= "{$colName} = {$searchVal}";
	}

	$deleteDistributorInfoQuery = "DELETE FROM crep_cms_distributor";
	if(!empty($whereClause))
		$deleteDistributorInfoQuery .= " WHERE {$whereClause}";
	
	$deleteDistributorInfoQueryResult = runQuery($deleteDistributorInfoQuery, $conn);
	if (!noError($deleteDistributorInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $deleteDistributorInfoQueryResult["errMsg"], null);
	}

	return setErrorStack($returnArr, -1, getErrMsg(-1), null);

}

function createDistributor($distributorArr, $fieldsStr, $conn)
{
	$res = array();
	$returnArr = array();

	if (empty($distributorArr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to insert cannot be empty", null);
	}
	if (empty($fieldsStr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Fields String cannot be empty", null);
	}

	$allValuesStr = "";	
	$insertDistributorQuery = "INSERT INTO crep_cms_distributor ({$fieldsStr}) VALUES ";
	foreach ($distributorArr as $distributorId=>$distributorDetails) {
		if (!empty($valuesStr)) {
			$allValuesStr .= ",";
		}
		$valuesStr = "";
		foreach ($distributorDetails as $colName=>$value) {
			$valuesStr .= $value.",";
		}
		$valuesStr = rtrim($valuesStr, ",");
		$allValuesStr .= "({$valuesStr})";
	}
	$insertDistributorQuery .= $allValuesStr;
	
	$insertDistributorQueryResult = runQuery($insertDistributorQuery, $conn);
	if (noError($insertDistributorQueryResult)) {
		return setErrorStack($returnArr, -1, $res, null);
	} else {
		return setErrorStack($returnArr, 3, $insertDistributorQueryResult["errMsg"], null);
	}
}

function updateDistributorInfo($arrToUpdate=null, $fieldSearchArr=null, $conn)
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

	$updateDistributorQuery = "UPDATE crep_cms_distributor SET {$setClause} WHERE {$whereClause}";
	$updateDistributorQueryResult = runQuery($updateDistributorQuery, $conn);
	if (noError($updateDistributorQueryResult)) {
		return setErrorStack($returnArr, -1, $res, null);
	} else {
		return setErrorStack($returnArr, 3, $updateDistributorQueryResult["errMsg"], null);
	}
}
?>