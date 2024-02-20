<?php
//Add group view page
session_start();

//prepare for request
//include necessary helpers
require_once('../../../config/config.php');

//check if session is active
$sessionCheck = checkSession();

//include some more necessary helpers
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/logs/logsProcessor.php');
require_once(__ROOT__.'/config/logs/logsCoreFunctions.php');

//include necessary models
require_once(__ROOT__.'/model/access-control/accessControlModel.php');
require_once(__ROOT__.'/model/user/userModel.php');

$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (noError($conn)) {
    $conn = $conn["errMsg"];
    $returnArr = array();
    
    //TO DO: Logs here
    
    $userEmail = null;
    if ( isset($_GET["userEmail"]) && !empty($_GET["userEmail"]) ) {
        $userEmail = cleanQueryParameter($conn, cleanXSS($_GET["userEmail"]));
    }

    $firstName = "";
    $lastName = "";
    $designation = "";
    $department = "";
    $phone = "";
    $comments = "";
    $readAccess = "";
    $writeAccess = "";
    $userGroups = "";
    $status = "";
    
    if (!is_null($userEmail)) {
        $userSearchArr = array('email'=>$userEmail);
        $fieldsStr = "firstname, lastname, designation, department, user_id, email, phone, comments, rights, `groups`,status";
        $userInfo = getUserInfo($userSearchArr, $fieldsStr, $conn);
        if (!noError($userInfo)) {
            //error fetching all groups info
            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5)." Error fetching user details.";
            print($returnArr["errMsg"]);
            exit;
        } else {
            $userInfo = $userInfo["errMsg"];

            $status = $userInfo[$userEmail]["status"];

            $firstName = $userInfo[$userEmail]["firstname"];
            $lastName = $userInfo[$userEmail]["lastname"];
            $designation = $userInfo[$userEmail]["designation"];
            $department = $userInfo[$userEmail]["department"];
            $phone = $userInfo[$userEmail]["phone"];
            $comments = $userInfo[$userEmail]["comments"];
            if (strpos($userInfo[$userEmail]["rights"], "1")!==false) {
                $readAccess = 1;
            }
            if (strpos($userInfo[$userEmail]["rights"], "2")!==false) {
                $writeAccess = 1;
            }
            $userGroups = $userInfo[$userEmail]["groups"];
            $returnArr["errCode"] = -1;
        }
    }

    //attempting to get all groups
    $groupSearchArr = null;
    $fieldsStr = "group_name, group_id";
    $allGroups = getGroupInfo($groupSearchArr, $fieldsStr, $conn);
    if (!noError($allGroups)) {
        //error fetching all groups info
        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error fetching group details.";
        print($returnArr["errMsg"]);
        exit;
    } else {
        $allGroups = $allGroups["errMsg"];
        $returnArr["errCode"] = -1;
    }
?>
<style>
fieldset.scheduler-border {
    border: 1px groove #ddd !important;
    padding: 0 1.4em 1.4em 1.4em !important;
    margin: 0 0 1.5em 0 !important;
    -webkit-box-shadow: 0px 0px 0px 0px #000;
    box-shadow: 0px 0px 0px 0px #000;
}

legend {

    width: AUTO;
    padding: 0px 10px 0px 10px;

}
</style>
<div class="row">
    <form id="addEditUserForm" name="addEditUserForm" action="javascript:;" data-parsley-validate="">
        <!-- success/error messages -->
        <?php
            $alertMsg = "";
            $alertClass = "";            
            if (!noError($returnArr)) {
                $alertClass = "alert-danger";
                $alertMsg = $returnArr["errMsg"];
            }
            ?>
        <div class="alert <?php echo $alertClass; ?>" style="display: none">
            <span>
                <?php echo $alertMsg; ?>
            </span>
        </div>
        <!-- end success/error messages -->
        <!-- hidden userEmail field -->
        <input type="hidden" name="userEmail" value="<?php echo $userEmail; ?>">
        <!-- end hidden userEmail field -->
        <!-- First name -->
        <div class="col-md-6 form-group label-floating is-empty">
            <label class="control-label">First Name</label>
            <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="firstName"
                name="firstName" value="<?php echo $firstName; ?>">
        </div>
        <!-- End First name -->
        <!-- Last name -->
        <div class="col-md-6 form-group label-floating is-empty">
            <label class="control-label">Last Name</label>
            <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="lastName"
                name="lastName" value="<?php echo $lastName; ?>">
        </div>
        <!-- End Last name -->
        <!-- Designation -->
        <div class="col-md-6 form-group label-floating is-empty">
            <label class="control-label">Designation</label>
            <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="designation"
                name="designation" value="<?php echo $designation; ?>">
        </div>
        <!-- End Designation -->
        <!-- Department -->
        <div class="col-md-6 form-group label-floating is-empty">
            <label class="control-label">Department</label>
            <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="department"
                name="department" value="<?php echo $department; ?>">
        </div>
        <!-- End Department -->
        <!-- Email -->
        <div class="col-md-6 form-group label-floating is-empty">
            <label class="control-label">Email</label>
            <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="email" name="email"
                value="<?php echo $userEmail; ?>" <?php if (!is_null($userEmail)) echo "readonly"; ?>>
        </div>
        <!-- End Email -->
        <!-- Mobile -->
        <div class="col-md-6 form-group label-floating is-empty">
            <label class="control-label">Mobile</label>
            <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="phone" name="phone"
                value="<?php echo $phone; ?>">
        </div>
         <!-- Mobile -->
         <div class="col-md-6 form-group label-floating  is-empty">
         <label class="control-label">Status</label>
                        <input type="radio" id="status1" name="status" value="1"   <?php if($status==1) echo "checked"; ?>> Active
                        <input type="radio" id="status2" name="status" value="0"  <?php if($status==0) echo "checked"; ?> >
                        In-active
                </div>
                <!-- End Mobile -->
        <!-- End Mobile -->
        <!-- Comments -->
        <div class="col-md-12 form-group label-floating is-empty">
            <label class="control-label">Comments</label>
            <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="comments"
                name="comments" value="<?php echo $comments; ?>">
        </div>
        <div class="col-md-12 form-group label-floating is-empty">
            <fieldset class="scheduler-border">
                <legend class="scheduler-border">Change Password </legend>
                <!-- Mobile -->
                <div class="col-md-6 form-group label-floating ">
                    <label class="control-label">New Password</label>
                    <input type="text" class="form-control" data-parsley-trigger="keyup" id="new_password"
                        name="new_password" value="">
                </div>
                <!-- End Mobile -->

            </fieldset>
        </div>
    
        <!-- End Comments -->
        <!-- Permissions -->
        <div class="col-md-12 form-group label-floating is-empty">
            <label class="control-label">Permissions</label>
            <input type="checkbox" id="readAccess" name="access[]" value="1"
                <?php if($readAccess==1) echo "checked"; ?>> Read
            <input type="checkbox" id="readAccess" name="access[]" value="2"
                <?php if($writeAccess==1) echo "checked"; ?>> Write
        </div>
        <!-- End perms -->
        <!-- Groups -->
        <div class="col-md-12 form-group label-floating is-empty">
            <label class="control-label">Groups</label>
            <?php
                foreach ($allGroups as $groupId=>$groupDetails) {
                    $checked = (strpos($userGroups, $groupDetails["group_name"])!==false)?"checked":"";
                ?>
            <input type="checkbox" id="group_<?php echo $groupId; ?>" name="groups[]"
                value="<?php echo $groupDetails["group_name"]; ?>" <?php echo $checked; ?>>
            <?php echo $groupDetails["group_name"]; ?>
            <?php
                }
                ?>
        </div>
        <!-- End perms -->
        <!-- submit button -->
        <div class="col-md-12 form-group">
            <button class="btn" type="submit">Save</button>
        </div>
    </form>
</div>
<script>
//managing the floating labels behaviour
$("form#addEditUserForm :input").each(function() {
    var input = $(this).val();
    if ($.trim(input) != "") {
        $(this).parent().removeClass("is-empty");
    }
    $(this).on("focus", function() {
        $(this).parent().removeClass("is-empty");
    })
    $(this).on("blur", function() {
        var input = $(this).val();
        if (input && $.trim(input) != "") {
            $(this).parent().removeClass("is-empty");
        } else {
            $(this).parent().addClass("is-empty");
        }
    })
});

//handle form submit
$('form#addEditUserForm').parsley().on('field:validated', function() {
        var ok = $('.parsley-error').length === 0;
        $('.bs-callout-info').toggleClass('hidden', !ok);
        $('.bs-callout-warning').toggleClass('hidden', ok);
    })
    .on('form:submit', function() {
        //validate that user is not on client+superadmin/client+distributor/distributor+superadmin
        var conflictingGroups = ["Client", "superadmin", "Distributors"];
        var noOfConflictingGroups = 0;
        $("input[name^='groups']:checked").each(function() {
            if (conflictingGroups.indexOf($(this).val()) > -1) {
                noOfConflictingGroups++;
            }
        });
        if (noOfConflictingGroups > 1) {
            $("#addEditUserForm .alert").
            removeClass("alert-success").
            addClass("alert-danger").
            fadeIn().
            find("span").
            html("User can belong to only one of the following groups at a time: " + conflictingGroups.join(","));
            return false;
        }

        var formData = new FormData($('#addEditUserForm')[0]);

        //resetting the error message
        $("#addEditUserForm .alert").
        removeClass("alert-success").
        removeClass("alert-danger").
        fadeOut().
        find("span").html("");

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/user/",
            data: formData,
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function(user) {
                if (user["errCode"]) {
                    if (user["errCode"] != "-1") { //there is some error
                        $("#addEditUserForm .alert").
                        removeClass("alert-success").
                        addClass("alert-danger").
                        fadeIn().
                        find("span").
                        html(user["errMsg"]);
                    } else {
                        $("#addEditUserForm .alert").
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
                $("#addEditUserForm .alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html("500 internal server error");
            }
        });

    });
</script>
<?php
} else {
    //error in DB conn
    print("Error in DB connection");
}