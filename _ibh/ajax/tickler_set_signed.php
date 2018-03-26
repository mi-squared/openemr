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

	$tickler_id = $_GET['tickler_id'];
	$phase = $_GET['phase'];
	$signed = (int) $_GET['signed']; // 0/1	
	
	
	
		
		sqlStatement("UPDATE ibh_tickler_reviews SET " . $phase . "=? WHERE id=?", array($signed, $tickler_id));
		
		echo '{"tickler_id":' . $tickler_id . '}';
		
	
	


	
?>