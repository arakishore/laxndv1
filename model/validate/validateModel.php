<?php

function getContentOwner($conn)
{
	$res = array();
	$returnArr = array();
	$extraArg=array();
	$getCOInfoQuery = "SELECT client_username FROM crep_cms_clients where   status = 1 ORDER BY client_username ASC";
	
	$getCOInfoQueryResult = runQuery($getCOInfoQuery, $conn);
 
	if (!noError($getCOInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $getCOInfoQueryResult["errMsg"], null);
	}
	while ($row = mysqli_fetch_assoc($getCOInfoQueryResult["dbResource"])){
	  array_push($res, $row['client_username']);
	}
	$errMsg = $res;
	$returnArr = setErrorStack($returnArr, -1, $errMsg, $extraArg);

	return setErrorStack($returnArr, -1, $res, null);
}

function updateContentOwner($table, $contentowner, $ids, $conn)
{
	$res = array();
	$returnArr = array();
	$updateQuery = "UPDATE {$table} SET content_owner = '{$contentowner}' WHERE id IN({$ids})";

	@unlink("polo_update.txt");
	file_put_contents("polo_update.txt", $updateQuery);
	@chmod("polo_update.txt",0777);	


	$updateQueryResult = runQuery($updateQuery, $conn);
	if (noError($updateQueryResult)) {
		return setErrorStack($returnArr, -1, $res, null);
	} else {
		return setErrorStack($returnArr, 3, $updateQueryResult["errMsg"], null);
	}
}
 
?>