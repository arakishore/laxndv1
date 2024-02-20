<?php
//Add/edit broker view page
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
require_once(__ROOT__.'/model/broker/brokerModel.php');

$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1).$conn["errMsg"];
} else {
    $conn = $conn["errMsg"];
    $returnArr = array();
    
    //TO DO: Logs here
    
    $brokerId = null;
    if ( isset($_GET["brokerId"]) && !empty($_GET["brokerId"]) ) {
        $brokerId = cleanQueryParameter($conn, cleanXSS($_GET["brokerId"]));
    }

    $brokerStatusMap = array(
        "1" => "Active",
        "0" => "Inactive",
        "2" => "Deleted"
    );
    $brokerTypesMap = array(
        "1" => "broker",
        "2" => "custodian",
        "3" => "fund"
    );
    
    $brokerName = $brokerType = "";
    $brokerStatus = "";
    $brokerBSERegNum = $brokerNSERegNum = $brokerCode = "";
    $brokerDeliveryType = $brokerGSTNum = "";
    $brokerBenfName = $brokerIFSC = $brokerBankAccNum = $brokerAccountBranch = "";
    $comment = "";

    if (!is_null($brokerId)) { //it is edit mode
        $brokerSearchArr = array('broker_id'=>$brokerId);
        $fieldsStr = "*";
        $brokerInfo = getBrokersInfo($brokerSearchArr, $fieldsStr, null, $conn);
        if (!noError($brokerInfo)) {
            //error fetching broker info
            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5)." Error fetching broker details.";
        } else {
            $brokerInfo = $brokerInfo["errMsg"][$brokerId];
            
            $brokerName = $brokerInfo["broker_name"];
            $brokerType = $brokerInfo["broker_type"];
            $brokerStatus = $brokerStatusMap[$brokerInfo["status"]];

            $brokerBSERegNum = $brokerInfo["bse_reg_no"];
            $brokerNSERegNum = $brokerInfo["nse_reg_no"];
            $brokerCode = $brokerInfo["broker_code"];
            
            $brokerDeliveryType = $brokerInfo["delivery_type"];
            $brokerGSTNum = $brokerInfo["gst_no"];
            
            $brokerBenfName = $brokerInfo["beneficiary_name"];
            $brokerIFSC = $brokerInfo["ifsc_code"];
            $brokerBankAccNum = $brokerInfo["bank_account_no"];
            $brokerAccountBranch = $brokerInfo["account_branch"];
            
            $comment = isset($brokerInfo["comments"])?$brokerInfo["comments"]:"";

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
    <form id="addEditBrokerForm" name="addEditBrokerForm" 
        action="javascript:;" data-parsley-validate=""
    >
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
        <!-- hidden old broker id field to define add/edit mode -->
        <input type="hidden" name="brokerId" value="<?php echo $brokerId; ?>" >
        <!-- Broker name, type, status row -->
        <div class="col-md-12">
            <!-- broker name -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Broker Name&#42;</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required="" id="brokerName" name="brokerName" 
                    value="<?php echo $brokerName; ?>"
                >
            </div>
            <!-- End broker name -->
            <!-- broker type -->
            <div class="col-md-4 form-group">
                <label class="control-label">Broker Type</label>
                <select class="form-control"  
                    id="brokerType" name="brokerType[]a" 
                    multiple
                >
                <?php
                foreach ($brokerTypesMap as $typeId=>$typeName) {
                    $selected = "";
                    if (strpos($brokerType, $typeName) !== false) {
                        $selected = "selected";
                    }
                ?>
                    <option value="<?php echo $typeName; ?>" <?php echo $selected; ?>>
                        <?php echo $typeName; ?>
                    </option>
                <?php
                }
                ?>
                </select>
            </div>
            <!-- End broker type -->
            <!-- Broker Status -->
            <div class="col-md-4 form-group">
                <select class="form-control" required="" 
                    id="brokerStatus" name="brokerStatus"
                >
                    <option value="" >Select Status</option>
                    <?php
                    foreach ($brokerStatusMap as $statusCode=>$statusName) {
                        $selected = "";
                        if (strtolower($statusName)==strtolower($brokerStatus)) {
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
            <!-- End Broker Status -->
        </div>
        <!-- end Broker name, type, status row -->
        
        <!-- BSE Reg, NSE Reg, Broker Code row -->
        <div class="col-md-12">
            <!-- BSE Reg Num -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">BSE Reg Num&#42;</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required="" id="bseRegNum" name="bseRegNum" 
                    value="<?php echo $brokerBSERegNum; ?>"
                >
            </div>
            <!-- End BSE Reg Num -->
            <!-- nse Reg Num -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">NSE Reg Num&#42;</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required="" id="nseRegNum" name="nseRegNum" 
                    value="<?php echo $brokerNSERegNum; ?>"
                >
            </div>
            <!-- End nse Reg Num -->
            <!-- Broker Code -->
            <div class="col-md-4 form-group label-floating is-empty">
                <label class="control-label">Broker Code&#42;</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required="" id="brokerCode" name="brokerCode" 
                    value="<?php echo $brokerCode; ?>"
                >
            </div>
            <!-- End Broker Code -->
        </div>
        <!-- end BSE Reg, NSE Reg, Broker Code row -->
        
        <!-- delivery type, gst num -->
        <div class="col-md-12">
            <!-- delivery type -->
            <div class="col-md-6 form-group label-floating is-empty">
                <label class="control-label">Delivery Type&#42;</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required="" id="deliveryType" name="deliveryType" 
                    value="<?php echo $brokerDeliveryType; ?>"
                >
            </div>
            <!-- End delivery type -->
            <!-- GST Num -->
            <div class="col-md-6 form-group label-floating is-empty">
                <label class="control-label">GST Num&#42;</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required="" data-parsley-gstnum="GSTNUM" 
                    id="gstNum" name="gstNum" 
                    value="<?php echo $brokerGSTNum; ?>"
                >
            </div>
            <!-- End GST Num -->
        </div>
        <!-- end delivery type, gst num -->

        <!-- Bank details row -->
        <div class="col-md-12">
            <!-- Beneficiary Name -->
            <div class="col-md-3 form-group label-floating is-empty">
                <label class="control-label">Beneficiary Name&#42;</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required="" id="benfName" name="benfName" 
                    value="<?php echo $brokerBenfName; ?>"
                >
            </div>
            <!-- End Beneficiary Name -->
            <!-- ifsc code -->
            <div class="col-md-3 form-group label-floating is-empty">
                <label class="control-label">IFSC code&#42;</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required="" id="ifscCode" name="ifscCode" 
                    value="<?php echo $brokerIFSC; ?>"
                >
            </div>
            <!-- End ifsc code -->
            <!-- Bank Account Num -->
            <div class="col-md-3 form-group label-floating is-empty">
                <label class="control-label">Bank Account Num&#42;</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required="" id="bankAccNum" name="bankAccNum" 
                    value="<?php echo $brokerBankAccNum; ?>"
                >
            </div>
            <!-- End Bank Account Num -->
            <!-- Bank branch name -->
            <div class="col-md-3 form-group label-floating is-empty">
                <label class="control-label">Branch Name&#42;</label>
                <input type="text" class="form-control"
                    data-parsley-trigger="keyup" required="" id="bankBranch" name="bankBranch" 
                    value="<?php echo $brokerAccountBranch; ?>"
                >
            </div>
            <!-- End Bank branch name -->
        </div>
        <!-- end Bank details row -->

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
    $("form#addEditBrokerForm :input").each(function () {
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

    $('#brokerType').multiselect({
        includeSelectAllOption: true
    });

    //handle form submit
    $('form#addEditBrokerForm').parsley().on('field:validated', function () {
        var ok = $('.parsley-error').length === 0;
        $('.bs-callout-info').toggleClass('hidden', !ok);
        $('.bs-callout-warning').toggleClass('hidden', ok);            
    })
    .on('form:submit', function () {
        var formData = new FormData($('#addEditBrokerForm')[0]);
        
        //resetting the error message
        $("#addEditBrokerForm .alert").
        removeClass("alert-success").
        removeClass("alert-danger").
        fadeOut().
        find("span").html("");
        
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/broker/",
            data: formData,
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function (user) {
                if (user["errCode"]) {
                    if (user["errCode"] != "-1") { //there is some error
                        $("#addEditBrokerForm .alert").
                            removeClass("alert-success").
                            addClass("alert-danger").
                            fadeIn().
                            find("span").
                            html(user["errMsg"]);
                    } else {
                        $("#addEditBrokerForm .alert").
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
                $("#addEditBrokerForm .alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html("500 internal server error");
            }
        });
    
    });
</script>