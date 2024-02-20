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
require_once(__ROOT__.'/model/distributor/distributorModel.php');

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

    $distributorStatusMap = array(
        "1" => "Active",
        "0" => "Inactive",
        "2" => "Deleted"
    );

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

                $logMsg = "Attempting to get count of all distributors.";
                $logData["step4"]["data"] = "4. {$logMsg}";
                
                //set the search array based on get parameters
                $distributorSearchArr = array("1"=>1);                
                $fieldsStr = "COUNT(*) as noOfdistributors";
                $allDistributorsCount = getDistributorsInfo($distributorSearchArr, $fieldsStr, null, $conn);
                if (!noError($allDistributorsCount)) {
                    //error fetching all distributors Count
                    $logMsg = "Couldn't fetch all distributors Count: {$allDistributorsCount["errMsg"]}.".
                                "Search params: ".json_encode($distributorSearchArr);
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

                    $logMsg = "Got all distributors count for page: {$page}. Now getting all distributors info";
                    $logData["step5"]["data"] = "5. {$logMsg}";
                    
                    $fieldsStr = "*";
                    $allDistributorsInfo = getDistributorsInfo(
                        $distributorSearchArr,
                        $fieldsStr,
                        null,
                        $conn,
                        $offset,
                        $resultsPerPage
                    );
                    if (!noError($allDistributorsInfo)) {
                        //error fetching all distributors info
                        $logMsg = "Couldn't fetch all distributors info: {$allDistributorsInfo["errMsg"]}";
                        $logData["step4.1"]["data"] = "4.1. {$logMsg}";
                        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                        
                        $returnArr["errCode"] = 5;
                        $returnArr["errMsg"] = getErrMsg(5)." Error fetching distributors details.";
                    } else {
                        $logMsg = "Got all distributors data for page: {$page}";
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
            $pageTitle = "Manage Distributors";
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
                                        <a href="<?php echo $rootUrl; ?>views/distributors/index.php">
                                                <i class="fa fa-handshake-o">&nbsp;</i>Manage distributors
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
                                    <!-- end success/error messages -->
<!------------------------------------------------------------------------------------------------------------------->
                                    <!--Newly Added Fileds -->
                                    <!--End of Newly Added Fields-->
 <!------------------------------------------------------------------------------------------------------------------------------------------>                                   

                                    <!-- Add distributors button -->
                                    <?php
                                    //if user has write access, show export button
                                    if ($userHighestPermOnPage == 2) {
                                    ?>
                                        <div class="col-md-12">
                                            <a href="javascript:;" 
                                                class="ls-modal btn btn-xs btn-warning pull-right" 
                                                onclick="showAddDistributorForm('', 'Add');"
                                            >
                                                <span class="fa fa-plus">Add Distributor</span>
                                            </a>
                                        </div>
                                    <?php 
                                    }
                                    ?>
                                    <!-- end Add distributors button -->
                                    <!-- distributors table -->
                                    <table class="table table-bordered table-condensed">
                                        <thead>
                                            <tr>
                                                <th>Distributor Name</th>
                                                <th>Fees Sharing</th>
                                                <th>Number of clients</th>
                                                <th>Comments</th>
                                                <th>Status</th>
                                                <?php
                                                //if user has write access, show export button
                                                if ($userHighestPermOnPage == 2) {
                                                ?>
                                                    <th>Actions</th>
                                                <?php
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach($allDistributorsInfo as $distributorId=>$distributorDetails){
                                            ?>
                                            <tr>
                                                <td><?php echo $distributorDetails["distributor_name"]; ?></td>
                                                <td>
                                                    <?php 
                                                    echo "Management Fee Share : "."<b>".$distributorDetails["management_fee_sharing"]."<b>";
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    echo "Number of clients : "."<b>".$distributorDetails["no_of_client"]."<b>";
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                        echo $distributorDetails["comments"];
                                                    ?>
                                                </td>
                                                <td><?php echo $distributorStatusMap[$distributorDetails["status"]]; ?></td>
                                                <?php
                                                //if user has write access, show export button
                                                if ($userHighestPermOnPage == 2) {
                                                ?>
                                                    <td>
                                                        <a href="javascript:;" 
                                                            class="ls-modal btn btn-xs btn-success" 
                                                            onclick="showAddDistributorForm(
                                                                '<?php echo $distributorDetails['distributor_id']; ?>', 
                                                                'Edit'
                                                            );"
                                                        >
                                                            <span class="fa fa-edit"></span>
                                                        </a>
                                                        <a class="btn btn-xs btn-danger" 
                                                            onclick="confirmDeleteDistributor(
                                                                '<?php echo $distributorDetails['distributor_id']; ?>', 
                                                                '<?php echo htmlentities($distributorDetails['distributor_name']); ?>'
                                                            );"
                                                        >
                                                            <span class="fa fa-close"></span>
                                                        </a>
                                                    </td>
                                                <?php
                                                }
                                                ?>                                                
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

        <!-- add distributor modal -->
        <div class="modal fade modal-lg" id="addDistributorModal">
            <!-- Modal content-->
            <div class="modal-content">        
                <div class="modal-header">
                    <h4 class="modal-title"><span></span> Distributor</h4>
                </div>
                <div class="modal-body">
                    <div class="alert" style="display: none">
                        <span></span>
                    </div>
                    <div class="modal-body-content"></div>                
                </div>
            </div>
        </div>
        <!-- end add distributor modal -->
        <script>
            function confirmDeleteDistributor(distributorId, distributorName) 
            {
                $("#deleteDistributorModal .modal-body #distributorToDelete").html(distributorId+"-"+distributorName);
                $("#deleteDistributorModal .modal-footer #deleteDistributorBtn").data("distributor-id", distributorId);
                $("#deleteDistributorModal").modal();
            }

            function deleteDistributor(buttonElement)
            {
                let distributorId = $(buttonElement).data("distributor-id");
                //resetting the error message
                $("#deleteDistributorModal .alert").
                    removeClass("alert-success").
                    removeClass("alert-danger").
                    fadeOut().
                    find("span").html("");

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "<?php echo $rootUrl; ?>controller/distributor/delete/",
                    data: {"distributorId":encodeURIComponent(distributorId)},
                    success: function (distributor) {
                        if (distributor["errCode"]) {
                            if (distributor["errCode"] != "-1") { //there is some error
                                $("#deleteDistributorModal .alert").
                                    removeClass("alert-success").
                                    addClass("alert-danger").
                                    fadeIn().
                                    find("span").
                                    html(distributor["errMsg"]);                                
                            } else {
                                $("#deleteDistributorModal .alert").
                                    removeClass("alert-danger").
                                    addClass("alert-success").
                                    fadeIn().
                                    find("span").
                                    html(distributor["errMsg"]);
                                    setTimeout(function(){
                                        window.location.reload();
                                    }, 3000);
                            }
                        }
                    },
                    error: function () {
                        $("#deleteDistributorModal .alert").
                            removeClass("alert-success").
                            addClass("alert-danger").
                            fadeIn().
                            find("span").
                            html("500 internal server error");
                    }
                });
            }

            function showAddDistributorForm(distributorId, actionType)
            {
                $("#addDistributorModal .modal-title span").html(actionType);
                $("#addDistributorModal .modal-body-content").load(
                    "<?php echo $rootUrl; ?>views/distributors/manage/?distributorId="+encodeURIComponent(distributorId)
                );
                $("#addDistributorModal").modal();
            }

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