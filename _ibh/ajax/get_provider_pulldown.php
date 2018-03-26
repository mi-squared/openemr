<?php
	
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");


	header('Content-Type: application/json');
	
	$provider_id = $_GET['provider_id'];

	$html = array(ibh_getUserPulldown("provider_pulldown", $provider_id));
	
	echo json_encode($html);
	
?>