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
        $where = array('user_id'=>$id);
       // printArr($conn);
        //exit;
        $userArr2 = getUserData($where,$conn);
        // printArr($userArr2);
        // exit();
     
        //exit;
           $id = $userArr2['result']['user_id'];   

        $firstname = $userArr2['result']['firstname'];
        $lastname = $userArr2['result']['lastname'];
        $email = $userArr2['result']['email'];
        $phone = $userArr2['result']['phone'];
        $department = $userArr2['result']['department'];
        $designation = $userArr2['result']['designation'];
        $comments = $userArr2['result']['comments'];
        $group = $userArr2['result']['groups'];
        $rights = explode(",",$userArr2['result']["rights"]);
        // printArr($rights);
        $groupArr = explode(",",$group);
        }
    }


?>
<div class="modal-dialog">
<?php //echo $_GET['id'];?>
                                        
                                          <!-- Modal content-->
      <div class="modal-content" style="width: 150%;">
          <div class="card">
            <div class="card-header" style="margin-top: -50px;background-color: #c5392a;">
                    <button _ngcontent-c4="" style="font-size: 4rem;padding-right: 2rem;" class="close" data-dismiss="modal" type="button">×</button>
                    <h3 class="title" style="padding-left: 16px;font-weight:500;">Add User</h3>
                </div>
            <div class="card-content"  ng-app="myApp" ng-controller="brokerCtrl">
                   
                    

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-black label-floating is-empty">
                                    <label class="control-label">First Name</label>
                                    <input type="text" class="form-control" required="" id="first_name" name="first_name" value ='<?php echo $firstname;?>' required>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group form-black label-floating is-empty">
                                    <label class="control-label">Last Name</label>
                                    <input type="text" class="form-control" required="" id="last_name" name="last_name" value ='<?php echo $lastname;?>'  required>
                                </div>
                            </div>
                            
                        </div>
                        <div class="row">
                           <div class="col-md-6">
                                <div class="form-group form-black label-floating is-empty">
                                    <label class="control-label">Designation</label>
                                    <input type="text" class="form-control" required="" id="designation" name="designation" value ='<?php echo $designation;?>' required>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group form-black label-floating is-empty">
                                    <label class="control-label">Department</label>
                                    <input type="text" class="form-control" required="" id="department" name="department" value ='<?php echo $department;?>'  required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-black label-floating is-empty">
                                    <label class="control-label">Email</label>
                                    <input type="email"  class="form-control" <?php if($email !='') { echo "readonly";}?> required="" id="email" name="email" value ='<?php echo $email;?>' >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-black label-floating is-empty">
                                    <label class="control-label">Mobile number</label>
                                    <input type="text" class="form-control" required="" id="phone" name="phone" value ='<?php echo $phone;?>'  required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                           <div class="col-md-12">
                                <div class="form-group form-black label-floating is-empty">
                                    <label class="control-label">Comments</label>
                                    <input type="text" class="form-control" required="" id="comments" name="comments" value ='<?php echo $comments;?>' required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                           <div class="col-md-12">
                                <div class="form-group form-black label-floating is-empty">
                                    <label class="control-label">Give Permission</label>

                                    <input type="checkbox" id="permission" name="permission[]" value="1" required <?php if(in_array(1, $rights)) echo "checked";?> ><span> Read </span>
                                    <input type="checkbox" id="permission" name="permission[]" value="2" <?php if(in_array(2, $rights)) echo "checked";?>  ><span> Write </span>
                                   <!--  <input type="checkbox" name="permission[]" id="permission" value="1"> Read 
                                    <input type="checkbox" name="permission[]" id="permission" value="2" style="margin-left: 40px;margin-top: 10px;"> Write -->
                                </div>
                          </div>
                        </div>

                      <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="form-group form-black label-floating is-empty" >
                                    <label class="control-label"> Group</label></br>
                                  <!--   <input type="checkbox" id="groups"  name="groups[]" value="superadmin" <?php echo  in_array('superadmin',$groupArr) ? 'checked="checked"' : '' ?> >Superadmin
                                    <input type="checkbox" id="groups" name="groups[]" value="content" <?php echo  in_array('content',$groupArr) ? 'checked="checked"' : '' ?> >Content -->
                                    <?php
                                   $getGroupName = getGroupName($conn);
                                   foreach($getGroupName['errMsg'] as $value1) {
                                           // printArr($value1);

                                   ?>
                                     <input style="margin-left: 10px;" type="checkbox" id="groups"  name="groups[]" value="<?php echo $value1['group_name'];?>" <?php echo  in_array($value1['group_name'],$groupArr) ? 'checked="checked"' : '' ?> ><?php echo $value1['group_name']?>
                                           
                                           <?php } ?>
                                 
                                </div>
                            </div>
                        </div>
                     </div>
                      <div id="rederror" style="">
                            <div id="errmsg_user" style="padding: 0px;color:red;"></div>
                            <br/>
                        </div>

                        <div id="greenerror" style="">
                            <div id="errmsgsuc_user" style="padding: 0px;color:green;"></div>
                            <br/>
                        </div>
                            <?php if($id == ''){?>
                    <input type="submit"  id="addBtn" name="addBtn" class="btn btn-danger pull-right" value="Add User" onclick="addUser();"/>
                    <?php }else{?>
                    <input type="hidden" id="id" name="id" class="btn btn-danger pull-right" value="<?php echo $id;?>"  />
                   <input type="submit"  id="addBtn" name="addBtn" class="btn btn-danger pull-right" value="Update User" onclick="updateUser();"/>
                     <?php }?>
                       <!-- <button type="submit" class="btn btn-danger pull-right" >Add User</button> -->
                        <div class="clearfix"></div>
                        <div id="wait_group" style="display:none;top:24%;width:69px;height:auto;position:absolute;left:40%;padding:2px;">
                        <img src='../assets/img/LoaderIcon.gif' width="54" height="54" />
                    
                </div>
       
            </div>
        </div>
</div>
<script type="text/javascript">
          
          $("form#addUserForm :input").each(function(){
               var input = $(this).val(); // This is the jquery object of the input, do what you will
               
               if(input != ""){
                 $(this).parent().removeClass("is-empty");
               }
            });
      
      
      
$(document).ajaxStart(function(){
         $("#wait_group").css("display", "block");

     });
     $(document).ajaxComplete(function(){
         $("#wait_group").css("display", "none");
     });


       
      </script>
