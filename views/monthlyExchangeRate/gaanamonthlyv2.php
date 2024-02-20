<?php
//Manage distributors view page
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

function getActivityInfo(
	$fieldSearchArr=null,
	$fieldsStr="",
	$dateField=null,
	$conn,
	$offset=null,
	$resultsPerPage=10,
	$orderBy="id"
)
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

 

	if (empty($fieldsStr))
		$fieldsStr = "*";

	$getDistributorInfoQuery = "SELECT {$fieldsStr} FROM 	monthly_rate_saavan_gaana_other";
	if (!empty($whereClause))
		$getDistributorInfoQuery .= " WHERE {$whereClause}";
	$getDistributorInfoQuery .= " ORDER BY ".$orderBy ." desc";
	if ($offset!==null)
		$getDistributorInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";
	$getDistributorInfoQueryResult = runQuery($getDistributorInfoQuery, $conn);
	if (!noError($getDistributorInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $getDistributorInfoQueryResult["errMsg"], null);
	}


	/* This function negotiates that an distributor_id must be fetched from the database. All distributor info is keyed by the distributor's distributor_id
	*  However, in case an distributor_id is not desired, like in the case of fetching counts, a default distributor_id of "-9999" will be used
	*/
	while ($row = mysqli_fetch_assoc($getDistributorInfoQueryResult["dbResource"])) {
		if (!isset($row["id"]))
			$row["id"] = "-9999";
			
		// $res = $row;
		$res[$row["id"]] = $row;
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
    $conn = $conn["errMsg"];

    $returnArr = array();

    //get the user info
    $email = $_SESSION['userEmail'];

    //initialize logs
    $logsProcessor = new logsProcessor();
    $initLogs = initializeJsonLogs($email);
    $logFilePath = $logStorePaths["distributors"];
    $logFileName="viewDistributors.json";

    $logMsg = "View Distributors process start.";
    $logData['step1']["data"] = "1. {$logMsg}";

    $logMsg = "Database connection successful.";
    $logData["step2"]["data"] = "2. {$logMsg}";

    $logMsg = "Attempting to get user info.";
    $logData["step3"]["data"] = "3. {$logMsg}";
 
    $userSearchArr = array('email'=>$email);
    $fieldsStr = "email, status, image, `groups`, rights, firstname, lastname";
    $userInfo = getUserInfo($userSearchArr, $fieldsStr, $conn);
    if (!noError($userInfo)) {
        //error fetching user info
        $logMsg = "Couldn't fetch user info: {$userInfo["errMsg"]}";
        $logData["step3.1"]["data"] = "3.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5).": Error fetching user details.";
    } else {
        //check if user not found
        $userInfo = $userInfo["errMsg"];
        if (empty($userInfo)) {
            //user not found
            $logMsg = "User not found: {$token}";
            $logData["step3.1"]["data"] = "3.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5).": This URL is invalid or expired.";
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
                $returnArr["errMsg"] = getErrMsg(5).": This URL is invalid or expired.";
            } else {
                //user is found and is active. Now validate the request parameters
                //pagination parameters
                $page = 1;
                if (isset($_GET['page']) && !empty($_GET["page"])) {
                    $page = preg_replace('#[^0-9]#i', '', $_GET['page']);
                }
                $resultsPerPage = RESULTSPERPAGE;
                $offset = ($page - 1) * $resultsPerPage;

                $logMsg = "Attempting to get count of all monthly rate of gaana.";
                $logData["step4"]["data"] = "4. {$logMsg}";
                
                //set the search array based on get parameters
                $distributorSearchArr = array("ndtype"=>'gaana');                
                $fieldsStr = "COUNT(*) as noOfMonths";
                $allDistributorsCount = getActivityInfo($distributorSearchArr, $fieldsStr, null, $conn);
              
                
                if (!noError($allDistributorsCount)) {
                    //error fetching all distributors Count
                    $logMsg = "Couldn't fetch all activity Count: {$allDistributorsCount["errMsg"]}.".
                                "Search params: ".json_encode($distributorSearchArr);
                    $logData["step4.1"]["data"] = "4.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                    
                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Error fetching Months rate gaana count details.";
                } else {
                    $allDistributorsCount = $allDistributorsCount["errMsg"]["-9999"]["noOfMonths"]; //why -9999? see function definition
                    //set the last page num
                    $lastPage = ceil($allDistributorsCount / $resultsPerPage);

                    if ($page <= 1) {
                        $page = 1;
                    } else if ($page > $lastPage) {
                        $page = $lastPage;
                    }

                    $logMsg = "Got all monthly rate of gaana count for page: {$page}. Now getting all monthly rate of gaana info";
                    $logData["step5"]["data"] = "5. {$logMsg}";
                    
                    $fieldsStr = "*";
                    $allDistributorsInfo = getActivityInfo(
                        $distributorSearchArr,
                        $fieldsStr,
                        null,
                        $conn,
                        $offset,
                        $resultsPerPage
                    );
                    if (!noError($allDistributorsInfo)) {
                        //error fetching all distributors info
                        $logMsg = "Couldn't fetch all monthly rate of gaana info: {$allDistributorsInfo["errMsg"]}";
                        $logData["step4.1"]["data"] = "4.1. {$logMsg}";
                        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                        
                        $returnArr["errCode"] = 5;
                        $returnArr["errMsg"] = getErrMsg(5)." Error fetching monthly rate of gaana details.";
                    } else {
                        $logMsg = "Got all monthly rate of gaana data for page: {$page}";
                        $logData["step4"]["data"] = "4. {$logMsg}";
                        $allDistributorsInfo = $allDistributorsInfo["errMsg"];
                        $returnArr["errCode"] = -1;
                    } //close getting all distributors info
                } //close getting all distributors count
            } //close checking if user is active
        } // close checking if user is found
    } // close user info
} //close db conn


?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="icon" type="image/png" href="<?php echo $rootUrl; ?>assets/img/nirvana_favicon.png" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>
        <?php echo APPNAME; ?>
    </title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />
    <link href="<?php echo $rootUrl; ?>assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo $rootUrl; ?>assets/css/material-dashboard.css?v=1.2.0" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Work+Sans:400,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/style.css">
    <script src="<?php echo $rootUrl; ?>assets/js/jquery.min.js" type="text/javascript"></script>
</head>

<body>
    <div class="wrapper">
        <?php 
            $pageTitle = "Monthly Gaana Rate";
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
                                    <h4 class="title">
                                        <?php echo cleanXSS($pageTitle); ?>
                                    </h4>
                                    
                                </div> <!-- end card header -->
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
<!------------------------------------------------------------------------------------------------------------------->
                                    <!--Newly Added Fileds -->
                                    <!--End of Newly Added Fields-->
 <!------------------------------------------------------------------------------------------------------------------------------------------>                                   
 
                                    <!-- distributors table -->
                                    <table class="table table-bordered table-condensed">
                                        <thead>
                                            <tr>
                                                
                                                <th>Month (Date)</th>
                                                <th>Free Playout Revenue</th>
                                                <th>Paid Playout Revenue</th>
                                               
                                                <th>Date Added</th>
                                                
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                          
                                            foreach($allDistributorsInfo as $distributorId=>$distributorDetails){
                                            ?>
                                            <tr>
                                            <td><?php echo $distributorDetails["month"]; ?>-<?php echo $distributorDetails["year"]; ?></td>
                                               
                                                <td><?php echo $distributorDetails["free_playout_revenue"]; ?></td>
                                                <td><?php echo $distributorDetails["paid_playout_revenue"]; ?></td>
                                               
                                                <td><?php echo $distributorDetails["date_added"]; ?></td>                                  
                                            </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                    <!-- end Clients table -->
                                    <!-- pagination -->
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination">
                                            <?php
                                            if($page>1){
                                            ?>
                                                <li class="page-item"><a class="page-link" href="?page=1">&laquo;</a></li>
                                                <li class="page-item"><a class="page-link" href="?page=<?php echo ($page-1); ?>">Prev</a></li>
                                            <?php
                                            }

                                            //loop through the pagination range after setting it to display page numbers
                                            if ($page == 1) {
                                                $startLoop = 1;
                                                $endLoop = ($lastPage < PAGINATIONRANGE) ? $lastPage : PAGINATIONRANGE;
                                            } else if ($page == $lastPage) {
                                                    $startLoop = (($lastPage - PAGINATIONRANGE) < 1) ? 1 : ($lastPage - PAGINATIONRANGE);
                                                    $endLoop = $lastPage;
                                            } else {
                                                    $startLoop = (($page - PAGINATIONRANGE) < 1) ? 1 : ($page - PAGINATIONRANGE);
                                                    $endLoop = (($page + PAGINATIONRANGE) > $lastPage) ? $lastPage : ($page + PAGINATIONRANGE);
                                            }
                                        
                                            for ($i = $startLoop; $i <= $endLoop; $i++) {
                                                $activeClass = ($i==$page)?"active":"";                                                        
                                            ?>
                                                <li class="page-item <?php echo $activeClass; ?>"><a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                                            <?php
                                            }
                                            ?>
                                            <?php
                                            if($page<$lastPage){
                                            ?>
                                                <li class="page-item"><a class="page-link" href="?page=<?php echo ($page+1); ?>">Next</a></li>
                                                <li class="page-item"><a class="page-link" href="?page=<?php echo ($lastPage); ?>">&raquo;</a></li>
                                            <?php
                                            }
                                            ?>
                                        </ul>
                                    </nav>
                                    <!-- end pagination -->
                                </div> <!-- end card content -->
                            </div> <!-- end card -->
                        </div> <!-- end col md 12 -->
                    </div> <!-- end row -->
                </div> <!-- end container fluid -->
            </div> <!-- end content -->
        </div> <!-- end main panel -->
    </div> <!-- end wrapper -->
    <?php
    //if user has write access, show add+delete modal
    if ($userHighestPermOnPage == 2) {
    ?>
        <!-- delete distributor modal -->
        <div class="modal fade" id="deleteDistributorModal">
            <!-- Modal content-->
            <div class="modal-content">        
                <div class="modal-header">
                    <h4 class="modal-title">Delete Distributor!</h4>
                </div>
                <div class="modal-body">
                    <div class="alert" style="display: none">
                        <span></span>
                    </div>
                    <p>
                        Are you sure you want to delete this distributor?
                        <p id="distributorToDelete"></p>
                    </p>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-danger" id="deleteDistributorBtn" data-distributor-id=""
                            onclick="deleteDistributor(this);"
                        >Continue</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- end delete distributor modal -->
 
        <script>
            

            
 

        </script>    
    <?php
    }
    
    //include the loader
    require_once(__ROOT__."/views/common/loader.php");
    ?>
    <!--   Core JS Files   -->
    <script src="<?php echo $rootUrl; ?>assets/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/material.min.js" type="text/javascript"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/perfect-scrollbar.jquery.min.js"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/parsley.js"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/material-dashboard.js"></script>
</body>

</html>