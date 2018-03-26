<?php
	
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");


	header('Content-Type: application/json');
	
	
	$pid = $_GET['pid'];
	
	// $html = "<select id='encounter-pulldown' data-pid='" . $pid . "'>";
	
	function ibh_get_user_name($id) {
		$uq = sqlStatement("SELECT fname, lname FROM users WHERE id='$id'");
		$row = sqlFetchArray($uq);
		return $row['fname'] . " " . substr($row['lname'],0,1) . ".";
	}
	
	$sql = "SELECT f.id, f.form_name, ev.pc_eventDate, fe.date, fe.provider_id, fe.encounter FROM form_encounter fe, forms f, openemr_postcalendar_events ev WHERE f.form_name != 'New Patient Encounter' AND f.form_name NOT LIKE '%Progress Note%' AND f.form_name NOT LIKE '%Authorization%' AND f.form_name NOT LIKE '%Diagnosis%' AND fe.encounter=ev.encounter AND fe.pid='$pid' AND f.encounter=fe.encounter ORDER BY fe.date DESC LIMIT 100";

	$data = array();

	$stuff = sqlStatement($sql, $data);

	while ($row = sqlFetchArray($stuff)) {
	
		$provider = $row['provider_id'];
		$enc = $row['encounter'];
		
		// $appt = ibh_get_appointment_info($enc);
		$appt_date = $row['pc_eventDate'];
		$date_10 = substr($appt_date,0,10);
		
		$html .= "<div class='form-list-item' data-encounter='" . $enc . "' data-date='" . $date_10 . "'  data-title='" . ibh_form_link($row['form_name']) . "' data-form_id='" . $row['id'] . "'><span class='form-list-date'>" . $date_10 . "</span> " . ibh_form_link($row['form_name']) . "&nbsp;&nbsp;<span class='form-list-provider'>(" . ibh_get_user_name($provider) . ")</span></div>";
	}

	
	echo json_encode(array("html"=>$html));
	
?>