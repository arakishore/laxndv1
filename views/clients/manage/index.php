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

    $clientStatusMap = array(
        "1" => "Active",
        "0" => "Inactive",
        "2" => "Deleted",
    );

    $logMsg = "Attempting to get distributor info.";
    $logData["step3"]["data"] = "3. {$logMsg}";

    //attempting to get all distributors
    $distributorSearchArr = array("status" => 1);
    $fieldsStr = "distributor_id, distributor_name, email";
    $allDistributors = getDistributorsInfo($distributorSearchArr, $fieldsStr, null, $conn);
    if (!noError($allDistributors)) {
        //error getting all distributors
        $logMsg = "Error fetching distributor info: {$allDistributors["errMsg"]}";
        $logData["step3.1"]["data"] = "3.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5) . $allDistributors["errMsg"];
    }
    $allDistributors = $allDistributors["errMsg"];

    $logMsg = "Distributor info fetched successfully.";
    $logData["step4"]["data"] = "4. {$logMsg}";

    $res_all_cms = array();
    $res_all_cms_default_value = array();
    $sql = "select * from cms_master where CMS like 'ND%'";
    $getClientInfoQueryResult = runQuery($sql, $conn);
    while ($row = mysqli_fetch_assoc($getClientInfoQueryResult["dbResource"])) {

        $res_all_cms[] = $row['CMS'];
        $res_all_cms_default_value[$row['CMS']] = 30;
    }
    $client_youtube_shares_detail = array();
    $client_id = "";
    $userName = "";
    $firstName = $lastName = "";
    $panNum = $gstNum = $clientSource = "";
    $clientStatus = $clientSource = "";
    $clientAddress = "";
    $mobNum = $email = "";
    $altEmail1 = $altEmail2 = $altEmail3 = $altEmail4 = "";
    $revenueShareYoutube = $revenueShareYoutubeRed = $revenueShareYoutubeAudioRed = "";
    $revenueShareYoutubeAudio = $revenueItunes = $revenueAppleMusic = $revenueAmazon = $revenueSaavan = $revenueGaana = $revenueSpotify  = "";
    $companyName = $companyAddress = $companyIndustry = "";
    $pocMobNum = $pocCompanyNum = $pocDesignation = "";
    $comment = "";
    $gst_per = "";
    if (isset($_GET["userName"])) {
        $userName = $_GET["userName"];
    }
    // if (!is_null($client_id)) {
    if (!empty($userName)) {
        $logMsg = "Attempting to get clients info.";
        $logData["step5"]["data"] = "5. {$logMsg}";
        
        $clientSearchArr = array('client_username' => $userName);
        $fieldsStr = "*";
        $clientInfo = getClientsInfo_showinfo($clientSearchArr, $fieldsStr, null, $conn);
        if (!noError($clientInfo)) {
            $logMsg = "Error fetching clients info: {$clientInfo["errMsg"]}";
            $logData["step5.1"]["data"] = "5.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5) . " Error fetching client details.";
        } else {
           

            $logMsg = "Clients info fetched successfully.";
            $logData["step6"]["data"] = "6. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $clientInfo = $clientInfo["errMsg"];
            $returnArr["errCode"] = -1;
            $email = array_keys($clientInfo);
            $email = $email[0];
            $clientInfo = $clientInfo[$email];
            $userName = $clientInfo["client_username"];
            $firstName = $clientInfo["client_firstname"];
            $lastName = $clientInfo["client_lastname"];
            $panNum = $clientInfo["pan"];
            $gstNum = $clientInfo["gst_no"];
            $gst_per = $clientInfo["gst_per"];
            $clientSource = $clientInfo["source"];
            $clientAddress = $clientInfo["address"];
            $clientStatus = $clientStatusMap[$clientInfo["status"]];
            $mobNum = $clientInfo["mobile_number"];
            $email = $clientInfo["email"];
            $altEmail1 = $clientInfo["email1"];
            $altEmail2 = $clientInfo["email2"];
            $altEmail3 = $clientInfo["email3"];
            $altEmail4 = $clientInfo["email4"];

            $clientTypeDetails = json_decode($clientInfo["client_type_details"]);
            $revenueShareYoutube = $clientTypeDetails->revenueShareYoutube;
            $revenueShareYoutubeRed = $clientTypeDetails->revenueShareYoutubeRed;
            $revenueShareYoutubeAudioRed = $clientTypeDetails->revenueShareYoutubeAudioRed;
            $revenueShareYoutubeAudio = $clientTypeDetails->revenueShareYoutubeAudio;
            $revenueItunes = $clientTypeDetails->revenueItunes;
            $revenueAppleMusic = $clientTypeDetails->revenueAppleMusic;

            $revenueAmazon = isset($clientTypeDetails->revenueAmazon) ? $clientTypeDetails->revenueAmazon : 0;

            $revenueGaana = isset($clientTypeDetails->revenueGaana) ? $clientTypeDetails->revenueGaana : 0;
            $revenueSaavan = isset($clientTypeDetails->revenueSaavan) ? $clientTypeDetails->revenueSaavan : 0;
            $revenueSpotify = isset($clientTypeDetails->revenueSpotify) ? $clientTypeDetails->revenueSpotify : 0;

            

            $companyTypeDetails = json_decode($clientInfo["company_details"]);
            $companyName = $companyTypeDetails->companyName;
            $companyAddress = $companyTypeDetails->companyAddress;
            $companyIndustry = $companyTypeDetails->companyIndustry;
            $pocMobNum = $companyTypeDetails->pocMobNum;
            $pocCompanyNum = $companyTypeDetails->pocCompanyNum;
            $pocDesignation = $companyTypeDetails->pocDesignation;
            $comment = $clientInfo["comments"];

            $client_youtube_shares = $clientInfo["client_youtube_shares"];
            
            $client_youtube_shares_detail = (!empty($client_youtube_shares))  ? json_decode($client_youtube_shares) : $res_all_cms_default_value;

        }
    } else {
        $returnArr["errCode"] = -1;
        $userName = "";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
    }

    
}
?>
<div class="row">
    <form id="addEditClientForm" name="addEditClientForm" action="javascript:;" data-parsley-validate="">
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
        <!-- hidden old client code fielld to define add/edit mode -->
        <input type="hidden" name="oldUsername" value="<?php echo $userName; ?>">
        <input type="hidden" name="oldEmail" value="<?php echo $email; ?>">

        <!-- Client name, pan, code row -->
        <div class="col-md-12">
            <!-- Client username -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Username&#42;</label>
                <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="userName"
                    name="userName" value="<?php echo $userName; ?>">
            </div>
            <!-- End Client username -->
            <!-- Client Firstname -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Firstname&#42;</label>
                <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="firstName"
                    name="firstName" value="<?php echo $firstName; ?>">
            </div>
            <!-- End Client Firstname -->
            <!-- Client Lastname -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Lastname&#42;</label>
                <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="lastName"
                    name="lastName" value="<?php echo $lastName; ?>">
            </div>
            <!-- End Client Lastname -->
        </div>

        <div class="col-md-12">
            <!-- Client PAN -->
            <div class="col-md-3 form-group label-floating is-empty">
                <label class="control-label">PAN Number&#42;</label>
                <input type="text" class="form-control" data-parsley-pannum="checkPan" required="" id="panNum"
                    name="panNum" value="<?php echo $panNum; ?>">
            </div>
            <!-- End Client PAN -->
            <!-- GST Num -->
            <div class="col-md-3 form-group label-floating is-empty">
                <label class="control-label">GST Reg Number</label>
                <input type="text" class="form-control" data-parsley-trigger="keyup" id="gstNum" name="gstNum"
                    data-parsley-gstnum="gstNum" value="<?php echo $gstNum; ?>">
            </div>
            <!-- End GST Num -->
             <!-- GST per -->
             <div class="col-md-3 form-group label-floating is-empty">
                <label class="control-label">GST Percentage</label>
                <input type="text" class="form-control" data-parsley-trigger="keyup" id="gst_per" name="gst_per"
                    data-parsley-gst_per="gst_per" value="<?php echo $gst_per; ?>">
            </div>
            <!-- End GST per -->
            <!--Client Address-->
            <div class="col-md-3 form-group label-floating is-empty">
                <label class="control-label">Address</label>
                <input type="text" class="form-control" data-parsley-trigger="keyup" id="clientAddress"
                    name="clientAddress" value="<?php echo $clientAddress; ?>">
            </div>
            <!-- Client Address -->
        </div>

        <div class="col-md-12">
            <!-- Mobile -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Mobile&#42;</label>
                <input type="text" class="form-control" data-data-parsley-trigger="keyup"
                    onkeypress='return restrictAlphabets(event)' id="phone" name="phone" value="<?php echo $mobNum; ?>">
            </div>
            <!-- End Mobile -->
            <!-- Email -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Email&#42;</label>
                <input type="email" class="form-control" data-parsley-trigger="keyup" required="" id="email"
                    name="email" value="<?php echo $email; ?>">
            </div>
            <!-- End Email -->
             <!-- Alt Email1 -->
             <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Alt Email 1</label>
                <input type="email" class="form-control" data-parsley-trigger="keyup" id="altEmail1" name="altEmail[1]"
                    value="<?php echo $altEmail1; ?>">
            </div>
            <!-- End Alt Email1 -->
        </div>

        <!-- Client alt email 1 and 2 row -->
        <div class="col-md-12">
           
            <!-- Alt Email2 -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Alt Email 2</label>
                <input type="email" class="form-control" data-parsley-trigger="keyup" id="altEmail2" name="altEmail[2]"
                    value="<?php echo $altEmail2; ?>">
            </div>
            <!-- End Alt Email2 -->
             <!-- Alt Email3 -->
             <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Alt Email 3</label>
                <input type="email" class="form-control" data-parsley-trigger="keyup" id="altEmail3" name="altEmail[3]"
                    value="<?php echo $altEmail3; ?>">
            </div>
            <!-- End Alt Email3 -->
            <!-- Alt Email4 -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Alt Email 4</label>
                <input type="email" class="form-control" data-parsley-trigger="keyup" id="altEmail4" name="altEmail[4]"
                    value="<?php echo $altEmail4; ?>">
            </div>
            <!-- End Alt Email4 -->
        </div>
        <!-- end Client alt email 1 and 2 row -->
       
        <!--  Status -->
        <div class="col-md-12">
            <!-- Client Status -->
            <div class="col-md-6 form-group">
                <select class="form-control" required="" id="status" name="status">
                    <option value="">Select Status</option>
                    <?php
                   foreach ($clientStatusMap as $statusCode => $statusName) {
                 $selected = "";
                 if (strtolower($statusName) == strtolower($clientStatus)) {
                      $selected = "selected='selected'";
                      }
                      ?>
                    <option <?php echo $selected; ?> value="<?php echo $statusCode; ?>">
                        <?php echo $statusName; ?>
                    </option>
                    <?php
                      }
                      ?>
                </select>
            </div>
            <!-- End Client Status -->
            <!-- Source -->
            <div class="col-md-6 form-group">
                <select class="form-control" required="" id="clientSource" name="clientSource">
                    <option value="">Select Source</option>
                    <option value="self" <?php
                        if (strtolower($clientSource)=="self") {
                            echo "selected='selected'";
                        }
                        ?>>
                        Self
                    </option>
                    <?php
                    foreach ($allDistributors as $distributorId => $distributorDetails) {
                        $selected = "";
                        if ($distributorDetails['distributor_id']==$clientSource) {
                            $selected = "selected='selected'";
                        }
                    ?>
                    <option <?php echo $selected; ?> value="<?php echo $distributorDetails['distributor_id']; ?>">
                        <?php echo $distributorDetails['distributor_name']; ?>
                    </option>
                    <?php
                    }
                    ?>
                </select>
            </div>
            <!-- End Source -->
        </div>
        <div class="col-md-12 companyTypeDetails"><hr>
        </div>
        <!--new client details-->
        <div class="col-md-12 clientTypeDetails">
            <div class="col-md-12">
                <!-- Revenue share -->
                <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">Revenue Share Youtube&#42;</label>
                    <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)"
                        pattern="^\d*(\.\d{0,2})?$" min="0" max="100" step="any" id="revenueShareYoutube" required=""
                        name="clientTypeDetails[revenueShareYoutube]" value="<?php echo $revenueShareYoutube; ?>">
                </div>
                <!-- Revenue share -->
                <!-- Validate Report Youtube -->
                <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">Revenue Share Youtube Red&#42;</label>
                    <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)"
                        min="0" max="100" step="any" id="revenueShareYoutubeRed"
                        name="clientTypeDetails[revenueShareYoutubeRed]" required="" pattern="^\d*(\.\d{0,2})?$"
                        value="<?php echo $revenueShareYoutubeRed; ?>">
                </div>
                <!-- Validate Report Youtube -->
                 <!-- Validate Report youtube Audio Red -->
                 <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">Revenue Share Youtube Audio&#42;</label>
                    <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)"
                        min="0" max="100" step="any" id="revenueShareYoutubeAudio"
                        name="clientTypeDetails[revenueShareYoutubeAudio]" required="" pattern="^\d*(\.\d{0,2})?$"
                        value="<?php echo $revenueShareYoutubeAudio; ?>">
                </div>
                <!-- Validate Report youtube Audio Red -->
            </div>
            <div class="col-md-12">
               
                <!--Validate Report Youtube Red -->
                <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">Revenue Share Youtube Audio Red&#42;</label>
                    <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)"
                        min="0" max="100" step="any" id="revenueShareYoutubeAudioRed"
                        name="clientTypeDetails[revenueShareYoutubeAudioRed]" required="" pattern="^\d*(\.\d{0,2})?$"
                        value="<?php echo $revenueShareYoutubeAudioRed; ?>">
                </div>
                <!-- Validate Report Youtube Red -->
                 <!-- Validate Report Youtube Audio -->
                 <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">Revenue Itunes&#42;</label>
                    <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)"
                        min="0" max="100" step="any" pattern="^\d*(\.\d{0,2})?$" id="revenueItunes"
                        name="clientTypeDetails[revenueItunes]" required="" value="<?php echo $revenueItunes; ?>">
                </div>
                <!-- Validate Report Youtube Audio -->
                <!-- Validate Report Itunes -->
                <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">Revenue Apple Music&#42;</label>
                    <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)"
                        min="0" max="100" step="any" pattern="^\d*(\.\d{0,2})?$" id="revenueAppleMusic"
                        name="clientTypeDetails[revenueAppleMusic]" required=""
                        value="<?php echo $revenueAppleMusic; ?>">
                </div>
                <!-- Validate Report Itunes -->
            </div>
            <div class="col-md-12">
               
                 <!-- Validate Report Saavan Audio -->
                 <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">Revenue Saavan&#42;</label>
                    <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)"
                        min="0" max="100" step="any" pattern="^\d*(\.\d{0,2})?$" id="revenueSaavan"
                        name="clientTypeDetails[revenueSaavan]" required="" value="<?php echo $revenueSaavan; ?>">
                </div>
                <!-- Validate Report Gaana Audio -->
                  <!-- Validate Report Itunes -->
                  <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">Revenue Gaana Music&#42;</label>
                    <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)"
                        min="0" max="100" step="any" pattern="^\d*(\.\d{0,2})?$" id="revenueGaana"
                        name="clientTypeDetails[revenueGaana]" required=""
                        value="<?php echo $revenueGaana; ?>">
                </div>
                <!-- Validate Report Itunes -->
                 <!-- Validate Report Itunes -->
                 <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">Revenue Spotify Music&#42;</label>
                    <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)"
                        min="0" max="100" step="any" pattern="^\d*(\.\d{0,2})?$" id="revenueSpotify"
                        name="clientTypeDetails[revenueSpotify]" required=""
                        value="<?php echo $revenueSpotify; ?>">
                </div>
                <!-- Validate Report Itunes -->

                <input type="hidden" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)"
                        min="0" max="100" step="any" pattern="^\d*(\.\d{0,2})?$" id="revenueAmazon"
                        name="clientTypeDetails[revenueAmazon]" required="" value="<?php echo $revenueAmazon; ?>">
            </div>
            
           
        </div>
        <div class="col-md-12 companyTypeDetails"><hr>
        </div>
        <div class="col-md-12 companyTypeDetails">
            <div class="col-md-12">
                 <?php
                 $client_youtube_shares_default = $res_all_cms_default_value;
                 $client_youtube_shares_detail = (!empty($client_youtube_shares))  ? json_decode($client_youtube_shares) : $res_all_cms_default_value;

                    foreach($client_youtube_shares_detail as $key => $valeHolding) {
                        $key1 = str_replace(' ', '_', $key);

                 ?>   
                <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">Youtube Holding (<?php echo $key?>)</label>
                    <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)"
                        min="0" max="100" step="any" pattern="^\d*(\.\d{0,2})?$" id="<?php echo $key1?>"
                        name="client_youtube_shares[<?php echo $key1?>]" required=""
                        value="<?php echo $valeHolding; ?>">
                </div>
                <?php }?>
            </div>
        </div>        
        <div class="col-md-12 companyTypeDetails"><hr>
        </div>
        <!-- end of new client details-->
        <!----------------------Company Details------------------->
        <div class="col-md-12 companyTypeDetails">
            <div class="col-md-12">
                <!-- Comapny Name -->
                <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">Company Name</label>
                    <input type="text" class="form-control" data-parsley-trigger="keyup" id="companyName"
                        name="companyTypeDetails[companyName]" value="<?php echo $companyName; ?>">
                </div>
                <!-- Company Name -->
                <!-- Comapny Address -->
                <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">Company Address</label>
                    <input type="text" class="form-control" data-parsley-trigger="keyup" id="companyAddress"
                        name="companyTypeDetails[companyAddress]" value="<?php echo $companyAddress; ?>">
                </div>
                <!-- Company Address -->
                <!--Company Industry -->
                <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">Company Industry</label>
                    <input type="text" class="form-control" data-parsley-trigger="keyup" id="companyIndustry"
                        name="companyTypeDetails[companyIndustry]" value="<?php echo $companyIndustry; ?>">
                </div>
                <!-- Company Industry -->
            </div>
            <div class="col-md-12">
                <!-- POC Mobile Number -->
                <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">POC Mobile Number</label>
                    <input type="text" class="form-control" data-parsley-trigger="keyup"
                        onkeypress='return restrictAlphabets(event)' id="pocMobNum" name="companyTypeDetails[pocMobNum]"
                        value="<?php echo $pocMobNum; ?>">
                </div>
                <!-- POC Mobile Number -->
                <!-- POC Company Number -->
                <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">POC Company Number</label>
                    <input type="text" class="form-control" data-parsley-trigger="keyup"
                        onkeypress='return restrictAlphabets(event)' id="pocCompanyNum"
                        name="companyTypeDetails[pocCompanyNum]" value="<?php echo $pocCompanyNum; ?>">
                </div>
                <!-- POC Company Number -->
                <!-- POC Designation -->
                <div class="col-md-4 form-group label-floating is-empty">
                    <label class="control-label">POC Designation</label>
                    <input type="text" class="form-control" data-parsley-trigger="keyup" id="pocDesignation"
                        name="companyTypeDetails[pocDesignation]" value="<?php echo $pocDesignation; ?>">
                    <!-- POC Designation -->
                </div>
            </div>
        </div>
        <!----------------------End of Client Details------------------>

        <!-- Comments -->
        <div class="col-md-12 form-group label-floating is-empty">
            <label class="control-label">Comments</label>
            <input type="text" class="form-control" data-parsley-trigger="keyup" id="comments" name="comments"
                value="<?php echo $comment; ?>">
        </div>
        <!-- End Comments -->

        <!-- submit button -->
        <div class="col-md-12 form-group">
            <button class="btn" type="submit">Save</button>
        </div>
    </form>
</div>

<script>
//These will allow user to enter not more than one decimal point
$('#revenueShareYoutube, #revenueShareYoutubeRed, #revenueShareYoutubeAudioRed, #revenueShareYoutubeAudio, #revenueItunes, #revenueAppleMusic')
    .keypress(function(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode == 8 || charCode == 37) {
            return true;
        } else if (charCode == 46 && $(this).val().indexOf('.') != -1) {
            return false;
        } else if (charCode > 31 && charCode != 46 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    });

//These will not allow user to enter alphabets in phone number fields
function restrictAlphabets(e) {
    var x = e.which || e.keycode;
    if ((x >= 48 && x <= 57) || x == 8 ||
        (x >= 35 && x <= 40) || x == 46)
        return true;
    else
        return false;
}

//These will not allow to enter more than two number after decimal point
$(document).on('keydown', 'input[pattern]', function(e) {
    var input = $(this);
    var oldVal = input.val();
    var regex = new RegExp(input.attr('pattern'), 'g');

    setTimeout(function() {
        var newVal = input.val();
        if (!regex.test(newVal)) {
            input.val(oldVal);
        }
    }, 0);
});

//These return 100 if user enter more than 100
function minmax(value, min, max) {
    if (parseInt(value) < min || isNaN(parseInt(value)))
        return "";
    else if (parseInt(value) > max)
        return 100;
    else return value;
}

window.Parsley
    .addValidator('pannum', {
        requirementType: 'string',
        validateString: function(value) {
            var regpan = /^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/;
            if (regpan.test(value)) {
                // valid pan card number
                return true;
            } else {
                // invalid pan card number
                return false;
            }
        },
        messages: {
            en: 'Invalid PAN Number'
        }
    });

window.Parsley
    .addValidator('gstnum', {
        requirementType: 'string',
        validateString: function(value) {
            var regGSTNum = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
            if (regGSTNum.test(value)) {
                // valid GST Num
                return true;
            } else {
                // invalid GST Num
                return false;
            }
        },
        messages: {
            en: 'Invalid GST Num'
        }
    });

//managing the floating labels behaviour
$("form#addEditClientForm :input").each(function() {
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

function changeClientType(dropDownElem) {
    $(".clientTypeDetails").addClass("hidden");
    var selectedClientType = $(dropDownElem).val();
    $("#" + selectedClientType + "ClientTypeDetails").removeClass("hidden");
}

//handle form submit
$('form#addEditClientForm').parsley().on('field:validated', function() {
        var ok = $('.parsley-error').length === 0;
        $('.bs-callout-info').toggleClass('hidden', !ok);
        $('.bs-callout-warning').toggleClass('hidden', ok);
    })
    .on('form:submit', function() {
        var formData = new FormData($('#addEditClientForm')[0]);

        //resetting the error message
        $("#addEditClientForm .alert").
        removeClass("alert-success").
        removeClass("alert-danger").
        fadeOut().
        find("span").html("");

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/client/",
            data: formData,
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function(user) {
                console.log("user_edit-add",user);
                if (user["errCode"]) {
                    if (user["errCode"] != "-1") { //there is some error
                        $("#addEditClientForm .alert").
                        removeClass("alert-success").
                        addClass("alert-danger").
                        fadeIn().
                        find("span").
                        html(user["errMsg"]);
                    } else {
                        $("#addEditClientForm .alert").
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
                $("#addEditClientForm .alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html("500 internal server error");
            }

        });

    });
</script>