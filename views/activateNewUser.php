<?php
	//set title to page
	$page_title = "Activate Admin User";

	    require_once('../config/config.php');
    require_once('../config/dbUtils.php');
    require_once('../config/errorMap.php');
    require_once('../model/accessModel.php');    
	require_once('../config/auth.php');

	 $connAdmin = "";
    /*---------------------- Connection With Database ---------------------------------*/
    $connAdmin = createDbConnection($host, $db_username, $db_password, $dbName);
    if (noError($connAdmin)) {
        $connAdmin = $connAdmin["errMsg"];
    } else {
        printArr("Database Error admin");
        exit;
    }



	$datastring = $_SERVER['QUERY_STRING'];
    $queryStringForTransaction = decodeurlWithParameters($datastring);
    $id=$queryStringForTransaction["id"];
    $email=$queryStringForTransaction["email"];
    $token=$queryStringForTransaction["token"];



    $result=getAdminData($id,$connAdmin);
    
    $datae=$result["errMsg"];
    //printArr($result);
$url = $rootUrlViews."login/";
    $emailtomatch=$datae["email"];
    $idtomatch=$datae["user_id"];
    $tokentomatch=$datae["token"];

	if(isset($email) && isset($id) && isset($token))
	{

	    if(($email==$emailtomatch) && ($id==$idtomatch) && ($token==$tokentomatch))
	    {
	    	
	        //$token= generateToken(12);
			
	        //$result=updateToken($email,$token,$connAdmin);
			
	        //$result=getAdminData($email,$connAdmin);
	        $result=$result["errMsg"];
	        $fname=$result["firstname"];
	        $lname=$result["lastname"];
	        $password = generatePassword(6, false, 'd');
//$password = "123456";
	        $salt = generateSalt(12);
			$hashPassword = encryptPassword($password,$salt);

	        //$password = generatePassword(6, false, 'd');

	        $userArr = activateUser($hashPassword,$salt,$emailtomatch,$connAdmin);
	       
	        //$recipients = $email;
	        $username=$fname." ".$lname;//'.$randonnumber.'
	        $emailSubject = "Your Account is Activated on ".$organisation." Admin Section ";

	        $recipient["name"] = $username;
            $recipient["email"] = $email;
	        //$url = $rootUrlViews."login/";
	        $message=  "<div>
                        Your ".$organisation." Admin account has been Activated.
                        <br>
                        Your Password is: <span style='font-weight:bold;'>".$password."</span></p><br>
                        <p> Login Url : <a href='".$url."' target='_blank'> ".$url."</a> and remember to change your password after your first login!</p>                        
                        </div>";
            $trans = 1;
            $type = 0;
            $icon = "";
            $response= sendMail($emailtomatch,$emailSubject, $message);
            
            session_start();
            $_SESSION["activate_user_message"] = "Your Account is activated. Please check your email for the password.";

	        header("Location: ".$url);
	    }else{
	    	

	    	session_start();
            $_SESSION["activate_user_message"] = "Invalid Token you are not valid user!!";

	        header("Location: ".$url);
	    }
	}else{
exit("herr");
		session_start();
        $_SESSION["activate_user_message"] = "Invalid Token you are not valid user!!";

	    header("Location: ".$url."?status=0");
	}
?>