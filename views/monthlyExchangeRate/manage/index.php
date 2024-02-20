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
require_once(__ROOT__.'/model/client-transaction/clientTransactionModel.php');

$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1) . $conn["errMsg"];
} else {
    $conn = $conn["errMsg"];
    $returnArr = array();

    //TO DO: Logs here

    $rateId = null;
    if (isset($_GET["rateId"]) && !empty($_GET["rateId"])) {
        $rateId = cleanQueryParameter($conn, cleanXSS($_GET["rateId"]));
    }
    $month = "";
    $revenueShareYoutube = $revenueShareYoutubeRed = $revenueShareYoutubeAudioRed = "";
    $revenueShareYoutubeAudio = $revenueItunes = $revenueAppleMusic = "";


    if (!is_null($rateId)) { //it is edit mode
        $rateSearchArr = array('rate_id' => $rateId);
        $fieldsStr = "*";
        $clientTransactionInfo = getMonthlyRateInfo($rateSearchArr, $fieldsStr, null, $conn);
        if (!noError($clientTransactionInfo)) {
            //error fetching distributor info
            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5) . " Error fetching distributor details.";
        } else {
            // These will display data on the form when edit mode is true
            $clientTransactionInfo = $clientTransactionInfo["errMsg"][$rateId];
          

            $month = date('Y-m-d',strtotime($clientTransactionInfo["month_year"]));
            $rateDetails = json_decode($clientTransactionInfo["rates_json"]);
            $revenueShareYoutube = $rateDetails->revenueShareYoutube;
            $revenueShareYoutubeRed = $rateDetails->revenueShareYoutubeRed;
            $revenueShareYoutubeAudioRed = $rateDetails->revenueShareYoutubeAudioRed;
            $revenueShareYoutubeAudio = $rateDetails->revenueShareYoutubeAudio;
            $revenueItunes = $rateDetails->revenueItunes;
            $revenueAppleMusic = $rateDetails->revenueAppleMusic;

            $returnArr["errCode"] = -1;
        }
    } else {
        $returnArr["errCode"] = -1;
    }
}
?>
<link rel="stylesheet" type="text/css" href="<?php echo $rootUrl; ?>assets/css/bootstrap-multiselect.css">
<div class="row">
    <form id="addEditMonthlyRateForm" name="addEditMonthlyRateForm" action="javascript:;" data-parsley-validate="" >
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
        <input type="hidden" name="rateId" value="<?php echo $rateId; ?>" >
        <input type="hidden" name="oldMonthlyRate" value="<?php echo $month; ?>" >
        <!--<input type="hidden" name="oldGstNum" value="<?php //echo $distributorGSTNum; ?>" >-->
       
        <?php
        if(!is_null($rateId))
        {
        ?>
        <!-- datepicker -->
        <div class="col-md-12">
            <div class="col-md-4 form-group label-floating is-empty">
            <input type="date" class="form-control marg-left-10" name="date" id="date" value="<?php echo $month; ?>">
            </div>
            <script>
                   $(function(){
                    $("#date").attr('readonly', 'readonly');
                  });
            </script>
        </div>
        <!-- End datepicker name -->
        <?php
        }
        else { 
        ?>
        <!-- datepicker -->
        <div class="col-md-12">
        <!-- datepicker -->
        <div class="col-md-12">
            <div class="col-md-4 form-group label-floating is-empty">
            <!--<input class="form-control marg-left-10" name="datepicker"  type="text" id="datepicker">-->
            <input type="date" class="form-control marg-left-10" name="date" id="date">
            </div>
            <script>
                   $(function(){
                   var pickerOpts = 
                   dateFormat: "yy-mm"
                    };  
                   $("#date").datepicker(pickerOpts);
                   dateFormat: "yy-mm-dd"
                    };  
                   $("#datepicker").datepicker(pickerOpts);
                  });
            </script>
        </div>
        <!-- End datepicker -->
        <?php
        } 
        ?>
    
        <!----------------------Client Details---------------------->
        <div class = "col-md-12 clientTypeDetails">
        <div class = "col-md-12">
         <!-- Revenue share -->
            <div class="col-md-4 form-group label-floating is-empty">
            <label class="control-label">Revenue Share Youtube&#42;</label>
                <input type="number" class="form-control"  onkeyup="this.value = minmax(this.value, 0, 99.99)" pattern="^\d*(\.\d{0,2})?$" min="0" max="100" step="any" id="revenueShareYoutube"
                 required="" name="clientTypeDetails[revenueShareYoutube]" value="<?php echo $revenueShareYoutube; ?>">
            </div>
            <!-- Revenue share -->
             <!-- Validate Report Youtube -->
             <div class="col-md-4 form-group label-floating is-empty">
             <label class="control-label">Revenue Share Youtube Red&#42;</label>
                <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)"  min="0" max="100" step="any"
                    id="revenueShareYoutubeRed" name="clientTypeDetails[revenueShareYoutubeRed]" required="" pattern="^\d*(\.\d{0,2})?$"
                     value="<?php echo $revenueShareYoutubeRed; ?>">
            </div>
            <!-- Validate Report Youtube -->
        </div>

        <div class = "col-md-12">
             <!-- Validate Report youtube Audio Red -->
             <div class="col-md-4 form-group label-floating is-empty">
            <label class="control-label">Revenue Share Youtube Audio&#42;</label>
                <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)" min="0" max="100" step="any"
                    id="revenueShareYoutubeAudio" name="clientTypeDetails[revenueShareYoutubeAudio]" required="" pattern="^\d*(\.\d{0,2})?$"
                    value="<?php echo $revenueShareYoutubeAudio; ?>">
            </div>
            <!-- Validate Report youtube Audio Red -->
             <!--Validate Report Youtube Red -->
             <div class="col-md-4 form-group label-floating is-empty">
             <label class="control-label">Revenue Share Youtube Audio Red&#42;</label>
                <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)" min="0" max="100" step="any"
                id="revenueShareYoutubeAudioRed" name="clientTypeDetails[revenueShareYoutubeAudioRed]"   required="" pattern="^\d*(\.\d{0,2})?$"
                value="<?php echo $revenueShareYoutubeAudioRed; ?>">
            </div>
            <!-- Validate Report Youtube Red -->
           
        </div>
        
        <div class = "col-md-12">
             <!-- Validate Report Youtube Audio -->
             <div class="col-md-4 form-group label-floating is-empty">
             <label class="control-label">Revenue Itunes&#42;</label>
                <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)" min="0" max="100" step="any" pattern="^\d*(\.\d{0,2})?$"
                    id="revenueItunes" name="clientTypeDetails[revenueItunes]" required="" value="<?php echo $revenueItunes; ?>">
            </div>
            <!-- Validate Report Youtube Audio -->
             <!-- Validate Report Itunes -->
             <div class="col-md-4 form-group label-floating is-empty">
             <label class="control-label">Revenue Apple Music&#42;</label>
                <input type="number" class="form-control" onkeyup="this.value = minmax(this.value, 0, 99.99)" min="0" max="100" step="any" pattern="^\d*(\.\d{0,2})?$"
                    id="revenueAppleMusic" name="clientTypeDetails[revenueAppleMusic]" required="" value="<?php echo $revenueAppleMusic; ?>">
            </div>
            <!-- Validate Report Itunes -->
        </div>
            <!-- client type row -->
        </div>
<!----------------------End of Client Details----------------------->

        <!-- submit button -->
        <div class="col-md-12 form-group">
            <button class="btn" type="submit">Save</button>
        </div>
    </form>
</div>
<script src="<?php echo $rootUrl; ?>assets/js/bootstrap-multiselect.js"></script>

<script>
  

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
     $('#revenueShareYoutube, #revenueShareYoutubeRed, #revenueShareYoutubeAudioRed, #revenueShareYoutubeAudio, #revenueItunes, #revenueAppleMusic').keypress(function(evt) {
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

    //managing the floating labels behaviour
    $("form#addEditMonthlyRateForm :input").each(function () {
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
    $('form#addEditMonthlyRateForm').parsley().on('field:validated', function () {
        var ok = $('.parsley-error').length === 0;
        $('.bs-callout-info').toggleClass('hidden', !ok);
        $('.bs-callout-warning').toggleClass('hidden', ok);
    })
    .on('form:submit', function () {
        var formData = new FormData($('#addEditMonthlyRateForm')[0]);

        //resetting the error message
        $("#addEditMonthlyRateForm .alert").
        removeClass("alert-success").
        removeClass("alert-danger").
        fadeOut().
        find("span").html("");

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/client-transaction/",
            data: formData,
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function (user) {
                if (user["errCode"]) {
                    if (user["errCode"] != "-1") { //there is some error
                        $("#addEditMonthlyRateForm .alert").
                            removeClass("alert-success").
                            addClass("alert-danger").
                            fadeIn().
                            find("span").
                            html(user["errMsg"]);
                    } else {
                        $("#addEditMonthlyRateForm .alert").
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
                $("#addEditMonthlyRateForm .alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html("500 internal server error");
            }
        });

    });
</script>