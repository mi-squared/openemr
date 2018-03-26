<?php
	
	
	header('Content-Type: application/json');
	
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

	$id = $_GET['dr_id'];
	$user_id = $_GET['user_id'];
	$pid = $_GET['pid'];
	$due_date = $_GET['due_date'];
	
	if ($_GET['delete_it'] == "true" && isset($_GET['delete_it'])) {
		
		$sql = "DELETE FROM dated_reminders WHERE dr_id=?";
		
		$stmt = sqlStatement($sql, array($id));
		
		if ($stmt) {
			echo '{"success":"' . $sql . ', ' . $id . '"}';
		} else {
			echo '{"success":false}';
		}
		
		
	} else {
		
		
		
		$content = $_GET['dr_message_text'];
	
		$pid_check = $pid ? "AND pid='$pid'": "";
		
		$get = sqlStatement("SELECT * FROM dated_reminders WHERE dr_id=?", array($id));
		$dr = sqlFetchArray($get);
		$date = $dr['dr_message_sent_date'];
		
		
		$sql = "UPDATE dated_reminders SET dr_message_text=?, dr_message_due_date=? WHERE dr_id=? " . $pid_check . " OR dr_message_sent_date='$date'";
		
		$stmt = sqlStatement($sql, array($content, $due_date, $id));
		
		if ($stmt) {
			echo '{"success":true}';
		} else {
			echo '{"success":false}';
		}
	
		
	}
	
	
	
	
?>