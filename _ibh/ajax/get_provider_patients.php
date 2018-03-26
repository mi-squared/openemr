<?php
	
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

	ini_set("display_errors", 1);
	
	header('Content-Type: application/json');
	
	$provider_id = $_GET['provider_id'];

	$pq = sqlStatement("SELECT p2p.patient_id, p.fname, p.lname, p.dob, p.providerID FROM ibh_patients_to_providers p2p, patient_data p WHERE p2p.patient_id=p.pid AND p2p.provider_id=? ORDER BY p.lname, p.fname", array($provider_id));
	
	$res = array();
	
	while ($p = sqlFetchArray($pq)){
		$res[] = array("fname"=>$p['fname'], "lname"=>$p['lname'], "dob"=>$p['dob'],"id"=>$p['patient_id'], "main_provider"=>$p['providerID']);
	}

	echo json_encode(array($res));
	
?>