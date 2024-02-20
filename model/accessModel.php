<?php
	function getipaddress($ip, $connAdmin){

	    $returnArr=array();
	    $extraArg=array();

	    $query = "SELECT * FROM ip_access WHERE ip_address='" . $ip . "'";
	    $result = runQuery($query, $connAdmin);

	    if (noError($result)) {
	        $res = array();
	        while ($row = mysqli_fetch_assoc($result["dbResource"])){
	            $res = $row;
	        }
	        $errMsg = $res;
	        $returnArr = setErrorStack($returnArr, -1, $errMsg, $extraArg);

	    } else {
	        $returnArr = setErrorStack($returnArr, 11, null, $extraArg);
	    }
	    return $returnArr;
	}

	function addipaddress($ipaddress, $connAdmin){
	    $extraArg=array();
	    $returnArr=array();
	    $query = "INSERT INTO `ip_access`(`id`, `ip_address`) VALUES (0,'" . $ipaddress . "');";
	    $result = runQuery($query, $connAdmin);
	    if (noError($result)) {
	        $errMsg = "IP Address Added Successfully.";
	        $returnArr = setErrorStack($returnArr, -1, $errMsg, $extraArg);
	    } else {
	        $returnArr = setErrorStack($returnArr, 10, null, $extraArg);
	    }
	    return $returnArr;

	}

	function deleteIp($where, $connAdmin){
	    $extraArg=array();
	    $returnArr=array();
	    $query = "DELETE FROM `ip_access` WHERE ".$where;

	    $result = runQuery($query, $connAdmin);

	    if (noError($result)) {
	        $errMsg = "IP Address Deleted Successfully.";
	        $returnArr = setErrorStack($returnArr, -1, $errMsg, $extraArg);
	    } else {
	        $returnArr = setErrorStack($returnArr, 13, null, $extraArg);
	    }
	    return $returnArr;
	}

	function isipaddresshasaccess($connAdmin){
		$result = getallipaddress($connAdmin);
	    $datas = $result["errMsg"];

	    $ips = array();

	    foreach ($datas as $value) {
	        array_push($ips, $value["ip_address"]);
	    }

	    
	}

	function getallipaddress($offset,$no_of_rows,$conn){
		$res = array();
		$cols = array();
		
		foreach($data as $key=>$val) {
    	    $cols[] = "$key= '$val'";
    	}
    	$count = count($cols);
    	if($count >1){
    		$where = implode(' OR ', $cols);
    	}else if($count == 1){
    		$where = implode(' ', $cols);
    	}else{
    		$where = 1;
    	}

	
	   $query = "select * from ip_access";
	   //$query .= " order by updated_on desc";
	   if ($offset != "" && $no_of_rows != "") {
            $query .= " LIMIT " . $offset . "," . $no_of_rows;
        }
        
       // echo $query;
	    $res["query"] = $query;
		$result = runQuery($query, $conn);


		if (noError($result)) {
	        
		  $dataArr = array();
	      while( $row = mysqli_fetch_assoc($result["dbResource"])) {
	      	$dataArr[] = $row;
    		}
	       if(!empty($dataArr)){
	        	$res["result"] = $dataArr;
	        	//$status = $row['status'];
	        	
	        		$returnArr = setErrorStack($returnArr, -1, null, $res);
	        	

	        }else{
	        	$returnArr = setErrorStack($returnArr, 12, null);
	        }

	    } else {
	        $returnArr = setErrorStack($returnArr, 3, null);
	    }

	    return $returnArr;
}

function getallUser($offset,$no_of_rows,$conn){
		$res = array();
		$cols = array();
		
		foreach($data as $key=>$val) {
    	    $cols[] = "$key= '$val'";
    	}
    	$count = count($cols);
    	if($count >1){
    		$where = implode(' OR ', $cols);
    	}else if($count == 1){
    		$where = implode(' ', $cols);
    	}else{
    		$where = 1;
    	}

	
	   $query = "select * from crep_cms_user";
	   //$query .= " order by updated_on desc";
	   if ($offset != "" && $no_of_rows != "") {
            $query .= " LIMIT " . $offset . "," . $no_of_rows;
        }
        
       // echo $query;
	    $res["query"] = $query;
		$result = runQuery($query, $conn);


		if (noError($result)) {
	        
		  $dataArr = array();
	      while( $row = mysqli_fetch_assoc($result["dbResource"])) {
	      	$dataArr[] = $row;
    		}
	       if(!empty($dataArr)){
	        	$res["result"] = $dataArr;
	        	//$status = $row['status'];
	        	
	        		$returnArr = setErrorStack($returnArr, -1, null, $res);
	        	

	        }else{
	        	$returnArr = setErrorStack($returnArr, 12, null);
	        }

	    } else {
	        $returnArr = setErrorStack($returnArr, 3, null);
	    }

	    return $returnArr;
	}

	function addUser($data,$conn){
		$res = array();
		$returnArr = array();
		$data['created_on'] = date("Y-m-d h:i:s");		
		$data['updated_on'] = date("Y-m-d h:i:s");	

		$keys = array();
		$values = array();

		foreach ($data as $key=>$val) {
		    $value = mysqli_real_escape_string($conn, $val);
		    $keys[] = $key;
		    $values[] = "'{$val}'";
		}

	   	$query = "INSERT INTO crep_cms_user (" . implode(",", $keys) . ") VALUES (" . implode(",", $values) . ");";
		$result = runQuery($query, $conn);
		if (noError($result)) {	        
	    	$returnArr = setErrorStack($returnArr, -1, null, null);
	    } else {
	        $returnArr = setErrorStack($returnArr, 3, null, null);
	    }

	    return $returnArr;
	}

	function getUserData($data,$conn){
		 //printArr($conn);
		$res = array();
		$returnArr = array();
		$cols = array();
		foreach($data as $key=>$val) {
    	    $cols[] = "$key = '$val'";
    	}
    	$count = count($cols);
    	if($count >1){
    		$where = implode(' AND ', $cols);
    	}else{
    		$where = implode(' ', $cols);
    	}
	   $query = "select * from crep_cms_user where " .$where ;
	  
	    $res["query"] = $query;
		$result = runQuery($query, $conn);

		if (noError($result)) {
	        
	       $row = mysqli_fetch_assoc($result["dbResource"]);

	       if(!empty($row)){
	        	$res["result"] = $row;
	        	$status = $row['status'];
	        	if($status != 1 && $status !=2){
	        		$returnArr = setErrorStack($returnArr, 13, null, null);
	        	}else{
	        		$returnArr = setErrorStack($returnArr, -1, null, $res);
	        	}

	        }else{
	        	$returnArr = setErrorStack($returnArr, 12, null);
	        }

	    } else {
	        $returnArr = setErrorStack($returnArr, 3, null);
	    }

	    return $returnArr;

	}
	function updateUser($data,$conn,$where){
		$res = array();
		$returnArr = array();
		$cols = array();
		$data['updated_on'] = date("Y-m-d h:m:s");
		foreach($data as $key=>$val) {
    	    $cols[] = "$key = '$val'";
    	}
    	$count = count($cols);
    	
	   	$query = "UPDATE crep_cms_user SET ".implode(', ', $cols) . " where ". $where  ;
		
	    $res["query"] = $query;
		$result = runQuery($query, $conn);

		if (noError($result)) {
	        
	    
	      $returnArr = setErrorStack($returnArr, -1, null, $res);

	    } else {
	        $returnArr = setErrorStack($returnArr, 3, null);
	    }

	    return $returnArr;

	}
function getallmodules($connAdmin){

	    $returnArr=array();
	    $extraArg=array();

	    $query = "SELECT module_name FROM modules";
	    $result = runQuery($query, $connAdmin);

	    if (noError($result)) {
	        $res = array();

	        while ($row = mysqli_fetch_assoc($result["dbResource"])) {
	            array_push($res, $row);
	        }
	        $errMsg = $res;
	        $returnArr = setErrorStack($returnArr, -1, $errMsg, $extraArg);

	    } else {
	        $returnArr = setErrorStack($returnArr, 18, null, $extraArg);
	    }

	    return $returnArr;
	}
	function getsubmodules($submodule, $connAdmin)
	{

	    $allmod = array();
	    foreach ($submodule as $value) {
	        $query = "SELECT submodule_virtual_name FROM modules WHERE module_name='" . $value . "'";
	        $result = runQuery($query, $connAdmin);

	        while ($row = mysqli_fetch_assoc($result["dbResource"]))

	            $res = $row;

	        $res = $res["submodule_virtual_name"];
	        $data = explode(",", $res);

	        foreach ($data as $value) {
	            array_push($allmod, $value);
	        }


	    }
	    return $allmod;
	}
	function submitUserdatagroup($data,$conn){

		$res = array();
		$returnArr = array();
    	
		$keys = array();
		$values = array();

		//$columns = array('name'=>'ash','surname'=>'khule');
		foreach ($data as $key=>$val) {
		    //$value = trim($_POST[$column]);
		    $value = mysqli_real_escape_string($conn, $val);
		    $keys[] = $key;
		    $values[] = "'{$val}'";
		}
		$query = "INSERT INTO crep_cms_groups (" . implode(",", $keys) . ") 
		          VALUES (" . implode(",", $values) . ");";


		
	    $res["query"] = $query;
		$result = runQuery($query, $conn);

		if (noError($result)) {
	        
	      $returnArr = setErrorStack($returnArr, -1, null, $res);

	    } else {
	        $returnArr = setErrorStack($returnArr, 3, null);
	    }

	    return $returnArr;
	}
	function updategroup($data,$conn,$where){
		$res = array();
		$returnArr = array();
		$cols = array();
		
		foreach($data as $key=>$val) {
    	    $cols[] = "$key = '$val'";
    	}
    	$count = count($cols);
    	
	   $query = "UPDATE crep_cms_groups SET ".implode(', ', $cols) . " where ". $where  ;
		
	    $res["query"] = $query;
		$result = runQuery($query, $conn);

		if (noError($result)) {
	        
	       
	      $returnArr = setErrorStack($returnArr, -1, null, $res);

	    } else {
	        $returnArr = setErrorStack($returnArr, 3, null);
	    }

	    return $returnArr;

	}
	function getallgroup($connAdmin){

	    $returnArr=array();
	    $extraArg=array();
	    

	    $query = "SELECT * FROM crep_cms_groups ORDER BY updated_on DESC";
	    $result = runQuery($query, $connAdmin);
//print_r($result);
	    if (noError($result)) {
	        $res = array();

	        while ($row = mysqli_fetch_assoc($result["dbResource"])) {
	            array_push($res, $row);
	        }
	        $errMsg = $res;
	        $returnArr = setErrorStack($returnArr, -1, $errMsg, $extraArg);

	    } else {
	        $returnArr = setErrorStack($returnArr, 18, null, $extraArg);
	    }

	    return $returnArr;
	}
	function deleteUser($where,$connAdmin){
//echo $where;
	  $extraArg=array();
	    $returnArr=array();
	    $query = "DELETE FROM `crep_cms_user` WHERE ".$where;
//xit;
	    $result = runQuery($query, $connAdmin);

	    if (noError($result)) {
	        $errMsg = "User Deleted Successfully.";
	        $returnArr = setErrorStack($returnArr, -1, $errMsg, $extraArg);
	    } else {
	        $returnArr = setErrorStack($returnArr, 13, null, $extraArg);
	    }
	    return $returnArr;
	}
	
	function getGroup($id, $connAdmin){
	    $returnArr=array();
	    $extraArg=array();
	    $query = "SELECT * FROM `crep_cms_groups` WHERE group_id='" . $id . "'";

	    $result = runQuery($query, $connAdmin);

	    if (noError($result)) {
	        $res = array();

	        while ($row = mysqli_fetch_assoc($result["dbResource"]))

	            $res = $row;

	        $errMsg = $res;
	        $returnArr = setErrorStack($returnArr, -1, $errMsg, $extraArg);
	    } else {
	        $returnArr = setErrorStack($returnArr, 24, null, $extraArg);
	    }
	    return $returnArr;
	}
	function deleteGroup($where,$connAdmin){
//echo $where;
	  $extraArg=array();
	    $returnArr=array();
	    $query = "DELETE FROM `crep_cms_groups` WHERE ".$where;
//xit;
	    $result = runQuery($query, $connAdmin);

	    if (noError($result)) {
	        $errMsg = "User Deleted Successfully.";
	        $returnArr = setErrorStack($returnArr, -1, $errMsg, $extraArg);
	    } else {
	        $returnArr = setErrorStack($returnArr, 13, null, $extraArg);
	    }
	    return $returnArr;
	}
	function getAdminData($id, $connAdmin){
	    $returnArr=array();
	    $extraArg=array();

	    $query = "SELECT * FROM crep_cms_user where user_id='" . $id . "'";

	    $result = runQuery($query, $connAdmin);

	    if (noError($result)) {
	        $res = array();

	        while ($row = mysqli_fetch_assoc($result["dbResource"])){
	            $res = $row;
	        }

	        $errMsg = $res;
	        $returnArr = setErrorStack($returnArr, -1, $errMsg, $extraArg);

	    } else {
	        $returnArr = setErrorStack($returnArr, 17, null, $extraArg);
	    }

	    return $returnArr;
	}


function selectGroups($groups,$connAdmin){


    /*
       selectGroups($groups,$connAdmin);

                which have 2 parameters
                1]$groups - groups array contain groups names
                2]Connection for admin database


                it's return data if there is record found for groupname in table.
     */

    $returnArr = array();

   $query="select * from crep_cms_groups where group_name='$groups'";

    $result = runQuery($query, $connAdmin);

    if(noError($result)){
        $res = array();

        while ($row = mysqli_fetch_assoc($result["dbResource"]))

            $res = $row;

        $returnArr["errCode"][-1]=-1;

        $returnArr["errMsg"]=$res;

    } else {

        $returnArr["errCode"][5]=5;

        $returnArr["errMsg"]=$result["errMsg"];

    }

    return $returnArr;
}

function checkgroupadded($groupname, $connAdmin){
	    $returnArr=array();
	    $extraArg=array();

	    $query = "SELECT * FROM crep_cms_groups WHERE group_name='" . $groupname . "'";

	    $result = runQuery($query, $connAdmin);

	    if (noError($result)) {
	        $res = array();
	        while ($row = mysqli_fetch_assoc($result["dbResource"])){
	            $res = $row;
	        }

	        $errMsg = $res;
	        $returnArr = setErrorStack($returnArr, -1, $errMsg, $extraArg);
	    } else {
	        $returnArr = setErrorStack($returnArr, 25, null, $extraArg);
	    }
	    return $returnArr;
	}

	function getGroupData($group_names, $connAdmin){
		$returnArr=array();
	    $extraArg=array();
	    // printArr("'".$group_names."'");
	    $group_names = str_replace(',', "','", $group_names);
	    $query = "SELECT * FROM crep_cms_groups WHERE group_name IN ('".$group_names."')";

	    $result = runQuery($query, $connAdmin);

	    if (noError($result)) {
	        $res = array();

	        while ($row = mysqli_fetch_assoc($result["dbResource"])){
	            //$res = $row;
	            array_push($res, $row);
	        }

	        $errMsg = $res;
	        $returnArr = setErrorStack($returnArr, -1, $errMsg, $extraArg);

	    } else {

	        $returnArr = setErrorStack($returnArr, 1, null, $extraArg);

	    }

	    return $returnArr;
	}
	function activateUser($hash, $salt,$email, $connAdmin){
	    $extraArg=array();
	    $returnArr=array();
	    //$hash=sha1Md5DualEncryption($hash);

	    $query = "UPDATE crep_cms_user SET password='" . $hash . "',salt='" . $salt . "',status='1' WHERE email='" . $email . "' ;";

	    $result = runQuery($query, $connAdmin);

	    if (noError($result)) {

	        $errMsg = "User Password updation sucess";
	        $returnArr = setErrorStack($returnArr, -1, $errMsg, $extraArg);
	    } else {
	        $returnArr = setErrorStack($returnArr, 18, null, $extraArg);
	    }
	    return $returnArr;
	}
	function getGroupName($connAdmin){

        $returnArr=array();
        $extraArg=array();

        $query = "SELECT group_name FROM crep_cms_groups";
        $result = runQuery($query, $connAdmin);
        // printArr($connAdmin);
        if (noError($result)) {
            $res = array();

            while ($row = mysqli_fetch_assoc($result["dbResource"])) {
                array_push($res, $row);
            }
            $errMsg = $res;
            $returnArr = setErrorStack($returnArr, -1, $errMsg, $extraArg);

        } else {

            $returnArr = setErrorStack($returnArr, 18, null, $extraArg);
        }

        return $returnArr;
    }
    function checkEmailExist($email,$conn){
    	 $returnArr=array();
	    $extraArg=array();

	    $query = "SELECT * FROM crep_cms_user WHERE email='" . $email . "'";
	    $result = runQuery($query, $conn);
//print_r($result);
	    if (noError($result)) {
	        $res = array();
	        while ($row = mysqli_fetch_assoc($result["dbResource"])){
	            $res = $row;
	        }

	        $errMsg = $res;
	        $returnArr = setErrorStack($returnArr, -1, $errMsg, $extraArg);
	    } else {
	        $returnArr = setErrorStack($returnArr, 19, null, $extraArg);
	    }
	    return $returnArr;
    }



    function getPageList($data,$connAdmin){

	    /*
	      getPageList($data,$connAdmin);

	         which have 2 parameters
	        1]$data=Modules in array
	        1]$connAdmin=Databse Connection for access control


	        it's Return Array with File NAme to virtual name  from page Mapping table.
	     */

	    $allmod=array();

	    $result=getPageMapping($connAdmin);
	    $result=$result["errMsg"];

	    foreach($data as $value) {
	        foreach($result as $newdata) {
	            $module_pagename=$newdata["module_pagename"];
	            $module_virtualpagename=$newdata["module_virtualpagename"];

	            if($value==$module_pagename)
	            {
	                $submodulename=$module_virtualpagename;
	                array_push($allmod, $submodulename);
	            }

	        }

	    }

	    return $allmod;

	}



	function getPageMapping($mypage, $connAdmin){

	    /*
	      getPageMapping($connAdmin);

	         which have 1 parameters
	        1]$connAdmin=Databse Connection for access control


	        it's Return Array with File NAme to virtula name  from page Mapping table.
	     */

	    $returnArr = array();

	    $query="select module_pagename,module_virtualpagename from page_mapping  WHERE module_pagename='" . $mypage . "'";

	    $result = runQuery($query, $connAdmin);

	    if(noError($result)){
	        $res = array();

	        $row = mysqli_fetch_assoc($result["dbResource"]);
	        // while ($row = mysqli_fetch_assoc($result["dbResource"])) {
	        //     array_push($res,$row);
	        // }

	        $returnArr["errCode"][-1]=-1;

	        $returnArr["errMsg"]=$row;

	    } else {

	        $returnArr["errCode"][5]=5;

	        $returnArr["errMsg"]=$result["errMsg"];

	    }

	    return $returnArr;

	}




?>

