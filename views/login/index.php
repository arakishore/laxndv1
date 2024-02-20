<?php
  //Login view page
  session_start();
  require_once('../../config/config.php');

  //The users id session parameter is being used by the reset password screen
  //once the user reaches the reset password screen and in case the user clicks the login link, we need to 
  //unset this parameter, else login page will think a user is logged in
  if (isset($_SESSION['users_id'])) {
    unset($_SESSION['users_id']);
  }
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo APPNAME; ?> Login</title>
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
  <?php
  //if user is currently logged in, then show the go to dashboard link
  if ( isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ) {
?>
  <div class="jumbotron text-center">
    <p>You seem to be logged in. <a href="<?php echo $rootUrl; ?>views/dashboard/">Go to dashboard&rarr; </a></p>
  </div>
  <?php
  } else {
?>
  <div class="container">
    <div class="innerContainer">
      <div class="col-md-12 text-center">
        <a href="<?php echo $rootUrl; ?>">
          <img src="<?php echo $rootUrl; ?>assets/img/nirvana_logo.jpg">
        </a>
      </div>
      <div class="col-md-12">
        <form id="loginForm" name="loginForm" data-parsley-validate="">
          <div class="alert" style="display: none">
            <span></span>
          </div>
          <div class="form-group">
            <label for="username">Username</label>
            <input type="email" id="username" name="username" class="input" placeholder="Username" data-parsley-trigger="keyup"
              required="" />
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Password" class="input"
              data-parsley-trigger="keyup" required="" />
          </div>
          <div class="form-group">
            <input type="submit" class="btn btn-default" value="Login">
          </div>
        </form>
        <div class="col-md-12">
          <a href="#" data-toggle="modal" data-target="#forgotPasswordModal">Forgot Password?</a>
        </div>
      </div>
    </div><!-- innercontainer end -->
  </div><!-- container end -->
  <!-- forgot password modal dialog-->
  <div id="forgotPasswordModal" class="modal col-md-12">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Forgot password</h3>
      </div>
      <div class="modal-body">
        <form id="forgotpassForm" name="forgotpassForm" action="javascript:;" data-parsley-validate="">
          <div class="alert" style="display: none">
            <span></span>
          </div>
          <div class="form-group">
            <label for="forgotPwdEmail">Please enter your registered email address:</label>
            <input type="email" id="forgotPwdEmail" name="email" class="input" placeholder="Email" data-parsley-trigger="keyup"
              required="" />
          </div>
          <div class="form-group">
            <input type="submit" class="btn btn-default" value="Submit">
          </div>
        </form>
      </div>
    </div>
  </div><!-- end forgot password modal -->
  <?php
  //include the loader
  require_once(__ROOT__."/views/common/loader.php");
  ?>
  <?php 
  } //close else for if user is logged in
?>
  <script type="text/javascript">
    $('form#loginForm').parsley().on('field:validated', function () {
        var ok = $('.parsley-error').length === 0;
        $('.bs-callout-info').toggleClass('hidden', !ok);
        $('.bs-callout-warning').toggleClass('hidden', ok);
      })
      .on('form:submit', function () {

        var formData = new FormData($('#loginForm')[0]);
        //resetting the error message
        $("#loginForm .alert").
          removeClass("alert-success").
          removeClass("alert-danger").
          fadeOut().
          find("span").html("");

        $.ajax({
          type: "POST",
          dataType: "json",
          url: "<?php echo $rootUrl; ?>controller/user/login/",
          data: formData,
          contentType: false,
          cache: false,
          contentType: false,
          processData: false,

          success: function (data) {
            console.log(data);
            if (data["errCode"]) {
              if (data["errCode"] != "-1") { //there is some error
                $("#loginForm .alert").
                  removeClass("alert-success").
                  addClass("alert-danger").
                  fadeIn().
                  find("span").
                  html(data["errMsg"]);
              } else {
                $("#loginForm .alert").
                  removeClass("alert-danger").
                  addClass("alert-success").
                  fadeIn().
                  find("span").
                  html(data["errMsg"]);
                window.location.href=data.url;
              }
            }
          },
          error: function () {
            $("#loginForm .alert").
              removeClass("alert-success").
              addClass("alert-danger").
              fadeIn().
              find("span").
              html("500 internal server error");
          }
        });
        return false;
      });
    
    //Forgot password form svalidation and submit handler
    $('form#forgotpassForm').parsley().on('field:validated', function () {
      var ok = $('.parsley-error').length === 0;
      $('.bs-callout-info').toggleClass('hidden', !ok);
      $('.bs-callout-warning').toggleClass('hidden', ok);
    }).
    on('form:submit', function () {
      var formData = new FormData($('#forgotpassForm')[0]);

      //resetting the error message
      $("#forgotpassForm .alert").
        removeClass("alert-success").
        removeClass("alert-danger").
        fadeOut().
        find("span").html("");

      $.ajax({
        type: "POST",
        dataType: "json",
        url: "<?php echo $rootUrl; ?>controller/password/forgot/",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        success: function (user) {
          if (user["errCode"]) {
            if (user["errCode"] != "-1") { //there is some error
              $("#forgotpassForm .alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html(user["errMsg"]);
            } else {
              $("#forgotpassForm .alert").
                removeClass("alert-danger").
                addClass("alert-success").
                fadeIn().
                find("span").
                html(user["errMsg"]);
            }
          }
        },
        error: function () {
          $("#forgotpassForm .alert").
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