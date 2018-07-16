<?php
	
	require_once("../../_ibh/ibh_functions.php");
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

	ini_set("display_errors", 1);
	
	header('Content-Type: application/json');
	
	$provider_id = $_GET['provider_id'];
	$pid = $_GET['patient_id'];
	
	$action = $_GET['action'];
	
	switch ($action) {
		
		case "remove":
			
			$remove = sqlStatement("DELETE FROM ibh_patients_to_providers WHERE patient_id='$pid' AND provider_id='$provider_id'");
			
			if ($remove) $json = array("removed"=>true);

		break;
		
		
		case "add":
			
			$added = sqlStatement("INSERT INTO ibh_patients_to_providers (patient_id, provider_id) VALUES (?, ?)", array($pid, $provider_id));
			
			if ($added) {
				   $json = array("added"=>true);
				} else {
				   $json = array("added"=>false);
				}

		break;
		
		
		
	}


	echo json_encode($json);
	
?>