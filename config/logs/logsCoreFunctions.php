<?php
	function initializeXMLLog($user){

	    $retArray = array();
	    $deviceType = "app";
	    $userIp = getClientIP();
	    $data = getLocationUserFromIP($userIp);
		//printArr($data);
	    $getUserCity = $data["city"];
	    $getUserCountry = $data["country"];
	    $userIp = $data["userIP"];


	    $activity_attribute=array();
	    $activity_attribute['user']=$user;
	    $activity_attribute['timestamp']=date("Y-m-d h:i:s A");
	    $activity_attribute['userIp']=$userIp;
	    $activity_attribute['device']=$deviceType;
	    $activity_attribute['country']=$getUserCountry;
	    $activity_attribute['state']="";
	    $activity_attribute['city']=$getUserCity;


	    
	    $request_attribute['REQUEST_URI']=$_SERVER["REQUEST_URI"];
	    $request_attribute['HTTP_REFERER']=$_SERVER["HTTP_REFERER"];
	    $request_attribute['SESSION_ID']=session_id();
	    $request_attribute['QUERY_STRING']=$_SERVER["QUERY_STRING"];

	    $retArray = array('activity' => $activity_attribute, 'request' => $request_attribute);
	    
	    return $retArray;
	}

	function initializeJsonLogs($user){

		$retArray = array();
		
		$deviceType = "web";
		
		$userIp = getClientIP();
		$browserAgent = getBrowserName();		
		
		$activityAttribute=array();
	    $activityAttribute['user']=$user;
	    $activityAttribute['timestamp']=date("Y-m-d h:i:s A");
	    $activityAttribute['userIp']=$userIp;
	    $activityAttribute['device']=$deviceType;
	    
		$requestAttribute['REQUEST_URI']=$_SERVER["REQUEST_URI"];
		if(isset($_SERVER["HTTP_REFERER"]))
	    	$requestAttribute['HTTP_REFERER']=$_SERVER["HTTP_REFERER"];
	    $requestAttribute['SESSION_ID']=session_id();
		$requestAttribute['QUERY_STRING']=$_SERVER["QUERY_STRING"];
	 

	    $retArray = array('activity' => $activityAttribute, 'request' => $requestAttribute);
	    
	    return $retArray;
	}

	function getBrowserName(){
	    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE)
	        $brawserName = 'Internet explorer';
	    elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== FALSE) //For Supporting IE 11
	        $brawserName = 'Internet explorer';
	    elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== FALSE)
	        $brawserName = 'Mozilla Firefox';
	    elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== FALSE)
	        $brawserName = 'Google Chrome';
	    elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== FALSE)
	        $brawserName = "Opera Mini";
	    elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== FALSE)
	        $brawserName = "Opera";
	    elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== FALSE)
	        $brawserName = "Safari";
	    else
	        $brawserName = 'Something else';
	    return $brawserName;
	}

	function getClientIP(){
		$ip_keys = array(
			'HTTP_CLIENT_IP', 
			'HTTP_X_FORWARDED_FOR', 
			'HTTP_X_FORWARDED', 
			'HTTP_X_CLUSTER_CLIENT_IP', 
			'HTTP_FORWARDED_FOR', 
			'HTTP_FORWARDED', 
			'REMOTE_ADDR'
		);
		
	    foreach ($ip_keys as $key) {
	        if (array_key_exists($key, $_SERVER) === true) {
	            foreach (explode(',', $_SERVER[$key]) as $ip) {
	                // trim for safety measures
	                $ip = trim($ip);
	                // attempt to validate IP
	                if (validate_ip($ip)) {
	                    return $ip;
	                }
	            }
	        }
	    }
	    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
	}

	function validate_ip($ip){
	    if (filter_var(
			$ip, 
			FILTER_VALIDATE_IP, 
			FILTER_FLAG_IPV4 | 
			FILTER_FLAG_NO_PRIV_RANGE | 
			FILTER_FLAG_NO_RES_RANGE | 
			FILTER_FLAG_IPV6
		) === false) {
	        return false;
	    }
	    return true;
	}	

?>