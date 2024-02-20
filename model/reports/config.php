<?php
//$rootUrl="http://localhost/content_repo_v2/";
//$rootUrl="http://13.234.2.91/content_repo_v2/";

$rootUrl="http://reports.nirvanadigital.com/content_repo_v4/";

//$rootUrl="http://35.154.241.241/content_repo_v2/"; // new os server 

$logsRootURL = $_SERVER['DOCUMENT_ROOT']."/content_repo_v2/logs/";


//base URLs for logs
$logStorePaths = array(
        "forgotPassword" => $logsRootURL."/password/forgot/",
        "resetPassword" => $logsRootURL."/password/reset/",
        "login" => $logsRootURL."/user/login/",
        "profile" => $logsRootURL."/user/profile/",
        "dashboard" => $logsRootURL."/dashboard/",
        "accessControl" => $logsRootURL."/access-control/",
        "clients" => $logsRootURL."/clients/",
        "distributors" => $logsRootURL."/distributors/",
        "reports" => array (
                "import" => $logsRootURL."/reports/import/"
        )
);

/*$host = "localhost";
$dbUsername = "root";
$dbPassword = "mysql";
$dbName = "digizen_content_reporting";
$db_username = "root";
$db_password = "mysql";
*/


//$host = "13.233.72.135"; // new os server 

$host = "65.1.160.198"; // live server 
//$host = "3.7.218.58";  // old static ip

$dbUsername = "laxmann";
$dbPassword = "SxDc@1234";
$dbName = "nirvana_wip";
$db_username = "laxmann";
$db_password = "SxDc@1234";



define('__ROOT__', dirname(dirname(__FILE__)));
define('APPNAME', "Nirvana Digital");
//define('IMPORTNOTIFIERS', "viral.hansinfotech@gmail.com");
define('IMPORTNOTIFIERS', "laxmannkaande@gmail.com");
define('RESULTSPERPAGE', 50);
define('PAGINATIONRANGE', 3);

define('US_HOLDING_PERCENTAGE', "30");

/*define('DBHOST', "localhost");
define('DBUSERNAME', "root");
define('DBPASSWORD', "mysql");
define('DBNAME', "digizen_content_reporting");
*/

//define('DBHOST', "3.7.218.58"); // old static ip

define('DBHOST', "65.1.160.198");
//define('DBHOST', "13.233.72.135");
define('DBUSERNAME', "laxmann");
define('DBPASSWORD', "SxDc@1234");
define('DBNAME', "nirvana_wip");

function printArr($array)
{
        echo "<pre>";
                print_r($array);
        echo "</pre>";
}

function noError($arr)
{
        if($arr["errCode"] == -1)
                return true;
        else
                return false;
}

function cleanQueryParameter($conn,$string)
{
        $string = trim($string);
        $string = addslashes($string);
        $string = mysqli_real_escape_string($conn,$string);

        return $string;
}

function cleanXSS($string)
{
        return htmlspecialchars($string,ENT_QUOTES,'UTF-8');
}

function sendMail($to, $subject, $message)
{
       /*
        SMTP Username:
        AKIAU5CSK3EPU4ONTGUS
        SMTP Password:
        BMx44WSd4ZHpAwM7EObL/9Tg/No1dNua+HKRoO/ot4gp
        
        Server Name:	
        email-smtp.ap-south-1.amazonaws.com
        Port:	25, 465 or 587
        Use Transport Layer Security (TLS):	Yes
       */
        $returnArr = array();

        $returnArr["errCode"]=-1;
        $returnArr["errMsg"]="Mail has been sent successfully";
        return $returnArr;	
        exit;
        $mail = new PHPMailer;
        //Tell PHPMailer to use SMTP
        $mail->isSMTP();
        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = 0;
        //Set the hostname of the mail server
        $mail->Host = 'email-smtp.ap-south-1.amazonaws.com';
        // $mail->Host = gethostbyname('smtp.gmail.com');
        // if your network does not support SMTP over IPv6
        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $mail->Port = 25;
        //Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPSecure = 'tls';
        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;
        //Username to use for SMTP authentication - use full email address for gmail
        $mail->Username ="AKIAU5CSK3EPU4ONTGUS";
        //Password to use for SMTP authentication
        $mail->Password = "BMx44WSd4ZHpAwM7EObL/9Tg/No1dNua+HKRoO/ot4gp";
        //Set who the message is to be sent from
        $mail->setFrom('mailsendteam007@gmail.com', 'Nirvana Digital');
        //Set an alternative reply-to address
        //$mail->addReplyTo('replyto@example.com', 'First Last');
        //Set who the message is to be sent to
        //$mail->addAddress($to);
	$mail->addAddress("laxmankaande@gmail.com");
        //Set the subject line
        $mail->Subject = $subject;


        $mail->msgHTML($message);
        $mail->AltBody = 'This is a plain-text message body';


		 @unlink("polomail.txt");
		file_put_contents("polomail.txt", $mail->send());
		@chmod("polomail.txt",0777);	
 

        if (!$mail->send()) {
                $returnArr["errCode"]=8;
                $returnArr["errMsg"]="Mail was not sent.. Something went wrong";
        } else {
                $returnArr["errCode"]=-1;
                $returnArr["errMsg"]="Mail has been sent successfully";
        }

        return $returnArr;
}

function checkSession()
{
        global $rootUrl;
        if (empty($_SESSION['user_id'])){
                header("Location:".$rootUrl."views/login/");
                exit;
        }
}

function moneyFormatIndia($number)
{
        $negative = "";
        if(strstr($number,"-"))
        {
                $number = str_replace("-","",$number);
                $negative = "-";
        }

        $split_number = @explode(".",$number);

        $rupee = $split_number[0];
        $paise = @$split_number[1];

        if(@strlen($rupee)>3)
        {
                $hundreds = substr($rupee,strlen($rupee)-3);
                $thousands_in_reverse = strrev(substr($rupee,0,strlen($rupee)-3));
                $thousands = '';
                for($i=0; $i<(strlen($thousands_in_reverse)); $i=$i+2)
                {
                        @$thousands .= $thousands_in_reverse[$i].$thousands_in_reverse[$i+1].",";
                }
                $thousands = strrev(trim($thousands,","));
                $formatted_rupee = $thousands.",".$hundreds;

        }
        else
        {
                $formatted_rupee = $rupee;
        }

        if((int)$paise>0)
        {
                $formatted_paise = ".".substr($paise,0,2);
        }else{
                $formatted_paise = '';
        }

        return $negative.$formatted_rupee.$formatted_paise;

}

function endsWith($haystack, $needle)
{
        $length = strlen($needle);
        if ($length == 0) {
        return true;
        }

        return (substr($haystack, -$length) === $needle);
}

function removeLineBreaks($str) {
        return preg_replace('/\s+/', ' ', trim($str));
}


function runBackgroundProcess($command)
{
        //file_put_contents("testprocesses.php","foreground start time = " . time() . "\n");to do: write to log
        //echo "<pre>  foreground start time = " . time() . "</pre>";to do: write to log

        // output from the command must be redirected to a file or another output stream 
        // http://ca.php.net/manual/en/function.exec.php
      //  exec("php ".$command." ", $commandOutput); var_dump($commandOutput);
      
      /* @unlink("polo.txt");
      file_put_contents("polo.txt", "php ".$command." > backgroundOutput.php 2>&1 & echo $!");
      @chmod("polo.txt",0777); */
      
        exec("php ".$command." > backgroundOutput.php 2>&1 & echo $!", $commandOutput); //keep better logging

       
       // echo "<pre>  foreground end time = " . time() . "</pre>"; //to do: write to log
       // file_put_contents("testprocesses.php","foreground end time = " . time() . "\n", FILE_APPEND);to do: write to log
        return $commandOutput;
}
function checkFileexist($filename)
{
	$returnArr = array();

	if (empty($filename)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." filename to create cannot be empty", null);
	}

	 

	if(!file_exists($filename)){
                
                    
                    $returnArr["errCode"]=81;
                    $returnArr["errMsg"] = $filename ." file does not exist";
	}  else {
                $returnArr["errCode"]=-1;
                $returnArr["errMsg"]="File is present";
        }
    return $returnArr;
}
function checkUnAssignedContentOwner($tableName,$columnname,$conn){
        $returnArr = array();
          

         $query = "SELECT count(*) as totalblank FROM `{$tableName}` where ({$columnname} IS NULL  || {$columnname} ='')";
	$queryresult = runQuery($query, $conn);
	if (!noError($queryresult)) {
		return setErrorStack($returnArr, 3, $queryresult["errMsg"], null);
	}
	$totalmaps = mysqli_fetch_assoc($queryresult["dbResource"]);
        $totalmaps=$totalmaps['totalblank'];
        

        return $totalmaps;		
}
?>