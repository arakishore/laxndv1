<?php
//Admin Dashboard view page
/*
0. Prepare for request
1. Validate session and current user details. Get necessary preresuisites for header, sidebar, access control
2. if current user belongs to Client/Distributor group, redirect to relevant dashboard
3. Validate portfolio date parameter. If not set, get latest MIS date from Distributor
4. get the MIS data from DB for client code BUOYANTCAP
5. remove columns 12/L, 8/H from header, subtotals, totals, cashreceivables, netassets and securities array
6. check if request is to export, then create excel, else print on screen
*/

session_start();

//prepare for request
//include necessary helpers
require_once('../../config/config.php');

//check if session is active
$sessionCheck = checkSession();

//include some more necessary helpers
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/logs/logsProcessor.php');
require_once(__ROOT__.'/config/logs/logsCoreFunctions.php');
require_once(__ROOT__.'/vendor/phpoffice/phpspreadsheet/src/Bootstrap.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

//include necessary models
require_once(__ROOT__.'/model/user/userModel.php');

function getProcesslistInfo(
	 
	$conn
	
)
{
	$res = array();
	$returnArr = array();
	$whereClause = "";
	
	 
 

	 

	$getDistributorInfoQuery = "show PROCESSLIST";
	$getDistributorInfoQueryResult = runQuery($getDistributorInfoQuery, $conn);
	if (!noError($getDistributorInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $getDistributorInfoQueryResult["errMsg"], null);
	}


	/* This function negotiates that an distributor_id must be fetched from the database. All distributor info is keyed by the distributor's distributor_id
	*  However, in case an distributor_id is not desired, like in the case of fetching counts, a default distributor_id of "-9999" will be used
	*/
	while ($row = mysqli_fetch_assoc($getDistributorInfoQueryResult["dbResource"])) {
		if (!isset($row["Id"]))
			$row["Id"] = "-9999";
			
		// $res = $row;
		$res[$row["Id"]] = $row;
	}
	
	return setErrorStack($returnArr, -1, $res, null);
}

//Connection With Database
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1).$conn["errMsg"];
} else {

    
  
    
  //  $to = "swamiwebservices@gmail.com";
 ///   $subject = "test ".time();
  //  $message = "swamiwebservices@gmail.com";
  //  echo "<br> checkg mail ";
   // sendMail($to, $subject, $message);
    
    $conn = $conn["errMsg"];

    $getClientInfoQuery = "SHOW TABLES LIKE 'youtube%'";

    $getClientInfoQueryResult = runQuery($getClientInfoQuery, $conn);
    $US_HOLDING_PERCENTAGE = US_HOLDING_PERCENTAGE;
    while ($row =  mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {
        //kham8888
       
        foreach ($row as $k => $v) {
            $tableName = $v;

     echo "<br>\n".     $truncateTableQuery = "ALTER TABLE {$tableName} ADD COLUMN  `holding_percentage` decimal(10,2) DEFAULT {$US_HOLDING_PERCENTAGE} ,ADD INDEX (holding_percentage);";
       //  $truncateTableQueryResult = runQuery($truncateTableQuery, $conn);
          
            $sql ="UPDATE  {$tableName} SET `holding_percentage` = '30' , final_payable_with_gst = final_payable";
          //  $truncateTableQueryResult = runQuery($sql, $conn); 

        }
    }
    $returnArr = array();

    //get the user info
    $username = $_SESSION['userEmail'];

    //initialize logs
    $logsProcessor = new logsProcessor();
    $initLogs = initializeJsonLogs($username);
    $logFilePath = $logStorePaths["dashboard"];
    $logFileName="dashboard.json";

    $logMsg = "Dashboard process start.";
    $logData['step1']["data"] = "1. {$logMsg}";

    $logMsg = "Database connection successful.";
    $logData["step2"]["data"] = "2. {$logMsg}";

    $logMsg = "Attempting to get user info.";
    $logData["step3"]["data"] = "3. {$logMsg}";

    $userSearchArr = array('email'=>$username);
    $fieldsStr = "email, status, image, `groups`, rights, firstname, lastname";
    $userInfo = getUserInfo($userSearchArr, $fieldsStr, $conn);
    if (!noError($userInfo)) {
        //error fetching user info
        $logMsg = "Couldn't fetch user info: {$userInfo["errMsg"]}";
        $logData["step3.1"]["data"] = "3.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error fetching user details.";
    } else {
        //check if user not found
        $userInfo = $userInfo["errMsg"];
        if (empty($userInfo)) {
            //user not found
            $logMsg = "User not found: {$token}";
            $logData["step3.1"]["data"] = "3.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5)." This URL is invalid or expired.";
        } else {
            //check if user is active
            //first get the user email
            $email = array_keys($userInfo);
            $email = $email[0];
            if ($userInfo[$email]["status"]!=1) {
                //user not active
                $logMsg = "User not active: {$token}";
                $logData["step3.1"]["data"] = "3.1 {$logMsg}";
                $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                $returnArr["errCode"] = 5;
                $returnArr["errMsg"] = getErrMsg(5)." This URL is invalid or expired.";
            } else {
                //user is found and is active. 
                //if user belongs to client group, go to client dashboard
                if (strpos($userInfo[$email]["groups"], "Client") !== false) {
                    print("Redirecting to client dashboard");
                    header("Location: ".$rootUrl."views/dashboard/client/");
                }
                //if user belongs to distributor group, go to dist dashboard
                if (strpos($userInfo[$email]["groups"], "Distributor") !== false) {
                    print("Redirecting to distributor dashboard");
                    header("Location: ".$rootUrl."views/dashboard/distributor/");
                }
                //Now validate the request parameters
                $returnArr["errCode"] = -1;
            } //close checking if user is active
        } //close checking if user not found
    } //close no error userinfo else
} //close check db conn
 
$processlistInfo = getProcesslistInfo($conn);
$processlistInfo = $processlistInfo["errMsg"];
 //print_r($allDistributorsInfo);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <link rel="icon" type="image/png" href="<?php echo $rootUrl; ?>assets/img/nirvana_favicon.png"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title><?php echo APPNAME; ?></title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />
    <link href="<?php echo $rootUrl; ?>assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo $rootUrl; ?>assets/css/material-dashboard.css?v=1.2.0" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Work+Sans:400,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php 
            $pageTitle = "Dashboard";
            //include access control
            require_once(__ROOT__.'/controller/access-control/checkUserAccess.php');
            require_once(__ROOT__."/views/common/sidebar.php");
        ?>
        <div class="main-panel">
            <?php 
                require_once(__ROOT__."/views/common/header.php");
            ?>
            <div class="content">
                <div class="container-fluid">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <!-- card header and breadcrumbs -->
                                <div class="card-header">
                                    <h4 class="title"><?php echo cleanXSS($pageTitle); ?></h4>
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="<?php echo $rootUrl."views/dashboard/"; ?>"><i class="fa fa-dashboard">&nbsp;</i>Dashboard</a>
                                        </li>
                                    </ol>
                                </div>
                                <div class="card-content">
                                    <!-- success/error messages -->
                                    <?php
                                    $alertMsg = "";
                                    $alertClass = "alert-success";
                                    if (!noError($returnArr)) {
                                        $alertClass = "alert-danger";
                                        $alertMsg = $returnArr["errMsg"];
                                    ?>
                                        <div class="alert <?php echo $alertClass; ?>">
                                            <span>
                                                <?php echo $alertMsg; ?>
                                            </span>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <!-- end success/error messages -->
                                    <!-- Search by date form -->   

                                     <!-- distributors table -->
                                     <div class="col-md-12">
                                                <h4>Database Server Load Stats</h4>
                                            </div>
                                     <table class="table table-bordered table-condensed">
                                        <thead>
                                            <tr>
                                            <th>Id</th>
                                                <th>User</th>
                                                <th>Host</th>
                                                <th>db</th>
                                                <th>Command</th>
                                                <th>Time</th>
                                                <th>State</th>
                                                <th>Info</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                          
                                            foreach($processlistInfo as $distributorId => $distributorDetails){
                                               // print_r($distributorDetails);
                                            ?>
                                            <tr>
                                            <td><?php echo $distributorDetails["Id"]; ?></td>
                                                <td><?php echo $distributorDetails["User"]; ?></td>
                                                <td><?php echo $distributorDetails["Host"]; ?></td>
                                                <td><?php echo $distributorDetails["db"]; ?></td>
                                                <td><?php echo $distributorDetails["Command"]; ?></td>
                                                 
                                                <td><?php echo $distributorDetails["Time"]; ?></td>                             
                                                <td><?php echo $distributorDetails["State"]; ?></td>                                  
                                                <td><?php echo $distributorDetails["Info"]; ?></td> 
                                            </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<!--   Core JS Files   -->
<script src="<?php echo $rootUrl; ?>assets/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $rootUrl; ?>assets/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo $rootUrl; ?>assets/js/material.min.js" type="text/javascript"></script>
<script src="<?php echo $rootUrl; ?>assets/js/perfect-scrollbar.jquery.min.js"></script>
<script src="<?php echo $rootUrl; ?>assets/js/material-dashboard.js"></script>
<script>
    $(window).on("load", function(e){
        //managing the floating labels behaviour
        $("form#getPortfolioForm :input").each(function () {
            var input = $(this).val();
            if ($.trim(input) != "") {
                $(this).parent().removeClass("is-empty");
            }
            $(this).on("focus", function(){
                $(this).parent().removeClass("is-empty");
            })
            $(this).on("blur", function(){
                var input = $(this).val();
                if (input && $.trim(input) != "") {
                    $(this).parent().removeClass("is-empty");
                } else {
                    $(this).parent().addClass("is-empty");
                }
            })
        });
    });
</script>
</html>