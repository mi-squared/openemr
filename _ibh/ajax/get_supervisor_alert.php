<?php
	
	ini_set("display_errors", 1);
	
	
	header('Content-Type: application/json');
	
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");


	$encounter_id = $_GET['encounter'];
	$html = "";
	
	$sql = "SELECT fe.*, fe.date as fe_date, fe.supervisor_id as fe_sup_id, u.fname as p_fname, u.lname as p_lname, pd.pid, pd.fname as fname, pd.lname as lname FROM form_encounter fe, patient_data pd, users u WHERE fe.encounter='$encounter_id' AND fe.provider_id=u.id AND fe.pid=pd.pid LIMIT 1";

	$stmt = sqlStatement($sql);
	$row = sqlFetchArray($stmt);
	
	if (encounterLocked($encounter_id) == 0) { 
			
			$lfb_class = "";
			$lfb_links = "";
			$lbfs = "";
			$lbf_links = "";
			$pid = $row['pid'];

			$forms = ibh_getEncounterForms($encounter_id);
			
			// forms returns false if any form is locked
			if ($forms) {
				
				if ($forms['lbf_count'] == 0) {
					$lfb_class = " lbf_alert";
				} else {
					
					$lbfs = $forms['forms'];
					
					foreach ($lbfs as $lbf) {
	
						$date_formatted = date("m/d/y", strtotime($lbf['date']));
	
						$lbf_links .= '<li><a class="encounter-setter" data-date="' . $date_formatted . '" data-enc="' . $encounter_id . '" href="/openemr/interface/patient_file/encounter/forms.php?supervisor_review=1&pid=' . $pid . '&set_encounter=' . $encounter_id . '" target="RTop">' . $lbf['name'] . '</a></li>';
						
					}
				}
				
				$sup = ibh_get_user_by_id($row['fe_sup_id']);
				
				$nice_e_date = date("D M j", strtotime($row['fe_date']));
				$nice_sa_date = date("F j @ g:i a", strtotime($row['last_supervisor_alert']));
				
				if ($nice_sa_date == "December 31 @ 5:00 pm") $nice_sa_date = "...";
				
				$html = "<tr class='assignment" . $lfb_class . "'><td>" . $row['p_fname'] . " " . $row['p_lname'] . "</td><td>" . $sup['fname'] . " " . $sup['lname'] . "</td><td>" . $row['lname'] . ", " . $row['fname'] . "</td><td>" . $row['reason'] . "</td><td>" . $row['encounter'] . "</td><td>" . $nice_e_date . "</td><td>" . $nice_sa_date . "</td><td><ul class='lbf-links'>" . $lbf_links . "</ul></td></tr>";
				
				}
				
				
			} // end if not locked
			
			 
	
	echo '{"encounter":' . $encounter_id . ', "html":' . json_encode($html) . '}';
	
	
?>