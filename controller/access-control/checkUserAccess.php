<?php
//TO DO: LOGS
//check access rights that this user has by combining groups with maximum permission possible
require_once(__ROOT__.'/model/access-control/accessControlModel.php');

//validating necessary requirements
/*
This page requires that:
1. There be an $email variable which has the current logged in user's email
2. There be a $userInfo variable which has the "groups" and "rights" array indices nested inside the $email index
3. requires a DB conn in the variable $conn

if any of these 3 are not found, the page will print an error and exit
*/
if (empty($email)) {
    printArr("Email not set for access control");
    exit;
}

if (!isset($userInfo[$email]["groups"]) || empty($userInfo[$email]["groups"])) {
    printArr("User belongs to no groups. <a href='".$rootUrl."/controller/logout.php'>Logout</a>");
    exit;
}

if (!isset($userInfo[$email]["rights"]) || empty($userInfo[$email]["rights"])) {
    printArr("User rights not set. <a href='".$rootUrl."/controller/logout.php'>Logout</a>");
    exit;
}

if (empty($conn)) {
    printArr("Cannot connect to database");
    exit;
}

//get all the details about all the groups that this user belongs to
$userInfo[$email]["groups"] = "'".str_replace(',', "','", $userInfo[$email]["groups"])."'";
$groupsInfo = getGroupInfoByGroupNames("*", $userInfo[$email]["groups"], $conn);
if (!noError($groupsInfo)) {
    printArr("Error fetching groups info: ".$groupsInfo["errMsg"]);
    exit;
}

/*
loop through all the groups that user has access to and create arrays for module and submodule.
For each module and submodule, settle on the highest right possible.
output is 2 arrays - modulesWithAccess and subModulesWithAccess
These 2 arrays are used in sidebar inorder to display the right menu items. 
Based on the active menu item, sidebar will assign the highest permission that the user has on the current page that he is viewing.
Based on that highest perm, we need to show/hide certain actions
*/
$groupsInfo = $groupsInfo["errMsg"];
$modulesWithAccess = $subModulesWithAccess = array();
foreach ($groupsInfo as $groupId=>$groupDetails) {
    //create modules array
    $groupModules = str_replace("\"", "", $groupDetails["right_on_module"]);
    $groupModules = explode(",", $groupModules);
    $maxGroupRight = max(explode(",", $groupDetails["group_rights"]));
    foreach ($groupModules as $num=>$moduleName) {
        if (isset($modulesWithAccess[$moduleName])) {
            //it was already in the modules array, assign the maximum permission
            $modulesWithAccess[$moduleName] = max($maxGroupRight, $modulesWithAccess[$moduleName]);
        } else {
            $modulesWithAccess[$moduleName] = $maxGroupRight;
        }
    }

    //create modules array
    $groupSubModules = str_replace("\"", "", $groupDetails["right_on_submodule"]);
    $groupSubModules = explode(",", $groupSubModules);
    foreach ($groupSubModules as $num=>$subModuleName) {
        if (isset($subModulesWithAccess[$subModuleName])) {
            //it was already in the modules array, assign the maximum permission
            $subModulesWithAccess[$subModuleName] = max($maxGroupRight, $subModulesWithAccess[$subModuleName]);
        } else {
            $subModulesWithAccess[$subModuleName] = $maxGroupRight;
        }
    }
}
?>