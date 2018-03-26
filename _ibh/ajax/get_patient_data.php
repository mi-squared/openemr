<?php
	
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");


	header('Content-Type: application/json');
	
	$_pid = $_GET['pid'];

	$checkins = ibh_get_patient($_pid);
	
	 echo '{"patient":' . json_encode($checkins) . '}';
	
?>