<?php
	
	
	header('Content-Type: application/json');
	
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

	$encounter = $_GET['encounter'];
	$has_fee = $_GET['has_fee'];
	
	if ($has_fee == "1") {
		$has_fee_q = " AND fee>0";
	} else {
		$has_fee_q = "";
	}
	
	$sql = "SELECT * FROM billing WHERE encounter=?" . $has_fee_q;
	
	$stmt = sqlStatement($sql, array($encounter));
	
	$bills = array();
	
    while ($b = sqlFetchArray($stmt)) {
	    $bills[] = $b;
	    
    }
 
	
	echo '{"encounter":' . $encounter . ', "billing":' . json_encode($bills) . '}';
	
	
?>