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
    $yyyymm=$_GET['selected_date'];
    $ymdata = explode('-',$yyyymm);
    //db connection successful
    $conn = $conn["errMsg"];
    $offset = 0;
    $resultsPerPage = 50;
    $year= $ymdata[0] ;
    $month=$ymdata[1];
    $clientsession = 'TeamFilm';
    $finance_report_table = 'youtube_finance_report_'.$year.'_'.$month;
    $youtube_report_table = 'youtube_video_report_'.$year.'_'.$month;

    $allchart=[];
    
    $chartInfo  = RevenueChartClientsYoutubeFinanceReport($finance_report_table, $youtube_report_table ,$conn,$offset,$resultsPerPage,'',$clientsession,$channelid );
    if (!noError($chartInfo)) {

 
        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error fetching clients details.";
    } else {  
         $chartInfo = $chartInfo["errMsg"];
        
         $revchart=[];
         $revchart['cols']=[
                ['id'=>'','label'=>'Time of the day','pattern'=>'','type'=>'date'],
                ['id'=>'','label'=>'All Revenue','pattern'=>'','type'=>'number'],
                ['id'=>'','label'=>'Partner-Provided','pattern'=>'','type'=>'number'],
                ['id'=>'','label'=>'UGC','pattern'=>'','type'=>'number']
                ];
        $revchart['rows']= $chartInfo;  
        $allchart['revenue']=$revchart;
        
    }
    $chartInfo  = ViewsChartClientsYoutubeFinanceReport($finance_report_table, $youtube_report_table ,$conn,$offset,$resultsPerPage,'',$clientsession,$channelid);
    if (!noError($chartInfo)) {

 
        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error fetching clients details.";
    } else {  
         $chartInfo = $chartInfo["errMsg"];
        
         $viewchart=[];
         $viewchart['cols']=[
                ['id'=>'','label'=>'Time of the day','pattern'=>'','type'=>'date'],
                ['id'=>'','label'=>'Overall views','pattern'=>'','type'=>'number'],
                ['id'=>'','label'=>'Partner-Provided views','pattern'=>'','type'=>'number'],
                ['id'=>'','label'=>'UGC views','pattern'=>'','type'=>'number']
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
