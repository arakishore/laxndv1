<?php
//Add/edit delete view page
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
require_once(__ROOT__.'/model/distributor/distributorModel.php');

$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1) . $conn["errMsg"];
} else {
    $conn = $conn["errMsg"];
    $returnArr = array();

    //TO DO: Logs here

    $distributorId = null;
    if (isset($_GET["distributorId"]) && !empty($_GET["distributorId"])) {
        $distributorId = cleanQueryParameter($conn, cleanXSS($_GET["distributorId"]));
    }

    $distributorStatusMap = array(
        "1" => "Active",
        "0" => "Inactive",
        "2" => "Deleted",
    );

    $distributorName = "";
    $email = "";
    $distributorStatus = "";
    $distributorGSTNum = "";
    $noOfClients = "";
    $managementFeeSharing = "";
    $distributorBenfName = $distributorIFSC = $distributorBankAccNum = $distributorAccountBranch = "";
    $comment = "";
    $pocDetails = array(
        "1" => array(
            "mobile_num" => "",
            "address" => "",
            "email" => "",
        ),
        "2" => array(
            "mobile_num" => "",
            "address" => "",
            "email" => "",
        ),
        "3" => array(
            "mobile_num" => "",
            "address" => "",
            "email" => "",
        ),
    );

    if (!is_null($distributorId)) { //it is edit mode
        $distributorSearchArr = array('distributor_id' => $distributorId);
        $fieldsStr = "*";
        $distributorInfo = getDistributorsInfo($distributorSearchArr, $fieldsStr, null, $conn);
        if (!noError($distributorInfo)) {
            //error fetching distributor info
            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5) . " Error fetching distributor details.";
        } else {
            $userArray = $distributorInfo["errMsg"];
            foreach ($userArray as $key => $value) {
                $email = $key;
            }
            $distributorInfo = $distributorInfo["errMsg"][$email];
            // These will display data on the form when edit mode is true
            // $distributorInfo = $distributorInfo["errMsg"][$email];
            // printArr($distributorInfo); exit;

            $distributorName = $distributorInfo["distributor_name"];
            $email = $distributorInfo["email"];
            $distributorStatus = $distributorStatusMap[$distributorInfo["status"]];
            $distributorGSTNum = $distributorInfo["gst_no"];
            $noOfClients = $distributorInfo["no_of_client"];
            $managementFeeSharing = $distributorInfo["management_fee_sharing"];
            $distributorBenfName = $distributorInfo["beneficiary_name"];
            $distributorIFSC = $distributorInfo["ifsc_code"];
            $distributorBankAccNum = $distributorInfo["bank_account_no"];
            $distributorAccountBranch = $distributorInfo["account_branch"];

            $pocDetails = json_decode($distributorInfo["poc_details"], true);
            $comment = isset($distributorInfo["comments"]) ? $distributorInfo["comments"] : "";

            $returnArr["errCode"] = -1;
        }
    } else {
        $returnArr["errCode"] = -1;
        $clientCode = "BCPL";
    }
}
?>
<link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/bootstrap-multiselect.css">
<div class="row">
    <form id="addEditDistributorForm" name="addEditDistributorForm" action="javascript:;" data-parsley-validate="" >
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

        <!-- hidden old distributor id to define add/edit mode -->
        <!-- <input type="hidden" name="distributorId" value="<?php //echo $distributorId; ?>" > -->
        <input type="hidden" name="oldGstNum" value="<?php echo $distributorGSTNum; ?>" >
        <input type="hidden" name="oldEmail" value="<?php echo $email; ?>" >

    
        <!-- distributor name, gst num, status row -->
        <div class="col-md-12">
            <!-- distributor name -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Distributor Name&#42;</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required=""
                    id="distributorName" name="distributorName"
                    value="<?php echo $distributorName; ?>"
                >
            </div>
            <!-- End distributor name -->
            <!-- GST Num -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">GST Num&#42;</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required="" data-parsley-gstnum="GSTNUM"
                    id="gstNum" name="gstNum"
                    value="<?php echo $distributorGSTNum; ?>"
                >
            </div>
            <!-- End GST Num -->
            <!-- distributor Status -->
            <div class="col-md-4 form-group">
                <select class="form-control" required=""
                    id="distributorStatus" name="distributorStatus"
                >
                    <option value="" >Select Status</option>
                    <?php
foreach ($distributorStatusMap as $statusCode => $statusName) {
    $selected = "";
    if (strtolower($statusName) == strtolower($distributorStatus)) {
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
            <!-- End distributor Status -->
        </div>
        <!-- end distributor name, type, status row -->

        <!-- no of clients, Management Fee Share -->
        <div class="col-md-12">
            <!-- # of clients -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label"># of clients</label>
                <input type="number" class="form-control"
                    data-parsley-trigger="keyup"
                    id="noOfClients" name="noOfClients"
                    value="<?php echo $noOfClients; ?>"
                >
            </div>
            <!-- End # of clients -->
             <!-- Email -->
             <div class="col-md-4 form-group label-floating is-empty">
            <label class="control-label">Email&#42;</label>
                <input type="email" class="form-control" data-parsley-trigger="keyup" required=""
                    id="email" name="email" value="<?php echo $email; ?>"
                    >
            </div>
            <!-- End Email -->
            <!-- Management Fee Share -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Management fee share&#42;</label>
                <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)" pattern="^\d*(\.\d{0,2})?$"
                    min="0" max="100" step="any"
                    data-parsley-trigger="keyup" required=""
                    id="managementFeeSharing" name="managementFeeSharing"
                    value="<?php echo $managementFeeSharing; ?>"
                >
            </div>
            <!--End of Management Fee Share -->
        </div>
        <!-- end no of clients, Management Fee Share -->

        <!-- Bank details row -->
        <div class="col-md-12">
            <!-- Beneficiary Name -->
            <div class="col-md-3 form-group label-floating is-empty">
                <label class="control-label">Beneficiary Name&#42;</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required="" id="benfName" name="benfName"
                    value="<?php echo $distributorBenfName; ?>"
                >
            </div>
            <!-- End Beneficiary Name -->
            <!-- ifsc code -->
            <div class="col-md-3 form-group label-floating is-empty">
                <label class="control-label">IFSC code&#42;</label>
                <input type="text" class="form-control" maxlength="11"
                data-parsley-trigger="keyup" data-validation="custom" data-validation-regexp="^[A-Za-z]{4}[a-zA-Z0-9]{7}$" id="ifscCode" name="ifscCode"  onkeypress="return blockSpecialChar(event)" 
                    value="<?php echo $distributorIFSC; ?>" 
                    required>
            </div>
            <!-- End ifsc code -->
            <!-- Bank Account Num -->
            <div class="col-md-3 form-group label-floating is-empty">
                <label class="control-label">Bank Account Num&#42;</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required="" id="bankAccNum" name="bankAccNum" minlength="9" maxlength="18" 
                    onkeypress='return restrictAlphabets(event)'  onkeypress="return blockSpecialChar(event)"
                    value="<?php echo $distributorBankAccNum; ?>"
                >
            </div>
            <!-- End Bank Account Num -->
            <!-- Bank branch name -->
            <div class="col-md-3 form-group label-floating is-empty">
                <label class="control-label">Branch Name&#42;</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required="" id="bankBranch" name="bankBranch"
                    value="<?php echo $distributorAccountBranch; ?>"
                >
            </div>
            <!-- End Bank branch name -->
        </div>
        <!-- end Bank details row -->

        <!-- poc1 row -->
        <div class="col-md-12">
            <h4>POC 1</h4>
            <!-- Address -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Address</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup"
                    id="poc_1_address" name="poc[1][address]"
                    value="<?php echo $pocDetails[1]["address"]; ?>"
                >
            </div>
            <!-- End Address -->
            <!-- email -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Email</label>
                <input type="email" class="form-control"
                    data-parsley-trigger="keyup"
                    id="poc_1_email" name="poc[1][email]"
                    value="<?php echo $pocDetails[1]["email"]; ?>"
                >
            </div>
            <!-- End email -->
            <!-- Mobile number -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Mobile number</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup"
                    id="poc_1_mobile_num" name="poc[1][mobile_num]" onkeypress='return restrictAlphabets(event)'
                    value="<?php echo $pocDetails[1]["mobile_num"]; ?>"
                >
            </div>
            <!-- End Mobile number -->
        </div>
        <!-- end poc1 row -->

        <!-- poc2 row -->
        <div class="col-md-12">
            <h4>POC 2</h4>
            <!-- Address -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Address</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup"
                    id="poc_2_address" name="poc[2][address]"
                    value="<?php echo $pocDetails[2]["address"]; ?>"
                >
            </div>
            <!-- End Address -->
            <!-- email -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Email</label>
                <input type="email" class="form-control"
                    data-parsley-trigger="keyup"
                    id="poc_2_email" name="poc[2][email]"
                    value="<?php echo $pocDetails[2]["email"]; ?>"
                >
            </div>
            <!-- End email -->
            <!-- Mobile number -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Mobile number</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup"
                    id="poc_2_mobile_num" name="poc[2][mobile_num]" onkeypress='return restrictAlphabets(event)'
                    value="<?php echo $pocDetails[2]["mobile_num"]; ?>"
                >
            </div>
            <!-- End Mobile number -->
        </div>
        <!-- end poc2 row -->

        <!-- poc3 row -->
        <div class="col-md-12">
            <h4>POC 3</h4>
            <!-- Address -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Address</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup"
                    id="poc_3_address" name="poc[3][address]"
                    value="<?php echo $pocDetails[3]["address"]; ?>"
                >
            </div>
            <!-- End Address -->
            <!-- email -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Email</label>
                <input type="email" class="form-control"
                    data-parsley-trigger="keyup"
                    id="poc_3_email" name="poc[3][email]"
                    value="<?php echo $pocDetails[3]["email"]; ?>"
                >
            </div>
            <!-- End email -->
            <!-- Mobile number -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Mobile number</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup"
                    id="poc_3_mobile_num" name="poc[3][mobile_num]" onkeypress='return restrictAlphabets(event)'
                    value="<?php echo $pocDetails[3]["mobile_num"]; ?>"
                >
            </div>
            <!-- End Mobile number -->
        </div>
        <!-- end poc3 row -->

        <!-- comments row -->
        <div class="col-md-12">
            <!-- Comments -->
            <div class="col-md-12 form-group label-floating is-empty">
                <label class="control-label">Comments</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup"
                    id="comments" name="comments"
                    value="<?php echo $comment; ?>"
                >
            </div>
        </div>
        <!-- End Comments -->

        <!-- submit button -->
        <div class="col-md-12 form-group">
            <button class="btn" type="submit">Save</button>
        </div>
    </form>
</div>
<script src="<?php echo $rootUrl; ?>assets/js/bootstrap-multiselect.js"></script>

<script>
    //These will block the special characters in textbox
    function blockSpecialChar(e){
        var k;
        document.all ? k = e.keyCode : k = e.which;
        return ((k > 64 && k < 91) || (k > 96 && k < 123) || k == 8 || k == 32 || (k >= 48 && k <= 57));
        }

       //These will not allow user to enter alphabets in phone number fields
       function restrictAlphabets(e){
				var x=e.which||e.keycode;
				if((x>=48 && x<=57) || x==8 ||
					(x>=35 && x<=40)|| x==46)
					return true;
				else
					return false;
			}

      //These will not allow to enter more than two number after decimal point
      $(document).on('keydown', 'input[pattern]', function(e){
            var input = $(this);
            var oldVal = input.val();
            var regex = new RegExp(input.attr('pattern'), 'g');

            setTimeout(function(){
            var newVal = input.val();
            if(!regex.test(newVal)){
            input.val(oldVal);
          }
        }, 0);
      });

      //These return 100 if user enter more than 100
     function minmax(value, min, max)
     {
         if(parseInt(value) < min || isNaN(parseInt(value)))
             return "";
         else if(parseInt(value) > max)
             return 100;
         else return value;
     }

       //These will allow user to enter not more than one decimal point
       $('#managementFeeSharing').keypress(function(evt) {
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
 
  // Validation for gst number 
    window.Parsley
    .addValidator('gstnum', {
        requirementType: 'string',
        validateString: function(value) {
            var regGSTNum = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
            if(regGSTNum.test(value)){
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
    $("form#addEditDistributorForm :input").each(function () {
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
    $('form#addEditDistributorForm').parsley().on('field:validated', function () {
        var ok = $('.parsley-error').length === 0;
        $('.bs-callout-info').toggleClass('hidden', !ok);
        $('.bs-callout-warning').toggleClass('hidden', ok);
    })
    .on('form:submit', function () {
        var formData = new FormData($('#addEditDistributorForm')[0]);

        //resetting the error message
        $("#addEditDistributorForm .alert").
        removeClass("alert-success").
        removeClass("alert-danger").
        fadeOut().
        find("span").html("");

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/distributor/",
            data: formData,
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function (user) {
                if (user["errCode"]) {
                    if (user["errCode"] != "-1") { //there is some error
                        $("#addEditDistributorForm .alert").
                            removeClass("alert-success").
                            addClass("alert-danger").
                            fadeIn().
                            find("span").
                            html(user["errMsg"]);
                    } else {
                        $("#addEditDistributorForm .alert").
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
                $("#addEditDistributorForm .alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html("500 internal server error");
            }
        });

    });
</script>