<?php

function getDistributorTransaction($data,$offset,$no_of_rows, $conn){

		$res = array();
		$cols = array();
		// global $blanks;
		foreach($data as $key=>$val) {
    	    $cols[] = "$key = '$val'";
    	}
    	$count = count($cols);
    	if($count >1){
    		$where = implode(' AND ', $cols);
    	}else{
    		$where = implode(' ', $cols);
    	}
	    $query = "select * from manage_distributor_transaction where 1=1";

	    $query .= " ORDER BY updated_on DESC";
	   	if($offset!= '' && $no_of_rows!= ''){
	        $query .= " LIMIT ".$offset.",".$no_of_rows;
		}
	
	    // printArr($query);
	    // exit();
	    //$res["query"] = $query;
		$result = runQuery($query, $conn);
		$res = array();
		 

		if (noError($result)) {
	        
	       while($row = mysqli_fetch_assoc($result["dbResource"])){
	       		array_push($res,$row);
	       }
	       $returnArr = setErrorStack($returnArr, -1, $res, null);

	    } else {
	        $returnArr = setErrorStack($returnArr, 3, null);
	    }

	    return $returnArr;

	}
	function getFilterDistributorTransaction($offset,$no_of_rows,$conn,$search_params){

		$res = array();
		$returnArr = array();
		$cols = array();
		// printArr($search_params);
		// printArr('hiii');
		// global $blanks;
		foreach($search_params as $key=>$val) {
    	    $cols[] = "$key = '$val'";
    	}
    	$count = count($cols);
    	if($count >1){
    		$where = implode(' AND ', $cols);
    	}else{
    		$where = implode(' ', $cols);
    	}
	    $query = "select * from manage_distributor_transaction where 1=1";

	    if(!empty(trim($search_params['distributor']))){
        	//echo "heer comes";
			        $query .= " AND distributor LIKE '%".trim($search_params['distributor'])."%'";
			}

		 if(!empty($search_params['start_date']) && !empty($search_params['end_date']))
	     {
	     
	     $start_date=date("Y-m-d", strtotime($search_params["start_date"]));
	     $end_date=date("Y-m-d", strtotime($search_params["end_date"]));
	     $query .= " AND transaction_date >='".$start_date."' AND transaction_date <='".$end_date."'";
	     
	   	}	

	    $query .= " ORDER BY updated_on DESC";
	   	if($offset!= '' && $no_of_rows!= ''){
	        $query .= " LIMIT ".$offset.",".$no_of_rows;
		}

	    //$res["query"] = $query;
		$result = runQuery($query, $conn);
		$res = array();
		
	    // printArr($result);
	    // exit();

		if (noError($result)) {
	        
	       while($row = mysqli_fetch_assoc($result["dbResource"])){
	       		$res[]=$row;
	       }
	      
	       $returnArr = setErrorStack($returnArr, -1, $res, null);

	    } else {
	        $returnArr = setErrorStack($returnArr, 3, null);
	    }

	    return $returnArr;

	}
	function addDistributorTransaction($data,$conn)
	{

		$created_on = date('Y-m-d H:i:s');
		$updated_on = date('Y-m-d H:i:s');

	   $query = "INSERT INTO `manage_distributor_transaction` (`amount`, 
	    												`distributor`, 
	    												 `currency`, 
	    												 `exchange_rate`, 
	    												 `transaction_id`, 
	    												 `transaction_mode`, 
	    												 `transaction_type`,
	    												 `transaction_date`,
	    												  `comments`, 
	    												  `status`, 
	    												  `created_on`, 
	    												  `updated_on`) 
				    										VALUES ( '".$data['amount']."', 
				    												 '".$data['distributor']."',
				    												  '".$data['currency']."', 
				    												  '".$data['exchange_rate']."', 
				    												  '".$data['transaction_id']."', 
				    												  '".$data['transaction_mode']."', 
				    												  '".$data['transaction_type']."',
				    												  '".date('Y-m-d H:i:s',strtotime($data['transaction_date']))."', 
				    												  '".$data['comments']."', 
				    												  '1', 
				    												  '".$created_on."', 
				    												  '".$updated_on."'
				    												);";

	    $result = runQuery($query, $conn);



	    if(noError($result)){


	        $returnArr["errCode"]=-1;
	        $returnArr["errMsg"]="updation sucess";
	    }else{
			$returnArr["errCode"] = "8";
	        $returnArr["errMsg"]=" updation failed".mysqli_error();
	    }
	    return $returnArr;
	}
	

	function getFiltersDistributorTransaction($offset,$no_of_rows,$conn,$search_params){

	$returnArr = array();
	global $blanks;
	//printArr($search_params);

	$query = "SELECT * FROM manage_fa_transaction WHERE 1=1";

	if(!empty(trim($search_params['distributor']))){
			        $query .= " AND distributor =".trim($search_params['distributor']);
			}

	    if(!empty($search_params['start_date']) || !empty($search_params['end_date']))
         {
             if(empty($search_params['end_date'])){
                 $search_params["end_date"] = date("d-m-Y");
             }
             if(empty($search_params['start_date'])){
                 $search_params["start_date"] = date("d-m-Y", strtotime('-30 days'));
             }
             $start_date=date("Y-m-d", strtotime($search_params["start_date"]));
             $end_date=date("Y-m-d", strtotime($search_params["end_date"]));
             if(!empty(trim($search_params['distributor']))){

                  $query .= " AND transaction_date >='".$start_date."' AND transaction_date <='".$end_date."'";
             }else{


                 $query .= " AND transaction_date >='".$start_date."' AND transaction_date <='".$end_date."'";
                }
        
           }

   	$query .= " ORDER BY updated_on DESC";

   	// printArr($query);

  	if($offset!= '' && $no_of_rows!= ''){
        $query .= " LIMIT ".$offset.",".$no_of_rows;
	}

	// echo $query;

	$result = runQuery($query,$connAdmin);

	$res = array();

	if(noError($result)){

		while($row = mysqli_fetch_assoc($result["dbResource"])){

			$res[] = $row;
		}
        $returnArr["errCode"]=-1;
        $returnArr["errMsg"]= $res;
    }else{
        $returnArr["errCode"] = "8";
        $returnArr["errMsg"]=" Error in adding FAQs".mysqli_error();
    }
    
    return $returnArr;
}

/*
function decodeurlWithParameters($datastring)
{
    /*
    decodeurlWithParameters($datastring)
    which have 1 parameters
    1]$datastring:-string to decode
    it's return decoded url
    // 
    $decodedquery = urldecode($datastring);
    parse_str($decodedquery, $queryStringForTransaction);

    return $queryStringForTransaction;
}*/


?>