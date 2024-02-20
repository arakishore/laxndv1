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

  //accept, sanitize and validate inputs 
  //need token first to create logs
  $token = "";
  if (!(isset($_GET["token"]) && !empty($_GET["token"]))) {
    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = getErrMsg(4).": Token cannot be empty";
  } else {
    $token = cleanQueryParameter($conn, cleanXSS($_GET["token"]));

    //initialize logs
    $logsProcessor = new logsProcessor();
    $initLogs = initializeJsonLogs($token);
    $logFilePath = $logStorePaths["forgotPassword"];
    $logFileName="resetPassword.json";

    $logMsg = "Reset password process start.";
    $logData['step1']["data"] = "1. {$logMsg}";

    $logMsg = "Database connection successful.";
    $logData["step2"]["data"] = "2. {$logMsg}";

    $logMsg = "Attempting to get user info.";
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

        $returnArr["errCode"] = 4;
        $returnArr["errMsg"] = getErrMsg(4).": This URL is invalid or expired.<p>".
                              "<a href='".$rootUrl."views/login'>Try logging in again &rarr;</a></p>";
    } else {
      //check if user not found
      $userInfo = $userInfo["errMsg"];
      if (empty($userInfo)) {
        //user not found
        $logMsg = "User not found: {$token}";
        $logData["step3.1"]["data"] = "3.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $returnArr["errCode"] = 4;
        $returnArr["errMsg"] = getErrMsg(4).": This URL is invalid or expired.<p>".
                            "<a href='".$rootUrl."views/login'>Try logging in again &rarr;</a></p>";
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

          $returnArr["errCode"] = 4;
          $returnArr["errMsg"] = getErrMsg(4).": This URL is invalid or expired.<p>".
                              "<a href='".$rootUrl."views/login'>Try logging in again &rarr;</a></p>";
        } else {
          //user is found and is active, lets remove the token and then show the reset password form
          $logMsg = "Email id exists and user is active. Need to remove the token and then show the reset password form";
          $logData["step4"]["data"] = "4. {$logMsg}";

          $arrToUpdate = array("token"=>"");
          $fieldSearchArr = array("email"=>$email);
          $updateUserInfo = updateUserInfo($arrToUpdate, $fieldSearchArr, $conn);
          if (!noError($updateUserInfo)) {
            //there was some error updating token
            $logMsg = "Error updating token: {$token}";
            $logData["step4.1"]["data"] = "4.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5).": Error processing token.";
          } else {
            //everything went well, set the return array and proceed to view where we need to display the reset password form
            //we will also start a session and add the user email in the session for use in the controller

            session_start();
            $_SESSION['users_id'] = $userInfo[$email]["user_id"];
            $_SESSION['firstname'] = $userInfo[$email]["firstname"];
            $_SESSION['lastname'] = $userInfo[$email]["lastname"];
            $_SESSION['userEmail'] = $email;

            $logMsg = "Success updating token: {$token}";
            $logData["step4.1"]["data"] = "4.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $returnArr["errCode"] = -1;
            $returnArr["errMsg"] = getErrMsg(-1).". Link is valid. Please reset your password.";
          } //update user info error
        } //check if user user is active
      } //check if user exists
    } //fetching user info
  } //check GET["token"]
} //connection to DB
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo APPNAME; ?> Reset Password</title>
  <script src="<?php echo $rootUrl; ?>assets/js/jquery.min.js"></script>
  <script src="<?php echo $rootUrl; ?>assets/js/bootstrap.min.js"></script>
  <link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/parsley.css">
  <link rel="icon" type="image/png" href="<?php echo $rootUrl; ?>assets/img/nirvana_favicon.png"/>
  <script src="<?php echo $rootUrl; ?>assets/js/parsley.js"></script>
  <link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/bootstrap.min.css">
  <link href='https://fonts.googleapis.com/css?family=Work+Sans:400,300,700' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/style.css">
</head>

<body>
  <div class="container">
    <div class="innerContainer">
      <div class="col-md-12 text-center">
        <a href="#">
          <img src="<?php echo $rootUrl; ?>assets/img/nirvana_logo.jpg">
        </a>
      </div>
      <div class="col-md-12">
        <?php
        if(!noError($returnArr)){
        ?>
          <div class="alert alert-danger">
            <span>
              <?php
                echo $returnArr["errMsg"];
                ?>
            </span>
          </div>
        <?php
        } else {
        ?>
          <form id="resetPassword" name="resetPassword" data-parsley-validate="">
            <div class="alert">
              <span></span>
            </div>
            <div class="form-group">
              <label for="password">Password</label>
              <input type="password" name="password" id="password" placeholder="Password" class="input"
                data-parsley-trigger="keyup" data-parsley-confirmpassword="#confirmPassword" required="" minlength="6" />
            </div>
            <div class="form-group">
              <label for="confirmPassword">Confirm Password</label>
              <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" class="input"
                data-parsley-trigger="keyup" required="" data-parsley-confirmpassword="#password" minlength="6" />
            </div>
            <div class="form-group">
              <input type="submit" class="btn btn-default" value="Reset Password">
            </div>
          </form>
        <?php          
        }
        ?>
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
      validateString: function(value, requirement) {
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
                  setTimeout(function() {window.location.href="<?php echo $rootUrl; ?>views/login"}, 2000);

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