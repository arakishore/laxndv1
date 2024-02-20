<?php
session_start();
    if ($_SESSION['user_id'] == ""){
        //echo $rootUrl."views/login/";
        header("Location:".$rootUrl."login/");
        
        exit;
      }
    $page_title = "Access Control";
    
  $uid = $_SESSION['user_id'];
  require_once('../config/config.php');
  require_once('../config/dbUtils.php');
  require_once('../model/accessModel.php');
  require_once('../config/errorMap.php');
  require_once('../model/checkAccess.php');
 $conn = createDbConnection($host, $db_username, $db_password, $dbName);

    if (noError($conn)) {
        $conn = $conn["errMsg"];

         //access control permission
        $myp = mypermissions($largest);
        // printArr($myp);
        // exit();

        /**** For ip address ******/
        $dataArr = getallipaddress('','',$conn);

        $row_count = count($dataArr['result']);


        $resultsPerPage = 10;
        $data = array();
        if (isset($_GET['page']) && !in_array($_GET["page"], $blanks)) {
          $pageno = preg_replace('#[^0-9]#i', '', $_GET['page']);
        }

        $start = ($pageno - 1) * $resultsPerPage;
        $pageno = $_GET['page'];

        $data = array();

        $lastpage = $row_count / $resultsPerPage;
        $lastpage = ceil($lastpage);

        if ($pageno <= 1) {
          $pageno = 1;
        } else if ($pageno > $lastpage) {
          $pageno = $lastpage;
        }

        if (!isset($_GET["page"]) || ($_GET["page"] == 1)) {
          $start = '0';
        } else {
          $start = $start;
        }

   
        $dataArr = getallipaddress($start,$resultsPerPage,$conn);
  
      
        $rw_count = count($dataArr['result']);


        /*********** For User **************/
         $userArr1 = getallUser('','',$conn);

        $allCount = count($userArr1['result']);

         
        $data = array();
        if (isset($_GET['page_no']) && !in_array($_GET["page_no"], $blanks)) {
          $pageNum = preg_replace('#[^0-9]#i', '', $_GET['page_no']);
        }

        $startNum = ($pageNum - 1) * $resultsPerPage;
        $pageNum = $_GET['page_no'];

        

        $lastpageNum = $allCount / $resultsPerPage;
        $lastpageNum = ceil($lastpageNum);

        if ($pageNum <= 1) {
          $pageNum = 1;
        } else if ($pageNum > $lastpageNum) {
          $pageNum = $lastpageNum;
        }

        if (!isset($_GET["page_no"]) || ($_GET["page_no"] == 1)) {
          $startNum = '0';
        } else {
          $startNum = $startNum;
        }

   
        $userArr1 = getallUser($startNum,$resultsPerPage,$conn);

  
      
        $user_rw_count1 = count($userArr1['result']);
        // printArr($user_rw_count1);

        /************* For group **********************/
           $groupArr = getallgroup($conn);
// printArr($groupArr['errMsg']);
// exit();
        $groupCount = count($groupArr['errMsg']);
//exit;

      }else{
        $msg = "Database connection failed. failed to load data";
      }


?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/nirvana_favicon.png" />
    <link rel="icon" type="image/png" href="../assets/img/nirvana_favicon.png" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title><?php echo APPNAME; ?> Access Control</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />
    <!-- Bootstrap core CSS     -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
    <!--  Material Dashboard CSS    -->
    <link href="../assets/css/material-dashboard.css?v=1.2.0" rel="stylesheet" />
    <!--  CSS for Demo Purpose, don't include it in your project     -->
    <link href="../assets/css/demo.css" rel="stylesheet" />
    <!--     Fonts and icons     -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700,300|Material+Icons" rel='stylesheet'>
    <link rel="stylesheet" type="text/css" href="../assets/css/parsley.css">
</head>
<style type="text/css">
    .fa-stack
    {
        width: 5em;
    }
    .user-footer
    {
        padding-left: 10px;
        padding-right: 10px;
    }
    .#mainNav .navbar-collapse .navbar-sidenav .nav-link-collapse:after
    {
        float: right;
        content: '\f107';
        font-family: 'FontAwesome';
    }
    /********* SIDE NAV BAR ***********/
    a {
    color:#000;
    }

    li {
    list-style:none;
    } 

    /*.panel-default>.panel-heading {
        color: #fff;
        background-color: #00436a;
        border-color: #ddd;
    }*/
    .panel
    {
        margin-bottom: 0px;
    }
    .panel-group .panel+.panel {
        margin-top: 0px;
    }
    .panel-group {
        margin-top: 35px;
    }
    .panel-collapse {
        background-color:transparent;
    }

    .glyphicon { 
    margin-right:10px; 
    }


    ul.list-group {
        margin:0px;
    }

    ul.bulletlist li {
        list-style:disc;
    }


    ul.list-group  li a {
     display:block;
     padding:5px 0px 5px 15px;
     text-decoration:none;
    }

    ul.list-group li {
        border-bottom: 1px dotted rgba(0,0,0,0.2);
    }
        

    /*ul.list-group  li a:hover, ul li a:focus {
     color:#fff;
     background-color: #00436a;
    }
*/
    .panel-title a:hover,
    .panel-title a:active,
    .panel-title a:focus,
    .panel-title .open a:hover,
    .panel-title .open a:active,
    .panel-title .open a:focus
     {
        text-decoration:none;
        color:#3C4858;
    }

    .panel-title>.small, .panel-title>.small>a, .panel-title>a, .panel-title>small, .panel-title>small>a {
            display: block;
    }
    .panel-default > .panel-heading
    {
        padding: 15px 15px;
    }
    @media (min-width: 768px){
    .navbar-collapse.collapse 
        {
        display: block!important;
        height: auto!important;
        padding-bottom: 0;
        overflow: visible!important;
        padding-left:0px; 
    }
    }

    @media (min-width: 992px){
    .menu-hide {
        display: none;
    }

    }
    .menu-hide .panel-default>.panel-heading {
        color: #3C4858;
        background-color: #8e8c8c;
        border-color: #ddd;
    }

    /********** END SIDEBAR *************/

    /********** NAVBAR TOGGLE *************/

    .navbar-toggle .icon-bar {
        background-color: #3C4858;
    }
    .navbar-toggle {
        padding: 11px 10px;
        margin-top: 8px;
        margin-right: 15px;
        margin-bottom: 8px;
        background-color: #a32638;
        border-radius: 0px;
    }

    /********** END NAVBAR TOGGLE *************/
    .sidebar-wrapper
    {
        width: 280px;
    }
    .panel-collapse ul li
    {
        padding-top: 7px;
        padding-bottom: 7px;
    }
    .breadcrumb-item a
    {
        color: #fff;
    }
    
</style>
<body>
    <div class="wrapper">
        <?php require_once('sidebar.php');?>
        <div class="main-panel">
            <?php require_once('header.php');?>
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card" style="height: auto;margin-left: 20px;">
                              <div class="card-header" style="background-color: #c5392a;">
                                <ul class="nav nav-tabs" id="myTab" style="background-color: #c5392a;">
                                    <!-- <li class="<?php if($_GET['type']=='' || $_GET['type']=='ip'){ echo 'active'; } ?>" ><a style="font-size: 1.2em;" data-toggle="tab" href="#ip">IP</a></li> -->

                                    <li class="<?php if($_GET['type']=='' ||  $_GET['type']=='group'){ echo 'active'; } ?>" ><a style="font-size: 1.2em;" data-toggle="tab" href="#group">Group</a></li>
                                    <li class="<?php if($_GET['type']=='user'){ echo 'active'; } ?>" ><a style="font-size: 1.2em;" data-toggle="tab" href="#addUser">Add User</a></li>
                                </ul>
                                   <!--  <h4 class="title active" style="padding-left: 16px;background-color: rgba(236, 241, 245, 0.41); font-weight:500;float: left;padding-right: 20px;">IP</h4>
                                    <h4 class="title" style="padding-left: 16px;font-weight:500;float: left;padding-right: 20px;">Group</h4>
                                    <h4 class="title" style="padding-left: 16px;font-weight:500;">Add User</h4> -->
                                    <ol class="breadcrumb" style="float: right;margin-top: -35px; width: auto;background: #c5392a;">
                                      <li class="breadcrumb-item"><a href="dashboard/"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                                      <li class="breadcrumb-item" style="font-weight: 500;"><a href="#">Access Control</a></li>
                                    </ol>
                                    <!-- <p class="category">Complete your profile</p> -->
                                </div>

                                <div class="tab-content">
                                  <div id="ip" class="tab-pane fade  <?php if($_GET['type']=='ip'){ echo 'in active'; } ?>">
                                      <div class="card-content table-responsive">
                                         <div class="col-md-12" style="background: #fff">
                                            <button style="float:right;font-size:20px;text-transform: capitalize;"
                                             type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#myIp">Add</button>
                                
                                        </div>
                                        <table class="table">
                                        <thead class="text-danger">
                                            <tr>
                                                <th width="10%">Sr No.</th>
                                                <th width="70%">IP Address</th>
                                                <th width="20%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                         <?php 

                                    if($rw_count > 0){
                                          $rw_count = (($pageno - 1) * $resultsPerPage) + 1;
                                          foreach($dataArr['result'] as $value) {
                                    ?>
                                            <tr>
                                                <td><?php echo $rw_count++;?></td>
                                                <td><?php echo $value['ip_address'];?></td>
                                                <td>
                                                    <a class="btn btn-xs btn-danger" data-toggle="modal" onclick="deleteIp('<?php echo $value['id'];?>');" data-target="#deleteIp">
                                                    <span style="font-size: 17px;;" class="fa fa-close"></span> </a>
                                                </td>
                                               
                                            </tr>
                                             <?php } } ?>
                                        </tbody>
                                </table>
                        
                                <div class="modal fade" id="myIp" role="dialog" >
                                        <div class="modal-dialog">
                                        
                                          <!-- Modal content-->
                                          <div class="modal-content" style="width: 150%;">
                                              <div class="card">
                                                <div class="card-header" style="margin-top: -50px;background-color: #c5392a;">
                                                        <button _ngcontent-c4="" style="font-size: 4rem;padding-right: 2rem;" class="close" data-dismiss="modal" type="button">×</button>
                                                        <h3 class="title" style="padding-left: 16px;font-weight:500;">Add IP</h3>
                                                    </div>
                                                <div class="card-content"  ng-app="myApp" ng-controller="brokerCtrl">
                                                <div id="rederror" style="">
                                                  <div id="errmsg" style="padding: 0px;color:red;"></div><br/>
                                                </div>
                                                <div id="greenerror" style="">
                                                        <div id="errmsgsuc" style="padding: 0px;color:green;"></div><br/>
                                                  </div>
                                                        <form id="addip"  name="addip" action="javascript:;" >
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group form-black label-floating is-empty">
                                                                        <label class="control-label">Enter IP</label>
                                                                        <input type="text" maxlength="50" id="ipname" name="ipname" class="form-control" >
                                                                    </div>
                                                                </div>
                                                            </div>
                                                           <button type="submit" class="btn btn-danger pull-right" onclick="submitaipddress()" >Add IP</button>
                                                            <div class="clearfix"></div>
                                                        </form>
                                                    </div>
                                            <!-- <div class="modal-header">
                                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                                              <h4 class="modal-title">Modal Header</h4>
                                            </div>
                                            <div class="modal-body">
                                              <p>Some text in the modal.</p>
                                            </div>
                                            <div class="modal-footer">
                                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                            </div> -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>




                                    <!-- delete modal -->
                                    <div class="modal fade" id="deleteIp" role="dialog" >
                                        <div class="modal-dialog">
                                        
                                            <!-- Modal content-->
                                            <div class="modal-content">
                                             <div id="del_wait" style="display:none;top:24%;width:69px;height:auto;position:absolute;left:40%;padding:2px;">
                                              <img src='../assets/img/LoaderIcon.gif' width="54" height="54" />
                                            </div>
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title">Delete IP !</h4>
                                                </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete this IP?</p>


                                                  <div id="rederror1" style="">
                                                    <div id="errorMsg" style="padding: 0px;color:red;"></div>
                                                    <br/>
                                                  </div>

                                                  <div id="greenerror1" style="">
                                                      <div id="sucessMsg" style="padding: 0px;color:green;"></div>
                                                      <br/>
                                                  </div>

                                            </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                    <button type="button" class="btn btn-danger" id="submit_btn" data-dismiss="modal">Continue</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- end delete modal -->


                                    <section class="content-footer" style="text-align: center; font-weight: bold;">
                                            <ul class="pagination">
                                                 
                                              <?php
                                                
                                                    if ($pageno > 1) {
                                                                print('<li><a href="access-control.php?type=ip&page=1">&laquo;</a></li>');
                                                        }

                                                        if ($pageno > 1) {
                                                            print('<li><a href="access-control.php?type=ip&page=' . ($pageno - 1) . '">Previous</a></li>');
                                                        }
                                                  
                                                      
                                                      if ($pageno == 1) {
                                                             $startLoop = 1;
                                                             $endLoop = ($lastpage < 5) ? $lastpage : 5;
                                                      } else if ($pageno == $lastpage) {
                                                              $startLoop = (($lastpage - 5) < 1) ? 1 : ($lastpage - 5);
                                                              $endLoop = $lastpage;
                                                      } else {
                                                              $startLoop = (($pageno - 3) < 1) ? 1 : ($pageno - 3);
                                                              $endLoop = (($pageno + 3) > $lastpage) ? $lastpage : ($pageno + 3);
                                                      }

                                                    
                                                        for ($i = $startLoop; $i <= $endLoop; $i++) {
                                                            if ($i == $pageno) {
                                                                print('   <li class = "active"><a href = "#">' . $pageno . '</a></li>');
                                                            } else {
                                                                print('<li><a href="access-control.php?type=ip&page=' . $i . '">' . $i . '</a></li>');
                                                                }
                                                              }
                                                        if ($pageno < $lastpage) {
                                                                print('<li><a href="access-control.php?type=ip&page=' . ($pageno + 1) . '">Next</a></li>');
                                                        }

                                                        if ($pageno != $lastpage) {
                                                              print('<li><a href="access-control.php?type=ip&page=' . $lastpage . '">&raquo;</a></li>');
                                                        }
                                                          

                                                         
                                                                             
                                                  ?>   
                                              
                                            </ul>
                                    </section>  
                            </div>

                         </div>
                              <div id="group" class="tab-pane fade <?php if($_GET['type']=='' || $_GET['type']=='group'){ echo 'in active'; } ?>">
                                  <div class="card-content table-responsive">
                                    <?php if ((in_array("write", $myp))) { ?> 
                                         <div class="col-md-12" style="background: #fff">
                                            <a href="addGroupForm.php" style="float:right;font-size:1.3em;text-transform: capitalize;margin-top:35px;" class="ls-modal1 btn btn-danger btn-sm" data-toggle="modal"  data-target="#editGroup"><i class="fa fa-plus"></i> Add</a>
                                           <!--  <button style="float:right;font-size:20px;text-transform: capitalize;"
                                             type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#editGroup">Add</button> -->
                                
                                        </div>
                                    <?php } ?> 
                                        <table class="table">
                                        <thead class="text-danger">
                                            <tr>
                                                <th>Group Name</th>
                                                <th>Group Rights</th>
                                                <th>Rights on modules</th>
                                                <th>Rights on sub modules</th>
                                                <?php if ((in_array("write", $myp))) { ?> 
                                                    <th>Actions</th>
                                                <?php } ?> 
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 

                                  
                                          foreach($groupArr['errMsg'] as $value) {
                                           $group_rights =  explode(',',$value['group_rights']); 
                                           $right_on_module = $value['right_on_module'];
                                           $right_on_submodule = $value['right_on_submodule'];
                                           $right_on_module =  explode(',',$value['right_on_module']); 
                                           $right_on_submodule =  explode(',',$value['right_on_submodule']); 
                                           //printArr($right_on_submodule);
                                           $sr = 0;
                                           $subMod=0;
                                          
                                    ?>
                                            <tr>
                                                <td><?php echo $value['group_name'];?></td>
                                                <td><!-- <?php echo $group_rights[0]==1?"Read":"Write";?></br><?php echo $group_rights[1]==1?"Read":"Write";?> -->
                                                    <?php if($group_rights[0]==1){
                                                            echo 'Read';
                                                        }else if($group_rights[0]==2){
                                                            echo 'Write';
                                                            } 
                                                            echo '<br>';
                                                            if($group_rights[1]==2){
                                                            echo 'Write';
                                                        }else if($group_rights[1]==1){
                                                            echo 'Read';
                                                            }?>
                                                </td>
                                                <td><?php foreach($right_on_module as $value1) { 
                                                     $sr++;
                                                    $module= trim($value1,'"');
                                                    echo  $sr."]". $module."</br>";
                                                    }?>
                                                    
                                                </td>


                                               <td><?php foreach($right_on_submodule as $subModule) { 
                                                     $subMod++;
                                                    $subModule= trim($subModule,'"');
                                                    echo  $subMod."]". $subModule."</br>";
                                                    }?>
                                                    
                                                </td>
                                                <td>
                                                     <?php if ((in_array("write", $myp))) { ?> 
                                                  <!--  <a class="btn btn-xs btn-success" data-toggle="modal" data-target="#editGroup">
                                                    <span style="font-size: 17px;;" class="fa fa-edit"></span> </a> -->
                                                        <?php if($value['group_id']!=1 && $value['group_id']!=2){ ?>
                                                        <a href="addGroupForm.php?id=<?php echo $value['group_id'];?>" class="ls-modal1 btn btn-xs btn-success" data-toggle="modal" data-target="#editGroup"> <span style="font-size: 17px;;" class="fa fa-edit"></span> </a>
                                                        <a class="btn btn-xs btn-danger" data-toggle="modal" data-target="#deleteGroup" onclick="deleteGroup('<?php echo $value['group_id'];?>');">
                                                        <span style="font-size: 17px;;" class="fa fa-close"></span> </a>
                                                        <?php } ?>
                                                     <?php } ?> 
                                                </td>
                                               
                                            </tr>
                                            <?php 

                                    }
                                    ?>
                                        </tbody>
                                </table>
                        
                                <div class="modal fade" id="myGroup" role="dialog" >
                                        <div class="modal-dialog">
                                        
                                          <!-- Modal content-->
                                          <div class="modal-content" style="width: 150%;">
                                              <div class="card">
                                                <div class="card-header" style="margin-top: -50px;background-color: #c5392a;">
                                                        <button _ngcontent-c4="" style="font-size: 4rem;padding-right: 2rem;" class="close" data-dismiss="modal" type="button">×</button>
                                                        <h3 class="title" style="padding-left: 16px;font-weight:500;">Add Group</h3>
                                                    </div>
                                                <div class="card-content"  ng-app="myApp" ng-controller="brokerCtrl">
                                                        <form action="#">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group form-black label-floating is-empty">
                                                                        <label class="control-label">Name</label>
                                                                        <input type="text" class="form-control" required="">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                        <div class="form-group form-black label-floating is-empty">
                                                                            <label class="control-label">Type</label>
                                                                            <input type="text" class="form-control" required="">
                                                                        </div>
                                                                </div>
                                                                <!-- <div class="col-md-6">
                                                                    <div class="form-group form-black label-floating is-empty">
                                                                        <label class="control-label">Email address</label>
                                                                        <input type="email" class="form-control" >
                                                                    </div>
                                                                </div> -->
                                                            </div>
                                                           <button type="submit" class="btn btn-danger pull-right">Add Group</button>
                                                            <div class="clearfix"></div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>




                                    <!-- delete modal -->
                                    <div class="modal fade" id="deleteGroup" role="dialog" >
                                        <div class="modal-dialog">
                                        
                                            <!-- Modal content-->
                                            <div class="modal-content">
                                             <div id="del_group" style="display:none;top:24%;width:69px;height:auto;position:absolute;left:40%;padding:2px;">
                                           <img src='../assets/img/LoaderIcon.gif' width="54" height="54" />
                                         </div>
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title">Delete Group !</h4>
                                                </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete this group?</p>
                                                <div id="rederror1" style="">
                                                      <div id="errmsg_del_group" style="padding: 0px;color:red;"></div>
                                                      <br/>
                                                </div>

                                                    <div id="greenerror1" style="">
                                                        <div id="errmsgsuc_del_group" style="padding: 0px;color:green;"></div>
                                                        <br/>
                                                    </div>
                                            </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                    <button type="button" class="btn btn-danger" id="deleteGroup1" data-dismiss="modal">Continue</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- end delete modal -->
                                    <!-- delete modal -->
                                    <form id="addGroupForm1" name="addGroupForm1" action="javascript:;"  data-parsley-validate="">
                                        <div class="modal fade" id="editGroup" role="dialog" >
                                            
                                        </div>
                                    </form>
                                    <!-- end delete modal -->



                                   <!--  <section class="content-footer" style="text-align: center; font-weight: bold;">
                                            <ul class="pagination">
                                                    <li><a href="#">&laquo;</a></li>
                                                    <li><a href="#">Previous</a></li>
                                                    <li class="active"><a href="#">1</a></li>
                                                    <li><a href="#">2</a></li>
                                                    <li><a href="#">3</a></li>
                                                    <li><a href="#">4</a></li>
                                                    <li><a href="#">5</a></li>
                                                    <li><a href="#">Next</a></li>
                                                    <li><a href="#">&raquo;</a></li>
                                            </ul>
                                    </section>   -->
                            </div>
                        </div>
                                  <div id="addUser" class="tab-pane fade <?php if($_GET['type']=='user'){ echo 'in active'; } ?>">
                                    <div class="card-content table-responsive">
                                     <?php if ((in_array("write", $myp))) { ?> 
                                         <div class="col-md-12" style="background: #fff">
                                            <!-- <button style="float:right;font-size:20px;text-transform: capitalize;"
                                             type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#myAddUser">Add</button> -->
                                               <a href="accessControlForm.php" style="float:right;font-size:1.3em;text-transform: capitalize;margin-top:35px;" class="ls-modal btn btn-danger btn-sm" data-toggle="modal"  data-target="#myAddUser"><i class="fa fa-plus"></i> Add</a>
                                
                                        </div>
                                    <?php } ?> 
                                        <table class="table">
                                        <thead class="text-danger">
                                            <tr>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Designation</th>
                                                <th>Department</th>
                                                <th>Group</th>
                                                <th>Comments</th>
                                                <th>Mobile Number</th>
                                                <th>Email Address</th>
                                                <?php if ((in_array("write", $myp))) { ?>
                                                    <th>Actions</th>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 

                                          //sexit("hereee");
                                   if($user_rw_count1 > 0){
                                          $user_rw_count1 = (($pageNum - 1) * $resultsPerPage) + 1;

                                          foreach($userArr1['result'] as $value1) {
                                            //printArr($value1);

                                    ?>
                                      
                                            <tr>
                                                <td><?php echo $value1['firstname'];?></td>
                                                <td><?php echo $value1['lastname'];?></td>
                                                <td><?php echo $value1['designation'];?></td>
                                                <td><?php echo $value1['department'];?></td>
                                                <td><?php echo $value1['groups'];?></td>
                                                <td><?php echo $value1['comments'];?></td>
                                                <td><?php echo $value1['phone'];?></td>
                                                <td><?php echo $value1['email'];?></td>
                                                <td>
                                                 <?php if ((in_array("write", $myp))) { ?> 
                                                    <a href="accessControlForm.php?id=<?php echo $value1['user_id'];?>" class="ls-modal btn btn-xs btn-success" data-toggle="modal" data-target="#myAddUser">
                                                    <span style="font-size: 17px;;" class="fa fa-edit"></span> </a>
                                                    <a  class="btn btn-xs btn-danger" data-toggle="modal" data-target="#deleteAddUser" onclick="deleteUser('<?php echo $value1['user_id'];?>');">
                                                    <span style="font-size: 17px;;" class="fa fa-close"></span> </a>
                                                 <?php } ?> 
                                                </td>
                                               
                                            </tr>
                                            <?php } }?>
                                        </tbody>
                                </table>
                              <form id="addUserForm" name="addUserForm" action="javascript:;"  data-parsley-validate="">
                                <div class="modal fade" id="myAddUser" role="dialog" >
                                        
                                    </div>
                                    </form>



                                    <!-- delete modal -->
                                    <div class="modal fade" id="deleteAddUser" role="dialog" >
                                        <div class="modal-dialog">
                                        <div id="del_user" style="display:block;top:24%;width:69px;height:auto;position:absolute;left:40%;padding:2px;">
                                              <img src='../assets/img/LoaderIcon.gif' width="54" height="54" />
                                            </div>
                                            <!-- Modal content-->
                                            <div class="modal-content">
                                            
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title">Delete User !</h4>
                                                </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete this User?</p>
                                                <div id="rederror_user" style="">
                                                      <div id="errmsg_del_user" style="padding: 0px;color:red;"></div>
                                                      <br/>
                                                </div>

                                                    <div id="greenerror_user" style="">
                                                        <div id="errmsgsuc_del_user" style="padding: 0px;color:green;"></div>
                                                        <br/>
                                                    </div>
                                                 
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                    <button type="button" class="btn btn-danger" id="deleteBtn" data-dismiss="modal">Continue</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- end delete modal -->
                                    <!-- delete modal -->
                                    <div class="modal fade" id="editAddUser" role="dialog" >
                                        <div class="modal-dialog">
                                        
                                            <div class="modal-content" style="width: 150%;">
                                              <div class="card">
                                                <div class="card-header" style="margin-top: -50px;background-color: #c5392a;">
                                                        <button _ngcontent-c4="" style="font-size: 4rem;padding-right: 2rem;" class="close" data-dismiss="modal" type="button">×</button>
                                                        <h3 class="title" style="padding-left: 16px;font-weight:500;">Add User</h3>
                                                    </div>
                                                <div class="card-content"  ng-app="myApp" ng-controller="brokerCtrl">
                                                        <form action="#">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group form-black label-floating is-empty">
                                                                        <label class="control-label">First Name</label>
                                                                        <input type="text" class="form-control" required="" id="first_name" name="first_name">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                        <div class="form-group form-black label-floating is-empty">
                                                                            <label class="control-label">Last Name</label>
                                                                            <input type="text" class="form-control" required="" id="last_name" name="last_name">
                                                                        </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group form-black label-floating is-empty">
                                                                        <label class="control-label">Mobile Number</label>
                                                                        <input type="text" class="form-control" required="" id="mobile_number" name="mobile_number">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                        <div class="form-group form-black label-floating is-empty">
                                                                            <label class="control-label">Email</label>
                                                                            <input type="text" class="form-control" required="" id="email" name="email">
                                                                        </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group form-black label-floating is-empty">
                                                                        <label class="control-label">Designation</label>
                                                                        <input type="text" class="form-control" required="" id="designation" name="designation">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                        <div class="form-group form-black label-floating is-empty">
                                                                            <label class="control-label">Department</label>
                                                                            <input type="text" class="form-control" required="" id="department" name="department">
                                                                        </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                        <div class="form-group form-black label-floating is-empty">
                                                                            <label class="control-label">Group</label>
                                                                            <input type="text" class="form-control" required="" id="group" name="group">
                                                                        </div>
                                                                </div>
                                                            </div>
                                                          <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <div class="form-group form-black label-floating is-empty">
                                                                        <label class="control-label"> Comment Down Here.</label>
                                                                        <textarea class="form-control" rows="3" id="comments" name="comments"></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                         </div>
                                                           <button type="submit" class="btn btn-danger pull-right">Add User</button>
                                                            <div class="clearfix"></div>
                                                        </form>
                                                    </div>
                                           
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- end delete modal -->


</div>
                                    <section class="content-footer" style="text-align: center; font-weight: bold;">
                                            <ul class="pagination">
                                                   <?php
                                                //echo $pageNum;
                                                    if ($pageNum > 1) {
                                                                print('<li><a href="access-control.php?type=user&page_no=1">&laquo;</a></li>');
                                                        }

                                                        if ($pageNum > 1) {
                                                            print('<li><a href="access-control.php?type=user&page_no=' . ($pageNum - 1) . '">Previous</a></li>');
                                                        }
                                                  
                                                      
                                                      if ($pageNum == 1) {
                                                             $startLoop1 = 1;
                                                             $endLoop1 = ($lastpageNum < 5) ? $lastpageNum : 5;
                                                      } else if ($pageNum == $lastpageNum) {
                                                              $startLoop1 = (($lastpageNum - 5) < 1) ? 1 : ($lastpageNum - 5);
                                                              $endLoop1 = $lastpageNum;
                                                      } else {
                                                              $startLoop1 = (($pageNum - 3) < 1) ? 1 : ($pageNum - 3);
                                                              $endLoop1 = (($pageNum + 3) > $lastpageNum) ? $lastpageNum : ($pageNum + 3);
                                                      }

                                                    
                                                        for ($j = $startLoop1; $j <= $endLoop1; $j++) {
                                                            if ($j == $pageNum) {
                                                                print('   <li class = "active"><a href = "#">' . $pageNum . '</a></li>');
                                                            } else {
                                                                print('<li><a href="access-control.php?type=user&page_no=' . $j . '">' . $j . '</a></li>');
                                                                }
                                                              }
                                                        if ($pageNum < $lastpageNum) {
                                                                print('<li><a href="access-control.php?type=user&page_no=' . ($pageNum + 1) . '">Next</a></li>');
                                                        }

                                                        if ($pageNum != $lastpageNum) {
                                                              print('<li><a href="access-control.php?type=user&page_no=' . $lastpageNum . '">&raquo;</a></li>');
                                                        }
                                                          

                                                         
                                                                             
                                                  ?>   
                                              
                                            </ul>
                                    </section>  
                            
                                  </div>
                                </div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="footer">
               <!--  <div class="container-fluid">
                    <nav class="pull-left">
                        <ul>
                            <li>
                                <a href="#">
                                    Home
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    Company
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    Portfolio
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    Blog
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <p class="copyright pull-right">
                        &copy;
                        <script>
                            document.write(new Date().getFullYear())
                        </script>
                        <a href="http://www.creative-tim.com">Creative Tim</a>, made with love for a better web
                    </p>
                </div> -->
            </footer>
        </div>
    </div>
</body>
<!--   Core JS Files   -->
<script src="../assets/js/jquery-3.2.1.min.js" type="text/javascript"></script>
<script src="../assets/js/bootstrap.min.js" type="text/javascript"></script>
<script src="../assets/js/material.min.js" type="text/javascript"></script>
<!--  Charts Plugin -->
<script src="../assets/js/chartist.min.js"></script>
<!--  Dynamic Elements plugin -->
<script src="../assets/js/arrive.min.js"></script>
<!--  PerfectScrollbar Library -->
<script src="../assets/js/perfect-scrollbar.jquery.min.js"></script>
<!--  Notifications Plugin    -->
<script src="../assets/js/bootstrap-notify.js"></script>
<!--  Google Maps Plugin    -->
<!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script> -->
<!-- Material Dashboard javascript methods -->
<script src="../assets/js/material-dashboard.js?v=1.2.0"></script>
<!-- Material Dashboard DEMO methods, don't include it in your project! -->
<!-- <script src="../assets/js/demo.js"></script> -->
<script src="../assets/js/parsley.js"></script>
<script type="text/javascript">

function addUser(){
  //if(typeof document.forms[0] !== 'undefined'){
    /*****************working data picker***************************/
    //$('#start_date1').datepicker();
     //console.log("here comes67679");
      $('form#addUserForm').parsley().on('field:validated', function() {  
      var ok = $('.parsley-error').length === 0;
      $('.bs-callout-info').toggleClass('hidden', !ok);
      $('.bs-callout-warning').toggleClass('hidden', ok);
    })
   .on('form:submit', function() {
      var formData = new FormData($('#addUserForm')[0]);
       formData.append("action","createUser");
       $.ajax({
          type: "POST",
          dataType: "json",
          url: "../controller/adminController.php",
          data: formData,
          contentType: false,
          cache: false, 
          processData: false,
        
          success: function(user) {
            console.log(user);
              if(user["errCode"]){
                  if(user["errCode"]!="-1")
                           {
                            
                              document.getElementById("errmsg_user").innerHTML =user['errMsg'];
                              document.getElementById("errmsgsuc_user").innerHTML = "";
                              setTimeout(function(){ window.location.reload(); }, 2000);
                           }
                           else
                           {
                              
                              document.getElementById("errmsgsuc_user").innerHTML =user['errMsg'];
                              document.getElementById("errmsg_user").innerHTML = "";
                              setTimeout(function(){ window.location.reload(); }, 2000);
                           }
              }
          },
          error: function() {
                   alert("Failed to add user please try again.");
              }
          });
    return false;

   });
     
      $(document).ajaxStart(function(){
         $("#wait").css("display", "block");

     });
     $(document).ajaxComplete(function(){
         $("#wait").css("display", "none");
     });


}

function submitaipddress(){
    var ipaddress= document.forms["addip"]["ipname"].value;

    $.ajax({
        type: "POST",
        dataType: "json",
        url: "../controller/adminController.php",
        data: {
            ipaddress: ipaddress,type:"addnewip"
        },
        success: function(user) {


            if(user["errCode"]){
                var msg=user["errMsg"];
                if(user["errCode"]!="-1")
                {
                    document.getElementById("errmsg").innerHTML = msg;
                    document.getElementById("errmsgsuc").innerHTML = "";
                   // setTimeout(function(){ window.location.reload(); }, 2000);
                }
                else
                {
                    document.getElementById("errmsg").innerHTML = "";
                    document.getElementById("errmsgsuc").innerHTML = msg;
                    document.getElementById("addip").reset();
                    setTimeout(function(){ window.location.reload(); }, 2000);
                }
            }
        },
        error: function() {
            bootbox.alert("<h4>Failed to add new admin</h4>", function() {});
        }
    });
    return false;
}

function deleteIp(id){

  //$(document).on("click", "#submit_btn", function(event){
    $( "#submit_btn" ).click(function() {
    ipId = id;
    
    $.ajax({
          type: "POST",
          dataType: "json",
          url: "../controller/adminController.php",
          data: { id: ipId,action:"delete" },
          success: function(user) {
              if(user["errCode"]){
                  if(user["errCode"]!="-1")
                           {
                            //alert(user['errMsg']);
                              document.getElementById("errorMsg").innerHTML =user['errMsg'];
                              document.getElementById("sucessMsg").innerHTML = "";
                              setTimeout(function(){ window.location.reload(); }, 2000);
                           }
                           else
                           {
                              //alert(user['errMsg']);
                              document.getElementById("sucessMsg").innerHTML =user['errMsg'];
                              document.getElementById("errorMsg").innerHTML = "";
                              setTimeout(function(){ window.location.reload(); }, 2000);
                           }
              }
          },
          error: function() {
                   alert("Failed to delete ip please try again.");
              }
          });
    return false;
  });

    $(document).ajaxStart(function(){
         $("#del_wait").css("display", "block");

     });
     $(document).ajaxComplete(function(){
         $("#del_wait").css("display", "none");
     });

}

function deleteUser(id){

  //$(document).on("click", "#submit_btn", function(event){
    $( "#deleteBtn" ).click(function() {
    userId = id;
    
    $.ajax({
          type: "POST",
          dataType: "json",
          url: "../controller/adminController.php",
          data: { id: userId,action:"userDelete" },
          success: function(user) {
              if(user["errCode"]){
                  if(user["errCode"]!="-1")
                           {
                            //alert(user['errMsg']);
                              document.getElementById("errmsgsuc_del_user").innerHTML =user['errMsg'];
                              document.getElementById("errmsgsuc_del_user").innerHTML = "";
                              setTimeout(function(){ window.location.reload(); }, 2000);
                           }
                           else
                           {
                              //alert(user['errMsg']);
                              document.getElementById("errmsgsuc_del_user").innerHTML =user['errMsg'];
                              document.getElementById("errmsgsuc_del_user").innerHTML = "";
                              setTimeout(function(){ window.location.reload(); }, 2000);
                           }
              }
          },
          error: function() {
                   alert("Failed to delete user please try again.");
              }
          });
    return false;
  });

    $(document).ajaxStart(function(){
         $("#del_user").css("display", "block");

     });
     $(document).ajaxComplete(function(){
         $("#del_user").css("display", "none");
     });

}
function updateUser(){
        $('form#addUserForm').parsley().on('field:validated', function() {  
        var ok = $('.parsley-error').length === 0;
        $('.bs-callout-info').toggleClass('hidden', !ok);
        $('.bs-callout-warning').toggleClass('hidden', ok);
    })
    .on('form:submit', function() {

      var formData = new FormData($('#addUserForm')[0]);
       formData.append("action","updateUser");
       $.ajax({
          type: "POST",
          dataType: "json",
          url: "../controller/adminController.php",
          data: formData,
          contentType: false,
          cache: false,
          contentType: false,
          processData: false,
         
          success: function(user) {
            console.log(user);
              if(user["errCode"]){
                  if(user["errCode"]!="-1")
                           {
                              document.getElementById("errmsg_user").innerHTML =user['errMsg'];
                              document.getElementById("errmsgsuc_user").innerHTML = "";
                              setTimeout(function(){ window.location.reload(); }, 2000);
                           }
                           else
                           {
                              document.getElementById("errmsgsuc_user").innerHTML =user['errMsg'];
                              document.getElementById("errmsg_user").innerHTML = "";
                              setTimeout(function(){ window.location.reload(); }, 2000);
                             
                           }
              }
          },
          error: function() {
                   alert("Failed to update user, please try again.");
              }
          });
       return false;
   });
   
    $(document).ajaxStart(function(){
       $("#wait_group").css("display", "block");

    });
    $(document).ajaxComplete(function(){
       $("#wait_group").css("display", "none");
    });

}

function updateGroup(){
    $('form#addGroupForm1').parsley().on('field:validated', function() {  
      var ok = $('.parsley-error').length === 0;
      $('.bs-callout-info').toggleClass('hidden', !ok);
      $('.bs-callout-warning').toggleClass('hidden', ok);
    })
     .on('form:submit', function() {
      var formData = new FormData($('#addGroupForm1')[0]);
       formData.append("action","updateGroup");
       $.ajax({
          type: "POST",
          dataType: "json",
          url: "../controller/adminController.php",
          data: formData,
          contentType: false,
          cache: false, 
          processData: false,
        
          success: function(user) {
            console.log(user);
              if(user["errCode"]){
                  if(user["errCode"]!="-1")
                           {
                            
                              document.getElementById("errmsg_group").innerHTML =user['errMsg'];
                              document.getElementById("errmsgsuc_group").innerHTML = "";
                               setTimeout(function(){ window.location.reload(); }, 2000);
                           }
                           else
                           {
                              
                              document.getElementById("errmsgsuc_group").innerHTML =user['errMsg'];
                              document.getElementById("errmsg_group").innerHTML = "";
                              setTimeout(function(){ window.location.reload(); }, 2000);
                           }
              }
          },
          error: function() {
                   alert("Failed to add group please try again.");
              }
          });
    return false;

   });

      $(document).ajaxStart(function(){
         $("#add_group").css("display", "block");

     });
     $(document).ajaxComplete(function(){
         $("#add_group").css("display", "none");
     });
  }

function submitUserdatagroup(){
    
    $('form#addGroupForm1').parsley().on('field:validated', function() {  
          var ok = $('.parsley-error').length === 0;
          $('.bs-callout-info').toggleClass('hidden', !ok);
          $('.bs-callout-warning').toggleClass('hidden', ok);
        })
     .on('form:submit', function() {
      var formData = new FormData($('#addGroupForm1')[0]);
       formData.append("action","addgroup");
       $.ajax({
          type: "POST",
          dataType: "json",
          url: "../controller/adminController.php",
          data: formData,
          contentType: false,
          cache: false, 
          processData: false,
        
          success: function(user) {
            console.log(user);
              if(user["errCode"]){
                  if(user["errCode"]!="-1")
                           {
                            
                              document.getElementById("errmsg_group").innerHTML =user['errMsg'];
                              document.getElementById("errmsgsuc_group").innerHTML = "";
                               setTimeout(function(){ window.location.reload(); }, 2000);
                           }
                           else
                           {
                              
                              document.getElementById("errmsgsuc_group").innerHTML =user['errMsg'];
                              document.getElementById("errmsg_group").innerHTML = "";
                              setTimeout(function(){ window.location.reload(); }, 2000);
                           }
              }
          },
          error: function() {
                   alert("Failed to add group please try again.");
              }
          });
    return false;

   });

      $(document).ajaxStart(function(){
         $("#add_group").css("display", "block");

     });
     $(document).ajaxComplete(function(){
         $("#add_group").css("display", "none");
     });


}

function deleteGroup(id){

  //$(document).on("click", "#submit_btn", function(event){
    $( "#deleteGroup1" ).click(function() {
    groupId = id;
    
    $.ajax({
          type: "POST",
          dataType: "json",
          url: "../controller/adminController.php",
          data: { id: groupId,action:"deleteGroup" },
          success: function(user) {
              if(user["errCode"]){
                  if(user["errCode"]!="-1")
                           {
                            //alert(user['errMsg']);
                              document.getElementById("errmsg_del_group").innerHTML =user['errMsg'];
                              document.getElementById("errmsgsuc_del_group").innerHTML = "";
                              
                              setTimeout(function(){ window.location.reload(); }, 2000);
                           }
                           else
                           {
                              //alert(user['errMsg']);
                              document.getElementById("errmsgsuc_del_group").innerHTML =user['errMsg'];
                              document.getElementById("errmsg_del_group").innerHTML = "";
                             
                              setTimeout(function(){ window.location.reload(); }, 2000);
                           }
              }
          },
          error: function() {
                   alert("Failed to delete group please try again.");
              }
          });
    return false;
  });

    $(document).ajaxStart(function(){
         $("#del_group").css("display", "block");

     });
     $(document).ajaxComplete(function(){
         $("#del_group").css("display", "none");
     });

}


    $('.ls-modal').on('click', function(e){
        e.preventDefault();
        $('#myAddUser').load($(this).attr('href'));
    });

    $('.ls-modal1').on('click', function(e){
        e.preventDefault();
        $('#editGroup').load($(this).attr('href'));


    });
    $(document).ready(function(){
        $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
            localStorage.setItem('activeTab', $(e.target).attr('href'));
        });
        var activeTab = localStorage.getItem('activeTab');
        if(activeTab){
            $('#myTab a[href="' + activeTab + '"]').tab('show');
        }
    });

</script>
</html>