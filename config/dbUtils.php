<?php

	/*
	*-----------------------------------------------------------------------------------------------------------
	*    Function createDbConnection
	*-----------------------------------------------------------------------------------------------------------
	*   Function Name :   createDbConnection()
	*   Purpose       :   Create datavase connection.
	*   Arguments     :   (string)$servername, (string)$username, (string)$password, (string)$dbname
	*   Returns       :   Array that contains errCode and errMsg having success or failure message.
	*/

	function createDbConnection($servername, $username, $password, $dbname){
		$returnArr = array();
    	$connAdmin = @mysqli_connect($servername, $username, $password);

    	if (!$connAdmin) {
	        $returnArr["errCode"] = 5;
	        $returnArr["errMsg"] = "Could not connect to DB";
	    } else {
	        // error reporting using array which is combination of error code n its respective message
	        if (!mysqli_select_db($connAdmin, $dbname)) {
	            $returnArr["errCode"] = 6;
	            $returnArr["errMsg"] = "Could not select DB";
	        } else {
	            $returnArr["errCode"] = -1;
	            $returnArr["errMsg"] = $connAdmin;
	        }
	    }
    	return $returnArr;
	}

	/*
	*-----------------------------------------------------------------------------------------------------------
	*    Function closeDBConnection
	*-----------------------------------------------------------------------------------------------------------
	*   Function Name :   closeDBConnection()
	*   Purpose       :   To close the Database connection.
	*   Arguments     :   (string)$conn
	*/

	function closeDBConnection($conn){
	    mysqli_close($conn);
	}


	/*
	*-----------------------------------------------------------------------------------------------------------
	*    Function runQuery
	*-----------------------------------------------------------------------------------------------------------
	*   Function Name :   runQuery()
	*   Purpose       :   To execute the sql queries.
	*   Arguments     :   (string)$query, (string)$conn
	*   Returns       :   Array that contains errCode and errMsg having success or failure message.
	*/

	function runQuery($query, $conn){
	    $returnArr = array();
	    $extraArg = array();
	    $result = mysqli_query($conn, $query);
		
	    if(!$result){
	        $extraArg['query']   = $query;
	        $returnArr = setErrorStack($returnArr,3,null,$extraArg);
	    }else{
	        $extraArg['dbResource'] = $result;
	        $errMsg = 'Query Successful';
	        $returnArr = setErrorStack($returnArr,-1,$errMsg,$extraArg);
	    }
		
	    return $returnArr;
	}

	function startTransaction($conn) {
	
		$returnArr = array();
		
		$result = mysqli_query($conn,"START TRANSACTION");
		if(!$result){
	        $extraArg['query']   = "";// $query;
	        $returnArr = setErrorStack($returnArr,3,null,$extraArg);
	    }else{
	        $extraArg['dbResource'] = $result;
	        $errMsg = 'Transaction Started';
	        $returnArr = setErrorStack($returnArr,-1,$errMsg,$extraArg);
	    }
		return $returnArr;
	}

	function commitTransaction($conn) {
		
		$returnArr = array();
		
		$result = mysqli_query($conn,"COMMIT");
		if (!$result) {
			$returnArr["errCode"][8] = 8;
			$returnArr["errMsg"] = "Could not commit transaction: ".mysqli_error($conn);
		} else {
			$returnArr["errCode"][-1] = -1;
			$returnArr["errMsg"] = "Transaction committed";
		}
		
		return $returnArr;
	}

	function rollbackTransaction($conn) {
		
		$returnArr = array();
		
		$result = mysqli_query($conn, "ROLLBACK");
		if (!$result) {
			$returnArr["errCode"][9] = 9;
			$returnArr["errMsg"] = "Could not rollback transaction: ".mysqli_error($conn);
		} else {
			$returnArr["errCode"][-1] = -1;
			$returnArr["errMsg"] = "Transaction rolled back";
		}
		
		return $returnArr;
	}
	
	function decodeurlWithParameters($datastring){
		/*
		decodeurlWithParameters($datastring)
		which have 1 parameters
		1]$datastring:-string to decode
		it's return decoded url
		*/
		$decodedquery = urldecode($datastring);
		parse_str($decodedquery, $queryStringForTransaction);

		return $queryStringForTransaction;
	}
	
	function checkTableExist($tableName,$conn){
		$returnArr = array();
		$query = "SHOW TABLES LIKE '{$tableName}'";  	 
		$res["query"] = $query;
		$result = runQuery($query, $conn);
		if (noError($result)) {			
		  	$resultscheck=mysqli_num_rows($result["dbResource"]);	
			$returnArr = setErrorStack($returnArr, -1, $resultscheck, null);	
		} else {			
			$returnArr = setErrorStack($returnArr, 3, null, null);
		}

		return $returnArr;		
	}

	function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
}
	function activitylogs($param,$conn){

		$tableName = "activity_reports";
		$tableArr = checkTableExist($tableName, $conn);
	//	print_r($tableArr);
		if ($tableArr['errMsg'] == '1') {
	
		} else {
			$sql = "CREATE TABLE {$tableName}  (
							  `id` bigint(11) NOT NULL AUTO_INCREMENT,
							  `table_name` varchar(5)   DEFAULT NULL,
							  `file_name` tinytext,
							  `status_name` varchar(50) DEFAULT NULL,
							  `status_flag` varchar(50) DEFAULT NULL,
							  `date_added` varchar(50)  DEFAULT NULL,
							  `ip_address` varchar(50) DEFAULT NULL,
							`login_user` varchar(50) DEFAULT NULL,
							`raw_data` text,
							`log_file` varchar(128) DEFAULT NULL,
							   PRIMARY KEY (id),
							   INDEX table_name (table_name),
							   INDEX file_name (file_name),
							   INDEX status_name (status_name),
							   INDEX status_flag (status_flag)
							)ENGINE=InnoDB DEFAULT CHARSET=UTF8";
	
	
			$createYoutubeTableQueryResult = runQuery($sql, $conn);
		}
	   
	
		$returnArr= [];
	 
		$keys = array();
		$values = array();
	
		foreach ($param as $key=>$val) {
			$value = mysqli_real_escape_string($conn, $val);
			$keys[] = "`".$key."`";
			$values[] = "'{$val}'";
		}
	
		 	$query = "INSERT INTO {$tableName} (" . implode(",", $keys) . ") VALUES (" . implode(",", $values) . ")";
			$queryresult = runQuery($query, $conn);
		//	$queryresult = mysqli_query($conn, $query);
		

		//	print_r($queryresult);
				 
			
	}
	
?>