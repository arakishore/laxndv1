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

$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (noError($conn)) {
    $conn = $conn["errMsg"];
    
    //TO DO: Logs here
    
    $groupId = null;
    if ( isset($_GET["groupId"]) && !empty($_GET["groupId"]) ) {
        $groupId = cleanQueryParameter($conn, cleanXSS($_GET["groupId"]));
    }
    $groupName = "";
    $readAccess = "";
    $writeAccess = "";
    $modulesWithAccess = "";
    $submodulesWithAccess = "";
    
    if (!is_null($groupId)) {
        $groupSearchArr = array('group_id'=>$groupId);
        $fieldsStr = "group_name, group_rights, right_on_module, right_on_submodule, group_id";
        $groupsInfo = getGroupInfo($groupSearchArr, $fieldsStr, $conn);
        if (!noError($groupsInfo)) {
            //error fetching all groups info
            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5)." Error fetching group details.";
            print($returnArr["errMsg"]);
            exit;
        } else {
            $groupsInfo = $groupsInfo["errMsg"];
            $groupName = $groupsInfo[$groupId]["group_name"];
            if (strpos($groupsInfo[$groupId]["group_rights"], "1")!==false) {
                $readAccess = 1;
            }
            if (strpos($groupsInfo[$groupId]["group_rights"], "2")!==false) {
                $writeAccess = 1;
            }
            $modulesWithAccess = $groupsInfo[$groupId]["right_on_module"];
            $submodulesWithAccess = $groupsInfo[$groupId]["right_on_submodule"];
        }
    }
?>
    <div class="row">
        <form id="addEditGroupForm" name="addEditGroupForm" action="javascript:;" data-parsley-validate="">
            <!-- error/success message -->
            <div class="alert" style="display: none">
                <span></span>
            </div>
            <!-- end error/success message -->
            <!-- hidden groupID field -->
            <input type="hidden" name="groupID" value="<?php echo $groupId; ?>">
            <!-- Group name -->
            <div class="col-md-12 form-group label-floating is-empty">
                <label class="control-label">Group Name</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required="" id="groupName" name="groupName" 
                    value="<?php echo $groupName; ?>"
                >
            </div>
            <!-- End Group name -->
            <!-- Group Perms -->
            <div class="col-md-12">
                <label class="control-label col-md-6">Group Permissions<span class="required">*</span></label>
                <div class="col-md-6">
                    <span class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="groupPermsRead" name="groupPerms[]" value="1"
                            <?php if($readAccess) echo "checked"; ?>
                        >
                        <label class="custom-control-label" for="groupPermsRead">Read</label>
                    </span>
                    <span class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="groupPermsWrite" name="groupPerms[]" value="2"
                            <?php if($writeAccess) echo "checked"; ?>
                        >
                        <label class="custom-control-label" for="groupPermsWrite">Write</label>
                    </span>
                </div>
            </div>
            <!-- End Group Perms -->
            <!-- Modules with access -->
            <div class="col-md-12 form-group">
                <label class="control-label col-md-6">Modules With Access<span class="required">*</span></label>
                <div class="col-md-6">
                    <select class="form-control" id="modulesWithAccess" onchange="showSubModules(this)" name="modulesWithAccess[]" multiple="" required="">
                    <?php
                    //get all modules and print options here
                    $fieldSearchArr = array("module_status"=>1);
                    $fieldsStr = "module_id, module_name, submodule_virtual_name";
                    $allModules = getModulesInfo($fieldSearchArr, $fieldsStr, $conn);
                    if (noError($allModules)) {
                        $allModules = $allModules["errMsg"];
                        $subModuleOptionsStr = "";
                        foreach ($allModules as $moduleId=>$moduleDetails) {
                            $allSubModules = explode(",", $moduleDetails["submodule_virtual_name"]);
                            foreach($allSubModules as $subModuleName){
                                $selected = "";
                                if (strpos(trim($submodulesWithAccess), trim($subModuleName)) !== false) {
                                    $selected = "selected='selected'";
                                }
                                $subModuleOptionsStr .= "<option ".$selected." data-module-name='{$moduleDetails["module_name"]}' value='{$subModuleName}'>".
                                "{$subModuleName}</option>";
                            }
                            $selected = "";
                            if (strpos($modulesWithAccess, $moduleDetails["module_name"]) !== false) {
                                $selected = "selected='selected'";
                            }
                    ?>
                            <option <?php echo $selected; ?> value="<?php echo $moduleDetails["module_name"]; ?>">
                                <?php echo $moduleDetails["module_name"]; ?>
                            </option>
                    <?php
                        }
                    } else {
                        print("Error fetching all modules");
                    }
                    ?>
                    </select>
                </div>
            </div>
            <!-- End Modules with access -->
            <!-- Sub Modules with access -->
            <div class="col-md-12">
                <label class="control-label col-md-6">Submodules With Access<span class="required">*</span></label>
                <div class="col-md-6">
                    <select class="form-control" id="submodulesWithAccess" name="submodulesWithAccess[]" multiple="" required="">
                        <?php echo $subModuleOptionsStr; ?>
                    </select>
                </div>
            </div>
            <!-- End Sub Modules with access -->
            <!-- submit button -->
            <div class="col-md-12 form-group">
                <button class="btn" type="submit">Save</button>
            </div>
        </form>
    </div>
    <script>

        //managing the floating labels behaviour
        $("form#addEditGroupForm :input").each(function () {
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

        //handle form submit
        $('form#addEditGroupForm').parsley().on('field:validated', function () {
            var ok = $('.parsley-error').length === 0;
            $('.bs-callout-info').toggleClass('hidden', !ok);
            $('.bs-callout-warning').toggleClass('hidden', ok);
        })
        .on('form:submit', function () {
            var formData = new FormData($('#addEditGroupForm')[0]);

            //resetting the error message
            $("#addEditGroupForm .alert").
            removeClass("alert-success").
            removeClass("alert-danger").
            fadeOut().
            find("span").html("");
            
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "<?php echo $rootUrl; ?>controller/group/",
                data: formData,
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                success: function (user) {
                    if (user["errCode"]) {
                        if (user["errCode"] != "-1") { //there is some error
                            $("#addEditGroupForm .alert").
                            removeClass("alert-success").
                            addClass("alert-danger").
                            fadeIn().
                            find("span").
                            html(user["errMsg"]);
                        } else {
                            $("#addEditGroupForm .alert").
                            removeClass("alert-danger").
                            addClass("alert-success").
                            fadeIn().
                            find("span").
                            html(user["errMsg"]);
                            setTimeout(function(){
                                window.location.reload();
                            }, 3000);
                        }
                    }
                },
                error: function () {
                    $("#addEditGroupForm .alert").
                    removeClass("alert-success").
                    addClass("alert-danger").
                    fadeIn().
                    find("span").
                    html("500 internal server error");
                }
            });
        
        });

        function showSubModules(selectElement)
        {
            //hide all submodule options first
            $("#submodulesWithAccess option").hide();
            $(selectElement).find("option:selected").each(function(){
                $("#submodulesWithAccess option[data-module-name="+$(this).val()+"]").show();
            });
            $("#submodulesWithAccess option").each(function(){
                if ($(this).css("display")==="none") {
                    $(this).removeAttr("selected");
                }
            });
        }

        <?php
        if (!is_null($groupId)) {
        ?>
            showSubModules($("#modulesWithAccess"));
        <?php
        }
        ?>
    </script>
    <style>
        #submodulesWithAccess option {
            display: none;
        }
    </style>
<?php
} else {
    //error in DB conn
    print("Error in DB connection");
}