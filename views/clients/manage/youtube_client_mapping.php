<?php
//Add/edit client view page
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
require_once(__ROOT__.'/model/client/clientModel.php');
require_once(__ROOT__.'/model/distributor/distributorModel.php');

$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1) . $conn["errMsg"];
} else {
    $email = $_SESSION['userEmail'];
    //initialize logs
    $logsProcessor = new logsProcessor();
    $initLogs = initializeJsonLogs($email);
    $logFilePath = $logStorePaths["clients"];
    $logFileName = "manageClient.json";

    $logMsg = "Manage client process start.";
    $logData['step1']["data"] = "1. {$logMsg}";

    $logMsg = "Database connection successful.";
    $logData["step2"]["data"] = "2. {$logMsg}";

    $conn = $conn["errMsg"];
    $returnArr = array();

   
 
    $userName = "";
  
    $id = ""; 
    $Channel = "";
    $partner_provided ="";
    $ugc = "";
    $Channel_id = "";
    $Label = "";
    $assetChannelID ="";
    $Label2 = "";
    $CMS = "";
    $client_youtube_shares = "0";
             

    if (isset($_GET["id"])) {
        $id = $_GET["id"];
    }
    // if (!is_null($userName)) {
    if (!empty($id)) {
        $logMsg = "Attempting to get clients  info youtube co-mapping .";
        $logData["step5"]["data"] = "5. {$logMsg}";
        
        $clientSearchArr = array('id' => $id);
        $fieldsStr = "id,client_username, client_firstname, client_lastname,  address, email, mobile_number,Channel,partner_provided,ugc,Channel_id,Label,Label2,assetChannelID,CMS,ccm.client_youtube_shares   ";
        $clientInfo = getClientsInfoYoutube($clientSearchArr, $fieldsStr, null, $conn);
       // print_r($clientInfo);
        if (!noError($clientInfo)) {
            $logMsg = "Error fetching clients info youtube co-mapping : {$clientInfo["errMsg"]}";
            $logData["step5.1"]["data"] = "5.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5) . " Error fetching client details.";
        } else {
            $logMsg = "Clients info youtube co-mapping fetched successfully.";
            $logData["step6"]["data"] = "6. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $clientInfo = $clientInfo["errMsg"];
            $returnArr["errCode"] = -1;
            $email = array_keys($clientInfo);
            $email = $email[0];
            $clientInfo = $clientInfo[$email];
            $userName = $clientInfo["client_username"];
           
            $id = $clientInfo["id"];
            $Channel = $clientInfo["Channel"];
            $partner_provided = $clientInfo["partner_provided"];
            $ugc = $clientInfo["ugc"];
            $Channel_id = $clientInfo["Channel_id"];
            $Label = $clientInfo["Label"];
            $assetChannelID = $clientInfo["assetChannelID"];
            $Label2 = $clientInfo["Label2"];
            $CMS = $clientInfo["CMS"];
            $client_youtube_shares = $clientInfo["client_youtube_shares"];
             
            
             
        }
    } else {
        $returnArr["errCode"] = -1;
        $userName = "";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
    }

    
}
?>
<div class="row">
    <form id="addEditClientFormYoutube" name="addEditClientFormYoutube" action="javascript:;" data-parsley-validate="">
        <input type="hidden" class="form-control"  id="oldid" name="oldid"
            value="<?php echo $id; ?>">
        <!--  success/error messages -->
        <?php
$alertMsg = "";
$alertClass = "";

if (!noError($returnArr)) {
    $alertClass = "alert-danger";
    $alertMsg = isset($returnArr["errMsg"]);
}
?>
        <div class="alert <?php echo $alertClass; ?>" style="display: none">
            <span>
                <?php echo $alertMsg; ?>
            </span>
        </div>
        <!-- end success/error messages -->


        <div class="col-md-12">
            <!-- Title -->
            <div class="col-md-4 form-group label-floating1 is-empty">
                <label class="control-label">Channel <?php echo $id; ?> &#42;</label>
                <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="Channel"
                    name="Channel" value="<?php echo $Channel; ?>">
            </div>
            <div class="col-md-4 form-group label-floating1 is-empty">
                <label class="control-label">partner_provided &#42;</label>
                <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="partner_provided"
                    name="partner_provided" value="<?php echo $partner_provided; ?>">
            </div>
            <div class="col-md-4 form-group label-floating1 is-empty">
                <label class="control-label">ugc &#42;</label>
                <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="ugc"
                    name="ugc" value="<?php echo $ugc; ?>">
            </div>
            <div class="col-md-4 form-group label-floating1 is-empty">
                <label class="control-label">Channel_id &#42;</label>
                <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="Channel_id"
                    name="Channel_id" value="<?php echo $Channel_id; ?>">
            </div>
            <div class="col-md-4 form-group label-floating1 is-empty">
                <label class="control-label">Label &#42;</label>
                <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="Label"
                    name="Label" value="<?php echo $Label; ?>">
            </div>
            <div class="col-md-4 form-group label-floating1 is-empty">
                <label class="control-label">assetChannelID &#42;</label>
                <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="assetChannelID"
                    name="assetChannelID" value="<?php echo $assetChannelID; ?>">
            </div>
            <div class="col-md-4 form-group label-floating1 is-empty">
                <label class="control-label">Label2 &#42;</label>
                <input type="text" class="form-control" data-parsley-trigger="keyup"   id="Label2"
                    name="Label2" value="<?php echo $Label2; ?>">
            </div>
            
            <!-- End Title -->
            <!-- Session -->
            <div class="col-md-4 form-group label-floating1 is-empty">
                <label class="control-label">CMS &#42;</label>
                <select name="CMS" id="cms" class="form-control">
                    <option value="ND1" <?php echo ($CMS=="ND1")?'selected':''?>>ND1</option>
                    <option value="ND2"  <?php echo ($CMS=="ND2")?'selected':''?>>ND2</option>
                    <option value="ND Music"  <?php echo ($CMS=="ND Music")?'selected':''?>>ND Music</option>
                    <option value="ND Kids"  <?php echo ($CMS=="ND Kids")?'selected':''?>>ND Kids</option>
                    <option value="applemusic"  <?php echo ($CMS=="applemusic")?'selected':''?>>Apple music</option>
                    <option value="itune"  <?php echo ($CMS=="itune")?'selected':''?>>Itune Music</option>
                    <option value="saavan"  <?php echo ($CMS=="saavan")?'selected':''?>>Saavan Music</option>
                    <option value="gaana"  <?php echo ($CMS=="gaana")?'selected':''?>>Gaana Music</option>
                    <option value="spotify"  <?php echo ($CMS=="spotify")?'selected':''?>>Spotify Music</option>
                    
                </select>
                <!-- <input type="text" class="form-control" data-data-parsley-trigger="keyup" id="CMS"
                    name="CMS" value="<?php echo $CMS; ?>"> -->
            </div>
            <!-- End Session -->
            <div class="col-md-4 form-group label-floating1 is-empty">
                <label class="control-label">Youtube Shares &#42;</label>
                <input type="text" class="form-control" data-parsley-trigger="keyup"   id="client_youtube_shares"
                    name="client_youtube_shares" value="<?php echo $client_youtube_shares; ?>">
            </div>
        </div>



        <!-- submit button -->
        <div class="col-md-12 form-group">
            <button class="btn" type="submit">Save</button>
        </div>
    </form>
</div>

<script>
//handle form submit
$('form#addEditClientFormYoutube').parsley().on('field:validated', function() {
        var ok = $('.parsley-error').length === 0;
        $('.bs-callout-info').toggleClass('hidden', !ok);
        $('.bs-callout-warning').toggleClass('hidden', ok);
    })
    .on('form:submit', function() {
        var formData = new FormData($('#addEditClientFormYoutube')[0]);

        //resetting the error message
        $("#addEditClientFormYoutube .alert").
        removeClass("alert-success").
        removeClass("alert-danger").
        fadeOut().
        find("span").html("");

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/client/client_mapping_youtube.php",
            data: formData,
        
            contentType: false,
            cache: false,
            processData: false,
            success: function(user) {
                if (user["errCode"]) {
                    if (user["errCode"] != "-1") { //there is some error
                        $("#addEditClientFormYoutube .alert").
                        removeClass("alert-success").
                        addClass("alert-danger").
                        fadeIn().
                        find("span").
                        html(user["errMsg"]);
                    } else {
                        $("#addEditClientFormYoutube .alert").
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
            error: function(msg) {
                console.log("error",msg);
                $("#addEditClientFormYoutube .alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html("500 internal server error");
            }

        });

    });
</script>