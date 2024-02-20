<?php
//Manage Clients view page
session_start();

//prepare for request
//include necessary helpers
require_once '../../../config/config.php';

//check if session is active
$sessionCheck = checkSession();

//include some more necessary helpers
require_once __ROOT__ . '/config/dbUtils.php';
require_once __ROOT__ . '/config/errorMap.php';
require_once __ROOT__ . '/config/logs/logsProcessor.php';
require_once __ROOT__ . '/config/logs/logsCoreFunctions.php';
require_once __ROOT__ . '/vendor/phpoffice/phpspreadsheet/src/Bootstrap.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

//include necessary models
require_once __ROOT__ . '/model/user/userModel.php';
require_once __ROOT__ . '/model/activate/activateModel.php';
require_once __ROOT__ . '/model/distributor/distributorModel.php';
require_once __ROOT__ . '/model/validate/validateModel.php';
require_once __ROOT__ . '/model/client/clientDashboardModel.php';
require_once __ROOT__ . '/model/client/clientModel.php';
require_once __ROOT__ . '/model/reports/appleModel.php';
//Connection With Database
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1) . $conn["errMsg"];
} else {
    $conn = $conn["errMsg"];
    $returnArr = array();
    $email = $_SESSION['userEmail'];
    $userName = $email;
    
  
    if(isset($_GET["userName"]) && !empty($_GET["userName"])) {
         $email = $_GET["userName"];
         $userName = $_GET['userName'];
    } else {
        $email = $_SESSION['userEmail'];
        $userName = $_SESSION['userEmail'];
    }

    $type_table = (isset($_GET['type_table'])) ? $_GET['type_table'] :'nd';


    $selectedDate = $_GET["reportMonthYear"];
    $year = date("Y", strtotime($selectedDate));
    $month = date("m", strtotime($selectedDate));

    //initialize logs
    $logsProcessor = new logsProcessor();
    $initLogs = initializeJsonLogs($email);
    $logFilePath = $logStorePaths["clients"];
    $logFileName = "viewClients.json";

    //check current month data avaialbe for client
    $dataavaiableforthismonth = false;
    $clientSearchArr = array('email' => $email);
    //echo "<br>we are in page youtuberedmusic_v2.php";
    //print_r($clientSearchArr);

    $fieldsStr = "client_username, email";
    $clientInfo = getClientsInfo_email($clientSearchArr, $fieldsStr, null, $conn);
     
    if (!noError($clientInfo)) {
        //error fetching latest client info
        $logMsg = "Error Fetching client info: " . $clientInfo["errMsg"];
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5) . " Could not get client Info for {$email}.";
    } else {
        $clientname = $clientInfo['errMsg'][$email]['client_username'];

        $table_type_name = 'report_audio_activation_'.$type_table.'_' . $year . '_' . $month;


        $allfantable = getAvilableActivateReportsYoutubev2($table_type_name, $clientname, $conn);
       
        if (!noError($allfantable)) {
            //error fetching latest client info
            $logMsg = "Error Fetching client info: " . $allfantable["errMsg"];
            $logData["step5.1"]["data"] = "5.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5) . " Could not get client Info for {$email}.";
        } else {
            $allfantable = $allfantable['errMsg'];
            if (!empty($allfantable)) {
                if (in_array($_GET['reportMonthYear'], $allfantable)) {
                    $dataavaiableforthismonth = true;
                }
            }
        }
    }
   
    if (!$dataavaiableforthismonth) {
    header('Location:' . $rootUrl . 'views/dashboard/client');
    }
 



    //end check current month data


     $table_name1 = 'report_audio_'.$type_table.'_' . $year . '_' . $month;
 
    //get current client
    $myclient = getClientsInfo_email(
        ['email' => $email],
        'client_username,email',
        null,
        $conn
    );
//print_r($myclient);

    if (!noError($myclient)) {

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5) . " Error fetching clients details.";
        echo json_encode($returnArr);exit;
    }
    $myclientname = current($myclient['errMsg']);
    $myclientname = $myclientname['client_username'];
    $_SESSION['client'] = $myclientname;


    
    //activity_downlaod_report module for checking is it quee
    $status_flag = 0;

    $selected_date = $year.'-'.$month;
    $type_cate = $type_table;
    $type_table = $type_table;
     $sql = "select * from activity_downlaod_report where  type_table='{$type_table}' and content_owner = '{$_SESSION['client']}' and selected_date='{$selected_date}' and type_cate='{$type_cate}' limit 0,1 ";
   $resultQyery = runQuery($sql, $conn);
   $resultQyeryscheck = mysqli_num_rows($resultQyery["dbResource"]);
  
   if ($resultQyeryscheck > 0) {
       $resultQyerydata = mysqli_fetch_assoc($resultQyery["dbResource"]);
       $status_flag =$resultQyerydata['status_flag'];
   }
   //end activity_downlaod_report
   
    //get all chennelid of this report

    $getchennels = getChannelsofthereportv2($table_name1, $conn, '', $_SESSION['client']);
   // $getchennels=[];
    //$getchennels['errMsg']=[];
    if (!noError($getchennels)) {

        //error fetching all clients info
        $logMsg = "Couldn't fetch all clients info: {$getchennels["errMsg"]}";
        $logData["step5.1"]["data"] = "5.1. {$logMsg}";
        $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5) . " Error fetching get chennels details.";

    }
    $getallchennels = $getchennels['errMsg'];

    //set different getter arguments if it is in export mode
    $export = false;
    if (isset($_GET["export"])) { 
    }
}

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
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" type="text/css"
        href="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.7/css/dataTables.checkboxes.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs/dt-1.10.21/datatables.min.css" />

    <script src="<?php echo $rootUrl; ?>assets/js/jquery.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js" type="text/javascript"></script>

    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap.min.js" type="text/javascript"></script>
    <script src="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.7/js/dataTables.checkboxes.min.js"
        type="text/javascript"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css"
        rel="stylesheet" />


</head>
<style>
.nav-pills>li.active>a,
.nav-pills>li.active>a:focus,
.nav-pills>li.active>a:hover {
    color: white;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    background: rgba(255, 255, 255, .8) url('http://i.stack.imgur.com/FhHRx.gif') 50% 50% no-repeat;
}

/* When the body has the loading class, we turn
   the scrollbar off with overflow:hidden */
body.loading .modal {
    overflow: hidden;
}

/* Anytime the body has the loading class, our
   modal element will be visible */
body.loading .modal {
    display: block;
}


.btn.btn-warning,
.btn.btn-warning:hover,
.btn.btn-warning:focus,
.btn.btn-warning:active,
.btn.btn-warning.active,
.btn.btn-warning:active:focus,
.btn.btn-warning:active:hover,
.btn.btn-warning.active:focus,
.btn.btn-warning.active:hover,
.open>.btn.btn-warning.dropdown-toggle,
.open>.btn.btn-warning.dropdown-toggle:focus,
.open>.btn.btn-warning.dropdown-toggle:hover,
.navbar .navbar-nav>li>a.btn.btn-warning,
.navbar .navbar-nav>li>a.btn.btn-warning:hover,
.navbar .navbar-nav>li>a.btn.btn-warning:focus,
.navbar .navbar-nav>li>a.btn.btn-warning:active,
.navbar .navbar-nav>li>a.btn.btn-warning.active,
.navbar .navbar-nav>li>a.btn.btn-warning:active:focus,
.navbar .navbar-nav>li>a.btn.btn-warning:active:hover,
.navbar .navbar-nav>li>a.btn.btn-warning.active:focus,
.navbar .navbar-nav>li>a.btn.btn-warning.active:hover,
.open>.navbar .navbar-nav>li>a.btn.btn-warning.dropdown-toggle,
.open>.navbar .navbar-nav>li>a.btn.btn-warning.dropdown-toggle:focus,
.open>.navbar .navbar-nav>li>a.btn.btn-warning.dropdown-toggle:hover {
    background-color: #099c88;
    color: #fff;
}
</style>

<body>


    <?php  
?>

    <!--Loading new page-->
    <div class="header" id="youtube1">


        <div class="row">
            <div class="col-lg-1">
                <div class="form-group" style="margin:10px; ">
                    <button type="button" data-dismiss="modal" style="float:left; padding:5px; font-size:15px;">
                        <a style="color:white;" href="../client/?userName=<?php echo $userName?>"><i
                                style="font-size:20px;" class="fa fa-arrow-left"></i>
                        </a></button>
                </div>
            </div>
            <div class="col-lg-5">


                <h4 class="modal2-title"><?php echo $type_table?> Music <?php echo $selectedDate?> </h4>


            </div>

        </div>
    </div>

    <div class="col-md-12">
        <div id="alert" class="alert alert-default" style="display: none;">

        </div>
    </div>
    <div class="col-md-4">


        <?php
        //echo $status_flag;
        if($status_flag==0 || $status_flag==3 ){
            ?>
        <button type="submit" name="export" id="export" class="btn btn-warning fa fa-download" style="font-size:20px;">
        </button>
        <?php }  else {

        $fileis = $_SESSION['client'] . '_report_audio_'.$type_table.'_' . $year . '_' . $month . '.zip';
        if (file_exists('../../../excelreports/' . $fileis)) {
            ?>
        <a href='../../../excelreports/<?=$fileis?>'><button type="button" name="btnDownload" id="btnDownload"
                class="btn btn-info">Download zip</button></a>
        <?php
        } else {
            ?>
        <div id="alertaaa" class="text-center alert alert-info"> Report generation is in queue, will notify via mail

        </div>
        <?php
        }
                
            }
?>




    </div>
    <div class="col-md-2">
        <a href="saavanmusic_detailview_v2.php?type_table=<?php echo $type_table?>&userName=<?php echo $userName?>&reportMonthYear=<?php echo $_GET["reportMonthYear"]?>"
            target="_blank"><button type="button" name="Detail" id="Detail" class="btn btn-warning">Detail View</button></a>
    </div>

    <!-- choose field drowpdoun-->
    
    </div>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>


 
    <?php

$sumoftotalpayout = 0;
$sumoftotalfinalpayable = 0;
$clientSearchArr = array("content_owner" => $_SESSION['client']);
$table_type_name = 'report_audio_activation_'.$type_table.'_' . $year . '_' . $month;


$allClientsInfo = getActivationReportSummaryv2(
    $table_type_name,
    $clientSearchArr,
    $_SESSION['client'],
    $conn
);

if (!noError($allClientsInfo)) {

    //error fetching all clients info
    $logMsg = "Couldn't fetch all activation info: {$allClientsInfo["errMsg"]}";
    $logData["step5.1"]["data"] = "5.1. {$logMsg}";
    $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);

    $returnArr["errCode"] = 5;
    $returnArr["errMsg"] = getErrMsg(5) . " Error fetching activation details.";
} else {
    if (!empty($allClientsInfo)) {
        $activationdata = $allClientsInfo['errMsg'];

    }

}
 
 
?>

    <!-- <table id="example" class="table table-striped table-hover" style="width:100%">
        <thead>
            <tr>
                <th>Channel Id</th>
                <th>Video Id</th>

                <th>Video title</th>
                <th>Youtube Payout</th>
                <th>Final Payable</th>
            </tr>
        </thead>
         

    </table>
-->
<div class="col-md-12">
            <div class="alert alert-default">
                <h5>Total  Received : <?php echo number_format($activationdata['total_amt_recd'],16,'.','')?> USD </h5>
               
                <h5>Final Payable : (<?php echo number_format($activationdata['total_amt_recd'],16,'.','')?>) x
                    <?php echo number_format($activationdata['shares'],2,'.','')?> % = <?php echo number_format($activationdata['final_payable'],16,'.','')?> </h5>

                   
                    
            </div>
        </div>


</body>

<script>
$(document).ready(function() {

    ///chart code start
    /*
    google.charts.load('current', {
        'packages': ['corechart']
    });
    google.charts.setOnLoadCallback(drawChart);
    google.charts.setOnLoadCallback(ViewsdrawChart);

    var jsonData = $.ajax({
        url: "../../../controller/client/dashboard/chart/getYoutubeRedmusicChartDatav2.php",
        dataType: "json",
        data: {
            selected_date: '<?php echo $_GET["reportMonthYear"]; ?> '
        },
        async: false
    }).responseText;

    jsonData = JSON.parse(jsonData);
    console.log("jsonData ", jsonData);
 */
    function drawChart() {


        var data = new google.visualization.DataTable(jsonData.revenue);

        // var data2 = new google.visualization.DataTable(jsonData.views);

        var options = {
            // title: 'Revenue for youtube',
            isStacked: 'percent',
            // height: 400,
            hAxis: {
                format: 'd/M/yy'
            },
            vAxis: {
                gridlines: {
                    color: 'none'
                },
                minValue: 0
            }
        };

        var revchart = new google.visualization.LineChart(document.getElementById('revenue_chart_div'));

        revchart.draw(data, {
            isStacked: 'percent'
        });



    }

    function ViewsdrawChart() {
        var data2 = new google.visualization.DataTable(jsonData.views);

        var options = {
            title: 'Views per day',
            isStacked: 'percent',
            height: 400,
            width: 1000,
            hAxis: {
                format: 'd/M/yy'
            },
            vAxis: {
                gridlines: {
                    color: 'none'
                },
                minValue: 0
            }
        };

        var viewchart = new google.visualization.LineChart(document.getElementById('views_chart_div'));

        viewchart.draw(data2, options);



    }

    ///end chart code
    /*
        var table = $('#example').DataTable({
            "processing": true,
            "searching": true,
            "orderCellsTop": true,
            "fixedHeader": true,
            "serverSide": true,
            "ajax": {
                "url": "../../../controller/client/dashboard/youtuberedmusic_v2.php",
                "data": function(d) {
                    return $.extend({}, d, {
                        "reportMonthYear": '<?php echo $_GET["reportMonthYear"] ?>',
                        "email": '<?php echo $_GET["userName"] ?>'
                    });
                },

            },
            "scrollY": "350px",
            "scrollCollapse": true,
            "lengthMenu": [
                [100, 200, 500],
                [100, 200, 500]
            ],


        });

    */

    $('#export').on('click', function(e) {

        var fewSeconds = 120;
    var btn = $(this);
    btn.prop('disabled', true);
    setTimeout(function(){
        btn.prop('disabled', false);
    }, fewSeconds*1000);
    
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/client/dashboard/export/",
            data: {
                selected_date: '<?php echo $_GET["reportMonthYear"]; ?>',
                type: '<?php echo $type_table?>',
                type_table: '<?php echo $type_table ?>'
            },
            success: function(response) {
                console.log(response);
                //handle error in response
                if (response["errCode"]) {
                    if (response["errCode"] != "-1") {

                        $("#alert").css("display", "block");
                        //there was an error, alert the error and hide the form.
                        $("#alert").
                        removeClass("alert-success").
                        addClass("alert-danger").
                        fadeIn().
                        html(response["errMsg"]);
                        // setTimeout(function(){
                        //     window.location.reload();
                        // }, 3000);
                        // $("#uploadMISFilesContainer").hide();
                    } else {
                        $("#alert").css("display", "block");
                        $("#alert").
                        removeClass("alert-danger").
                        addClass("alert-success").
                        fadeIn().
                        html(response["errMsg"]);
                        setTimeout(function() {
                            window.location.reload();
                        }, 3000);

                    }
                }
            },
            error: function() {
                $(".alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html("500 Internal Server Error");
            }
        });
    });
 


});
$body = $("body");

$(document).on({
    ajaxStart: function() {
        $body.addClass("loading");
    },
    ajaxStop: function() {
        $body.removeClass("loading");
    }
});
</script>
<script src="<?php echo $rootUrl; ?>assets/js/bootstrap.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>
<div class="modal">
    <!-- Place at bottom of page -->
</div>

</html>