<?php
	
	
	header('Content-Type: application/json');
    require_once( "../../interface/globals.php");
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");


	$finder = "";
	
	if (isset($_GET['pc_eid'])) {
		$finder = $_GET['pc_eid'];
		$finder_field = "pc_eid"; 
	} else if (isset($_GET['encounter'])) {
		$finder = $_GET['encounter'];
		$finder_field = "encounter";
	}

	
	$sql = "SELECT pc_apptstatus FROM openemr_postcalendar_events WHERE " . $finder_field . "=?";
	
	$stmt = sqlStatement($sql, array($finder));
	$res = sqlFetchArray($stmt);
	
	echo '{"pc_apptstatus":"' . $res['pc_apptstatus'] . '", "finder":"' . $finder . '"}';
	
	
?>