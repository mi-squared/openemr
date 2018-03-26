<?php
	
	
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");


	header('Content-Type: application/json');
	

	$_pid = $_GET['pid'];
	

	$exceptions = ibh_get_patient_pa_cat_exceptions($_pid, "codes");
	
	
	echo '{"pa_cat_exceptions":' . json_encode($exceptions) . '}';
	
	
?>