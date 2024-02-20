<?php
  session_start();
require_once('../../../config/config.php'); 
//include some more necessary helpers
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/logs/logsProcessor.php');
require_once(__ROOT__.'/config/logs/logsCoreFunctions.php');
require_once(__ROOT__.'/model/validate/validateModel.php');
require_once(__ROOT__.'/model/client/clientDashboardModel.php');
require_once(__ROOT__.'/model/client/clientModel.php');
require_once(__ROOT__.'/model/reports/appleModel.php');
// DB table to use

$selectedDate = $_GET["reportMonthYear"];  
$year     = date("Y", strtotime($selectedDate));
$month    = date("m", strtotime($selectedDate));
 
 

// Table's primary key
$primaryKey = 'id';
$contentowner='';  
//Connection With Database
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    //error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1).$conn["errMsg"];
} else {
    $conn = $conn["errMsg"];
    $returnArr = array();
 
 
    //get my client name
    if(isset($_GET["email"]) && !empty($_GET["email"])) {
        $email = $_GET["email"];
    } else {
      $email = $_SESSION['userEmail'];
    }

    $type_table = $_GET['type_table'];

    $myclient = getClientsInfo_email(
        ['email'=>$email],
        'email,client_username',
        null,
        $conn
    );
//print_r($myclient);

      if (!noError($myclient)) {
        
        $returnArr["errCode"] = 5;
        $returnArr["errMsg"] = getErrMsg(5)." Error fetching clients details.";
        echo json_encode($returnArr);exit;
    }
    $myclientname =current($myclient['errMsg']);
    $myclientname =$myclientname['client_username'];
    $_SESSION['client'] =  $myclientname;
    
    //get data from finanace report 
       
         $searchdata =isset($_GET['search']['value']) ? $_GET['search']['value'] :'';
       
         $offset = isset($_GET['start']) ? $_GET['start'] :'0';
         $limit =  isset($_GET['length']) ? $_GET['length'] :'10000';  
        
         $table_type_name = 'report_audio_'.$type_table.'_' . $year . '_' . $month;
   
        $clientInfo = getClientsSpotifyMusicReportv2($table_type_name,$conn,$offset,$limit,$searchdata,$_SESSION['client']);
        
        //print_r($clientInfo);
        
        if (!noError($clientInfo)) {
            //error fetching client info
            $logMsg = "Error fetching client info to check for duplicate: {$clientInfo["errMsg"]}";
            $logData["step5.1"]["data"] = "5.1. {$logMsg}";
            $logsProcessor->writeJSON($logFileName, $logFilePath, $logData, $initLogs["activity"]);
            $rollback = rollbackTransaction($conn);
            $returnArr["errCode"] = 5;
            $returnArr["errMsg"] = getErrMsg(5) . " Error finding client info. Please try again after some time.";
            echo (json_encode($returnArr));
            exit;
        }else{
            $data = $clientInfo['errMsg'];   
            $tabledata = [];
            if($data['total'] > 0 ){
                $alldata =$data['data']; 
                foreach($alldata as $k=>$v){
                      $tabledata [] =array_values($alldata[$k]); 
                }  
                
            }
          
            $draw = isset($_GET['draw']) ? $_GET['draw']  :'';
           
            $datatableres = [
                'draw'=>$draw,
                "recordsTotal"=> $data['total'],
                "recordsFiltered"=> $data['total'],
                "data"=>$tabledata
            ];
            echo json_encode($datatableres);exit;
        }
	
}

?>
 