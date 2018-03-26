<?php
	
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");


	header('Content-Type: application/json');
	
/*

		$ret['d90'] = date("Y-m-d", $d90);
		$ret['d180'] = date("Y-m-d", $d180);
		$ret['d270'] = date("Y-m-d", $d270);
		$ret['d360'] = date("Y-m-d", $d360);
	

*/

	$tx_form_id = $_GET['tx_form_id'];
	$cda_form_id = $_GET['cda_form_id'];
	$pid = $_GET['patient_id'];
	$action = $_GET['action'];
	
	
	
	if ($action == "clear") {
		
		$clear_id = $_GET['clear_id'];
		sqlStatement("DELETE FROM ibh_tickler_reviews WHERE id=?", array($clear_id));
		
		echo '{"tickler_id":' . $clear_id . '}';
		
	} else if ($action == "update") {
		
		$tickler_id = $_GET['tickler_id'];
		sqlStatement("UPDATE ibh_tickler_reviews SET init_tp=? WHERE id=?", array($tx_form_id, $tickler_id));
		
		echo '{"tickler_id":' . $tickler_id . '}';
		
	} else {
		// SET
	    $sql = "INSERT INTO ibh_tickler_reviews (patient_id, init_cda, init_tp) VALUES (?, ?, ?)";

		$insert_id=sqlInsert($sql, array($pid, $cda_form_id, $tx_form_id));

		$form_info = ibh_get_form_info($tx_form_id, true);

		echo '{"tickler_id":' . $insert_id . ', "row": ' . json_encode($form_info) . '}';
		
	}
	


	
?>