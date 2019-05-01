<?php
	
	
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");


	header('Content-Type: application/json');
	
	$category = $_GET['cat'];
	$_pid = $_GET['pid'];
	$date = $_GET['date'];

	$prior_auths = ibh_get_patient_prior_auths($_pid, true);
	
	
	echo '{"prior_auths":' . json_encode($prior_auths) . '}';
	
	
?>