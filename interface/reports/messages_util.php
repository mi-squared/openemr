<?php


$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";
require_once "$srcdir/appointments.inc.php";
require_once "$srcdir/clinical_rules.php";


// IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");


function ibh_getEncounterForms_2($encounter) {
	
	// $sql = "SELECT f.form_name, f.date, f.form_id, f.formdir, es.is_lock FROM forms f, esign_signatures es WHERE f.id=es.tid AND f.encounter='$encounter' AND f.deleted=0 ORDER BY f.date DESC LIMIT 5";
	
	
	$sql = "SELECT f.form_name, f.date, f.form_id, f.formdir FROM forms f LEFT JOIN esign_signatures es ON f.id=es.tid WHERE f.encounter='$encounter' AND f.deleted=0 ORDER BY f.date DESC LIMIT 5";


	$data = array();
	
	$stuff = sqlStatement($sql, $data);
	
	$ct = 0;
	$lbf_ct = 0;
	$lbfs = array();
	
	$form_is_locked = false;

	while ($row = sqlFetchArray($stuff)) {
		
		if ($row['is_lock'] == 1) { $form_is_locked = true; }
		
		$formdir = $row['formdir'];
		if (substr($formdir, 0 , 3) == "LBF") {
			$lbf_ct++;
			$forms[] = array("name"=>$row['form_name'], "date"=>$row['date'], "id"=>$row['form_id'], "dir"=>$row['formdir']);
		}
		
		$ct ++;
		
	}
	
	if ($form_is_locked) return false;
	
	return array("count"=>$ct, "lbf_count"=>$lbf_ct, "forms"=>$forms);
	
}


print_r(ibh_getEncounterForms_2(28970));





/*
	
	// OLD FUNCTION TO INSERT supervisor_id INTO form_encounter
	
	
$query = "SELECT * FROM pnotes WHERE title='New Document' OR title='Supervisor Alert' AND date>'2016-09-01 00:00:00' ORDER BY date DESC"; //(CHEMED) facility filter

$ures = sqlStatement($query);

$ct = 0;

while ($urow = sqlFetchArray($ures)) {
	$ct++;
	echo "<br><br>" . $urow['encounter'] . "::" . $urow['assigned_to'];
	
	$supervisor = $urow['assigned_to'];
	$encounter = $urow['encounter'];
	
	// sqlStatement("INSERT INTO `ibh_supervisor_to_encounter` (`supervisor_username`, `encounter_id`) VALUES ('$supervisor','$encounter')");
	
	$getid = sqlStatement("SELECT id FROM users WHERE username='$supervisor'");
	$id = sqlFetchArray($getid);
	$supervisor_user_id = $id['id'];
	
	echo "<br>::: " . $supervisor_user_id;
	
	
	// sqlStatement("UPDATE form_encounter SET supervisor_id='$supervisor_user_id' WHERE encounter='$encounter'");
	
           
}

echo "<br><br> ct:" . $ct;
*/




?>
