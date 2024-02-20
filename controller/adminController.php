<?php
    session_start();
    require_once('../config/config.php');
    require_once('../config/dbUtils.php');
    require_once('../config/errorMap.php');
    require_once('../model/accessModel.php');
    require_once('../config/auth.php');
    require_once('../libphp-phpmailer/autoload.php');

    /////adding logs////////////////
    //xml log files setup
    require_once('../config/logs/xmlProcessor.php');
    require_once('../config/logs/logsCoreFunctions.php');

    /* Log initialization start */
    $username = (isset($_POST['username']))?$_POST['username']:"";
    
    /*---------------------- Connection With Database ---------------------------------*/

    $conn = createDbConnection($host, $db_username, $db_password, $dbName);
 //printArr($_POST);
//exit("hereeee");
    if (noError($conn)) {
        $conn = $conn["errMsg"];

        //--------------------------------2.Database Connection Success---------------------------------------------------------
        $logMsg = "Database connection successful.";
        $xml_data["step2"]["data"] = "2. {$logMsg}";

        if(isset($_POST['type']) && $_POST['type'] == "addnewip"){

            $ipaddress = cleanQueryParameter($conn, cleanXSS($_POST["ipaddress"]));

            if ($_POST["ipaddress"] == "") {
                $returnArr["errCode"] = 2;
                $returnArr["errMsg"] = " IP address is Required !!";
                echo(json_encode($returnArr));
                exit;
            }

            if (filter_var($ipaddress, FILTER_VALIDATE_IP) === false) {
                $returnArr["errCode"] = 2;
                $returnArr["errMsg"] = " Please add Valid IP address";

                echo(json_encode($returnArr));
                exit;
            }

            $checkip = getipaddress($ipaddress, $conn);
            $data = $checkip["errMsg"];
            $ip = $data["ip_address"];
            if ($ipaddress == $ip) {
                $returnArr["errCode"] = -8;
                $returnArr["errMsg"] = "IP address already present in database ";

                echo(json_encode($returnArr));
                exit;
            }


            $result = addipaddress($ipaddress, $conn);
            if ($result["errCode"] == -1) {

                $_SESSION['ipaccess'] = "IP address added to system success ";
                $returnArr["errCode"] = -1;
                $returnArr["errMsg"] = "New IP address added sucessfully ";
                echo(json_encode($returnArr));
            } else {
                $returnArr["errCode"] = 2;
                $returnArr["errMsg"] = "Admin Error in ip address adding ";
                echo(json_encode($returnArr));
            }
        }
        if(isset($_POST['action']) && $_POST['action'] == 'delete')
           {

            $transId = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
            $where = 'id='.$transId;

            $userArr = deleteIp($where,$conn);

             if($userArr['errCode'] == -1){
                    $logMsg = "IP addres deleted Successfully. ";
                    $xml_data["step3"]["data"] = "3. {$logMsg}";

                    $returnArr["errCode"] = -1;
                    $returnArr["errMsg"] = "IP addres deleted Successfully";
                    echo(json_encode($returnArr));

                 }else{

                    $logMsg = "Error in mysql while deleting IP addres.";
                    $xml_data["step3"]["data"] = "3. {$logMsg}";

                    $returnArr["errCode"] = 4;
                    $returnArr["errMsg"] = "Error in mysql while deleting IP addres.";
                    echo(json_encode($returnArr));
                 }
           }

        if(isset($_POST['action']) && $_POST['action'] == 'createUser')
           {

            if(isset($_POST['type']) && $_POST['type']=='addClient'){

                $name=explode(' ', cleanQueryParameter($conn, cleanXSS($_POST["client_name"])));
                $fname=$name[0];
                $lname= '';
                for ($i=1; $i < sizeof($name) ; $i++) {
                   $lname= $name[$i].' ';
                }

                $first_name = cleanQueryParameter($conn, cleanXSS($fname));
                $last_name = cleanQueryParameter($conn, cleanXSS($lname));
                $email = cleanQueryParameter($conn, cleanXSS($_POST["email"]));
                $phone = cleanQueryParameter($conn, cleanXSS($_POST["mobile_number"]));
                $department = '';
                $designation = 'Client';
                $comments = 'New client added.';
                //$userRights = cleanQueryParameter($conn, cleanXSS($_POST["comments"]));

                $emailArr = checkEmailExist($email,$conn);

                if($emailArr['errCode'] ==19){
                    $returnArr["errCode"] = 19;
                    $returnArr["errMsg"] = $emailArr['errMsg'];
                    echo(json_encode($returnArr));
                    exit;
                }
                $groups = 'Client';
                $permission = '1,2';


                $token=generateToken(12);
                //$where = 'id='.$transId;
            }else{
                $first_name = cleanQueryParameter($conn, cleanXSS($_POST["first_name"]));
                $last_name = cleanQueryParameter($conn, cleanXSS($_POST["last_name"]));
                $email = cleanQueryParameter($conn, cleanXSS($_POST["email"]));
                $phone = cleanQueryParameter($conn, cleanXSS($_POST["phone"]));
                $department = cleanQueryParameter($conn, cleanXSS($_POST["department"]));
                $designation = cleanQueryParameter($conn, cleanXSS($_POST["designation"]));
                $comments = cleanQueryParameter($conn, cleanXSS($_POST["comments"]));
                //$userRights = cleanQueryParameter($conn, cleanXSS($_POST["comments"]));

                $emailArr = checkEmailExist($email,$conn);

                if($emailArr['errCode'] ==19){
                    $returnArr["errCode"] = 19;
                    $returnArr["errMsg"] = $emailArr['errMsg'];
                    echo(json_encode($returnArr));
                    exit;
                }
                $groups = $_POST["groups"];
                $groups = implode(',', $groups);
                $permission = $_POST["permission"];
                $permission = implode(',', $permission);

                $token=generateToken(12);
                //$where = 'id='.$transId;
            }

            $data = array('firstname'=>$first_name,'lastname'=>$last_name,'email'=>$email,'phone'=>$phone,'department'=>$department,'designation'=>$designation,'password'=>'','salt'=>'','token'=>$token,'comments'=>$comments,'image'=>'','status'=>'1','rights'=>$permission, 'groups'=>$groups);

            // exit();

            $userArr = addUser($data,$conn);
            //printArr($userArr);
             if($userArr['errCode'] == -1){
                $data = array('email'=>$email);
                $result=getUserData($data,$conn);

                $result=$result["result"];
                //printArr($result);
                $admin_id=$result["user_id"];
                $admin_email=$result["email"];

            $url = $rootUrl.'views/activateNewUser.php?id='.$admin_id.'&email='.$admin_email.'&token='.$token;

            $to = $email;
            //$username=$fname." ".$lname;
            $subject = "Welcome To {APPNAME}";

            $recipient["name"] = $username;
            $recipient["email"] = $email;
            $recipient["userId"] = $result["user_id"];

            $message = '<table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary"><tbody>
                            <tr>
                                <td align="left">
                                    <table border="0" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td style="font-family: sans-serif;font-size: 14px;vertical-align: top;box-sizing: border-box">
                                                        <p>Dear Sir/Madam,</p>
                                                        <p>Welcome to the online portal of the '.APPNAME.' App.</p>
                                                       
                                                        <p>Please click the link below to authenticate your account</p>
                                                        <p>
                                                            <a href="'.$url.'" target="_blank" style="background-color:#3498db;border-color:#3498db;color:#fff;border:1px solid #3498db;border-radius:5px;cursor:pointer;display:inline-block;font-weight:700;margin:0;padding:12px 25px;text-decoration:none">Click here to activate your user account.</a>
                                                        </p>
                                                        <p>Once the account is authenticated, you will get another email to set up the password. If you do not receive the email, please click the forgot password link and finish the set-up of the account.</p>
                                                        <p>In the first week of every month, you will get an email stating that the portfolio snapshot statement is now online.</p>
                                                        <p>For any questions, please feel free to reach out to us at the below-the portfolio snapshot statement is now online.</p>
                                                        <p>&nbsp;</p>
                                                        <p>Regards,</p>
                                                    
                                                        <p><img src="../assets/img/nirvana_logo.jpg" style="width: 1.3in, height: 1.2in"></p>
                                                        <p><span>'.APPNAME.'</span></p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>';

            $trans = 1;
            $type = 0;
            $icon = "";
            $returnArr = sendMail($to,$subject,$message);

        /*    $datatt=getAdminData($email,$connAdmin);
            $datatt=$datatt["errMsg"];

            $groupNames = "";

            $groupResult = getGroupData(str_replace(" ", ",", $datatt["group_id"]), $connAdmin);
            foreach ($groupResult["errMsg"] as $key1 => $value1) {
                $groupNames .= str_replace(" ", "_", $value1['group_name'])." ";
            }
            //printArr(trim($groupNames));die;
            $groupNames = trim($groupNames);

            //$datatt=$datatt["group_id"];
            $datatt=$groupNames;

            $permsiion=array();
            $resultsss=explode(" ",$datatt);
            
            foreach($resultsss as $res){

                $result=selectGroups($res,$connAdmin);
                $resultnew=$result["errMsg"];
                $rightss=$resultnew["group_rights"];
                array_push($permsiion,$rightss);

            }

            $largest = max($permsiion);*/

            //setUserPermissions($email,$largest,$connAdmin);


                    $logMsg = "User added Successfully. ";
                    $xml_data["step3"]["data"] = "3. {$logMsg}";
                    
                    $returnArr["errCode"] = -1;
                    $returnArr["errMsg"] = "User added Successfully";
                    echo(json_encode($returnArr));

                 }else{

                    $logMsg = "Error in mysql while adding user.";
                    $xml_data["step3"]["data"] = "3. {$logMsg}";

                    $returnArr["errCode"] = 4;
                    $returnArr["errMsg"] = "Error in mysql while adding user.";
                    echo(json_encode($returnArr));
                 }
            

           }

        if(isset($_POST['action']) && $_POST['action'] == 'updateUser')
           {

           
            $first_name = cleanQueryParameter($conn, cleanXSS($_POST["first_name"]));
            $last_name = cleanQueryParameter($conn, cleanXSS($_POST["last_name"]));
            $phone = cleanQueryParameter($conn, cleanXSS($_POST["phone"]));
            $department = cleanQueryParameter($conn, cleanXSS($_POST["department"]));
            $designation = cleanQueryParameter($conn, cleanXSS($_POST["designation"]));
            $comments = cleanQueryParameter($conn, cleanXSS($_POST["comments"]));
            //$email = cleanQueryParameter($conn, cleanXSS($_POST["email"]));
            $id = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
            $groups = $_POST["groups"];
            $groups = implode(',', $groups);

            $permission = $_POST["permission"];
            $permission = implode(',', $permission);

              
            $where = 'user_id='.$id;

            $data = array('firstname'=>$first_name,'lastname'=>$last_name,'groups'=>$groups,'phone'=>$phone,'department'=>$department,'designation'=>$designation,'comments'=> $comments,'rights'=>$permission,);

               
              


            $userArr = updateUser($data,$conn,$where);

             if($userArr['errCode'] == -1){
                    $logMsg = "User data updated Successfully. ";
                    $xml_data["step3"]["data"] = "3. {$logMsg}";
                    
                    $returnArr["errCode"] = -1;
                    $returnArr["errMsg"] = "User data updated Successfully";
                    echo(json_encode($returnArr));

                 }else{

                    $logMsg = "Error in mysql while updating user.";
                    $xml_data["step3"]["data"] = "3. {$logMsg}";

                    $returnArr["errCode"] = 4;
                    $returnArr["errMsg"] = "Error in mysql while updating user.";
                    echo(json_encode($returnArr));
                 }
            

           }

        if(isset($_POST['type']) && $_POST['type'] == "getsubmodules"){
                $allmodules = $_POST["allmodules"];
                $data = getsubmodules($allmodules, $conn);

                // printArr($value);
                // exit();

                $submodule = '<label class="control-label col-md-3 col-sm-3 col-xs-12">Permision on Sub-Module</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <select class="form-control" id="submodulename" name="submodulename[]" multiple required="">
                        <option value="">Select Submodules</option>';
                        
                    foreach ($data as $value) {
                    $submodule .='<option value='.$value.'>'.$value.'</option>';

                        }
                        

                   $submodule .= '</select></div>';
                   echo $submodule;

        }
         if(isset($_POST['action']) && $_POST['action'] == 'userDelete')
           {


            $userId = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
              
            $where = 'user_id='.$userId;

            $userArr = deleteUser($where,$conn);

             if($userArr['errCode'] == -1){
                    $logMsg = "User deleted Successfully. ";
                    $xml_data["step3"]["data"] = "3. {$logMsg}";
                    
                    $returnArr["errCode"] = -1;
                    $returnArr["errMsg"] = "User deleted Successfully";
                    echo(json_encode($returnArr));

                 }else{

                    $logMsg = "Error in mysql while deleting User.";
                    $xml_data["step3"]["data"] = "3. {$logMsg}";

                    $returnArr["errCode"] = 4;
                    $returnArr["errMsg"] = "Error in mysql while deleting User.";
                    echo(json_encode($returnArr));
                 }
            

           }
           if(isset($_POST['action']) && $_POST['action'] == 'addgroup')
           {

            $groupname = cleanQueryParameter($conn, cleanXSS($_POST["groupname"]));
            $modulename = implode(',', $_POST["modulename"]);
            $modulename = json_encode($modulename, TRUE);
            $submodulename = implode(',', $_POST["submodulename"]);
            $permsissions = $_POST["permsissions"];
            $permsissions = implode(',', $permsissions);
            $created_on = date('Y-m-d H:i:s');
            $updated_on = date('Y-m-d H:i:s');

              
            //$where = 'id='.$transId;
            $data = array('group_name'=>$groupname,'group_rights'=>$permsissions,'right_on_module'=>$modulename,'right_on_submodule'=>$submodulename, 'created_on'=>$created_on ,'updated_on'=>$updated_on);
               
             
          // exit; 


            $userArr = submitUserdatagroup($data,$conn);

             if($userArr['errCode'] == -1){
                    $logMsg = "Group added Successfully. ";
                    $xml_data["step3"]["data"] = "3. {$logMsg}";
                    
                    $returnArr["errCode"] = -1;
                    $returnArr["errMsg"] = "Group added Successfully";
                    echo(json_encode($returnArr));

                 }else{

                    $logMsg = "Error in mysql while adding group.";
                    $xml_data["step3"]["data"] = "3. {$logMsg}";

                    $returnArr["errCode"] = 4;
                    $returnArr["errMsg"] = "Error in mysql while adding group.";
                    echo(json_encode($returnArr));
                 }
            

           }
           if(isset($_POST['action']) && $_POST['action'] == 'updateGroup')
           {

           //print_r($_POST);
            $groupname = cleanQueryParameter($conn, cleanXSS($_POST["groupname"]));
            $modulename = implode(',', $_POST["modulename"]);
            $modulename = json_encode($modulename, TRUE);
            $submodulename = implode(',', $_POST["submodulename"]);
            $permsissions = $_POST["permsissions"];
            $permsissions = implode(',', $permsissions);
            $created_on = date('Y-m-d H:i:s');
            $updated_on = date('Y-m-d H:i:s');
            //$email = cleanQueryParameter($conn, cleanXSS($_POST["email"]));
            $id = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
           /* $groups = $_POST["groups"];
            $groups = implode(',', $groups);
*/
              
            $where = 'group_id='.$id;

            $data = array('group_name'=>$groupname,'group_rights'=>$permsissions,'right_on_module'=>$modulename,'right_on_submodule'=>$submodulename,'created_on'=>$created_on ,'updated_on'=>$updated_on);
               
              


            $userArr = updategroup($data,$conn,$where);

             if($userArr['errCode'] == -1){
                    $logMsg = "Group data updated Successfully. ";
                    $xml_data["step3"]["data"] = "3. {$logMsg}";
                    
                    $returnArr["errCode"] = -1;
                    $returnArr["errMsg"] = "Group data updated Successfully";
                    echo(json_encode($returnArr));

                 }else{

                    $logMsg = "Error in mysql while updating group.";
                    $xml_data["step3"]["data"] = "3. {$logMsg}";

                    $returnArr["errCode"] = 4;
                    $returnArr["errMsg"] = "Error in mysql while updating group.";
                    echo(json_encode($returnArr));
                 }
            

           }
           if(isset($_POST['action']) && $_POST['action'] == 'deleteGroup')
           {

              $userId = cleanQueryParameter($conn, cleanXSS($_POST["id"]));
              
            $where = 'group_id='.$userId;

            $userArr = deleteGroup($where,$conn);

             if($userArr['errCode'] == -1){
                    $logMsg = "Group deleted Successfully. ";
                    $xml_data["step3"]["data"] = "3. {$logMsg}";
                    
                    $returnArr["errCode"] = -1;
                    $returnArr["errMsg"] = "Group deleted Successfully";
                    echo(json_encode($returnArr));

                 }else{

                    $logMsg = "Error in mysql while deleting group.";
                    $xml_data["step3"]["data"] = "3. {$logMsg}";

                    $returnArr["errCode"] = 4;
                    $returnArr["errMsg"] = "Error in mysql while deleting group.";
                    echo(json_encode($returnArr));
                 }
            
           }


    } else {

                $logMsg = "Database connection failed..";
                $xml_data["step2"]["data"] = "2. {$logMsg}";

                $returnArr["errCode"] = 3;
                $returnArr["errMsg"] = "Could not connect to database";
                
    }

?>
