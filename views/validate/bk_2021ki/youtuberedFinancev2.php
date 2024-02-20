<?php
//Manage Clients view page
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
require_once(__ROOT__.'/model/client/clientModel.php');
require_once(__ROOT__.'/model/distributor/distributorModel.php');
require_once(__ROOT__.'/model/validate/validateModel.php');

//Connection With Database
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1).$conn["errMsg"];
} else {
    $conn = $conn["errMsg"];
    $returnArr = array();
     
 
    $contentowner = getContentOwner($conn);
   }
   
   $selectedDate = "";
    if (isset($_GET["reportMonthYear"])) {
        $selectedDate = cleanQueryParameter($conn, cleanXSS($_GET["reportMonthYear"]));
    }
   $nd  = "";
   if (isset($_GET["nd"])) {
    $nd = cleanQueryParameter($conn, cleanXSS($_GET["nd"]));
    }

   $year     = date("Y", strtotime($selectedDate));
   $month    = date("m", strtotime($selectedDate));
   $table_name = 'youtube_red_music_video_finance_report_'.$nd.'_'.$year.'_'.$month; ;
  
   $checkUnAssignedContentOwner = checkUnAssignedContentOwner($table_name,'content_owner',$conn);
   //$listassetChannelID = checkUnAssignedContentOwnerdistinct($table_name,'content_owner',$conn);
  //print_r($listassetChannelID);

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

</style>

<body>

 

    <!--Loading new page-->
    <div class="header" id="youtube1">


        <div class="row">
            <div class="col-lg-1">
                <div class="form-group" style="margin:10px; ">
                    <button type="button" data-dismiss="modal" style="float:left; padding:5px; font-size:15px;">
                        <a style="color:white;" href="../validate/"><i style="font-size:20px;"
                                class="fa fa-arrow-left"></i>
                        </a></button>
                </div>
            </div>
            <div class="col-lg-11">


                <h4 class="modal2-title">Validate Report Youtube Red Finance Report v2 <?php echo $nd?></h4>


            </div>

        </div>
    </div>
    <div class="row">

        <!-- choose field drowpdoun-->
        <div class="col-md-12">
            <!-- <div class="col-md-2" style="margin-left:1vw; ">
           
            <div class="head2" style="margin-left:1vw; margin-top:4vh;">
                <select name="userName" id="searchby" class="form-control">
                    <option value="">Choose a field</option>
                    <option value="Video_id">Video ID</option>
                    <option value="video_title">Video Title</option>
                    <option value="asset_channel">Channel</option>
                    <option value="uploader2">Uploader</option>
                    <option value="uploader">Content Owner</option>
                    <option value="content_type">Content Type</option>
                    <option value="asset_id">Asset ID</option>
                </select>
            </div>
        </div> -->
            <!-- search button -->







            <!-- end Status -->
            <!-- search button -->
            <div class="" style="margin-left:1vw;">
                <input type="hidden" name="nd" id="nd" value="<?php echo $nd?>">
                <!-- <button type="submit" id="search" class="btn btn-success fa fa-search">
                    </button> -->
                <td colspan="7" align="center" style="padding-top:3px; ">
                    <select name="userName" id="contentowner" class="form-control1 selectpicker"
                        data-live-search="true">
                        <option value="">--Select Content Owner--</option>
                        <?php 
                            foreach($contentowner['errMsg'] as $c){
                              echo '<option value="'.$c.'">'.$c.'</option>';
                               
                            }
                      ?>
                    </select>

                    <a class="btn btn-success" id="savebulk" data-toggle="collapse" href="#collapseExample5"
                        role="button" aria-expanded="false" aria-controls="collapseExample"
                        style="margin-left:0.5vw;">Bulk Assign</a>
                    <!-- <a class="btn btn-success" id="onlyunassigned"  href="#unassigned" role="button"
                    aria-expanded="false" aria-controls="collapseExample" style="margin-left:0.5vw;">View Unassigned</a> -->
                    <select id="sortBy" class="form-control1 selectpicker">
                        <option value="">--All--</option>
                        <option value="Unassigned">--Unassigned--</option>
                        <option value="assigned">--Assigned--</option>
                    </select>
                </td>
                <div class="col-md-6 pull-right">
                    <div class="alert" style="display: none">
                        <span>
                            <?php echo $alertMsg; ?>
                        </span>
                    </div>
                </div>

            </div>
            <!-- end search button -->


            <!-- Table Header + Save Button -->

        </div>
    </div>
    <?php
     if((int)$checkUnAssignedContentOwner > 0){
     ?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-danger" style="">
                <span>Systems show #<?php echo $checkUnAssignedContentOwner?> content owner is Not assigned  <a class="btn btn-success" id="btnExportunAssigned" data-toggle="collapse" href="#"   style="margin-left:0.5vw;">Export</a></span>
                <div id="alert" class="alert alert-default" style="display: none;">

</div>
            </div>
        </div>
    </div>
    <?php }?>

    <!--main table page-->
    <div class="post-search-panel">
        <input type="hidden" id="searchInput" placeholder="Type keywords..." />

    </div>
    <table id="example" class="table table-striped table-hover" style="width:100%">
        <thead>
            <?php
             if($nd=="redmusic"){
                 ?>
                 <tr>
                <th><input type="checkbox" name="select_all" value="1" id="example-select-all"></th>
                <th>Video Id</th>
                <th>content Type</th>
                <th>asset_labels</th>
               
                <th>Content Owner</th>
                <th>partner Revenue</th>
                <th>assetID</th>
                <th>From-file</th>
                
               
            </tr> 
                 <?php
             } else {
            ?>
            <tr>
                <th><input type="checkbox" name="select_all" value="1" id="example-select-all"></th>
                <th>Video Id</th>
                <th>content Type</th>
                <th>video Title</th>
               
                <th>Content Owner</th>
                <th>partner Revenue</th>
                <th>assetID</th>
                <th>From-file</th>
                
               
            </tr>
            <?php }?>
        </thead>

    </table>


    <div class="modal fade" id="deleteDistributorModal">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Assing content owner</h4>
            </div>
            <div class="modal-body">
                <p id="alertmsg">Are you sure you want to bulk assign for selected records?</p>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="deleteDistributorBtn">Ok</button>
                </div>
            </div>
        </div>
    </div>



</body>

<script>
$(document).ready(function() {


    $('#btnExportunAssigned').on('click', function(e) {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/validate/export/",
            data: {
                selected_date: '<?php echo $_GET["reportMonthYear"];?>',
                type: 'youtube_red_music_video_finance_report',
                nd: '<?php echo $nd?>'
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
            error: function(jqXHR, exception) {
                var msg = '';
        if (jqXHR.status === 0) {
            msg = 'Not connect.\n Verify Network.';
        } else if (jqXHR.status == 404) {
            msg = 'Requested page not found. [404]';
        } else if (jqXHR.status == 500) {
            msg = 'Internal Server Error [500].';
        } else if (exception === 'parsererror') {
            msg = 'Requested JSON parse failed.';
        } else if (exception === 'timeout') {
            msg = 'Time out error.';
        } else if (exception === 'abort') {
            msg = 'Ajax request aborted.';
        } else {
            msg = 'Uncaught Error.\n' + jqXHR.responseText;
        }
        console.log("msg",msg);
                $(".alert").
                removeClass("alert-success").
                addClass("alert-danger").
                fadeIn().
                find("span").
                html("500 Internal Server Error");
            }
        });
    });


    /*    $.ajax({url: "../../controller/validate/youtubev2.php",
           data: {
                   reportMonthYear: '<?php //echo $_GET["reportMonthYear"]?>',
                   mode: 'youtube_video_claim_report_nd1'
               },
           success: function(result){
           $("#div1").html(result);
       }}); */

    $('#example thead tr').clone(true).appendTo('#example thead');
    $('#example thead tr:eq(1) th').each(function(i) {
        var title = $(this).text();
        if (title) {
            $(this).html('<input type="text" placeholder="Search ' + title + '" />');

            $('input', this).on('keyup change', function() {
                if (table.column(i).search() !== this.value) {
                    table
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        }
    });
    var table = $('#example').DataTable({
        "bInfo": false,
        "processing": true,
        // "searching": false,
        "orderCellsTop": true,
        "fixedHeader": true,
        "serverSide": true,
        "ajax": {
            "url": "../../controller/validate/youtuberedFinancev2.php",
            "data": function(d) {
                return $.extend({}, d, {
                    "search_keywords": $("#searchInput").val().toLowerCase(),
                    "filter_option": $("#sortBy").val().toLowerCase(),
                    "report": 'youtube_red_music_video_finance_report',
                    "reportMonthYear": '<?php echo $_GET["reportMonthYear"]?>',
                    "nd": '<?php echo $_GET["nd"]?>'
                });
            }
        },
        "scrollY": "350px",
        "scrollCollapse": true,
        "lengthMenu": [
            [50, 100, 200, 500, 1000],
            [50, 100, 200, 500, 1000]
        ],
        'columnDefs': [{
            'targets': 0,
            'checkboxes': {
                'selectRow': true
            }
        }],
        'select': {
            'style': 'multi'
        },
        'initComplete': function(settings, json) {
            console.log('rest');
            $('.selectpicker').selectpicker('refresh');
        }
    });
    $('.selectpicker').selectpicker();
    // table.draw();

    // Redraw the table based on the custom input
    $('#searchInput,#sortBy').bind("keyup change", function() {
        table.draw();
    });
    // $('#onlyunassigned').bind("click", function(){
    //     table.draw();
    // });
    // Handle click on "Select all" control
    $('#example-select-all').on('click', function() {
        // Get all rows with search applied
        var rows = table.rows({
            'search': 'applied'
        }).nodes();
        // Check/uncheck checkboxes for all rows in the table
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });

    // Handle click on checkbox to set state of "Select all" control
    $('#example tbody').on('change', 'input[type="checkbox"]', function() {
        // If checkbox is not checked
        if (!this.checked) {
            var el = $('#example-select-all').get(0);
            // If "Select all" control is checked and has 'indeterminate' property
            if (el && el.checked && ('indeterminate' in el)) {
                // Set visual state of "Select all" control
                // as 'indeterminate'
                el.indeterminate = true;
            }
        }
    });

    // Handle form submission event
    $('#savebulk').on('click', function(e) {
        var rows_selected = table.column(0).checkboxes.selected();
        if (rows_selected.length > 0) {
            confirmbox();
        }

    });
    $('#deleteDistributorBtn').on('click', function(e) {
        bulkassign();
        $("#deleteDistributorModal").modal('toggle');
    });


    function bulkassign() {
        var rows_selected = table.column(0).checkboxes.selected();
        var rows_selected_ids = rows_selected.join(',');
        saveContentowner(rows_selected_ids, $('#contentowner').val());
    }

    function confirmbox() {
        $("#alertmsg").html('Are you sure you want to bulk assign for selected records?');
        $("#deleteDistributorModal").modal();
    }

    $(document).on('change', '.cselect', function() {
        var id = $(this).data("id");
        saveContentowner(id, this.value);
    });

    function saveContentowner(ids, contentowner) {
        console.log(ids);
        if (!ids) {
            alert('Please select records..');
            return false;
        }
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $rootUrl; ?>controller/validate/contentOwner.php",
            data: {
                id: ids,
                nd: '<?php echo $nd?>',
                contentOwner: contentowner,
                reportMonthYear: '<?php echo $_GET["reportMonthYear"]?>',
                report: 'youtube_red_music_video_finance_report'
            },
            success: function(response) {
                table.ajax.reload();
                //handle error in response
                if (response["errCode"]) {
                    if (response["errCode"] != "-1") {
                        console.log("hiee");
                        $(".alert").css("display", "block");
                        //there was an error, alert the error and hide the form.
                        $(".alert").
                        removeClass("alert-success").
                        addClass("alert-danger").
                        fadeIn().
                        find("span").
                        html(response["errMsg"]);

                    } else {
                        $(".alert").css("display", "block");
                        $(".alert").
                        removeClass("alert-danger").
                        addClass("alert-success").
                        fadeIn().
                        find("span").
                        html(response["errMsg"]);
                        $(".alert").fadeOut(3000);
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
    }


});
</script>
<script src="<?php echo $rootUrl; ?>assets/js/bootstrap.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>

</html>