<?php
//validating necessary requirements
/*
This page requires that:
1. There be an $email variable which has the current logged in user's email
2. There be a $userInfo variable which has the "firstname" and "lastname" array indices nested inside the $email index

if any of these 2 are not found, the page will print an error and exit
*/
if (empty($email)) {
    printArr("Email not set for header");
    exit;
}

if (!isset($userInfo[$email]["firstname"]) || empty($userInfo[$email]["firstname"])) {
    printArr("Firstname not set for header");
    exit;
}

if (!isset($userInfo[$email]["lastname"]) || empty($userInfo[$email]["lastname"])) {
    printArr("Lastname not set for header");
    exit;
}
?>


<nav class="navbar navbar-transparent navbar-absolute">
    <div class="container-fluid" >
  
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            
            <a class="navbar-brand" href="#">
                <?php echo isset($pageTitle)?cleanXSS($pageTitle):""; ?>
            </a>
            
        </div>
        <div class="navbar-collapse">
            <ul class="nav navbar-nav navbar-right">

            
                <li>
                
                    <a href="#pablo" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-user"></i>
                        <span>
                            <?php echo cleanXSS($userInfo[$email]["firstname"])." ".cleanXSS($userInfo[$email]["lastname"]); ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header">
                            <?php
                            if (isset($userInfo[$email]["image"]) && !empty($userInfo[$email]["image"])) {
                                $userImageUrl = $rootUrl."assets/img/profile_images/".$userInfo[$email]["image"];
                            ?>
                                <img id="userDP" src="<?php echo $userImageUrl; ?>">
                            <?php
                            } else {
                            ?>
                                <p>No image uploaded</p>
                            <?php
                            }
                            ?>
                            <p class="align-center">
                                <?php echo $userInfo[$email]["firstname"]." ".$userInfo[$email]["lastname"]; ?>
                            </p>
                        </li>
                        <li class="user-footer">
                            <a href="<?php echo $rootUrl; ?>views/profile/" class="btn btn-flat">Profile</a>
                            <a href="<?php echo $rootUrl; ?>views/password/change/" class="btn btn-flat">Change Password</a>
                            <a href="<?php echo $rootUrl; ?>controller/logout.php" class="btn btn-danger btn-flat">
                                <i class="fa fa-power-off">LOGOUT</i>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
