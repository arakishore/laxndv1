<?php
//////////////////////////////Prepare for request/////////////////////////////////
session_start();

//require helpers
require_once '../../../config/config.php';

require_once __ROOT__ . '/config/dbUtils.php';
require_once __ROOT__ . '/config/errorMap.php';
require_once __ROOT__ . '/config/auth.php';

//include necessary models

require_once __ROOT__ . '/model/reports/reportsModel.php';

require_once __ROOT__ . '/model/reports/youtubeVideoModel.php';
require_once __ROOT__ . '/model/reports/youtubeRedFinanceModel.php';
require_once __ROOT__ . '/model/reports/youtubeRedModel.php';
require_once __ROOT__ . '/model/reports/youtubeAudioFinanceModel.php';
require_once __ROOT__ . '/model/reports/youtubeAudioRedModel.php';

require_once __ROOT__ . '/model/reports/youtubeClaimReportsModel.php';
require_once __ROOT__ . '/model/reports/amazonModel.php';

require_once __ROOT__ . '/model/reports/appleModel.php';

//TO DO: Logs

$res = array();
	$returnArr = array();
$fileLocation = '';
$controller = '';
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
    $returnArr["errCode"] = 4;
    $returnArr["errMsg"] = "Error Connecting to DB";
    echo (json_encode($returnArr));
    exit;
} else {
    //db connection successful
    $conn = $conn["errMsg"];
    // printArr($_POST); exit;
    $selectedDate = cleanQueryParameter($conn, cleanXSS($_POST["selected_date"]));
    $year = date("Y", strtotime($selectedDate));
    $month = date("m", strtotime($selectedDate));
    //echo $selectedDate;exit;
    $startTransaction = startTransaction($conn);
    if (!noError($startTransaction)) {

        $returnArr["errCode"] = 3;
        $returnArr["errMsg"] = getErrMsg(3) . " Couldn't start transaction: {$startTransaction["errMsg"]}";

        echo (json_encode($returnArr));
        exit;
    }

////////////////code start from here v2.0//////////////////////
        $ndtype = cleanQueryParameter($conn, cleanXSS($_POST["nd"]));
   
    $sql = "select * from monthly_rate_saavan_gaana_other where year='{$year}' and month='{$month}' and ndtype='{$ndtype}'";
    $sqlResult = runQuery($sql, $conn);

    if (!noError($sqlResult)) {
        $clientInfo =  setErrorStack($returnArr, 3, $sqlResult["errMsg"], null);
    }

/* This function negotiates that an email must be fetched from the database. All user info is keyed by the user's email
 *  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used
 */
    $resultscheck=mysqli_num_rows($sqlResult["dbResource"]);	
    if($resultscheck > 0 ){
        $res = mysqli_fetch_assoc($sqlResult["dbResource"]);
      
        $clientInfo =   setErrorStack($returnArr, -1, $res, null);
        echo(json_encode($clientInfo));
        exit;
    } else {
        $clientInfo =   setErrorStack($returnArr, 10, $res, null);
        echo(json_encode($clientInfo));
        exit;
    }
    
}
