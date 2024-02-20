<?php
//Access Control view page
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

//include necessary models
require_once(__ROOT__.'/model/user/userModel.php');
require_once(__ROOT__.'/model/access-control/accessControlModel.php');

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
    $logFilePath = $logStorePaths["accessControl"];
    $logFileName="accessControl.json";

    $logMsg = "Access control process start.";
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
                //user is found and is active. Now validate the request parameters
                //pagination parameters
                $page = 1;
                if (isset($_GET['page']) && !empty($_GET["page"])) {
                    $page = preg_replace('#[^0-9]#i', '', $_GET['page']);
                }
                $offset = ($page - 1) * RESULTSPERPAGE;

                $tab = "users";
                if (isset($_GET['tab']) && !empty($_GET["tab"])) {
                    $tab = cleanXSS($_GET['tab']);
                }
                
                if ($tab=="users") { 

                    $logMsg = "Attempting to get count of all users.";
                    $logData["step4"]["data"] = "4. {$logMsg}";
                    
                    $userSearchArr = array("1"=>1);
                    $fieldsStr = "COUNT(*) as noOfUsers";
                    $allUsersCount = getUserInfo($userSearchArr, $fieldsStr, $conn);
                    if (!noError($allUsersCount)) {
                        //error fetching all users Count
                        $logMsg = "Couldn't fetch all users Count: {$allUsersCount["errMsg"]}";
                        $logData["step4.1"]["data"] = "4.1. {$logMsg}";
                        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                        
                        $returnArr["errCode"] = 5;
                        $returnArr["errMsg"] = getErrMsg(5)." Error fetching user details.";
                    } else {
                        $allUsersCount = $allUsersCount["errMsg"]["anonymous"]["noOfUsers"]; //why anonymous? see function definition
                        //set the last page num
                        $lastPage = ceil($allUsersCount / RESULTSPERPAGE);

                        if ($page <= 1) {
                            $page = 1;
                        } else if ($page > $lastPage) {
                            $page = $lastPage;
                        }
                        
                        $logMsg = "Got all users count for page: {$page}. Now getting all users info";
                        $logData["step5"]["data"] = "5. {$logMsg}";
                        $Keyword = (isset($_GET['Keyword'])) ? $_GET['Keyword'] :'';
                        if($Keyword!=""){
                            $userSearchArr = array("email"=>$Keyword);  
                        } else {
                           
                            $userSearchArr = array("1"=>1);  
                        }
                       // $userSearchArr = array("1"=>1);

                        $fieldsStr = "firstname, lastname, email, phone, designation, department, `groups`, comments, status";
                        $allUsersInfo = getUserInfo($userSearchArr, $fieldsStr, $conn, $offset, RESULTSPERPAGE);
                        if (!noError($allUsersInfo)) {
                            //error fetching all users info
                            $logMsg = "Couldn't fetch all users info: {$allUsersInfo["errMsg"]}";
                            $logData["step4.1"]["data"] = "4.1. {$logMsg}";
                            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                            
                            $returnArr["errCode"] = 5;
                            $returnArr["errMsg"] = getErrMsg(5)." Error fetching user details.";
                        } else {
                            $logMsg = "Got all users data for page: {$page}";
                            $logData["step4"]["data"] = "4. {$logMsg}";
                            $allUsersInfo = $allUsersInfo["errMsg"];
                            $returnArr["errCode"] = -1;
                        } //close getting all users info
                    } //close getting all users count
                } else if ($tab=="groups") {

                    $logMsg = "Attempting to get count of all groups.";
                    $logData["step4"]["data"] = "4. {$logMsg}";
                    
                    $groupSearchArr = array('1'=>1);
                    $fieldsStr = "COUNT(*) as noOfGroups";
                    $allGroupsCount = getGroupInfo($groupSearchArr, $fieldsStr, $conn);
                    if (!noError($allGroupsCount)) {
                        //error fetching all groups Count
                        $logMsg = "Couldn't fetch all groups Count: {$allGroupsCount["errMsg"]}";
                        $logData["step4.1"]["data"] = "4.1. {$logMsg}";
                        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                        
                        $returnArr["errCode"] = 5;
                        $returnArr["errMsg"] = getErrMsg(5)." Error fetching group details.";
                    } else {
                        $allGroupsCount = $allGroupsCount["errMsg"]["-9999"]["noOfGroups"]; //why -9999? see function definition
                        //set the last page num
                        $lastPage = ceil($allGroupsCount / RESULTSPERPAGE);

                        if ($page <= 1) {
                            $page = 1;
                        } else if ($page > $lastPage) {
                            $page = $lastPage;
                        }
                        
                        $logMsg = "Got all groups count for page: {$page}. Now getting all groups info";
                        $logData["step5"]["data"] = "5. {$logMsg}";
                        
                        $groupSearchArr = array('1'=>1);
                        $fieldsStr = "group_name, group_rights, right_on_module, right_on_submodule, group_id";
                        $allGroupsInfo = getGroupInfo($groupSearchArr, $fieldsStr, $conn, $offset, RESULTSPERPAGE);
                        if (!noError($allGroupsInfo)) {
                            //error fetching all groups info
                            $logMsg = "Couldn't fetch all groups info: {$allGroupsInfo["errMsg"]}";
                            $logData["step4.1"]["data"] = "4.1. {$logMsg}";
                            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                            
                            $returnArr["errCode"] = 5;
                            $returnArr["errMsg"] = getErrMsg(5)." Error fetching group details.";
                        } else {
                            $logMsg = "Got all groups data for page: {$page}";
                            $logData["step4"]["data"] = "4. {$logMsg}";
                            $allGroupsInfo = $allGroupsInfo["errMsg"];
                            $returnArr["errCode"] = -1;
                        } //close getting all groups info
                    } //close getting all groups count
                } //closing if else tab type
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
    <!-- Bootstrap core CSS     -->
    <link href="<?php echo $rootUrl; ?>assets/css/bootstrap.min.css" rel="stylesheet" />
    <!--  Material Dashboard CSS    -->
    <link href="<?php echo $rootUrl; ?>assets/css/material-dashboard.css?v=1.2.0" rel="stylesheet" />
    <!--     Fonts and icons     -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <!--Google Font - Work Sans-->
    <link href='https://fonts.googleapis.com/css?family=Work+Sans:400,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/style.css">
    <script src="<?php echo $rootUrl; ?>assets/js/jquery.min.js" type="text/javascript"></script>
</head>

<body>
    <div class="wrapper">
        <?php 
            $pageTitle = "Access Control";
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
                                    <!-- start tabs users and groups -->
                                    <ul class="nav nav-tabs">
                                        <li class="<?php echo ($tab=="users")?"active":""; ?>"><a
                                                href="index.php?tab=users">Users</a></li>
                                        <li class="<?php echo ($tab=="groups")?"active":""; ?>"><a
                                                href="index.php?tab=groups">Groups</a></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade in active">
                                            <!-- end tables -->
                                            <?php
                                            if ($tab=="users") {
                                                $userStatusMap = array(
                                                    "1" => "Active",
                                                    "0" => "Inactive"
                                                )
                                            ?>
                                            <div class="col-md-8">
                                                <form class="form-inline" method="get">
                                                     
                                                    <div class="form-group mx-sm-3 mb-2">
                                                        <label for="Keyword" class="sr-only">Keyword</label>
                                                        <input type="text" class="form-control" id="Keyword" name="Keyword"
                                                            placeholder="Email">
                                                    </div>
                                                    <button type="submit" class="btn mb-2">Search</button>
                                                </form>
                                            </div>
                                            <div class="col-md-4">

                                                <a href="javascript:;"
                                                    class="ls-modal btn btn-xs btn-warning pull-right"
                                                    onclick="showAddUserForm('', 'Add');">
                                                    <span class="fa fa-plus">Add User</span>
                                                </a>
                                            </div>
                                            <table class="table table-bordered table-condensed">
                                                <thead>
                                                    <tr>
                                                        <th>First Name</th>
                                                        <th>Last Name</th>
                                                        <th>Email</th>
                                                        <th>Phone</th>
                                                        <th>Designation</th>
                                                        <th>Department</th>
                                                        <th>Group</th>
                                                        <th>Comments</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        foreach($allUsersInfo as $userEmail=>$userDetails){
                                                        ?>
                                                    <tr>
                                                        <td><?php echo $userDetails["firstname"]; ?></td>
                                                        <td><?php echo $userDetails["lastname"]; ?></td>
                                                        <td><?php echo $userDetails["email"]; ?></td>
                                                        <td><?php echo $userDetails["phone"]; ?></td>
                                                        <td><?php echo $userDetails["designation"]; ?></td>
                                                        <td><?php echo $userDetails["department"]; ?></td>
                                                        <td><?php echo $userDetails["groups"]; ?></td>
                                                        <td><?php echo $userDetails["comments"]; ?></td>
                                                        <td><?php echo $userStatusMap[$userDetails["status"]]; ?></td>
                                                        <td>
                                                            <a href="javascript:;"
                                                                class="ls-modal btn btn-xs btn-success"
                                                                onclick="showAddUserForm('<?php echo $userDetails["email"]; ?>', 'Edit');">
                                                                <span class="fa fa-edit"></span>
                                                            </a>
                                                            <a class="btn btn-xs btn-danger"
                                                                onclick="confirmDeleteUser('<?php echo $userDetails["email"]; ?>');">
                                                                <span class="fa fa-close"></span>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                        }
                                                        ?>
                                                </tbody>
                                            </table>
                                            <?php
                                            } if ($tab=="groups") {
                                            ?>
                                            <div class="col-md-12">
                                                <a href="javascript:;"
                                                    class="ls-modal btn btn-xs btn-warning pull-right"
                                                    onclick="showAddGroupForm('', 'Add');">
                                                    <span class="fa fa-plus">Add Group</span>
                                                </a>
                                            </div>
                                            <table class="table table-bordered table-condensed">
                                                <thead>
                                                    <tr>
                                                        <th>Group Name</th>
                                                        <th>Group Rights</th>
                                                        <th>Modules with access</th>
                                                        <th>Submodules with access</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        foreach ($allGroupsInfo as $groupId => $groupDetails) {
                                                        ?>
                                                    <tr>
                                                        <td><?php echo $groupDetails["group_name"]; ?></td>
                                                        <td>
                                                            <?php 
                                                                    $rightsStr = "";
                                                                    if (strpos($groupDetails["group_rights"], "1")!==false) {
                                                                        $rightsStr .= (empty($rightsStr))?"Read":"<br/>Read";
                                                                    }
                                                                    if (strpos($groupDetails["group_rights"], "2")!==false) {
                                                                        $rightsStr .= (empty($rightsStr))?"Write":"<br/>Write";
                                                                    }
                                                                    echo $rightsStr;
                                                                ?>
                                                        </td>
                                                        <td>
                                                            <ol>
                                                                <?php
                                                                    $moduleRightsArr = explode(",", $groupDetails["right_on_module"]);
                                                                    foreach ($moduleRightsArr as $num=>$moduleName) {
                                                                    ?>
                                                                <li><?php echo trim($moduleName, '"'); ?></li>
                                                                <?php
                                                                    }
                                                                    ?>
                                                            </ol>
                                                        </td>
                                                        <td>
                                                            <ol>
                                                                <?php
                                                                    $submoduleRightsArr = explode(",", $groupDetails["right_on_submodule"]);
                                                                    foreach ($submoduleRightsArr as $num=>$submoduleName) {
                                                                    ?>
                                                                <li><?php echo trim($submoduleName, '"'); ?></li>
                                                                <?php
                                                                    }
                                                                    ?>
                                                            </ol>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:;"
                                                                class="ls-modal btn btn-xs btn-success"
                                                                onclick="showAddGroupForm('<?php echo $groupId; ?>', 'Edit');">
                                                                <span class="fa fa-edit"></span>
                                                            </a>
                                                            <a class="btn btn-xs btn-danger"
                                                                onclick="confirmDeleteGroup('<?php echo $groupId; ?>');">
                                                                <span class="fa fa-close"></span>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                        }
                                                        ?>
                                                </tbody>
                                            </table>
                                            <?php
                                            }
                                            ?>
                                            <!-- end tables -->
                                            <!-- start pagination -->
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination">
                                                    <?php
                                                    if($page>1){
                                                    ?>
                                                    <li class="page-item"><a class="page-link"
                                                            href="?page=1&tab=<?php echo $tab; ?>">&laquo;</a></li>
                                                    <li class="page-item"><a class="page-link"
                                                            href="?page=<?php echo ($page-1); ?>&tab=<?php echo $tab; ?>">Prev</a>
                                                    </li>
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
                                                    <li class="page-item <?php echo $activeClass; ?>"><a
                                                            class="page-link"
                                                            href="?page=<?php echo $i; ?>&tab=<?php echo $tab; ?>"><?php echo $i; ?></a>
                                                    </li>
                                                    <?php
                                                    }
                                                    ?>
                                                    <?php
                                                    if($page<$lastPage){
                                                    ?>
                                                    <li class="page-item"><a class="page-link"
                                                            href="?page=<?php echo ($page+1); ?>&tab=<?php echo $tab; ?>">Next</a>
                                                    </li>
                                                    <li class="page-item"><a class="page-link"
                                                            href="?page=<?php echo ($lastPage); ?>&tab=<?php echo $tab; ?>">&raquo;</a>
                                                    </li>
                                                    <?php
                                                    }
                                                    ?>
                                                </ul>
                                            </nav>
                                            <!-- end pagination -->
                                        </div>
                                    </div>
                                    <!-- end tabs users and groups -->
                                </div> <!-- end card content -->
                            </div> <!-- end card -->
                        </div> <!-- end col md 12 -->
                    </div> <!-- end row -->
                </div> <!-- end container fluid -->
            </div> <!-- end content -->
        </div> <!-- end main panel -->
    </div> <!-- end wrapper -->
    <?php
    if ($tab=="users") {
    ?>
    <!-- delete user modal -->
    <div class="modal fade" id="deleteUserModal">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Delete User!</h4>
            </div>
            <div class="modal-body">
                <div class="alert" style="display: none">
                    <span></span>
                </div>
                <p>
                    Are you sure you want to delete this User?
                <p id="userEmailToDelete"></p>
                </p>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="deleteUserBtn" data-user-email=""
                        onclick="deleteUser(this);">Continue</button>
                </div>
            </div>
        </div>
    </div>
    <!-- end delete user modal -->
    <!-- add user modal -->
    <div class="modal fade" id="addUserModal">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><span></span> User</h4>
            </div>
            <div class="modal-body">
                <div class="alert" style="display: none">
                    <span></span>
                </div>
                <div class="modal-body-content"></div>
            </div>
        </div>
    </div>
    <!-- end add user modal -->
    <!-- user tab specific scripts -->
    <script>
    function confirmDeleteUser(userEmail) {
        $("#deleteUserModal .modal-body #userEmailToDelete").html(userEmail);
        $("#deleteUserModal .modal-footer #deleteUserBtn").data("user-email", userEmail);
        $("#deleteUserModal").modal();
    }

    function deleteUser(buttonElement) {
        let userEmail = $(buttonElement).data("user-email");
        //resetting the error message
        $("#deleteUserModal .alert").
        removeClass("alert-success").
        removeClass("alert-danger").
        fadeOut().
        find("span").html("");

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/user/delete/",
            data: {
                "userEmail": encodeURIComponent(userEmail)
            },
            success: function(user) {
                if (user["errCode"]) {
                    if (user["errCode"] != "-1") { //there is some error
                        $("#deleteUserModal .alert").
                        removeClass("alert-success").
                        addClass("alert-danger").
                        fadeIn().
                        find("span").
                        html(user["errMsg"]);
                    } else {
                        $("#deleteUserModal .alert").
                        removeClass("alert-danger").
                        addClass("alert-success").
                        fadeIn().
                        find("span").
                        html(user["errMsg"]);
                        setTimeout(function() {
                            window.location.reload();
                        }, 3000);
                    }
                }
            },
            error: function() {
                $("#deleteUserModal .alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html("500 internal server error");
            }
        });
    }

    function showAddUserForm(userEmail, actionType) {
        $("#addUserModal .modal-title span").html(actionType);
        $("#addUserModal .modal-body-content").load("user/?userEmail=" + encodeURIComponent(userEmail));
        $("#addUserModal").modal();
    }
    </script>
    <?php
    } else if ($tab=="groups") {
    ?>
    <!-- delete group modal -->
    <div class="modal fade" id="deleteGroupModal">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Delete Group!</h4>
            </div>
            <div class="modal-body">
                <div class="alert" style="display: none">
                    <span></span>
                </div>
                <p>
                    Are you sure you want to delete this group?
                </p>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="deleteGroupBtn" data-group-id=""
                        onclick="deleteGroup(this);">Continue</button>
                </div>
            </div>
        </div>
    </div>
    <!-- end delete group modal -->
    <!-- add group modal -->
    <div class="modal fade" id="addGroupModal">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><span></span> Group</h4>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
    <!-- end add group modal -->
    <!-- group tab specific scripts -->
    <script>
    function confirmDeleteGroup(groupId) {
        $("#deleteGroupModal .modal-body #groupIdToDelete").html(groupId);
        $("#deleteGroupModal .modal-footer #deleteGroupBtn").data("group-id", groupId);
        $("#deleteGroupModal").modal();
    }

    function deleteGroup(buttonElement) {
        let groupId = $(buttonElement).data("group-id");
        //resetting the error message
        $("#deleteGroupModal .alert").
        removeClass("alert-success").
        removeClass("alert-danger").
        fadeOut().
        find("span").html("");

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/group/delete/",
            data: {
                "groupId": encodeURIComponent(groupId)
            },
            success: function(group) {
                if (group["errCode"]) {
                    if (group["errCode"] != "-1") { //there is some error
                        $("#deleteGroupModal .alert").
                        removeClass("alert-success").
                        addClass("alert-danger").
                        fadeIn().
                        find("span").
                        html(group["errMsg"]);
                    } else {
                        $("#deleteGroupModal .alert").
                        removeClass("alert-danger").
                        addClass("alert-success").
                        fadeIn().
                        find("span").
                        html(group["errMsg"]);
                        setTimeout(function() {
                            window.location.reload();
                        }, 3000);
                    }
                }
            },
            error: function() {
                $("#deleteGroupModal .alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html("500 internal server error");
            }
        });
    }

    function showAddGroupForm(groupId, actionType) {
        $("#addGroupModal .modal-title span").html(actionType);
        $("#addGroupModal .modal-body").load("group/?groupId=" + encodeURIComponent(groupId));
        $("#addGroupModal").modal();
    }
    </script>
    <?php
    }
    ?>
    <?php
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