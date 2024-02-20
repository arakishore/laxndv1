<?php

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simple to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
require_once('../../config/config.php');
//include some more necessary helpers
require_once(__ROOT__.'/config/dbUtils.php');
require_once(__ROOT__.'/config/errorMap.php');
require_once(__ROOT__.'/config/logs/logsProcessor.php');
require_once(__ROOT__.'/config/logs/logsCoreFunctions.php');
require_once(__ROOT__.'/model/validate/validateModel.php');

// DB table to use

$selectedDate = $_GET["reportMonthYear"];
$year     = date("Y", strtotime($selectedDate));
$month    = date("m", strtotime($selectedDate));
$table = 'amazon_video_report'.'_'.$year.'_'.$month; ;
 
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
	$contentowner = getContentOwner($conn);
	
}
$contentowner= $contentowner['errMsg'];

function selectbox($d,$id){
	global $contentowner;
	 
	$selectbox = '<select class="mdb-select md-form cselect"  data-id="'.$id.'"  searchable="Search here..">';
	$selectbox.= "<option value='' >-Select owner-</option>";
	foreach ($contentowner as $v) {
		if($d==$v){
			$selectbox.= "<option value='{$v}' selected>{$v}</option>";
		}else{
			$selectbox.= "<option value='{$v}' >{$v}</option>";
		}
	 }

	$selectbox.='</select>';   

	return $selectbox;
}


// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
	array( 'db' => 'id',  'dt' => 0 ),
	array( 'db' => 'seasonID',  'dt' => 1 ),
	array( 'db' => 'titleName',  'dt' => 2 ),	
	array( 'db' => 'royaltyCurrency',  'dt' => 3 ),	
	array( 'db' => 'royaltyAmount',  'dt' => 4 ),	
	array( 'db' => 'periodStart',     'dt' => 5),
	array( 'db' => 'periodEnd',   'dt' => 6 ),
	array( 'db' => 'content_owner',   'dt' => 7),
 
);

 
$searchFilter = array(); 
if(!empty($_GET['search_keywords'])){ 
    $searchFilter['search'] = array( 
        'seasonID' => $_GET['search_keywords'], 
        'titleName' => $_GET['search_keywords']
       
    ); 
} 
if(!empty($_GET['filter_option'])){   
    $searchFilter['filter'] = array( 
        'content_owner' =>  ''
	); 
 
} 
 
 
// SQL server connection information
$sql_details = array(
	'user' => DBUSERNAME,
	'pass' => DBPASSWORD,
	'db'   => DBNAME,
	'host' => DBHOST
);
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require( 'amazon.ssp.class.php' );

echo json_encode(
	SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);