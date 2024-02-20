<?php
//////////////////////////////Prepare for request/////////////////////////////////
session_start();

//require helpers
require_once ('../../../../config/config.php');
require_once (__ROOT__ . '/config/dbUtils.php');
require_once (__ROOT__ . '/config/errorMap.php');
require_once (__ROOT__ . '/config/auth.php');

//include necessary models
require_once(__ROOT__.'/model/client/clientDashboardModel.php');
require_once(__ROOT__.'/model/reports/appleModel.php'); 
//TO DO: Logs
$returnArr = array();
$fileLocation = '';
$controller = '';
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);

if (!noError($conn))
{
    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = "Error Connecting to DB";
    echo (json_encode($returnArr));
    exit;
}
else
{ 
    $channelid = null;
    if(isset($_GET['channelId']) && $_GET['channelId']!=''){
        $channelid = $_GET['channelId'];
    }

    $type_table = "applemusic";
    if(isset($_GET['type_table']) && $_GET['type_table']!=''){
        $type_table = $_GET['type_table'];
    }

    $yyyymm=$_GET['selected_date'];
    $ymdata = explode('-',$yyyymm);
    //db connection successful
    $conn = $conn["errMsg"];
    $offset = 0;
    $resultsPerPage = 50;
    $year= trim($ymdata[0]) ;
    $month=trim($ymdata[1]);
    $clientsession = NULL;//'TeamFilm';
 

    $allchart=[];
     
    $table_type_name = 'report_audio_'.$type_table.'_' . $year . '_' . $month;
    $chartInfo  = RevenueChartClientsAppleMusicReportv2($table_type_name,$conn,$offset,$resultsPerPage,'',$clientsession,$channelid );

   // print_r($chartInfo);

    if (!noError($chartInfo)) {

 
        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error fetching clients details.";
    } else {  
         $chartInfo = $chartInfo["errMsg"];
        
         // ISRC   ,ItemTitle,Quantity ,Label , sum(USD) as FinalPayable

         $revchart=[];
         $revchart['cols']=[

                ['label'=>'Label','type'=>'string'],
                ['label'=>'All Revenue','type'=>'number'],
               
                ];
        $revchart['rows']= $chartInfo;  
        $allchart['revenue']=$revchart;
        
    }
    $chartInfo  = ViewsChartClientsAppleMusicReportv2($table_type_name,$conn,$offset,$resultsPerPage,'',$clientsession,$channelid);
    //print_r($chartInfo);
    if (!noError($chartInfo)) {

 
        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error fetching clients details.";
    } else {  
         $chartInfo = $chartInfo["errMsg"];
        
         $viewchart=[];
         $viewchart['cols']=[
            ['label'=>'ISRC','type'=>'string'],
            ['label'=>'View','type'=>'number'],
          
                ];
        $viewchart['rows']= $chartInfo;  
        $allchart['views']=$viewchart;
        
    }

    echo json_encode($allchart);exit;




    $data = '{
        "cols": [
              {"id":"","label":"Time of Day","pattern":"","type":"date"},
              {"id":"","label":"All Revenue","pattern":"","type":"number"},
              {"id":"","label":"Partner-Provided","pattern":"","type":"number"},
              {"id":"","label":"UGC","pattern":"","type":"number"}
            ],
        "rows": [
              {"c":[{"v":"Date(2015, 0, 1)"},{"v":5},{"v":3},{"v":2}]},
              {"c":[{"v":"Date(2015, 0, 2)"},{"v":7},{"v":5},{"v":6}]},
              {"c":[{"v":"Date(2015, 0, 3)"},{"v":8},{"v":6},{"v":12}]}
              
            ]
      }';
    

   echo $data;
    exit;

}

?>
