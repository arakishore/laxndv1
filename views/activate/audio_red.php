<?php
/*
File - views/upload-mis-files.php
view file that shows the form to upload MIS and Trial Balance excel files. Form submits to controller/mis/upload/index.php
The error/success messages are also displayed here after form submission
*/

//Manage distributors view page
session_start();

//prepare for request
//include necessary helpers
require_once('../../config/config.php');

//check if session is active
$sessionCheck = checkSession();

//include some more necessary helpers
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/logs/logsProcessor.php');
require_once(__ROOT__.'/config/logs/logsCoreFunctions.php');
require_once(__ROOT__.'/vendor/phpoffice/phpspreadsheet/src/Bootstrap.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

//include necessary models
require_once(__ROOT__.'/model/user/userModel.php');
require_once(__ROOT__.'/model/distributor/distributorModel.php');


//Connection With Database
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
//error connecting to DB
$returnArr["errCode"] = 1;
$returnArr["errMsg"] = getErrMsg(1).$conn["errMsg"];
} else {
$conn = $conn["errMsg"];

$returnArr = array();

//get the user info
$email = $_SESSION['userEmail'];

//initialize logs
$logsProcessor = new logsProcessor();
$initLogs = initializeJsonLogs($email);
// $logFilePath = $logStorePaths["mis"];
// $logFileName="uploadMISView.json";

// $logMsg = "View MIS process start.";
// $logData['step1']["data"] = "1. {$logMsg}";

// $logMsg = "Database connection successful.";
// $logData["step2"]["data"] = "2. {$logMsg}";

// $logMsg = "Attempting to get user info.";
// $logData["step3"]["data"] = "3. {$logMsg}";

$userSearchArr = array('email'=>$email);
$fieldsStr = "email, status, image, `groups`, rights, firstname, lastname";
$userInfo = getUserInfo($userSearchArr, $fieldsStr, $conn);
if (!noError($userInfo)) {
    //error fetching user info
    $logMsg = "Couldn't fetch user info: {$userInfo["errMsg"]}";
    $logData["step3.1"]["data"] = "3.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 5;
    $returnArr["errMsg"] = getErrMsg(5).": Error fetching user details.";
} else {
    //check if user not found
    $userInfo = $userInfo["errMsg"];
    if (empty($userInfo)) {
        //user not found
        $logMsg = "User not found: {$email}";
        $logData["step3.1"]["data"] = "3.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5).": This URL is invalid or expired.";
    } else {
        //check if user is active
        //first get the user email
        $email = array_keys($userInfo);
        $email = $email[0];
        if ($userInfo[$email]["status"]!=1) {
            //user not active
            $logMsg = "User not active: {$email}";
            $logData["step3.1"]["data"] = "3.1 {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5).": This URL is invalid or expired.";
        } else {

            $selectedMisDate = isset($_POST["selected_date"])?cleanXSS($_POST["selected_date"]):"";
            if (isset($_GET["selected_date"]) && !empty($_GET["selected_date"])) {
                $selectedMisDate = cleanQueryParameter($conn, cleanXSS($_GET["selected_date"]));
            }

            //Store date, month, year separatly to make the path.
            $time  = strtotime($selectedMisDate);
            $day   = date('d',$time);
            $month = date('m',$time);
            $year  = date('Y',$time);
        
            //user is found and is active. Do nothing
            $logMsg = "user is found and is active. Do nothing: {$email}";
            $logData["step3.1"]["data"] = "3.1 {$logMsg}";
            // $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $returnArr["errCode"] = 1;
            $returnArr["errMsg"] = "";
        } //close checking if user is active
    } // close checking if user is found
} // close user info
} //close db conn


?>
<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8" />
<link rel="icon" type="image/png" href="<?php echo $rootUrl; ?>assets/img/nirvana_favicon.png" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<title>
    <?php echo APPNAME; ?>
</title>
<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
<meta name="viewport" content="width=device-width" />
<link href="<?php echo $rootUrl; ?>assets/css/bootstrap.min.css" rel="stylesheet" />
<link href="<?php echo $rootUrl; ?>assets/css/material-dashboard.css?v=1.2.0" rel="stylesheet" />
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
<link href='https://fonts.googleapis.com/css?family=Work+Sans:400,300,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/style.css">
<script src="<?php echo $rootUrl; ?>assets/js/jquery.min.js" type="text/javascript"></script>

</head>
  <style>

  </style>
  
  <body>

    

     
 <!--Loading new page-->
 <div class="header" id="youtube1" >
  

       <div class="row" style="padding-top:3vh;">
  <div class="col-lg-1">
  <div class="form-group" style="margin-left:2vw; margin-bottom: 0px;">
              <button type="button"   data-dismiss="modal" style="float:left; padding:5px; font-size:15px;">
            <a style="color:white;" href="../validate/"><i style="font-size:20px;" class="fa fa-arrow-left"></i>
                </a></button>
</div>
  </div>
  <div class="col-lg-3">
  <div class="form-group" >
      
      <h4 class="modal2-title" >Validate Report Youtube Audio Red</h4>
      
      </div>
  </div>
  <div class="col-lg-2">
  <div class="form-group" style="margin-left:0vw; ">
           <!-- <div class='padit'>
      
           <select id='reportMonths1' onchange='loadReport()'><option value='-1'>
             Choose a date

           </option>
      
           <option  value=''></option>
           <option  value=''></option>
           <option  value=''></option>
           <option  value=''></option>
           <option  value=''></option>
           <option  value=''></option>
         
           </optgroup>
           </select>
           </div> -->
           
       </div>
  </div>
</div>
</div>


           <!-- choose field drowpdoun-->
                <div class="col-md-12" id="head">
                  <div class="col-md-2">
                  <!-- choose field search drop down -->
                      <div class="head2" style="margin-left:1vw; margin-top:7vh;">
                      <select name="userName" id="userName" class="form-control"> 
                                <option value="">Choose a field</option>
                                  <option value="Video_id">Video ID</option>
                                  <option value="video_title" selected="selected">Video Title</option>
                                  <option value="asset_channel">Channel</option>
                                  <option value="uploader2">Uploader</option>
                                  <option value="uploader">Content Owner</option>
                                  <option value="content_type">Content Type</option>
                                  <option value="asset_id">Asset ID</option>
                      </select>
                    </div>
                 </div>
                 <!-- search button -->
                  <!-- <div class="col-md-2">
                      <div class="" style="margin-left:1vw; margin-top:7vh;">
                      <div class="md-form active-purple-2 mb-3">
                        
                  <input class="form-control" type="text" placeholder="Search Keyword" aria-label="Search">
                 
                  </div>
                      </div>
                  </div> -->

                  <!-- <div class="container my-5"> -->

    <div class="col-md-2 " style="margin-left:1vw; margin-top:4vh;">
      <div class="form-group">
        <input type="text" id="name" class="form-control" required>
        <label class="form-control-placeholder" for="name">Search Keyword</label>
      </div>
    
    </div>

<!-- </div> -->

                
    <!-- end Status -->
      <!-- search button -->
        <div class="" style="margin-left:1vw; margin-top:6vh;">
            <button type="submit" class="btn btn-success fa fa-search">
            </button>
            <td colspan="7" align="center" style="padding-top:3px; ">
        <a id="but12" class="btn btn-success" data-toggle="collapse" href="#collapseExample5" 
         role="button" aria-expanded="false" aria-controls="collapseExample" > Save Entire Report</a>
    
        <a class="btn btn-success" data-toggle="collapse" href="#collapseExample5" 
         role="button" aria-expanded="false" aria-controls="collapseExample" style="margin-left:0.5vw;">Save Selected Videos Only</a>
        </td>
        </div>
    <!-- end search button -->

    
 <!-- Table Header + Save Button -->

        </div>
</div>


<!--main table page-->


 <div id="table-scroll" class="table-scroll">
  <div id="faux-table" class="faux-table" aria="hidden"></div>
  <div class="table-wrap">
    <table id="main-table" class="main-table">
      <thead class="head1" style="background-color:#03A9F4;">
        <tr>
          <th >
      
          </th>
          <th > </th>
          <th > <button class="btn" type="submit" style="background-color:#9e9e9e; margin-top:2vh;">Save</button>
                 </th>
          <th>
          <button class="btn" type="submit" style="background-color:#9e9e9e; margin-top:2vh;">
          <!-- <div class="button" style="margin-top:15px;" > -->
          <select name="userName" id="userName" class="form-control1">
             <option value="" style="color:#fff;">Select Owner</option>
             <option value=""></option>
            <option value=""></option>
            <option value=""></option>
             <option value=""></option>
            <option value=""></option>
            <option value=""></option>
            <option value=""></option>

          </select>
          </button>
          </th>
          <th >

         
          </th>
          <th scope="col" >
      
        </th>
          <th scope="col" ></th>
          <th scope="col" ></th>
          <th scope="col"></th>
        </tr>
        <tr>
          <th scope="col">
          <input type="checkbox" id="checkAll">
          </th>
          <th  scope="col">AA</th>
          <th scope="col">Video id</th>
          <th scope="col">Video title</th>
          <th scope="col">Channel</th>
          <th scope="col">CMS</th>
          <th scope="col">Uploader</th>
          <th scope="col">Content Type</th>
          <th scope="col">Content Owner</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th>
          <input type="checkbox" id="checkAll">
          </th>
          <td>
          <span class="big_bold "  id="checkAll" style="color: green">Y</span></td>
           </td>
          <td>
          <a href="" class="commonLinks" target="_blank">0GUO9dUB8Kg</a>
          </td>
          <td> MINHA MOTO RACING SHINERAY MODIFICADA  MINHA MOTO RACING SHINERAY MODIFICADA MINHA </td>
          <td>Márcio Maia</td>
          <td>Nirvanadigital2</td>
          <td>marcmamp1 </td>
          <td>UGC</td>
          <td>
          <input onkeyup="suggestUser(event)" id="userBox_1" type="text" value="" autocomplete="off" 
            name="contentOwner[0GUO9dUB8Kg]" videoid="0GUO9dUB8Kg" style="width:150px" onfocus="showUsersOptions(this);">
          </td>
        </tr>
        <tr>
          <th>
          <input type="checkbox" id="checkAll">
          </th>
          <td>
          <span class="big_bold " style="color: green">Y</span></td>
           </td>
          <td>
          <a href="" class="commonLinks" target="_blank">0GUO9dUB8Kg</a>
          </td>
          <td>   crazy twins have a morel mushroom story to share...  </td>
          <td>Márcio Maia</td>
          <td>Nirvanadigital2</td>
          <td>marcmamp1 </td>
          <td>UGC</td>
          <td>
          <input onkeyup="suggestUser(event)" id="userBox_1" type="text" value="" autocomplete="off" 
            name="contentOwner[0GUO9dUB8Kg]" videoid="0GUO9dUB8Kg" style="width:150px" onfocus="showUsersOptions(this);">
          </td>
        </tr>
        <tr>
          <th>
          <input type="checkbox" id="checkAll">
          </th>
          <td>
          <span class="big_bold " style="color: green">Y</span></td>
           </td>
          <td>
          <a href="" class="commonLinks" target="_blank">0GUO9dUB8Kg</a>
          </td>
          <td> MINHA MOTO RACING SHINERAY MODIFICADA</td>
          <td>Márcio Maia</td>
          <td>Nirvanadigital2</td>
          <td>marcmamp1 </td>
          <td>UGC</td>
          <td>
         
          <input onkeyup="suggestUser(event)" id="userBox_6" type="text" value="" autocomplete="off"
           name="contentOwner[0HTXWOfaHOU]" videoId="0HTXWOfaHOU" style="width:150px" onfocus="showUsersOptions(this);">
                                                            
          </td>
        </tr>
        <tr>
          <th>
          <input type="checkbox" id="checkAll">
          </th>
          <td>
          <span class="big_bold " style="color: green">Y</span></td>
           </td>
          <td>
          <a href="" class="commonLinks" target="_blank">0GUO9dUB8Kg</a>
          </td>
          <td> MINHA MOTO RACING SHINERAY MODIFICADA</td>
          <td>Márcio Maia</td>
          <td>Nirvanadigital2</td>
          <td>marcmamp1 </td>
          <td>UGC</td>
          <td>
          <input onkeyup="suggestUser(event)" id="userBox_1" type="text" value="" autocomplete="off" 
            name="contentOwner[0GUO9dUB8Kg]" videoid="0GUO9dUB8Kg" style="width:150px" onfocus="showUsersOptions(this);">
          </td>
        </tr>
        <tr>
          <th>
          <input type="checkbox" id="checkAll">
          </th>
          <td>
          <span class="big_bold " style="color: green">Y</span></td>
           </td>
          <td>
          <a href="" class="commonLinks" target="_blank">0GUO9dUB8Kg</a>
          </td>
          <td> MINHA MOTO RACING SHINERAY MODIFICADA</td>
          <td>Márcio Maia</td>
          <td>Nirvanadigital2</td>
          <td>marcmamp1 </td>
          <td>UGC</td>
          <td>
          <input onkeyup="suggestUser(event)" id="userBox_1" type="text" value="" autocomplete="off" 
            name="contentOwner[0GUO9dUB8Kg]" videoid="0GUO9dUB8Kg" style="width:150px" onfocus="showUsersOptions(this);">
          </td>
        </tr>
        <tr>
          <th>
          <input type="checkbox" id="checkAll">
          </th>
          <td>
          <span class="big_bold " style="color: green">Y</span></td>
           </td>
          <td>
          <a href="" class="commonLinks" target="_blank">0GUO9dUB8Kg</a>
          </td>
          <td> MINHA MOTO RACING SHINERAY MODIFICADA</td>
          <td>Márcio Maia</td>
          <td>Nirvanadigital2</td>
          <td>marcmamp1 </td>
          <td>UGC</td>
          <td>
          <input onkeyup="suggestUser(event)" id="userBox_1" type="text" value="" autocomplete="off" 
            name="contentOwner[0GUO9dUB8Kg]" videoid="0GUO9dUB8Kg" style="width:150px" onfocus="showUsersOptions(this);">
          </td>
        </tr>
        <tr>
          <th>
          <input type="checkbox" id="checkAll">
          </th>
          <td>
          <span class="big_bold " style="color: green">Y</span></td>
           </td>
          <td>
          <a href="" class="commonLinks" target="_blank">0GUO9dUB8Kg</a>
          </td>
          <td> MINHA MOTO RACING SHINERAY MODIFICADA</td>
          <td>Márcio Maia</td>
          <td>Nirvanadigital2</td>
          <td>marcmamp1 </td>
          <td>UGC</td>
          <td>
          <input onkeyup="suggestUser(event)" id="userBox_1" type="text" value="" autocomplete="off" 
            name="contentOwner[0GUO9dUB8Kg]" videoid="0GUO9dUB8Kg" style="width:150px" onfocus="showUsersOptions(this);">
          </td>
        </tr>
        <tr>
          <th>
          <input type="checkbox" id="checkAll">
          </th>
          <td>
          <span class="big_bold " style="color: green">Y</span></td>
           </td>
          <td>
          <a href="" class="commonLinks" target="_blank">0GUO9dUB8Kg</a>
          </td>
          <td> MINHA MOTO RACING SHINERAY MODIFICADA</td>
          <td>Márcio Maia</td>
          <td>Nirvanadigital2</td>
          <td>marcmamp1 </td>
          <td>UGC</td>
          <td>
          <input onkeyup="suggestUser(event)" id="userBox_1" type="text" value="" autocomplete="off" 
            name="contentOwner[0GUO9dUB8Kg]" videoid="0GUO9dUB8Kg" style="width:150px" onfocus="showUsersOptions(this);">
          </td>
        </tr>
        <tr>
          <th>
          <input type="checkbox" id="checkAll">
          </th>
          <td>
          <span class="big_bold " style="color: green">Y</span></td>
           </td>
          <td>
          <a href="" class="commonLinks" target="_blank">0GUO9dUB8Kg</a>
          </td>
          <td> MINHA MOTO RACING SHINERAY MODIFICADA</td>
          <td>Márcio Maia</td>
          <td>Nirvanadigital2</td>
          <td>marcmamp1 </td>
          <td>UGC</td>
          <td>
          <input onkeyup="suggestUser(event)" id="userBox_1" type="text" value="" autocomplete="off" 
            name="contentOwner[0GUO9dUB8Kg]" videoid="0GUO9dUB8Kg" style="width:150px" onfocus="showUsersOptions(this);">
          </td>
        </tr>
    
        </tr>
       
       
        
       
      </tbody>
    
        <tfoot >
            <tr>
                <!-- <th></th>
                <th>
                  
                </th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th> -->
            </tr>
       
      </tfoot>
      <tfoot>
        <tr>
  
          <th></th>
          <td>  </td>
          <td>
          <button class="btn" type="submit" style="background-color:#9e9e9e; margin-top:2vh;">Save</button>
                  
          </td>
          <td>

          <button class="btn" type="submit" style="background-color:#9e9e9e; margin-top:2vh;">
          <!-- <div class="button" style="margin-top:15px;" > -->
          <select name="userName" id="userName" class="form-control1">
             <option value="">Select Owner</option>
             <option value=""></option>
            <option value=""></option>
            <option value=""></option>
             <option value=""></option>
            <option value=""></option>
            <option value=""></option>
            <option value=""></option>

          </select>
          </button>
          </td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
      </tfoot>
    </table>


    
  </div>
  
</div>

    <nav aria-label="Page navigation" style="margin-top:6vh;">
      <ul class="pagination">
        <li class="page-item active"><a class="page-link" href="../validate/youtube.php">1</a></li>
        <li class="page-item "><a class="page-link" href="../validate/red.php">2</a></li>

        <li class="page-item "><a class="page-link" href="../validate/audio_red.php">3</a></li>
        <li class="page-item "><a class="page-link" href="../validate/audio1.php">4</a></li>
        <li class="page-item "><a class="page-link" href="../validate/itunes.php">5</a></li>
        <li class="page-item "><a class="page-link" href="../validate/apple_music.php">6</a></li>
        <li class="page-item"><a class="page-link" href="../validate/youtube.php">Next</a></li>
        <li class="page-item"><a class="page-link" href="../validate/youtube.php">»</a></li>
      </ul>
    </nav>

    </body>

    <script>

     (function() {
    
  var fauxTable = document.getElementById("faux-table");
  var mainTable = document.getElementById("main-table");
  var clonedElement = mainTable.cloneNode(true);
  var clonedElement2 = mainTable.cloneNode(true);
  clonedElement.id = "";
  clonedElement2.id = "";
  fauxTable.appendChild(clonedElement);
  fauxTable.appendChild(clonedElement2);
  
})();

$("#checkAll").click(function () {
     $('input:checkbox').not(this).prop('checked', this.checked);
 });

 
    </script>
</html>   