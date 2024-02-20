<?php
//Change Password page
session_start();

//prepare for request
//include necessary helpers
require_once('../../../config/config.php');
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/logs/logsProcessor.php');
require_once(__ROOT__.'/config/logs/logsCoreFunctions.php');

//include necessary models
require_once(__ROOT__.'/model/user/userModel.php');

//check if session is active
$sessionCheck = checkSession();

//get the user info
$email = $_SESSION['userEmail'];
//Connection With Database
$conn = createDbConnection($host, $db_username, $db_password, $dbName);
if (noError($conn)) {
    $conn = $conn["errMsg"];
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
                //user is found and is active. do nothing
            }
        }
    }
}
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
            $pageTitle = "Change Password";
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
                                            <a href="<?php echo $rootUrl."views/password/change"; ?>"><i class="fa fa-key">&nbsp;</i>Change Password</a>
                                        </li>
                                    </ol>
                                </div>
                                <!-- Change Password Form -->
                                <form method="post" enctype="multipart/form-data" id="changePasswordForm" name="changePasswordForm"
                                action="javascript:;" data-parsley-validate="">
                                    <div class="card-content">
                                        <!-- success/error messages -->
                                        <div class="alert" style="display: none">
                                            <span></span>
                                        </div>
                                        <!-- end success/error messages -->
                                        <!-- old password -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-black label-floating is-empty">
                                                    <label class="control-label">Old Password</label>
                                                    <input type="password" id="oldPassword" name="oldPassword" class="form-control"
                                                    required minlength="6">
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end old password -->
                                        <!-- new password -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-black label-floating is-empty">
                                                    <label class="control-label">New Password</label>
                                                    <input type="password" id="newPassword" name="password" class="form-control"
                                                    required data-parsley-trigger="keyup" data-parsley-confirmpassword="#confirmPassword" minlength="6" >
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end new password -->
                                        <!-- Confirm password -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-black label-floating is-empty">
                                                    <label class="control-label">Confirm Password</label>
                                                    <input type="password" id="confirmPassword" name="confirmPassword" class="form-control"
                                                    required data-parsley-trigger="keyup" data-parsley-confirmpassword="#newPassword"  minlength="6" >
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end Confirm password -->
                                        <!-- Save button -->
                                        <div class="row">
                                            <input type="submit" class="btn btn-danger" value="Save" />
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
        window.Parsley
            .addValidator('confirmpassword', {
            requirementType: 'string',
            validateString: function(value, requirement) {
                console.log("Checking: "+value+"--"+$(requirement).val());
                if(value == $(requirement).val()) {
                //remove both error messages
                $(".parsley-confirmpassword").remove();
                return true;
                } else {
                return false;
                }
            },
            messages: {
                en: 'Password and Confirm Password should be identical'
            }
        });
        //managing the floating labels behaviour
        $("form#changePasswordForm :input").each(function () {
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

    //handle form upload
    $('form#changePasswordForm').parsley().on('field:validated', function () {
        var ok = $('.parsley-error').length === 0;
        $('.bs-callout-info').toggleClass('hidden', !ok);
        $('.bs-callout-warning').toggleClass('hidden', ok);
    })
    .on('form:submit', function () {
        var formData = new FormData($('#changePasswordForm')[0]);
        formData.append("type", "changePassword");

        //resetting the error message
        $("#changePasswordForm .alert").
            removeClass("alert-success").
            removeClass("alert-danger").
            fadeOut().
            find("span").html("");

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/password/reset/",
            data: formData,
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function (user) {
                if (user["errCode"]) {
                    if (user["errCode"] != "-1") { //there is some error
                        $("#changePasswordForm .alert").
                            removeClass("alert-success").
                            addClass("alert-danger").
                            fadeIn().
                            find("span").
                            html(user["errMsg"]);
                    } else {
                        $("#changePasswordForm .alert").
                            removeClass("alert-danger").
                            addClass("alert-success").
                            fadeIn().
                            find("span").
                            html(user["errMsg"]+"<br/>You will be automatically redirected to login in 5 seconds...");
                            setTimeout(function() {window.location.href="<?php echo $rootUrl; ?>views/login"}, 5000);
                    }
                }
            },
            error: function () {
                $("#changePasswordForm .alert").
                    removeClass("alert-success").
                    addClass("alert-danger").
                    fadeIn().
                    find("span").
                    html("500 internal server error");
            }
        });
    });
</script>
</html>