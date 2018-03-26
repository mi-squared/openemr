<?php
	
	
	header('Content-Type: application/json');
	
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

	
	$pc_eid = $_GET['pc_eid'];
	
	
	
	if ($_GET['delete'] == 'true') {
		
		// DELETE IT!!
		
		$sql = "DELETE FROM openemr_postcalendar_events WHERE pc_eid=?";
	
		$stmt = sqlStatement($sql, array($pc_eid));
		
		if ($stmt) {
			echo '{"success":true, "deleted": true}';
		} else {
			echo '{"success":false, "deleted": false}';
		}
		



	} else {
		
		// UPDATE STATUS
		
		$pc_apptstatus = $_GET['pc_apptstatus'];

		$sql = "UPDATE openemr_postcalendar_events SET pc_apptstatus=? WHERE pc_eid=?";
	
		$stmt = sqlStatement($sql, array($pc_apptstatus, $pc_eid));
		
		if ($stmt) {
			echo '{"success":true}';
		} else {
			echo '{"success":false}';
		}
		
	
	
	}
	
	
	
?>