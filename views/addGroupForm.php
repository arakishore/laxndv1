<?php error_reporting(0);
  require_once('../config/config.php');
  require_once('../config/dbUtils.php');
  require_once('../model/accessModel.php');
  require_once('../config/errorMap.php');
 $conn = createDbConnection($host, $db_username, $db_password, $dbName);
    if (noError($conn)) {
        $conn = $conn["errMsg"];
    $id = $_GET['id'];
    if($id != ''){
        
     
        $groupArr = getGroup($id,$conn);
//print_r($groupArr);
         $groupData = $groupArr["errMsg"];

        $group_name = $groupData["group_name"];
        $gpid = $groupData["group_id"];
         //print_r($groupData["group_rights"]);
        $group_rights = explode(",",$groupData["group_rights"]);
        $right_on_module = explode(",",trim($groupData["right_on_module"],'"'));
        $right_on_submodule = explode(",",trim($groupData["right_on_submodule"],'"'));
    //print_r($right_on_submodule);
       
        // printArr($group_rights);
        }
    }


?>
<div class="modal-dialog">
                                      
  <!-- Modal content-->
  <div class="modal-content" style="width: 150%;">
      <div class="card">
        <div class="card-header" style="margin-top: -50px;background-color: #c5392a;">
                <button _ngcontent-c4="" style="font-size: 4rem;padding-right: 2rem;" class="close" data-dismiss="modal" type="button">×</button>
                <h3 class="title" style="padding-left: 16px;font-weight:500;">Add Group</h3>
            </div>
        <div class="card-content"  ng-app="myApp" ng-controller="brokerCtrl">
          
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Group Name<span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">  
                        <input id="groupname" class="date-picker form-control col-md-7 col-xs-12" name="groupname" id="groupname" type="text" maxlength="50" value="<?php echo $group_name;  ?>" required="">
                      </div>
                  </div>
                </div>
                  
              </div>
              <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Permision Group<span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <div class="row">
                            <div class="control-group" style="padding:0px;">
                                  <input type="checkbox" id="permsissions" name="permsissions[]" value="1" required <?php if(in_array(1, $group_rights)) echo "checked";?> ><span> Read </span>
                                  <input type="checkbox" id="permsissions" name="permsissions[]" value="2" <?php if(in_array(2, $group_rights)) echo "checked";?>  ><span> Write </span>
                                  
                            </div>
                        </div>
                      </div>
                  </div>
               </div>

                  
              </div>
              <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12">
                                  Permision on Module
                          <span class="required">*</span>
                          </label>
                          <div class="col-md-6 col-sm-6 col-xs-12" style="border: 1px solid;padding:0px;">
                          <?php
                            $result=getallmodules($conn);
                            $resultnew=$result["errMsg"];
                            $allmodules = array();
                          ?>
                              <select class="form-control" id="modulename" name="modulename[]" multiple="" required="">
                                 <?php

                                foreach($resultnew as $value){
                                 

                                   if (in_array($value["module_name"], $right_on_module)) {
                                      echo "<option selected value=".$value["module_name"].">" . $value["module_name"] . "</option>";
                                       array_push($allmodules, $value["module_name"]);
                                   }else{
                                    echo "<option value=".$value["module_name"].">".$value["module_name"]."</option>";
                                   }
                                  
                                }
                              ?>                   
                              </select>

                          </div>
                      </div>
                  </div>
              </div>  
              <div class="row">
                <div class="col-md-12">
                <div class="form-group" id="submodulesdata"></div>
                <?php
                        $submodule = $allmodules;
                        $data1 = getsubmodules($submodule, $conn);
                        //print_r($right_on_submodule);
                        // echo "reacheeee";
                        if($id !=''){
                    ?>
                  <div class="form-group groupDiv">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12">
                                  Permision on Sub Module
                          <span class="required">*</span>
                          </label>
                          <div class="col-md-6 col-sm-6 col-xs-12" style="border: 1px solid;padding:0px;">
                               <select class="form-control" id="submodulename" name="submodulename[]" multiple>
                            <option value="">Select Submodules</option>
                            <?php
                                foreach ($data1 as $value1) {
                                  //echo $value1;
                                   //print_r($right_on_submodule);
                                  
                                  // echo "insidee";
                                    if (in_array($value1, $right_on_submodule )) {
                                      //echo "heree1";
                                        echo "<option selected value=".$value1.">" . $value1 . "</option>";
                                    } else {
                                      //echo "heree2";
                                        echo "<option value=".$value1.">" . $value1 . "</option>";
                                    }
                                }
                            ?>
                        </select>
                          </div>
                      </div> 
                      <?php } ?>
                  </div>
              </div> 
              <div id="rederror" style="">
                            <div id="errmsg_group" style="padding: 0px;color:red;"></div>
                            <br/>
                        </div>

                        <div id="greenerror" style="">
                            <div id="errmsgsuc_group" style="padding: 0px;color:green;"></div>
                            <br/>
                        </div>
                        <?php if($id == ''){?>
              <input type="submit"  id="addBtn" name="addBtn" class="btn btn-danger pull-right" value="Add Group" onclick="submitUserdatagroup()"/>
               <?php }else{?>
               <input type="hidden" id="id" name="id" class="btn btn-danger pull-right" value="<?php echo $id;?>"  />
                   <input type="submit"  id="addBtn" name="addBtn" class="btn btn-danger pull-right" value="Update Group" onclick="updateGroup();"/>
                     <?php }?>
             <!-- <button type="submit" id="addBtn" class="btn btn-danger pull-right" onclick="submitUserdatagroup()">Add Group</button> -->
              <div class="clearfix"></div>
               <div id="add_group" style="display:none;top:24%;width:69px;height:auto;position:absolute;left:40%;padding:2px;">
                        <img src='../assets/img/LoaderIcon.gif' width="54" height="54" />
          
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#modulename').on('change', function () {
     $('.groupDiv').remove();
    var optionSelected = $("option:selected", this);
    var len=optionSelected.length;
    var i;
    var allmodules = [];
    for(i=0;i<len;i++)
    {
        var data=optionSelected[i].value;
        allmodules.push(data);
    }

    $.ajax({
        type: "POST",
        dataType: "html",
        url: "../controller/adminController.php",
        data: {
            allmodules:allmodules,type:"getsubmodules"
        },
        success: function(user) {
            $("#submodulesdata").html('');


            $("#submodulesdata").html('');
            $("#submodulesdata").html(user);

        },
        error: function() {

            bootbox.alert("<h4>Failed to get Submodules</h4>", function() {

            });

        }
    });
});

</script>