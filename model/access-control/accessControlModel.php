<?php

function getGroupInfo($fieldSearchArr=null, $fieldsStr="", $conn, $offset=null, $resultsPerPage=10)
{
	$res = array();
	$returnArr = array();
	$whereClause = "";
	
	//looping through array passed to create another array of where clauses
	if (!is_null($fieldSearchArr)) {
		foreach ($fieldSearchArr as $colName=>$searchVal) {
			if(!empty($whereClause))
				$whereClause .= " AND ";
			$whereClause .= "{$colName} = '{$searchVal}'";
		}
	}

	if(empty($fieldsStr))
		$fieldsStr = "*";

	$getGroupInfoQuery = "SELECT {$fieldsStr} FROM crep_cms_groups";
	if(!empty($whereClause))
		$getGroupInfoQuery .= " WHERE {$whereClause}";
	if($offset!==null)
		$getGroupInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";
	
	$getGroupInfoQueryResult = runQuery($getGroupInfoQuery, $conn);
	if (!noError($getGroupInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $getGroupInfoQueryResult["errMsg"], null);
	}

	/* This function negotiates that a group_id must be fetched from the database. All user info is keyed by the group's id
	*  However, in case an id is not desired, like in the case of fetching counts, a default ID of "-9999" will be used
	*/
	while ($row = mysqli_fetch_assoc($getGroupInfoQueryResult["dbResource"])) {
		if(!isset($row["group_id"]))
			$row["group_id"] = "-9999";
			
		$res[$row["group_id"]] = $row;
	}
	
	return setErrorStack($returnArr, -1, $res, null);
}

function getGroupInfoByGroupNames($fieldsStr="*", $groupNamesStr="", $conn)
{
	$res = array();
	$returnArr = array();
	
	if (empty($groupNamesStr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Group names string cannot be empty", null);
	}

	if(empty($fieldsStr))
		$fieldsStr = "*";

	$getGroupInfoQuery = "SELECT {$fieldsStr} FROM crep_cms_groups WHERE group_name IN ({$groupNamesStr})";
	$getGroupInfoQueryResult = runQuery($getGroupInfoQuery, $conn);
	if (!noError($getGroupInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $getGroupInfoQueryResult["errMsg"], null);
	}

	/* This function negotiates that a group_id must be fetched from the database. All user info is keyed by the group's id
	*  However, in case an id is not desired, like in the case of fetching counts, a default ID of "-9999" will be used
	*/
	while ($row = mysqli_fetch_assoc($getGroupInfoQueryResult["dbResource"])) {
		if(!isset($row["group_id"]))
			$row["group_id"] = "-9999";
			
		$res[$row["group_id"]] = $row;
	}
	
	return setErrorStack($returnArr, -1, $res, null);
}

function getModulesInfo($fieldSearchArr=null, $fieldsStr="", $conn, $offset=null, $resultsPerPage=10)
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

	if(empty($fieldsStr))
		$fieldsStr = "*";

	$getModuleInfoQuery = "SELECT {$fieldsStr} FROM crep_cms_modules";
	if(!empty($whereClause))
		$getModuleInfoQuery .= " WHERE {$whereClause}";
	if($offset!==null)
		$getModuleInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";
	
	$getModuleInfoQueryResult = runQuery($getModuleInfoQuery, $conn);
	if (!noError($getModuleInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $getModuleInfoQueryResult["errMsg"], null);
	}

	/* This function negotiates that a module_id must be fetched from the database. All user info is keyed by the module's id
	*  However, in case an id is not desired, like in the case of fetching counts, a default ID of "-9999" will be used
	*/
	while ($row = mysqli_fetch_assoc($getModuleInfoQueryResult["dbResource"])) {
		if(!isset($row["module_id"]))
			$row["module_id"] = "-9999";
			
		$res[$row["module_id"]] = $row;
	}
	
	return setErrorStack($returnArr, -1, $res, null);
}

function createGroup($groupArr, $fieldsStr, $conn)
{
	$res = array();
	$returnArr = array();

	if (empty($groupArr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to insert cannot be empty", null);
	}
	if (empty($fieldsStr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Fields String cannot be empty", null);
	}

	$allValuesStr = "";	
	$insertGroupQuery = "INSERT INTO crep_cms_groups ({$fieldsStr}) VALUES ";
	foreach ($groupArr as $groupName=>$groupDetails) {
		if (!empty($valuesStr)) {
			$allValuesStr .= ",";
		}
		$valuesStr = "";
		foreach ($groupDetails as $colName=>$value) {
			$valuesStr .= $value.",";
		}
		$valuesStr = rtrim($valuesStr, ",");
		$allValuesStr .= "({$valuesStr})";
	}
	$insertGroupQuery .= $allValuesStr;
	
	$insertGroupQueryResult = runQuery($insertGroupQuery, $conn);
	if (noError($insertGroupQueryResult)) {
		return setErrorStack($returnArr, -1, $res, null);
	} else {
		return setErrorStack($returnArr, 3, $insertGroupQueryResult["errMsg"], null);
	}
}

function deleteGroupInfo($groupSearchArr, $conn)
{
	$returnArr = array();
	$whereClause = "";

	if (empty($groupSearchArr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to search cannot be empty", null);
	}
	
	//looping through array passed to create another array of where clauses
	foreach ($groupSearchArr as $colName=>$searchVal) {
		if(!empty($whereClause))
			$whereClause .= " AND ";
		$whereClause .= "{$colName} = {$searchVal}";
	}

	$deleteGroupInfoQuery = "DELETE FROM crep_cms_groups";
	if(!empty($whereClause))
		$deleteGroupInfoQuery .= " WHERE {$whereClause}";
	
	$deleteGroupInfoQueryResult = runQuery($deleteGroupInfoQuery, $conn);
	if (!noError($deleteGroupInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $deleteGroupInfoQueryResult["errMsg"], null);
	}

	return setErrorStack($returnArr, -1, getErrMsg(-1), null);

}

function updateGroupInfo($arrToUpdate=null, $fieldSearchArr=null, $conn)
{
	$res = array();
	$returnArr = array();

	if (empty($arrToUpdate)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to update cannot be empty", null);
	}
	if (empty($fieldSearchArr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to search cannot be empty", null);
	}
	
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
		$setClause .= "{$colName} = {$setVal}";
	}

	$updateGroupQuery = "UPDATE crep_cms_groups SET {$setClause} WHERE {$whereClause}";
	$updateGroupQueryResult = runQuery($updateGroupQuery, $conn);
	if (noError($updateGroupQueryResult)) {
		return setErrorStack($returnArr, -1, $res, null);
	} else {
		return setErrorStack($returnArr, 3, $updateGroupQueryResult["errMsg"], null);
	}
}