<?php
//Profile view+edit page
session_start();

//prepare for request
//include necessary helpers
require_once('../../config/config.php');

//check if session is active
$sessionCheck = checkSession();

//include some mroe necessary helpers
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/logs/logsProcessor.php');
require_once(__ROOT__.'/config/logs/logsCoreFunctions.php');

//include necessary models
require_once(__ROOT__.'/model/user/userModel.php');

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
    $logFilePath = $logStorePaths["profile"];
    $logFileName="viewProfile.json";

    $logMsg = "Profile process start.";
    $logData['step1']["data"] = "1. {$logMsg}";

    $logMsg = "Database connection successful.";
    $logData["step2"]["data"] = "2. {$logMsg}";

    $logMsg = "Attempting to get user info.";
    $logData["step3"]["data"] = "3. {$logMsg}";

    $userSearchArr = array('email'=>$email);
    $fieldsStr = "status, `groups`, rights, firstname, lastname, phone, email, department, designation, email, comments, image";
    $userInfo = getUserInfo($userSearchArr, $fieldsStr, $conn);
    //print_r($userInfo);
    $right_access = 0;
    $right_access_readonly = "readonly";
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
        $rights = $userInfo[$email]["rights"];
        $rights_expl = explode(",", $rights);
       // print_r($rights_expl);
        if(in_array("2",$rights_expl)){
            $right_access = 1;
            $right_access_readonly = "";
        }
        if (empty($userInfo)) {
            //user not found
            $logMsg = "User not found: {$token}";
            $logData["step3.1"]["data"] = "3.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5).": User not found.";
            //TO DO: Display these errors somewhere on the screen
        } else {
            //check if user is active
            if ($userInfo[$email]["status"]!=1) {
                //user not active
                $logMsg = "User not active: {$email}";
                $logData["step3.1"]["data"] = "3.1 {$logMsg}";
                $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
                
                $returnArr["errCode"] = 5;
                $returnArr["errMsg"] = getErrMsg(5).": User not active.";
            } else {
                //user is found and is active use this data in the form
                $logMsg = "User info fetched successfully.";
                $logData["step4"]["data"] = "4. {$logMsg}";
                $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
            } //close checking if user is active
        } //close checking if user not found
    } //close no error userinfo else
} //connection to DB
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta charset="utf-8" />
    <link rel="icon" type="image/png" href="<?php echo $rootUrl; ?>assets/img/nirvana_favicon.png" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title><?php echo APPNAME; ?></title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />
    <link href="<?php echo $rootUrl; ?>assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo $rootUrl; ?>assets/css/material-dashboard.css?v=1.2.1" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Work+Sans:400,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/style.css">
    <script src="<?php echo $rootUrl; ?>assets/js/jquery.min.js" type="text/javascript"></script>
</head>

<body>
    <div class="wrapper">
        <?php 
            $pageTitle = "Profile";
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
                                            <a href="<?php echo $rootUrl."views/dashboard.php"; ?>"><i class="fa fa-dashboard">&nbsp;</i>Dashboard</a>
                                        </li>
                                        <li class="breadcrumb-item">
                                            <a href="<?php echo $rootUrl."views/profile/"; ?>"><i class="fa fa-user">&nbsp;</i>Profile</a>
                                        </li>
                                    </ol>
                                </div>
                                <!-- Edit profile Form -->
                                <form method="post" enctype="multipart/form-data" id="updateProfileForm" name="updateProfileForm"
                                action="javascript:;" data-parsley-validate="">
                                    <div class="card-content">
                                        <!-- success/error messages -->
                                        <div class="alert" style="display: none">
                                            <span></span>
                                        </div>
                                        <!-- end success/error messages -->
                                        <!-- fname, lname -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group label-floating is-empty">
                                                    <label class="control-label">First Name</label>
                                                    <input type="text" <?php echo $right_access_readonly?> class="form-control"
                                                        data-parsley-trigger="keyup" required="" id="firstName" name="firstName" 
                                                        value="<?php echo (isset($userInfo[$email]["firstname"]))?
                                                        $userInfo[$email]["firstname"]:"";?>"
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group label-floating is-empty">
                                                    <label class="control-label">Last Name</label>
                                                    <input type="text" <?php echo $right_access_readonly?> class="form-control" id="lastName" name="lastName"
                                                        data-parsley-trigger="keyup" required="" 
                                                        value="<?php echo (isset($userInfo[$email]["lastname"]))?
                                                        $userInfo[$email]["lastname"]:"";?>"
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end fname, lname -->
                                        <!-- mob no, email -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group label-floating is-empty">
                                                    <label class="control-label">Mobile Number</label>
                                                    <input type="text" <?php echo $right_access_readonly?> class="form-control" id="phone" name="phone" 
                                                        value="<?php echo (isset($userInfo[$email]["phone"]))?
                                                        $userInfo[$email]["phone"]:"";?>"
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group label-floating is-empty">
                                                    <label class="control-label">Email Address</label>
                                                    <input type="text" <?php echo $right_access_readonly?> class="form-control" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$"
                                                        name="email" data-parsley-trigger="keyup" required=""
                                                        value="<?php echo $email;?>"
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end mob no, email -->
                                        <!-- dept, designation -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group label-floating is-empty">
                                                    <label class="control-label">Designation</label>
                                                    <input type="text" <?php echo $right_access_readonly?> class="form-control" id="designation"
                                                        name="designation" 
                                                        value="<?php echo (isset($userInfo[$email]["designation"]))?
                                                        $userInfo[$email]["designation"]:"";?>"
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group label-floating is-empty">
                                                    <label class="control-label">Department</label>
                                                    <input type="text" <?php echo $right_access_readonly?> class="form-control" id="department"
                                                        name="department" 
                                                        value="<?php echo (isset($userInfo[$email]["department"]))?
                                                        $userInfo[$email]["department"]:"";?>"
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end dept, designation -->
                                        <!-- comment -->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <div class="form-group label-floating is-empty">
                                                        <label class="control-label"> Comments</label>
                                                        <textarea class="form-control" rows="3" id="comments" name="comments" 
                                                        placeholder="Please enter comments here"><?php
                                                            if(isset($userInfo[$email]["comments"])){
                                                                echo trim($userInfo[$email]["comments"]);
                                                            }
                                                        ?>
                                                        </textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end comment -->
                                        <!-- profile pic style="background: #c5392a;color: #fff; border-radius: 4px;padding: 1.3rem 1.6rem;font-size: 1em;float: left;"
                                        -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="file-loading">
                                                <?php
                                            if($right_access==1){
                                            ?>
                                                    <label class="control-label">
                                                        Upload Profile Pic
                                                        <input id="profilePic" name="profilePic" type="file" 
                                                            value="<?php echo (isset($userInfo[$email]["image"]))?
                                                            $userInfo[$email]["image"]:"";?>" 
                                                            accept='image/*'
                                                        >
                                                    </label>
                                                    <?php
                                                    }
                                                    ?>
                                                    <?php
                                                    if ( isset($userInfo[$email]["image"]) && !empty($userInfo[$email]["image"]) ) {                                                            
                                                    ?>
                                                        <img id="userDP"
                                                            src="<?php echo $rootUrl."assets/img/profile_images/".$userInfo[$email]["image"]; ?>"
                                                        >
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end profile pic -->
                                        <!-- Save button -->
                                        <div class="row text-center">
                                            <?php
                                            if($right_access==1){
                                            ?>
                                            <input type="submit" class="btn btn-danger" value="Save" />
                                            <?php }?>
                                        </div>
                                        <!-- end Save button -->
                                    </div> <!-- end card-content -->
                                </form>
                            </div><!-- end card -->
                        </div><!-- end col md 12 -->
                    </div><!-- end row -->
                </div><!-- end container fluid -->
            </div><!-- end content -->
        </div><!-- end main panel -->
    </div><!-- end wrapper -->
    <?php
    //include the loader
    require_once(__ROOT__."/views/common/loader.php");
    ?>
</body>
<!--   Core JS Files   -->
<script src="<?php echo $rootUrl; ?>assets/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo $rootUrl; ?>assets/js/material.min.js" type="text/javascript"></script>
<script src="<?php echo $rootUrl; ?>assets/js/perfect-scrollbar.jquery.min.js"></script>
<script src="<?php echo $rootUrl; ?>assets/js/material-dashboard.js"></script>
<script src="<?php echo $rootUrl; ?>assets/js/parsley.js"></script>

<script type="text/javascript">
    $(window).on("load", function(e){
        //managing the floating labels behaviour
        $("form#updateProfileForm :input").each(function () {
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
    <?php
        if($right_access==1){
        ?>
    //handle form upload
    $('form#updateProfileForm').parsley().on('field:validated', function () {
        var ok = $('.parsley-error').length === 0;
        $('.bs-callout-info').toggleClass('hidden', !ok);
        $('.bs-callout-warning').toggleClass('hidden', ok);
    })
    .on('form:submit', function () {
        var formData = new FormData($('#updateProfileForm')[0]);
        formData.append("type", "editprofile");

        //resetting the error message
        $("#updateProfileForm .alert").
        removeClass("alert-success").
        removeClass("alert-danger").
        fadeOut().
        find("span").html("");

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/user/profile/",
            data: formData,
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function (user) {
                if (user["errCode"]) {
                    if (user["errCode"] != "-1") { //there is some error
                        $("#updateProfileForm .alert").
                            removeClass("alert-success").
                            addClass("alert-danger").
                            fadeIn().
                            find("span").
                            html(user["errMsg"]);
                    } else {
                        $("#updateProfileForm .alert").
                            removeClass("alert-danger").
                            addClass("alert-success").
                            fadeIn().
                            find("span").
                            html(user["errMsg"]);
                        window.location.reload();
                    }
                }
            },
            error: function () {
                $("#updateProfileForm .alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html("500 internal server error");
            }
        });
    });
    <?php }?>
</script>
</html>