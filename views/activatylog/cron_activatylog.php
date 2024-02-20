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

	$getDistributorInfoQuery = "SELECT {$fieldsStr} FROM activity_downlaod_report";
	if (!empty($whereClause))
		$getDistributorInfoQuery .= " WHERE {$whereClause}";
	$getDistributorInfoQuery .= " ORDER BY ".$orderBy ." desc , status_flag asc";
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

                $logMsg = "Attempting to get count of all activity_reports.";
                $logData["step4"]["data"] = "4. {$logMsg}";
                
                //set the search array based on get parameters
                   $clientSearchArr = array("1"=>1);
                                
                   $Keyword = (isset($_GET['Keyword'])) ? $_GET['Keyword'] :'';
                   if($Keyword!=""){
                       $clientSearchArr = array("content_owner"=>$Keyword);  
                   } else {
                       
                       $clientSearchArr = array("1"=>1);  
                   }
                //set the search array based on get parameters
                    
                $fieldsStr = "COUNT(*) as noOfdistributors";
                $allDistributorsCount = getActivityInfo($clientSearchArr, $fieldsStr, null, $conn);
              
                
                if (!noError($allDistributorsCount)) {
                    //error fetching all distributors Count
                    $logMsg = "Couldn't fetch all activity Count: {$allDistributorsCount["errMsg"]}.".
                                "Search params: ".json_encode($clientSearchArr);
                    $logData["step4.1"]["data"] = "4.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                    
                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." Error fetching distributor count details.";
                } else {
                    $allDistributorsCount = $allDistributorsCount["errMsg"]["-9999"]["noOfdistributors"]; //why -9999? see function definition
                    //set the last page num
                    $lastPage = ceil($allDistributorsCount / $resultsPerPage);

                    if ($page <= 1) {
                        $page = 1;
                    } else if ($page > $lastPage) {
                        $page = $lastPage;
                    }

                 
                    $logMsg = "Got all activity_reports count for page: {$page}. Now getting all activity_reports info";
                    $logData["step5"]["data"] = "5. {$logMsg}";
                    
                    $fieldsStr = "*";
                    $allDistributorsInfo = getActivityInfo(
                        $clientSearchArr,
                        $fieldsStr,
                        null,
                        $conn,
                        $offset,
                        $resultsPerPage
                    );
                    if (!noError($allDistributorsInfo)) {
                        //error fetching all distributors info
                        $logMsg = "Couldn't fetch all activity_reports info: {$allDistributorsInfo["errMsg"]}";
                        $logData["step4.1"]["data"] = "4.1. {$logMsg}";
                        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                        
                        $returnArr["errCode"] = 5;
                        $returnArr["errMsg"] = getErrMsg(5)." Error fetching activity_reports details.";
                    } else {
                        $logMsg = "Got all activity_reports data for page: {$page}";
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
            $pageTitle = "Report Export Log";
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
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="<?php echo $rootUrl?>controller/client/dashboard/export/cron_exportreport.php" target="_blank">
                                                <i class="fa fa-users">&nbsp;</i>Manually Call cron
                                            </a>
                                        </li>
                                    </ol>
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
                 <div class="col-md-8">
                                                <form class="form-inline" method="get">
                                                     
                                                    <div class="form-group mx-sm-3 mb-2">
                                                        <label for="Keyword" class="sr-only">Keyword</label>
                                                        <input type="text" class="form-control" id="Keyword" name="Keyword"
                                                            placeholder="Client">
                                                    </div>
                                                    <button type="submit" class="btn mb-2">Search</button>
                                                </form>
                                            </div>                 
 
                                    <!-- distributors table -->
                                    <table class="table table-bordered table-condensed">
                                        <thead>
                                            <tr>
                                             <th>Action</th>  
                                                <th>Client</th>
                                               <!--  <th>Email</th> -->
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Date Start</th>
                                                <th>Date End</th>
                                                <th>Month-year</th>
                                                <th>For</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                          
                                            foreach($allDistributorsInfo as $distributorId=>$distributorDetails){
                                            ?>
                                            <tr>
                                       <td> <a  href="javascript:void(0);" class="btn btn-xs btn-danger" onclick="confirmDeleteClient('<?php echo trim($distributorDetails['content_owner']);?>','<?php echo $distributorDetails['selected_date']; ?>-<?php echo trim($distributorDetails['title_name']);?>','<?php echo htmlentities(trim($distributorDetails['id']));?>');">
                                            <span class="fa fa-close"></span>
                                        </a>
                                            </td>   
                                                <td><?php echo $distributorDetails["content_owner"]; ?></td>
                                          <!--       <td><?php echo $distributorDetails["email"]; ?></td> -->
                                                <td><?php echo $distributorDetails["type_table"]; ?></td>
                                                <td><?php echo $distributorDetails["status_message"]; ?></td>
                                                <td><?php echo $distributorDetails["date_start"]; ?></td>     
                                                <td><?php echo $distributorDetails["date_end"]; ?></td>                             
                                                <td><?php echo $distributorDetails["selected_date"]; ?></td>                                  
                                                <td><?php echo $distributorDetails["title_name"]; ?></td> 
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
        <!-- delete Client modal -->
        <div class="modal fade" id="deleteClientModal">
            <!-- Modal content-->
            <div class="modal-content">        
                <div class="modal-header">
                    <h4 class="modal-title">Delete Log!</h4>
                </div>
                <div class="modal-body">
                    <div class="alert" style="display: none">
                        <span></span>
                    </div>
                    <p>
                        Are you sure you want to delete this Log?
                        <p id="clientCodeToDelete"></p>
                    </p>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-danger" id="deleteClientBtn" data-client-code=""
                            onclick="deleteClient(this);"
                        >Continue</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- end delete client modal -->
    

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
    <script>
            function confirmDeleteClient(userName,other,id) 
            {
                $("#deleteClientModal .modal-body #clientCodeToDelete").html(userName+'-'+other);
                $("#deleteClientModal .modal-footer #deleteClientBtn").data("row-id", id);
                $("#deleteClientModal").modal();
            }

            function deleteClient(buttonElement)
            {
                let row_id = $(buttonElement).data("row-id");
                //resetting the error message
                $("#deleteClientModal .alert").
                    removeClass("alert-success").
                    removeClass("alert-danger").
                    fadeOut().
                    find("span").html("");

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "<?php echo $rootUrl; ?>controller/activatylog/delete/",
                    data: {"id":encodeURIComponent(row_id)},
                    success: function (client) {
                        if (client["errCode"]) {
                            if (client["errCode"] != "-1") { //there is some error
                                $("#deleteClientModal .alert").
                                    removeClass("alert-success").
                                    addClass("alert-danger").
                                    fadeIn().
                                    find("span").
                                    html(client["errMsg"]);                                
                            } else {
                                $("#deleteClientModal .alert").
                                    removeClass("alert-danger").
                                    addClass("alert-success").
                                    fadeIn().
                                    find("span").
                                    html(client["errMsg"]);
                                    setTimeout(function(){
                                        window.location.reload();
                                    }, 3000);
                            }
                        }
                    },
                    error: function () {
                        $("#deleteClientModal .alert").
                            removeClass("alert-success").
                            addClass("alert-danger").
                            fadeIn().
                            find("span").
                            html("500 internal server error");
                    }
                });
            }

           
            
        </script>
</body>

</html>