<?php
//Reset Password view page

//prepare for request
//include necessary helpers
require_once('../../../config/config.php');
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/auth.php');
require_once(__ROOT__.'/config/logs/logsProcessor.php');
require_once(__ROOT__.'/config/logs/logsCoreFunctions.php');
require_once(__ROOT__.'/libphp-phpmailer/autoload.php');

//include necessary models
require_once(__ROOT__.'/model/user/userModel.php');

$returnArr = array();
$returnArr["errClass"] = "alert-success";

//Connection With Database
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1).$conn["errMsg"];
} else {
    $conn = $conn["errMsg"];

    //accept, sanitize and validate inputs 
    //need token first to create logs
    $token = "";
    if (!(isset($_GET["token"]) && !empty($_GET["token"]))) {
        $returnArr["errCode"] = 4;
        $returnArr["errMsg"] = getErrMsg(4)." Token cannot be empty";
    } else {
        $token = cleanQueryParameter($conn, cleanXSS($_GET["token"]));

        //initialize logs
        $logsProcessor = new logsProcessor();
        $initLogs = initializeJsonLogs($token);
        $logFilePath = $logStorePaths["accessControl"];
        $logFileName="activateUser.json";

        $logMsg = "User activation process start.";
        $logData['step1']["data"] = "1. {$logMsg}";

        $logMsg = "Database connection successful. Attempting to start transaction.";
        $logData["step2"]["data"] = "2. {$logMsg}";

        $startTransaction = startTransaction($conn);
        if (!noError($startTransaction)) {
            //error starting transaction
            $logMsg = "Couldn't start transaction: {$startTransaction["errMsg"]}";
            $logData["step2.1"]["data"] = "2.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5)." Could not start transaction.";
        } else {
            $logMsg = "Transaction started. Attempting to get user info.";
            $logData["step3"]["data"] = "3. {$logMsg}";

            //get the user info
            $userSearchArr = array('token'=>$token);
            $fieldsStr = "email, status, token, user_id, firstname, lastname";
            $userInfo = getUserInfo($userSearchArr, $fieldsStr, $conn);
            if (!noError($userInfo)) {
                //error fetching user info
                $logMsg = "Couldn't fetch user info: {$userInfo["errMsg"]}";
                $logData["step3.1"]["data"] = "3.1. {$logMsg}";
                $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                rollbackTransaction($conn);

                $returnArr["errCode"] = 5;
                $returnArr["errMsg"] = getErrMsg(5)." This URL is invalid or expired.";
            } else {
                //check if user not found
                $userInfo = $userInfo["errMsg"];
                if (empty($userInfo)) {
                    //user not found
                    $logMsg = "User not found: {$token}";
                    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
                    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                    rollbackTransaction($conn);

                    $returnArr["errCode"] = 5;
                    $returnArr["errMsg"] = getErrMsg(5)." This URL is invalid or expired.";
                } else {
                    //check if user is active
                    //first get the user email
                    $email = array_keys($userInfo);
                    $email = $email[0];
                    if ($userInfo[$email]["status"]!=0) {
                        //user active
                        $logMsg = "User already active: {$token}";
                        $logData["step3.1"]["data"] = "3.1 {$logMsg}";
                        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                        rollbackTransaction($conn);

                        $returnArr["errCode"] = 5;
                        $returnArr["errMsg"] = getErrMsg(5)." This URL is invalid or expired.";
                    } else {
                        //user is found and is inactive, lets remove the token and then show the reset password form
                        $logMsg = "Email id exists and user is not active. Need to remove the token and update passwords";
                        $logData["step4"]["data"] = "4. {$logMsg}";

                        $salt = generateSalt();
                        $password = generatePassword(6, false, "d");
                        $encryptedPassword = encryptPassword($password, $salt);
                        
                        $arrToUpdate = array(
                            "token" => "", 
                            "salt" => $salt,
                            "password" => $encryptedPassword,
                            "status" => 1
                        );
                        $fieldSearchArr = array("email"=>$email);
                        $updateUserInfo = updateUserInfo($arrToUpdate, $fieldSearchArr, $conn);
                        if (!noError($updateUserInfo)) {
                            //there was some error updating token
                            $logMsg = "Error updating token: {$token}";
                            $logData["step4.1"]["data"] = "4.1. {$logMsg}";
                            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                            rollbackTransaction($conn);

                            $returnArr["errCode"] = 5;
                            $returnArr["errMsg"] = getErrMsg(5)."Error processing token.";
                        } else {
                            //everything went well, send email then set the return array and proceed to display message
                            $logMsg = "Success activating user: {$token}";
                            $logData["step4.1"]["data"] = "4.1. {$logMsg}";

                            $to = $email;
                            $subject = APPNAME.' - Investor Portal Account Activated.';
                            $loginUrl = $rootUrl.'views/login/';

                            $userName = $userInfo[$email]["firstname"]." ".$userInfo[$email]["lastname"];
                            $emailBody = createAccountActivationEmail($userName, $loginUrl, $password);
                            $sendMail = sendMail($to, $subject, $emailBody);

                            if (!noError($sendMail)) {
                                //error sending email
                                $logMsg = "Error sending email: {$sendMail["errMsg"]}";
                                $logData["step6.1"]["data"] = "6.1. {$logMsg}";
                                $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                                rollbackTransaction($conn);

                                $rollback = rollbackTransaction($conn);

                                $returnArr["errCode"] = 5;
                                $returnArr["errMsg"] = getErrMsg(5).
                                    " Error sending email with activation.".
                                    " Please use the forgot password option on the <a href='".$rootUrl."views/login/'>Login</a> screen";
                                echo(json_encode($returnArr));
                                exit;
                            } 

                            //Everything completed successfully
                            $logMsg = "Email sent successfully";
                            $logData["step7"]["data"] = "7. {$logMsg}";
                            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

                            $returnArr["errCode"] = -1;
                            $returnArr["errMsg"] = getErrMsg(-1).
                                "Token valid. An auto generated password has been emailed to you. Please use that to ".
                                "<a href='".$rootUrl."views/login/'>Login</a>";
                            $returnArr["errClass"] = "alert-success";

                            commitTransaction($conn);
                        } //update user info error
                    } //check if user user is active
                } //check if user exists
            } //fetching user info
        }
    } //check GET["token"]
} //connection to DB
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?php echo APPNAME; ?> User Activation</title>
    <script src="<?php echo $rootUrl; ?>assets/js/jquery.min.js"></script>
    <script src="<?php echo $rootUrl; ?>assets/js/bootstrap.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/parsley.css">
    <link rel="icon" type="image/png" href="<?php echo $rootUrl; ?>assets/img/nirvana_favicon.png" />
    <script src="<?php echo $rootUrl; ?>assets/js/parsley.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/bootstrap.min.css">
    <!--Google Font - Work Sans-->
    <link href='https://fonts.googleapis.com/css?family=Work+Sans:400,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/style.css">
</head>

<body>
    <div class="container">
        <div class="innerContainer">
            <div class="col-md-12 text-center">
                <a href="#">
                    <img src="<?php echo $rootUrl; ?>assets/img/nirvana_favicon.jpg">
                </a>
            </div>
            <div class="col-md-12">
                <div class="alert <?php echo $returnArr["errClass"]; ?>">
                    <span>
                        <?php
                        echo $returnArr["errMsg"];
                    ?>
                    </span>
                </div>
            </div>
        </div><!-- innercontainer end -->
    </div><!-- container end -->
    <?php
    //include the loader
    require_once(__ROOT__."/views/common/loader.php");
    ?>
    <script type="text/javascript">
        window.Parsley
            .addValidator('confirmpassword', {
                requirementType: 'string',
                validateString: function (value, requirement) {
                    console.log("Checking: " + value + "--" + $(requirement).val());
                    if (value == $(requirement).val()) {
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
        $('#confirm_password').on('change', function () {
            if ($('#password').val() != $('#confirm_password').val()) {
                $('#message').html('Passwords do not match.').css('color', 'red');
            }
        });

        $('form#resetPassword').parsley().on('field:validated', function () {
                var ok = $('.parsley-error').length === 0;
                $('.bs-callout-info').toggleClass('hidden', !ok);
                $('.bs-callout-warning').toggleClass('hidden', ok);
            })
            .on('form:submit', function () {
                var formData = new FormData($('#resetPassword')[0]);

                //resetting the error message
                $("#resetPassword .alert").
                removeClass("alert-success").
                removeClass("alert-danger").
                fadeOut().
                find("span").html("");

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "../../../controller/password/reset/",
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (user) {
                        if (user["errCode"]) {
                            if (user["errCode"] != "-1") { //there is some error
                                $("#resetPassword .alert").
                                removeClass("alert-success").
                                addClass("alert-danger").
                                fadeIn().
                                find("span").
                                html(user["errMsg"]);
                            } else {
                                $("#resetPassword .alert").
                                removeClass("alert-danger").
                                addClass("alert-success").
                                fadeIn().
                                find("span").
                                html(user["errMsg"]);
                            }
                        }
                    },
                    error: function () {
                        $("#resetPassword .alert").
                        removeClass("alert-success").
                        addClass("alert-danger").
                        fadeIn().
                        find("span").
                        html("500 internal server error");
                    }
                });
                return false;
            });
    </script>
</body>