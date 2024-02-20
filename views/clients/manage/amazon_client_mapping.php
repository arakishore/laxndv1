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
    $title_name = "";
    $session_id = ""; 
   

    if (isset($_GET["userName"])) {
        $userName = $_GET["userName"];
    }
    // if (!is_null($userName)) {
    if (!empty($userName)) {
        $logMsg = "Attempting to get clients  info Amazon.";
        $logData["step5"]["data"] = "5. {$logMsg}";
        
        $clientSearchArr = array('client_username' => $userName);
        $fieldsStr = "client_username, client_firstname, client_lastname,  address, email, mobile_number,title_name,session_id,assin  ";
        $clientInfo = getClientsInfoAmazon($clientSearchArr, $fieldsStr, null, $conn);
       // print_r($clientInfo);
        if (!noError($clientInfo)) {
            $logMsg = "Error fetching clients info Amazon: {$clientInfo["errMsg"]}";
            $logData["step5.1"]["data"] = "5.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5) . " Error fetching client details.";
        } else {
            $logMsg = "Clients info Amazon fetched successfully.";
            $logData["step6"]["data"] = "6. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $clientInfo = $clientInfo["errMsg"];
            $returnArr["errCode"] = -1;
            $email = array_keys($clientInfo);
            $email = $email[0];
            $clientInfo = $clientInfo[$email];
            $userName = $clientInfo["client_username"];
            $title_name = $clientInfo["title_name"];
            $session_id = $clientInfo["session_id"];
             
            
             
        }
    } else {
        $returnArr["errCode"] = -1;
        $userName = "";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
    }

    
}
?>
<div class="row">
    <form id="addEditClientFormAmazon" name="addEditClientFormAmazon" action="javascript:;" data-parsley-validate="">
        <input type="hidden" class="form-control"  id="oldSession_id" name="oldSession_id"
            value="<?php echo $session_id; ?>">
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
            <div class="col-md-4 form-group label-floating1 is-empty">
                <label class="control-label">Client &#42;</label>

                <?php
                                                $clientsSearchArr = array("status"=>1);
                                                $fieldsStr = "email, client_username, client_firstname";
                                                $allClients = getClientsInfo($clientsSearchArr, $fieldsStr, null, $conn);
                                                if (!noError($allClients)) {
                                                    printArr("Error fetching all clients");
                                                    exit;
                                                }
                                                $allClients = $allClients["errMsg"];
                                                ?>

                <select name="userName" id="userName" class="form-control">
                    <option value="">Select Client</option>
                    <?php
                                                    foreach ($allClients as $clientEmail => $clientDetails) {
                                                        $selected = "";
                                                        if (isset($clientSearchArr["client_username"]) && ($clientDetails['client_username']==$clientSearchArr["client_username"])) {
                                                            $selected = "selected='selected'";
                                                        }
                                                    ?>
                    <option <?php echo $selected; ?> value="<?php echo $clientDetails['client_username']; ?>">
                        <?php echo $clientDetails['client_username']."-".$clientDetails['client_firstname']; ?>
                    </option>
                    <?php
                                                    }
                                                    ?>
                </select>

            </div>
            <!-- Title -->
            <div class="col-md-4 form-group label-floating1 is-empty">
                <label class="control-label">Title &#42;</label>
                <input type="text" class="form-control" data-parsley-trigger="keyup" required="" id="title_name"
                    name="title_name" value="<?php echo $title_name; ?>">
            </div>
            <!-- End Title -->
            <!-- Session -->
            <div class="col-md-4 form-group label-floating1 is-empty">
                <label class="control-label">Session Id &#42;</label>
                <input type="text" class="form-control" data-data-parsley-trigger="keyup" id="session_id"
                    name="session_id" value="<?php echo $session_id; ?>">
            </div>
            <!-- End Session -->

        </div>



        <!-- submit button -->
        <div class="col-md-12 form-group">
            <button class="btn" type="submit">Save</button>
        </div>
    </form>
</div>

<script>
//handle form submit
$('form#addEditClientFormAmazon').parsley().on('field:validated', function() {
        var ok = $('.parsley-error').length === 0;
        $('.bs-callout-info').toggleClass('hidden', !ok);
        $('.bs-callout-warning').toggleClass('hidden', ok);
    })
    .on('form:submit', function() {
        var formData = new FormData($('#addEditClientFormAmazon')[0]);

        //resetting the error message
        $("#addEditClientFormAmazon .alert").
        removeClass("alert-success").
        removeClass("alert-danger").
        fadeOut().
        find("span").html("");

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/client/client_mapping.php",
            data: formData,
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function(user) {
                if (user["errCode"]) {
                    if (user["errCode"] != "-1") { //there is some error
                        $("#addEditClientFormAmazon .alert").
                        removeClass("alert-success").
                        addClass("alert-danger").
                        fadeIn().
                        find("span").
                        html(user["errMsg"]);
                    } else {
                        $("#addEditClientFormAmazon .alert").
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
                $("#addEditClientFormAmazon .alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html("500 internal server error");
            }

        });

    });
</script>