<?php
	
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");


	header('Content-Type: application/json');
	
/*

		$ret['d90'] = date("Y-m-d", $d90);
		$ret['d180'] = date("Y-m-d", $d180);
		$ret['d270'] = date("Y-m-d", $d270);
		$ret['d360'] = date("Y-m-d", $d360);
	
		data:{form_id:form_id, tickler_id:tickler_id, target:column_id},
*/

	$tickler_id = $_GET['tickler_id'];
	$target = $_GET['target'];
	
	// these are optional
	$form_id = isset($_GET['form_id']) ? $_GET['form_id']: 0;
	
	$clear = isset($_GET['clear']) && $_GET['clear'] == '1' ? true: false;
	
	$col = "";
	
	switch($target) {
		case "cda": $col = "init_cda"; break;
		case "d90":  $col = "r1"; break;
		case "d180": $col = "r2"; break;
		case "d270": $col = "r3"; break;
		case "d360": $col = "r4"; break;
	}
	
	if ($clear) {
		
		sqlStatement("UPDATE ibh_tickler_reviews SET $col='' WHERE id=?", array($tickler_id));
		$cleared = "yes";
	} else {
		if ($col) {
			sqlStatement("UPDATE ibh_tickler_reviews SET $col=? WHERE id=?", array($form_id, $tickler_id));
			$cleared="no";
		}
	}
	
	
    
   

	echo '{"col":"' . $col . '", "form_id":"' . $form_id . '", "tickler_id":' . $tickler_id . ', "cleared":"' . $cleared . '"}';

	
?>