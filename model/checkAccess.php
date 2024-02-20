<?php

		require_once(__ROOT__.'/config/config.php');
        require_once(__ROOT__.'/config/dbUtils.php');
	    require_once(__ROOT__.'/config/auth.php');
	    require_once(__ROOT__.'/config/errorMap.php');
	     require_once(__ROOT__.'/model/accessModel.php');

		$connAdmin = "";
		/*---------------------- Connection With Database ---------------------------------*/
		$connAdmin = createDbConnection($host, $db_username, $db_password, $dbName);
		if (noError($connAdmin)) {
			$connAdmin = $connAdmin["errMsg"];
		} else {
			printArr("Database Error admin");
			exit;
		}
		$largesttpermission=array();

		$userid = $_SESSION["user_id"];
    	$result = getAdminData($userid, $connAdmin);
    	$result = $result["errMsg"];

    	//-----------------------Get Group Name with space seprate by ids----------------------

    	$groupNames = "";

    	$groupResult = getGroupData($result["groups"], $connAdmin);
    	// printArr($groupResult); exit;
    	foreach ($groupResult["errMsg"] as $key => $value) {    		
    		$groupNames .= str_replace(" ", "_", $value['group_name'])." ";
    	}
    	$groupNames = trim($groupNames);
    	
    	//-------------------------------------------------------------------------------------

	    $gps = $groupNames;
	    $userp = $result["rights"];
	    array_push($largesttpermission,$userp);
	    $groups = explode(" ", $gps);
	    $superadmin = "superadmin";

	    $datatt1 = getAdminData($userid, $connAdmin);
	    $datatt2 = $datatt1["errMsg"];
	    $datatt = $groupNames;

	    $permsiion = array();
	    $modulestt = array();
	    $modulesSubs = array();
	    $groupnamess = array();
	    $resultsss = explode(" ",$datatt);

	    foreach ($resultsss as $res) {
	        $result = selectGroups($res, $connAdmin);
	        $resultnew = $result["errMsg"];
	        $rightss = $resultnew["group_rights"];
	        $group_name = $resultnew["group_name"];
	        $modules = $resultnew["right_on_module"];
	        $modulesSub = $resultnew["right_on_submodule"];
	        array_push($permsiion, trim($rightss));
	        array_push($modulestt, trim($modules));
	        array_push($modulesSubs, trim($modulesSub));
	        array_push($groupnamess, trim($group_name));
	    }

	    function getMypermission(){
	        global $groupnamess;
	    	return $groupnamess;
	    }

	    $rightsnew = $datatt2["rights"];
    	array_push($permsiion, trim($rightsnew));

		$modulesttdecoded = array();
		foreach ($modulestt as $value) {
			$result = explode(",", $value);			
	        foreach ($result as $data) {
	            array_push($modulesttdecoded, trim($data));
	        }
	    }

	    $pages = array();
	    foreach($modulesSubs as $values) {
        	$modulesSubs = explode(",", $values);
        	foreach ($modulesSubs as $val) {
            	array_push($pages, trim($val));
        	}
    	}

    	$mymodules=array();
    	foreach($modulestt as $val) {
    		$modulestt = explode(",", $val);
	        foreach ($modulestt as $val) {
	            array_push($mymodules, trim($val,'"'));
	        }
	    }
	    $groupnamess=getMypermission();

	    $links = $_SERVER['PHP_SELF'];
	    $link_arrayy = explode('/', $links);
	    $mypagepage = trim(end($link_arrayy));
	    foreach($groupnamess as $values) {
	    	$result = checkgroupadded(trim($values), $connAdmin);
	    	$result = $result["errMsg"];

	        $modulepages = explode(",",$result["right_on_submodule"]);
        	$virtualName = getPageMapping($mypagepage,$connAdmin);
        	$virtualPageName = (isset($virtualName['errMsg']['module_virtualpagename']))?$virtualName['errMsg']['module_virtualpagename']:"";
	        foreach ($modulepages as $mypage) {
	        	if($virtualPageName == trim($mypage)) {
	                $result = checkgroupadded(trim($values), $connAdmin);
	                $result = $result["errMsg"];
	                array_push($largesttpermission);
	                $moduleright = $result["right_on_module"];
	            }
	        }
	    }

	    $largest = max($largesttpermission);
	    function mypermissions($largest){
			define('CAN_READ', 1 << 0);   // 000001 CAN_READ 1
	        define('CAN_WRITE', 1 << 1); // 000010 CAN_WRITE 2
	        define('CAN_EXECUTE', 1 << 2);   // 000100 4
	        // printArr($largest);
	        $permission=str_split($largest);
	        // printArr($permission);
	        // exit();

	        foreach($permission as $per)
	        {
	            if($per==1)
	            {
	                $read=CAN_READ;
	            }

	            if($per==2)
	            {
	                $write=CAN_WRITE;
	            }

	            if($per==4)
	            {
	                $execute=CAN_EXECUTE;
	            }
	        }


	        $individualpermission=$read +  $write + $execute;

	        function isCanRead($sRule) {
	            return ($sRule & CAN_READ) ? 'read' : '';
	        }
	        function isCanwrite($sRule) {
	            return ($sRule & CAN_WRITE) ? 'write' : '';
	        }
	        function isCanAll($sRule) {
	            return ($sRule & CAN_EXECUTE) ? 'execute' : '';
	        }


	        $sRule=$individualpermission;

	        $myp=array();


	        $sRuleread=isCanRead($sRule);
	        if(!empty($sRuleread)) {
	            array_push($myp, $sRuleread);
	        }
	         $sRulewrite=isCanwrite($sRule);
	        if(!empty($sRulewrite)) {
	            array_push($myp, $sRulewrite);
	        }
	         $sRuleexe= isCanAll($sRule);
	        if(!empty($sRuleexe)) {
	            array_push($myp, $sRuleexe);
	        }
	        return $myp;
    	}

?>