<?php

	//global array to define all error messages
	$errorArray = array(
	    "-1" => "Success",
	    "1" => "Error in creating database connection",
	    "2" => "Error in selecting database",
	    "3" => "Error in mysql query",
	    "4" => "Mandatory fields missing",
		"5" => "Error in fetching data",
		"6" => "Session error",
		"7" => "Validation error",
		"8" => "Error sending email",
		"9" => "Duplicate entry"
	);
	/*
	"6" => "Error in fetching global settings",
	    "7" => "All feilds are empty",
	    "8" => "file is already exists",
	    "9" => "File size is too large",
	    "10" => "File is not an image",
	    "11" => "Device Id required.",
	    "12" => "Sorry, we could not find this email address.",
	    "13" => "Sorry,User is not active currently.",
	    "14" => "Sorry, we could not find any document for this date.",
	    "15" => "FCM registration id required.",
	    "16" => "No Device Found.",
	    "17" => "App version required.",
	    "18" => "Falied to activate user",
	    "19" => "Email already exists",
	    "20" => "Failed to load data,please try again.",
	    "21" => "Password does not match",
	    "22" => "Password length at least 6 characters.",
	    "23" => "Mobile Number does not exists.",
	    "24" => "Email already exists.",
	    "25" => "Please upload holding sheet.",
	    "26" => "Old password doesn't match.",
	    "27" => "Error sending SMS.",
	    "28" => "User does not exists",
	    "29" => "Invalid username or password.",
	    "30" => "Username already exists.",
	    "31" => "User already active.",
	    "32" => "User not active.",
	    "33" => "Please upload holding sheet for global.",
	    "34" => "User not active.Please activate user.",
	    "35" => "Invalid Password",
	    "36" => "This user is invalid. Please try again or contact support@omblee.com",
	    "37" => "Invalid Mobile Number.",
	    "38" => "Activation code is required",
	    "39" => "Invalid activation code",
	    "40" => "Password is required.",
	    "41" => "Invalid Password",
	    "42" => "OTP is required",
	    "43" => "Invalid OTP",
	    "44" => "Old password is required",
	    "45" => "Repeat password is required",
	    "46" => "User id is required",
	    "47" => "management fees for this quarter not found",
	    "50" => "Please upload today's holding client and holding global sheets",
	    "51" => "Please upload today's holding client sheet",
	    "52" => "Please upload today's holding global sheet",
	    "53" => "Please add security first",
	    "54" => "Please select security for each row",
		"200" => ""
		*/

	/**
	* function name: getErrorMsg();
	* parameters: $errCode(number)
	* purpose: to getting perticular error message from error array.
	*/
	function getErrMsg($errCode){
		global $errorArray;
		$errMsg = $errorArray[$errCode];
		if(!isset($errMsg) || empty($errMsg)){
			$errMsg  = "Undefined error code and error message";
		}
		return $errMsg;
	}

	/**
	* function name: setErrorStack();
	* parameters: $returnArr(array),$errCode(number)
	* purpose: to getting error stack.
	 */
	function setErrorStack($returnArr, $errCode, $errMsg, $extraArgs=null)
	{
		$resArr = array();
		if(!isset($errMsg) && empty($errMsg)){
			$errMsg = getErrMsg($errCode);
		}

		$resArr["errCode"] = $errCode;
		$resArr["errMsg"] = $errMsg;
		if (isset($extraArgs) && !empty($extraArgs)) {
			$resArr = array_merge($resArr, $extraArgs);
		}
		return $resArr;
	}

	$errorFormat = array(
		"1001" => "errorValidation"
	);

	function getFormatedMessage($code, $field_array, $returnArr){
		global $errorFormat;
		$code_value = $errorFormat[$code];

		$returnArr["errCode"] = $code;
		$i = 1;
		foreach ($field_array as $key => $value) {
			$returnArr[$code_value]["field".$i] = $value;
			$i++;
		}

		return $returnArr;
	}
?>
