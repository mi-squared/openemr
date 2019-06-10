<?php


function foo() {
	echo "foo!";
}


define("IBH_INTERPRETER_FEE", 20); //***IBH HARD CODE
define("IBH_TRANSPORTATION_FEE", 20);//***IBH HARD CODE



function ibh_getEncounterCodeInfo($code) {
		
		$code = trim($code);


    //***IBH HARD CODE
		$codes = array(
			"Individual Therapy 30 min"=>array("mod"=>0.5),
			"Individual Therapy 45 min"=>array("mod"=>1.0),
			"Individual Therapy 60 min"=>array("mod"=>1.25),
			"Individual Therapy CDA"=>array("mod"=>1.5),
			"90847 - 90847 - Family therapy with Patient Present"=>array("mod"=>1.0), // 45 > 60
			"Family Therapy w/o Patient Present"=>array("mod"=>1.0),
			"Family Therapy w/ Patient Present"=>array("mod"=>1.0),
			"Assessment"=>array("mod"=>0.25),
			"Treatment Plan"=>array("mod"=>0.25),
			"Treatment Plan Review"=>array("mod"=>0.25),
			"H0032 - H0032 - Treatment Plan"=>array("mod"=>0.25),
			"Peer Support"=>array("mod"=>0.25),
			"Family Support"=>array("mod"=>0.25),
			"Pro Bono"=>array("mod"=>1),
			"Crisis CBRS"=>array("mod"=>0.25),
			"CBRS Skill Training"=>array("mod"=> 0.25),
			"T1017 - T1017 - Case Management"=>array("mod"=> 0.25),
			"Case Management"=>array("mod"=> 0.25),
			"Telephonic Case Management"=>array("mod"=>0.25),
            "Assessment – Behavioral Health" =>array("mod"=>0.25)
		);
		
		if (!$code) return $codes;
		
		
		if (array_key_exists($code, $codes)) {
			$arr = $codes[$code];
			return array("code"=>$code, "exists"=>true, "mod"=>$arr['mod']);
		} else {
			return array("code"=>$code, "exists"=>false, "mod"=>0);
		}
					
}



function ibh_get_diagnosis($pid) {
	
	$fid = sqlStatement("SELECT form_id, encounter FROM forms WHERE form_name = 'Patient Diagnosis' AND pid = $pid ORDER BY encounter DESC LIMIT 1");
	$pdi = sqlFetchArray($fid);

	$f_id = sqlStatement("SELECT field_value FROM lbf_data WHERE form_id = '".$pdi['form_id']."' AND field_id LIKE '%diag%'");

	$i=0; 
	$diags = array();
	  
	while ($res = sqlFetchArray($f_id)){
	      
	      	$code = $res['field_value'];
	      	
			$dc = explode(":", $code);
    					
			if ($dc[0] == 'ICD10'){
				
				$q = sqlQuery("SELECT short_desc FROM icd10_dx_order_code WHERE formatted_dx_code LIKE '".$dc[1]."'");
				$diags[] = $q['short_desc'] . " (" . $code . ")";
				
			} else if ($dc[0] == 'DSM5'){

				$q = sqlQuery("SELECT code_text FROM codes WHERE code LIKE '".$dc[1]."'");
				$diags[] = $q['code_text'] . " (" . $code . ")";
				
			}
			
	}
	
	if (count($diags) == 0) {
		return false;
	} else {
		return $diags;
	}

}



function ibh_appt_status_picker($id="pc_apptstatus", $sel="") {

    //***IBH HARD CODE
	$status_arr = array(
		array("symbol"=>"-", "label"=>"- None"),
		array("symbol"=>"*", "label"=>"* Reminder done"),
		array("symbol"=>"x", "label"=>"x Canceled"),
		array("symbol"=>"?", "label"=>"? No show"),
		array("symbol"=>"<>", "label"=>"&lt;&gt; Arrived"),
		array("symbol"=>"@", "label"=>"@ Checked In"),
		array("symbol"=>"<", "label"=>"&lt; In Session"),
		array("symbol"=>">", "label"=>"&gt; Checked out"),
		array("symbol"=>"$", "label"=>"$ Coding done"),
		array("symbol"=>"%", "label"=>"% Canceled &lt; 24h")
	);
	
	$html = "<select name='pc_apptstatus' class='appt-changer' id='" . $id . "'><option value=''>Select...</option>";
	
	foreach ($status_arr as $s) {
		$selected = ($sel == $s['symbol']) ? "selected": "";
		$html .= "<option value='" . $s['symbol'] . "' " . $selected . ">" . $s['label'] . "</option>";
	}
	
	$html .= "</select>";
	
	return $html;
	
}

function ibh_get_pid_from_encounter($encounter) {
	$sql = "SELECT pid FROM form_encounter WHERE encounter=? LIMIT 1";
	$query = sqlStatement($sql, array($encounter));
	
	$ret = sqlFetchArray($query);
	
	return $ret['pid'];
	
}

// takes the $_POST data (as $post) from encounter signing; this is 
// called in /library/ESign/Form/Controller.php [esign_form_submit()]
function ibh_esign_rider($post, $esign_id = 0) {
	
	
	// use the esign id to get the esign user id....
	if ($esign_id) {
		// maybe doing this in an esign module...
	}
	
	// THIS COULD BE BAD, coming from the GLOBALS variable
	// We'll get the PID from the encounter to make sure 
	// there's no cross-tab globals beign munged up.
	// XXX $pid = $post['pid']; XXX
	
	// maybe we ought to get the PID from here...
	$encounter = $post['encounterId'];
	
	// this to prevent $GLOBALS $pid
	$pid = ibh_get_pid_from_encounter($encounter);
	
	$mssg = "";
	
	$modifier = ($post['mod'] && $post['mod'] != 1) ? $post['mod']: "";
	
	if ($post["transportation"] == '1') {
		esign_transportation($pid, $encounter, $modifier);
	}
	
	if ($post["checkout"] == '1') {
		$mssg .= "co.";
		$mssg .= ibh_esign_checkout($pid, $modifier, $encounter);
	}
	
	if ($post["interpreter_used"] == '1') {
		esign_interpreter($pid, $encounter, $post['interpreter_minutes'], $post['interpreter_name'], $modifier);
	}
	
	
	if ($post["interactive_complexity"] == '1') {
		esign_interactive_complexity($encounter, $pid, $modifier);
	}
	
	return $mssg;
	
	
}

function ibh_get_esign_signature($esign_id) {
	
	$sql = "SELECT name FROM ibh_esign_info WHERE esign_id=? LIMIT 1";
	$query = sqlStatement($sql, array($esign_id));
	
	$ret = sqlFetchArray($query);
	
	return $ret['name'];
	
}
		
		
function ibh_get_appointment_info($encounter) {
	
	$sql = "SELECT pc_title, pc_startTime, pc_eventDate, pc_apptstatus FROM openemr_postcalendar_events WHERE encounter=?";
	$query = sqlStatement($sql, array($encounter));
	
	$ret = sqlFetchArray($query);
	
	if ($ret) {
		return $ret;
	} else {
		return false;
	}
	
}	

function ibh_get_patient($pid) {
	
	if (!$pid) $pid = $_SESSION['pid'];
	$stmt = sqlStatement("SELECT * FROM patient_data WHERE pid='$pid'");
	return sqlFetchArray($stmt);
	
}




function ibh_send_message($from_username, $send_to_username, $pid, $title, $body, $encounter=0) {
	
	$date = date("Y-m-d G:i:s");
	
    $sql = "INSERT INTO pnotes (date, body, pid, user, groupname, activity, authorized, title, assigned_to, deleted, message_status, encounter) VALUES ('$date', ?, '$pid', '$from_username', 'IBH', '1', '1', ?, '$send_to_username', '0', 'New', '$encounter')";

    sqlStatement($sql, array($body, $title));
    
}
	

function ibh_get_session_user_id() {
	
	$username = $_SESSION['authUser'];
	
	$stmt = sqlStatement("SELECT id FROM users WHERE username='$username'");
	$arr = sqlFetchArray($stmt);
	return $arr['id'];
	
}


function ibh_get_user_names_by_ids($users_string) {
	$users = explode(",", $users_string);
	$ret = array();
	
	foreach ($users as $u) {
		$sql = "SELECT fname, lname FROM users WHERE id='$u' and active = 1"; //The active = 1 might have to be removed
		$res = sqlStatement($sql);
		$arr = sqlFetchArray($res);
		$ret[] = $arr['fname'] . " " . $arr['lname'];
		
	}
	return $ret;
}



function ibh_get_prior_auth_bills($auth) {
	
	
	$html = "";
	$pid = $auth['pid'];
	$days_remaining = "";
	$bills_claimed = array();
	
	$codes_array_loaded = array($auth['code1'], $auth['code2'], $auth['code3'], $auth['code4'], $auth['code5'], $auth['code6'],$auth['code7']);
	
	$actual_codes = array();
	foreach ($codes_array_loaded as $pa_code) {
		if ($pa_code) $actual_codes[] = $pa_code;
	}
	
	$auth_from = $auth['auth_from'] . " 00:00:00";
	$auth_to = $auth['auth_to'] . " 23:59:59";
	$units_remaining = $auth['units'];
	
	// REMAINING DAYS, IF ANY
	$expires = strtotime($auth['auth_to'] . " 23:59:59");
	$today = strtotime(date("Y-m-d H:i:s"));
	$ct = 0;
	
	$days_remaining = ceil(($expires - $today) / 86400);
	if ($days_remaining <= 0) $days_remaining = 0;
	
	foreach ($actual_codes as $billing_code) {
		
		// query billing for code within these dates
		$bsql = "SELECT * FROM billing WHERE pid='$pid' AND code='$billing_code' AND (date >='$auth_from' AND date <= '$auth_to')";
		
        $billsq = sqlStatement($bsql);
        while($bill = sqlFetchArray($billsq)) {
			$ct++;
		    if (!in_array($bill['id'], $bills_claimed)) {

			    $html .= "<div class='pa-billing-bill'><div class='pa-billing-code'>" . $bill['code'] . "</div>" . $bill['date'] . "<br><em>" . $bill['code_text'] . "</em><br>encounter: " . $bill['encounter'] . "<br>units: " . $bill['units'] . "</div>";
			    
			    $units_remaining = $units_remaining - $bill['units'];
		       
		        $bills_claimed[] = $bill['id'];
	        } // end if it's NOT claimed
			  
        } 	
    }
    
    if ($ct) {
	    return array("html"=>$html, "days_remaining"=>$days_remaining, "units_remaining"=>$units_remaining);
    } else {
	    return false;
    }
    
    
}



function ibh_run_daily_tasks() {
	
	$date = date("Y-m-d");
	$sql = "SELECT id FROM ibh_tasks WHERE date ='$date' AND name='check_pa_days'";
	$res = sqlStatement($sql);
	$ret = "";
	
	if (sqlNumRows($res) == 0) {
		// do task
		$ret .= "CHECKING FOR PA ALERTS<br>";
		$ret .= ibh_check_for_prior_auth_day_alerts();
		
		// insert task log
		sqlStatement("INSERT INTO ibh_tasks (date, name) VALUES ('$date', 'check_pa_days')");
	}
	
	return $ret;
	
}

// REAL DE
function ibh_get_patient_pa_cat_exceptions($pid, $type="codes") {
	
	$ret = array();
	
	$cats = ibh_get_categories_array(true);
		
	$stmt = sqlStatement("SELECT cat_id FROM ibh_patient_pa_cat_exceptions WHERE pid=?", array($pid));

	while ($r = sqlFetchArray($stmt)) {
		if ($type == "codes") {
			$ret[] = $cats["cat-" . $r['cat_id']];
		} else {
			// for ids only, used in PA checkboxes in demographics
			$ret[] = $r['cat_id'];
		}
	}
	
	return $ret;		
}




function ibh_get_patient_prior_auths($pid, $valid=false, $pan="") {
	
	$auths = array();
	
	$dates = "";
	
	if ($valid) {
		$today = date("Y-m-d");
		// get only active prior auths
		$dates = "AND archived=0 AND auth_to >= '$today'";
	}

	if ($_GET['apptdate']){

	    $dates .= "  AND auth_to >= '{$_GET['apptdate']}' AND auth_from <= '{$_GET['apptdate']}'  ";
    }
	
	if ($pan) {
		$dates .= " AND prior_auth_number='$pan'";
	}
	
	$sql = "SELECT id FROM form_prior_auth WHERE pid =". $pid . " " . $dates . " ORDER BY date DESC";
	$res = sqlStatement($sql);

    while($auth = sqlFetchArray($res)){
		$id = $auth['id'];
		$auths[] = ibh_get_prior_auth ($id);
	}
    
    return $auths;			
}


function ibh_get_patient_checkins($pid) {
			
	$sql = "SELECT pc_eid, pc_title, pc_eventDate, pc_startTime FROM openemr_postcalendar_events WHERE pc_pid=? AND pc_apptstatus='@'";
	$res = sqlStatement($sql, array($pid));
	$checkins = array();
	
    while ($foo = sqlFetchArray($res)) {
	    $checkins[] = $foo;
	    
    }
    
    return $checkins;
    		
}


function ibh_get_billing_numbers($encounter, $code) {
	$sql = "SELECT fee FROM billing WHERE billed=1 AND encounter='$encounter' AND code='$code'";
	$b = sqlStatement($sql);
	return sqlFetchArray($b);
}
	


function ibh_wrap_billing_codes($codes_arr) {
	$html = "";
	foreach ($codes_arr as $code) {
		$html .= "<span class='pa-code'>" . $code . "</span>";
	}
	return $html;
}


function ibh_get_pa_actual_codes($auth) {
	
	$codes_array_loaded = array($auth['code1'], $auth['code2'], $auth['code3'], $auth['code4'], $auth['code5'], $auth['code6'],$auth['code7']);
	
	$actual_codes = array();
	
	foreach ($codes_array_loaded as $pa_code) {
		if ($pa_code) $actual_codes[] = trim($pa_code);
	}
	
	return $actual_codes;
}


// get a single prior auth with its warnings and remaining units
// this is fairly redundant with ibh_get_prior_auth_warnings() below
// returns raw prior_auth along with key metrics, array of non-blank codes
function ibh_get_prior_auth ($id) {
	
	$sql = "SELECT * FROM form_prior_auth WHERE id='$id'";
		
	$res = sqlStatement($sql);
	
	$auth = sqlFetchArray($res);	
	
	$actual_codes = ibh_get_pa_actual_codes($auth);
	$auth["codes"] = $actual_codes;
	
	$auth_date = date("F j, Y", strtotime($auth['date']));
	$auth_from = $auth['auth_from'] . " 00:00:00";
	$auth_to = $auth['auth_to'] . " 23:59:59";
	$bills_claimed = array();
	$pid = $auth['pid'];
	
	// REMAINING DAYS, IF ANY
	$expires = strtotime($auth['auth_to'] . " 23:59:59");
	$today = strtotime(date("Y-m-d H:i:s"));
	
	$days_remaining = ceil(($expires - $today) / 86400);
	if ($days_remaining <= 0) $days_remaining = 0;
	$auth["days_remaining"] = $days_remaining;
	$billed_units = 0;
	
	// cycle through each of the valid 7-ish codes
	foreach ($actual_codes as $billing_code) {

		$billing_code = explode(":", $billing_code);
        $cpt4 = $billing_code[0];

        if(isset($billing_code[1])){
            $mod = $billing_code[1];
        }else{

            $mod = "";
        }


        //***IBH HARD CODE: Required CPT Codes that need modifier.
        if(in_array($cpt4, "H0031, H0032")) {

            $bsql = "SELECT date, encounter, code, code_text, id, units, modifier FROM billing WHERE pid='$pid' AND code='$cpt4' AND modifier='$mod' AND (date >='{$auth_from}' AND date <= '{$auth_to}')";

        }else{

            $bsql = "SELECT date, encounter, code, code_text, id, units, modifier FROM billing WHERE pid='$pid' AND code='$cpt4'  AND (date >='{$auth_from}' AND date <= '{$auth_to}')";

        }
		
        $billsq = sqlStatement($bsql);
        while($bill = sqlFetchArray($billsq)) {
			
		    if (!in_array($bill['id'], $bills_claimed)) {
			
		    $bills_claimed[] = $bill['id'];
		    $billed_units += $bill['units'];
		    
		    }
	    } // end if it's NOT claimed
			
    } // end foreach code cycle	

	$auth["bills"] = count($bills_claimed);
	
	
	$auth["units_remaining"] = $auth['units'] + ((-1 * $billed_units) + $auth['unit_adjustment']);
	
	
	return $auth;
	
}


// Alerts chosen staff to either unit threshold or day threshold
// @param $pa_number = unique alphanumeric code ("AE34599") rather than id key
// type = "days" | "units"
function ibh_alert_prior_auth($pa_id, $type="days") {
	
	$auth = ibh_get_prior_auth($pa_id);
	$pid = $auth['pid'];
	$patient = ibh_get_patient($pid);
	
	$alerts_to = explode(",", $auth['alerts_to']);
	
	if ($type == "units") {
		$subject = "Prior Auth #" . $auth['prior_auth_number'] . ": " . $auth['units_remaining'] . " units left!";
		$body = "Prior auth warning (UNITS): Patient " . $patient['fname'] . " " . $patient['lname'] . " has " . $auth['units_remaining'] . " units remaining on Prior Auth #" . $auth['prior_auth_number'] . ". <a target='RTop' href='/openemr/interface/forms/prior_auth/display.php?prior_auth_number=" . $auth['prior_auth_number'] . "&pid=" . $pid . "'>Click here to view Prior Auth</a>.";
		
	} else {
		$subject = "Prior Auth #" . $auth['prior_auth_number'] . ": " . $auth['days_remaining'] . " days left!";
		$body = "Prior auth warning (DAYS): Patient " . $patient['fname'] . " " . $patient['lname'] . " has " . $auth['days_remaining'] . " days remaining on Prior Auth #" . $auth['prior_auth_number'] . ". <a target='RTop' href='/openemr/interface/forms/prior_auth/display.php?prior_auth_number=" . $auth['prior_auth_number'] . "&pid=" . $pid . "'>Click here to view Prior Auth</a>.";
		
	}

	foreach ($alerts_to as $user_id) {
		$user = ibh_get_user_by_id($user_id);
		$username = $user['username'];
		// echo "<br>ALERT TO " . $username;
		
		ibh_send_message("openemr", $username, $pid, $subject, $body);
		
	}

}



// generated from billing events 
// @ /interface/forms/custom_odt/update_fs.php
function ibh_check_for_prior_auth_units($pid, $billing_code) {
	// cycle through all valid prior auths
	
	$auths = ibh_get_patient_prior_auths($pid, true);

	foreach($auths as $auth) {
		
		if (in_array($billing_code, $auth['codes']) 
			&& $auth['alert_units'] == $auth['units_remaining']) {
			
			ibh_alert_prior_auth($auth['id'], "units");
			return true;
		}
		

	}
	
	return false;
	
}


// generated every day with a cron
// checks ALL active prior auths
function ibh_check_for_prior_auth_day_alerts() {
	// once a day, check for prior auths that have REACHED
	// the EXACT "days remaining" as they have days remaining
	$auths = array();
	$today = date("Y-m-d");


    //

    $sql = "SELECT id, pid, auth_from, auth_to, units, alert_units, alert_days, " .
        " DATE_FORMAT('{$today}','%Y-%m-%d') , datediff(auth_to, DATE_FORMAT('{$today}','%Y-%m-%d') ) as date_diff  " .
        "FROM form_prior_auth WHERE (datediff(auth_to, DATE_FORMAT('{$today}','%Y-%m-%d') ) > -7) and (datediff(auth_to, DATE_FORMAT('{$today}','%Y-%m-%d') ) = alert_days) ORDER BY date DESC";

	$res = sqlStatement($sql);
	$ret = "";
	
	
    while($auth = sqlFetchArray($res)){
		$id = $auth['id'];
		$auths[] = ibh_get_prior_auth ($id);
	}

	foreach($auths as $auth) {
		if ($auth['alert_days'] >= $auth['days_remaining']) {	
			ibh_alert_prior_auth($auth['id'], "days");
			// $ret .= "ALERT: " . $auth['prior_auth_number'] . "<br>";
		}
	}
	return $ret;
}


function ibh_get_prior_auth_num($pid, $billing_code) {
	// gets active prior auths
	$prior_auths = ibh_get_patient_prior_auths($pid, true);
	$codes = array();
	
	foreach($prior_auths as $pa) {
		$codes = $pa['codes'];
		$pa_num = $pa['prior_auth_number'];
		foreach ($codes as $code) {
			if ($billing_code == $code) {
				return $pa_num;
			}
		}
	}
}


// this is focused on the patient, and all their prior_auths:
// are there any warnings, etc.?
function ibh_get_prior_auth_warnings($pid) {
	
	$valid_auths = array();
	
	$prior_auths = ibh_get_patient_prior_auths($pid, true);
	
	while($auth = sqlFetchArray($prior_auths)){ 

		$codes_array_loaded = array($auth['code1'], $auth['code2'], $auth['code3'], $auth['code4'], $auth['code5'], $auth['code6'],$auth['code7']);
		
		$actual_codes = array();
		foreach ($codes_array_loaded as $pa_code) {
			if ($pa_code) $actual_codes[] = trim($pa_code);
		}
	
		$auth_date = date("F j, Y", strtotime($auth['date']));
		$auth_from = $auth['auth_from'] . " 00:00:00";
		$auth_to = $auth['auth_to'] . " 23:59:59";
		
		$units_remaining = $auth['units'];
		
		// REMAINING DAYS, IF ANY
		$expires = strtotime($auth_to);
		$today = strtotime(date("Y-m-d"));
		
		$days_remaining = ceil(($expires - $today) / 86400);
		if ($days_remaining <= 0) $days_remaining = 0;
		
		foreach ($actual_codes as $billing_code) {
			
			$bsql = "SELECT * FROM billing WHERE pid='$pid' AND code='$billing_code' AND (date >'$auth_from' AND date < '$auth_to')";
			// echo $bsql;
			
	        $billsq = sqlStatement($bsql);
	        while($bill = sqlFetchArray($billsq)) {
	
			    if (!in_array($bill['id'], $bills_claimed)) {

				    $units_remaining = $units_remaining - $bill['units'];
			       
			        $bills_claimed[] = $bill['id'];
		        } // end if it's NOT claimed
				
	        } 

		} // end foreach code cycle
		
		
		if ($days_remaining > 0 && $units_remaining > 0) {
			$valid_auths[] = array("codes"=>$actual_codes, "auth_code"=>$auth['prior_auth_number'], "days_remaining"=>$days_remaining, "units"=> $auth['units'], "units_remaining"=>$units_remaining);
		}
		
		
	} // end $prior_auths while	
	
	if (count($valid_auths) > 0) {
		return $valid_auths;
	} else {
		return 0;
	}

}


function ibh_array_clone($array) {
    return array_map(function($element) {
        return ((is_array($element))
            ? call_user_func(__FUNCTION__, $element)
            : ((is_object($element))
                ? clone $element
                : $element
            )
        );
    }, $array);
}


function ibh_get_era_svc_codes($svc_list) {
	
	$all_codes = array();
	
	foreach ($svc_list as $svc) {
		
		$codes = $svc['adj'][0];
		$code = $codes['group_code'] . "-" . $codes['reason_code'];
		
		$message = ibh_get_era_warning_code($code);
		if ($message) {
			$all_codes[] = array("era_code"=>$code, "message"=>$message);
		}
		
	}
	
	return $all_codes;
}



function ibh_get_era_warning_code($c) {
    //***IBH HARD CODE
	$warning_codes = array(
		"CO-18"=>"Duplicate Claim or service",
		"OA-18"=>"Duplicate Claim or service",
		"CO-16"=>"Claim/Service lacks information which is needed for adjudication",
		"PI-16"=>"Claim/Service lacks information which is needed for adjudication",
		"CO-97"=>"Payment adjusted because the benefit for this service is included in the primary procedure/inclusive",
		"CO-22"=>"Payment adjusted because this care may be covered by another provider",
		"CO-197"=>"Payment denied/reduced for absence of precertification/no authorization",
		"PR-185"=>"The rendering provider is not eligible to perform the service billed",
		"CO-185"=>"The rendering provider is not eligible to perform the service billed",
		"CO-A1"=>"Claim/Service denied",
		"CO-151"=>"Payment adjusted because the payer deems the information submitted does not support this many services",
		"CO-96"=>"Non-Covered Charges"
	);
	
	$ret = "";
	
	foreach($warning_codes as $code => $descr) {
		if ($c == $code) $ret = $descr;
	}
	
	return $ret;
}



function ibh_get_user_by_id($id) {
		
	$stmt = sqlStatement("SELECT * FROM users WHERE id=? and active = 1", array($id));
	$arr = sqlFetchArray($stmt);
	return $arr;
	
}

function ibh_encounter_title_pulldown($sel, $type="id") {
	
	$cres = sqlStatement("SELECT * FROM openemr_postcalendar_categories ORDER BY pc_catname");
	
	$html = "<select id='encounter_title_pulldown' name='pc_catid'>";
	
	while ($crow = sqlFetchArray($cres)) {
				
		if ($type == "id") {
			$val = attr($crow['pc_catid']);
		} else {
			$val = str_replace("'", "", $crow['pc_catname']);
		}
		
		$html .= "<option value='" . $val . "'";
		
		$title = text(xl_appt_category($crow['pc_catname']));
		
		if ($title == $sel) $html .= " selected";
		
		$html .= ">" . $title . "</option>\n";
	}
	
	$html .= "</select>";
	
	return $html;

}


function ibh_get_categories_array($assoc = true) {
	 $cats = array();
	 // GET ALL CATEGORIES
	$cres = sqlStatement("SELECT * FROM openemr_postcalendar_categories WHERE pc_catname LIKE '%:%' ORDER BY pc_catname");

	while ($crow = sqlFetchArray($cres)) {
	
		$code = trim(explode(":", $crow['pc_catname'])[1]);
		$val = $crow['pc_catid'];
		if ($assoc) {
			$cats['cat-' . $val] = $code;
		} else {
			$cats[] = $code;
		}
		
	}
	
	return $cats;
}


function ibh_category_checkboxes($pid = false) {
	
	$non_sels = "";
	if ($pid) {
		$non_sels = ibh_get_patient_pa_cat_exceptions($pid, "ids");
	}
	// 
	$cres = sqlStatement("SELECT * FROM openemr_postcalendar_categories WHERE pc_catname LIKE '%:%' ORDER BY pc_catname");
	
	$html  = ""; // $pid . "<br>" . print_r($non_sels, true);
	
	
	$html .= "<div class='cat-checkers'>";
	
	while ($crow = sqlFetchArray($cres)) {
				
		$title = $crow['pc_catname'];
		$val = $crow['pc_catid'];
		
		$checked = in_array($val, $non_sels) ? "": "checked";
		
		$html .= "<input type='checkbox' value='1' name='cat-" . $val . "' id='" . $val . "' " . $checked . "><label for='" . $val . "'>" . $title . "</label><br>";
		
	}
	
	$html .= "</div>";
	
	return $html;

}




function ibh_get_insurance_company_pulldown($sel="", $option="") {
		
	$cres = sqlStatement("SELECT * FROM insurance_companies ORDER BY name");
	
	$html = "<select id='insurance_company' name='insurance_company'>";
	if ($option) $html .= $option;
	
	while ($crow = sqlFetchArray($cres)) {

		
		$html .= "<option value='" . $crow['id'] . "'";
				
		if ($crow['id'] == $sel) $html .= " selected";
		
		$html .= ">" . $crow['name'] . "</option>\n";
	}
	
	$html .= "</select>";
	
	return $html;
}
	
function ibh_archive_encounter($encounter_id) {
	
	$enc_info = ibh_get_encounter_info($encounter_id);
	$enc_date = $enc_info['date'];
	$enc_provider = $enc_info['provider_id'];
	$enc_patient = $enc_info['pid'];
	$user = $_SESSION['authUser'];
	
	
	sqlStatement("INSERT INTO ibh_archived_encounters (encounter, date, pid, provider_id, user) values('$encounter_id','$enc_date',  '$enc_patient', '$enc_provider', '$user')");
	
}
	
// gets encounter and provider info in one fell swoop
function ibh_get_encounter_info($encounter_id) {
	
	$arr2 = array();
	
	$sql = "SELECT * FROM form_encounter fe, users u WHERE fe.encounter=? AND fe.provider_id=u.id  LIMIT 1";

	$data = array($encounter_id);
	
	$stmt = sqlStatement($sql, $data);
	
	$arr1 =  sqlFetchArray($stmt);
	
	$sup = $arr1['supervisor_id'];

	$arr1['slash_date'] = date("m/d/y", strtotime($arr1['date']));
	
	if ($sup == 0) {
		// NO SUPERVISOR
		$arr2['supervisor_fname'] = "<em>None selected yet.</em>";
		$arr2['supervisor_lname'] = "";
		$arr2['supervisor_username'] = "";
		
	} else {
		// GET SUPERVISOR INFO IN ADDITION TO PROVIDER (USER) INFO
		$stmt2 = sqlStatement("SELECT username as supervisor_username, fname as supervisor_fname, lname as supervisor_lname FROM users WHERE id='$sup'");
	
		$arr2 = sqlFetchArray($stmt2);
	}
	
	
	return $arr1 + $arr2;
}


function ibh_get_encounter_times($encounter) {
	
	$stmt = sqlStatement("SELECT * FROM openemr_postcalendar_events WHERE encounter='$encounter' LIMIT 1");
	
	if (sqlNumRows($stmt) > 0) {
		return sqlFetchArray($stmt);
	} else {
		return array("message"=>"We can't get accurate appointment times/durations, as it was before 2/15/17.");
	}
	
}




function ibh_user_is_supervisor() {
	$username = $_SESSION['authUser'];
	
	$stmt = sqlStatement("SELECT info FROM users WHERE username='$username' and active = 1");
	$arr = sqlFetchArray($stmt);
	if ( $arr['info'] == "Supervisor" || $arr['info'] == "Supervisor:") return true;
	
	return false;
}


 /*
	 cols
	 											 v['code_type']."</td><td align='center'>" .
												 $v['code']."</td><td align='center'>" . 
												 $v['modifier']."</td><td align='center'>" .
												 $v['justify']."</td><td align='center'>" .
												 $v['units']."</td><td align='center'>" .
												 $v['fee']."</td><td align='center'>" .
												 $v['notecodes']."</td><td align='center'>" .
												 $v['code_text']."</td></tr>";
*/ 
function ibh_get_encounter_billing ($pid, $encounter)
{
	
	$all=array();
	
    $res = sqlStatement("select * from billing b where b.pid = ? and b.encounter = ? and b.activity=1 order by code_type, date ASC", array($pid, $encounter));
	
	$date = "";
	for($iter=0; $row=sqlFetchArray($res); $iter++) {
	      $all[] = $row;
	      $date = $row['date'];
	}

	// add copay etc data to billing array
	$arq = sqlStatement("SELECT * FROM ar_activity where pid =? and encounter =?", array($pid,$encounter));//new fees screen copay gives account_code='PCP'
  
	while($ar = sqlFetchArray($arq)){
 	  $ara = array();
 	  $ara['code_type'] = $ar['code_type'];
 	  $ara['code'] = $ar['code'];
 	  $ara['modifier'] = "";
 	  $ara['justify'] = "";
 	  $ara['units'] = "";
 	  $ara['fee'] = number_format(-1 * $ar['pay_amount'], 2);
 	  $ara['notecodes'] = $ar['memo'];
 	  $ara['code_text'] = "Co-pay";
 	  
 	  $ara['date'] = $date;
 	  
 	  
 	  $all[] = $ara;
	}
	
	return $all;
}


function ibh_delete_encounter_signature($form_id, $encounter, $delete_all=false) {
	
	$stmt = sqlStatement("SELECT * FROM form_encounter WHERE encounter='$encounter'");
	$arr = sqlFetchArray($stmt);
	$form_raw_id = $form_id;
	$encounter_provider_id = $arr['provider_id'];
	
	if ($delete_all) {
		$del = sqlStatement("DELETE FROM esign_signatures WHERE tid='$form_raw_id'");
	} else {
		$del = sqlStatement("DELETE FROM esign_signatures WHERE tid='$form_raw_id' AND uid='$encounter_provider_id'");
	}
	
	if ($del) {
		return true;
	} else {
		return false;
	}
	
}

/*
function ibh_facility_pulldown($sel = "", $name="facility_id") {
	
	$html = "<select name='" . $name . "'>";
	
	$facs = array(
		"Boise"=>3, // 3
		"Caldwell"=>4, // 4
		"Mountain Home"=>5, // 5
		"Peak"=>6, // 6	
		"NJC"=>7, // 7
		"Riverside"=>8, // 8
		"Nampa"=>9, // 9
	);
	
	foreach ($facs at $n => $v) {
		$sel_str = ($sel == $v) ? " selected": "";
		$html .= "<option value='" . $v . "' $sel_str>" . $n . "</option";
	}
	
	$html .= "</select>";
	
	return $html;
	
}
*/

// This provides a quicker lookup!
function ibh_get_facility_name($id = "") {
    //***IBH HARD CODE
	if (!$id) return "Idaho Behavioral Health Boise";
	
	$facs = array("", "", "", // 0,1,2
		"Idaho Behavioral Health Boise", // 3
		"Idaho Behavioral Health Caldwell", // 4
		"Idaho Behavioral Health Mountain Home", // 5
		"Idaho Behavioral Health Peak", // 6	
		"Idaho Behavioral Health NJC", // 7
		"Idaho Behavioral Health Riverside", // 8
		"Idaho Behavioral Health Nampa", // 9
		"**ADD NEW FACILITY**" // 10 d
	);
	return $facs[$id];
	
}


	
function ibh_getBillingData($from_date, $to_date, $provider, $facility="", $code="") {
	
	$data = array($from_date, $to_date);
	$prov = ""; $fac = ""; $cde = "";
	
	if ($code) {
		$cde = "b.code = ? AND ";
		array_unshift($data, $code);
	}
	
	if ($provider) {
		$prov = "b.provider_id = ? AND ";
		array_unshift($data, $provider);
	}
	
	if ($facility) {
		$fac = "f.facility_id = ? AND ";
		array_unshift($data, $facility);
	}
	
	$sql = "SELECT b.*, u.fname as ufname, u.lname as ulname, u.mname as umname, pd.fname, pd.lname, pd.mname, f.facility_id as enc_facility_id, f.facility as enc_facility FROM billing b JOIN users u ON b.provider_id=u.id JOIN form_encounter f ON b.encounter=f.encounter JOIN patient_data pd ON b.pid=pd.pid WHERE f.pid=pd.pid AND " . $fac . $prov . $cde . " f.date >= ? AND f.date <= ? ORDER BY b.code_text";
	
	return sqlStatement($sql, $data);	
		
	}
	

/*
	ibh_getSupervisorPulldown
	@param el  what to name the select element, both name and ID
	@param sel the selected supervisor user id
*/
function ibh_getUserPulldown($el="", $sel="", $supervisor=false) {
	
	$selq = ""; 
	$html = "<select name='$el' id='$el' class='user-pulldown'><option value=''>Choose provider...</option>";
	
	$sup = $supervisor ? "AND info LIKE '%upervisor%'": "";
	
	$sql = "SELECT * FROM users WHERE active=1 AND authorized='1'" . $sup . " ORDER BY lname, fname";
	
	$stuff = sqlStatement($sql);
	// print_r($stuff);
	
	while ($row = sqlFetchArray($stuff)) {
		$s = $row['id'] == $sel ? "selected": "";
		
		$html .= "<option value='" . $row['id'] . "' " . $s . ">" . $row['lname'] . ", " . $row['fname'] . "</option>";
	}
	
	$html .= "</select>";
	
	return $html;
}




function ibh_getEncounterForms($encounter) {
	
	$sql = "SELECT f.form_name, f.date, f.form_id, f.formdir FROM forms f WHERE f.encounter='$encounter' AND f.deleted=0 ORDER BY f.date DESC LIMIT 5";
	
	$data = array();
	
	$stuff = sqlStatement($sql, $data);
	
	$ct = 0;
	$lbf_ct = 0;
	$lbfs = array();
	
	//$form_is_locked = false;

	while ($row = sqlFetchArray($stuff)) {
		
		//if ($row['is_lock'] == 1) { $form_is_locked = true; }
		
		$formdir = $row['formdir'];
		if (substr($formdir, 0 , 3) == "LBF") {
			$lbf_ct++;
			$forms[] = array("name"=>$row['form_name'], "date"=>$row['date'], "id"=>$row['form_id'], "dir"=>$row['formdir']);
		}
		
		$ct ++;
		
	}
	
	//if ($form_is_locked) return false;
	
	return array("count"=>$ct, "lbf_count"=>$lbf_ct, "forms"=>$forms);
	
}

function encounterLocked($e) {
	
	$sql = "SELECT f.id FROM forms f, esign_signatures es WHERE es.is_lock=1 AND es.tid=f.id AND f.encounter='$e' LIMIT 1";
	
	$results = sqlStatement($sql);
	
	return sqlNumRows($results);

	
}
	



function ibh_getSupevisorAlertEncounters($supervisor="", $provider="", $sort="fe_date-DESC") {
	
	$current_user = ibh_get_session_user_id(); // $_SESSION["authUser"];
	
	if ($supervisor) {
		$append = " AND fe.supervisor_id='$supervisor' ";
	} else if ($provider) {
		$append = " AND fe.provider_id='$provider' ";
	} else {
		$append = " AND (fe.supervisor_id='$current_user' OR fe.provider_id='$current_user') ";
	}
		
	// find from last 14 days
	$ndays = time() - (14 * 86400);
	$ndays_f = date("Y-m-d H:i:s", $ndays);
	
	$sql = "SELECT fe.encounter FROM form_encounter fe WHERE fe.date > '" . $ndays_f . "' AND fe.supervisor_id > 0 " . $append . " ORDER BY fe.date DESC";

	$results = sqlStatement($sql);
	
	return $results;
	
	}


	
/*
	ibh_getSupevisorAlerts
	@param supervisor    supervisor USERNAME (not id)
	@param provider      provider USERNAME (not id)
*/
function ibh_getSupevisorAlerts($supervisor="", $provider="", $sort="fe_date-DESC") {
	
	$current_user = ibh_get_session_user_id(); // $_SESSION["authUser"];
	
	if (!$sort) $sort = "fe_date-DESC";
	$sort = str_replace("-", " ", $sort);
	
	if ($supervisor) {
		$append = " AND fe.supervisor_id='$supervisor' ";
	} else if ($provider) {
		$append = " AND fe.provider_id='$provider' ";
	} else {
		$append = " AND (fe.supervisor_id='$current_user' OR fe.provider_id='$current_user') ";
	}
		
	// find from last 14 days
	$ndays = time() - (14 * 86400);
	$ndays_f = date("Y-m-d H:i:s", $ndays);
	
	$sql = "SELECT fe.*, fe.date as fe_date, fe.supervisor_id as fe_sup_id, u.fname as p_fname, u.lname as p_lname, pd.pid, pd.fname as fname, pd.lname as lname FROM form_encounter fe, patient_data pd, users u WHERE fe.date > '" . $ndays_f . "' AND fe.supervisor_id > 0 AND fe.provider_id=u.id AND fe.pid=pd.pid " . $append . " ORDER BY " . $sort;

	$results = sqlStatement($sql);
	
	return $results;
	
	}
	
	
	


function ibh_get_patient_diagnosis($pid) {
	
	//retrieve the last diagnosis for the patient
	$f_id = sqlStatement("SELECT form_id, encounter FROM forms WHERE form_name='Patient Diagnosis' AND pid=$pid AND deleted=0 ORDER BY encounter DESC LIMIT 1");

    $res = sqlFetchArray($f_id);
	 
	$diag = sqlStatement("SELECT field_value FROM lbf_data WHERE form_id = '" . $res['form_id'] . "'");
	$i = 0;
	
	while($current_diag = sqlFetchArray($diag)){
		if($current_diag['field_value']) {
			return $current_diag['field_value'];
		} 
	}
	
	return "NO:DIAG"; 		

}



function esign_interpreter($pid, $encounter, $minutes, $name, $modifier = ''){
   	
	$enc = ibh_get_encounter_info($encounter);
	
	$prov = $enc['provider_id'];

	$justify = ibh_get_patient_diagnosis($pid);
	$just = explode(":", $justify);
   
	$fee = IBH_INTERPRETER_FEE;
	
	$total = $fee * $minutes;
	$en = date("Y-m-d H:i:s");
    if(is_array($modifier)){

        $modifier = implode(':', $modifier);

    }

    if($modifier == '1'){
        $modifier = "";
    }
    //***IBH HARD CODE
    sqlStatement("INSERT INTO billing SET " .
                "date = '$en' , " .
                "code_type = 'CPT4', " .
                "code = 'T1013', " .
                "pid = '$pid', " .
                "provider_id = '$prov', " .
                "modifier = '". $modifier ."', ".
                "user = '" . $_SESSION['authUserID'] . "', " .
                "groupname = 'default', " .
                "authorized = '1', " .
                "encounter = '$encounter', " .
                "code_text = 'Interpreter', " .
                "activity = '1', " .
                "units = '$minutes', " .
                "fee = '$total', " .
                "justify = '". $just[0] . "|". $just[1] .":', " .
				"notecodes = '".$minutes ." Units - ". $name."'" 
                 );       	
}


// AKA psychotherapy home visit or transportation reimbursement per diem
function esign_transportation($pid, $encounter, $modifier) {
	
	$enc = ibh_get_encounter_info($encounter);
	
	$prov = $enc['provider_id'];

	$justify = ibh_get_patient_diagnosis($pid);
	$just = explode(":", $justify);
   
	$fee = IBH_TRANSPORTATION_FEE;
	
	$d = date("Y-m-d H:i:s");
    if(is_array($modifier)){

        $modifier = implode(':', $modifier);

    }

    if($modifier == '1'){
        $modifier = "";
    }


    sqlStatement("INSERT INTO billing SET " .
                "date = '$d' , " .
                "code_type = 'CPT4', " .
                "code = 'T2002', " .
                "pid = '$pid', " .
                "provider_id = '$prov', " .
                "modifier = '". $modifier ."', ".
                "user = '" . $_SESSION['authUserID'] . "', " .
                "groupname = 'default', " .
                "authorized = '1', " .
                "encounter = '$encounter', " .
                "code_text = 'Non-emergency transportation; per diem', " .
                "activity = '1', " .
                "units = '1', " .
                "fee = '$fee', " .
                "justify = '". $just[0] . "|". $just[1] .":', " .
				"notecodes = 'Psychotherapy Home Visit'" 
                 ); 
    return true; 
    
}


// creates exception for location to be listed as "12" when
// encounter has a "transportation reimbursement"
function ibh_get_location($claim, $claim_location = 11) {
	
	$loc = $claim_location;
	
	foreach ($claim->procs as $proc) {

		if ($proc['code'] == "T2002") {
	    	$loc = 12;
		}
  	}
  	
  	return $loc;
}




	
	
// LEGACY FUNCTIONS FROM ENCOUNTER/FORM ESIGN
// FORMERLY update_fs.php

function esign_interactive_complexity($encounter, $pid, $modifier = ''){

    //Get date of the encounter.
    //so that the correct billing entry can be recorded

	$t = date("H:m:s");

    $res = ibh_get_encounter_info($encounter);

    $e_date = $res['date'];
    $prov = $res['provider_id'];

    $en_date = explode(" ", $e_date);
    $en = $en_date[0] . " " . $t; // fudging here... adding a current time to time of encounter...

	$justify = ibh_get_patient_diagnosis($pid);
	$just = explode(":", $justify);

    if(is_array($modifier)){

        $modifier = implode(':', $modifier);

    }

    if($modifier == '1'){
        $modifier = "";
    }


    sqlStatement("INSERT INTO billing SET " .
                "date = '$en' , " .
                "code_type = 'CPT4', " .
                "code = '90785', " .
                "modifier = '". $modifier ."', ".
                "pid = '$pid', " .
                "provider_id = '$prov', " .
                "user = '" . $_SESSION['authUserID'] . "', " .
                "groupname = 'Default', " .
                "authorized = '1', " .
                "encounter = '$encounter', " .
                "code_text = 'Interactive Complexity', " .
				"bill_date = '".date("Y-m-d")."', " .  
                "activity = '1', " .
                "units = '1', " .
                "fee = '20.00', " .
                "justify = '".$just[0]."|".$just[1] .":'"
                 ); 
    
    return true;    
}
	
	
	

    function ibh_esign_checkout($pid, $modifier, $encounter){
			  	// $mssg .= "(" . $pid . "," . $modifier . ", " . $encounter . ")";
        if(is_array($modifier)){

            $modifier = implode(':', $modifier);

        }

        if($modifier == '1'){
            $modifier = "";
        }
				function getPrice($c){
				  
					$sql = "SELECT a.id, b.pr_id, b.pr_price FROM codes AS a, prices AS b WHERE a.code LIKE '$c' AND b.pr_id = a.id";
					$p = sqlStatement($sql);
					$pres = sqlFetchArray($p);
					
					return $pres['pr_price'];
				
				}
			  
				//retrieve the appointment title to get billing code info
			  
				function bInfo($pid, $encounter){
					//retrieve encounter date that should match appointment date
				  
					$sql = "SELECT date, provider_id FROM form_encounter WHERE encounter = '$encounter'";
					$enD = sqlStatement($sql);
					$enDa = sqlFetchArray($enD);
					$enDate = $enDa['date'];
					$prov = $enDa['provider_id'];				  
					      
					$sql = "SELECT pc_title, pc_startTime, pc_endTime FROM openemr_postcalendar_events WHERE encounter='$encounter'";
					$ti = sqlStatement($sql);
					$ttx = sqlFetchArray($ti);
					$title = $ttx['pc_title'];
					$stime = $ttx['pc_startTime'];
					$etime = $ttx['pc_endTime'];
					
					return array($title, $prov, $enDate, $stime, $etime);
				}
			  
				//retrieve the provider id
				    //retrieve code description
				function icd($code, $type){
				  
					if($type == ICD9 ){
						$txt = sqlStatement("SELECT long_desc FROM  icd9_dx_code WHERE formatted_dx_code LIKE '$code' ");
						$res = sqlFetchArray($txt);
						$desc = $res['long_desc'];
					}else {
						$txt = sqlStatement("SELECT long_desc FROM  icd10_dx_order_code WHERE formatted_dx_code LIKE '$code' ");
						$res = sqlFetchArray($txt);
						$desc = $res['long_desc'];    
					}
					
					return $desc;
				  
				}
				// Code that runs and calls functions to gather information for the billing insert.

                $now = date("Y-m-d H:m:s");
                $bInfo = bInfo($pid, $encounter);      //billing info
		
                $bData = explode(":", $bInfo[0]);
			
                $t = $bData[0];                 // text
                $c = $bData[1];                 // code
			
				$c = trim($c);
				$price = getPrice($c);
		
                $prov = $bInfo[1];   
                  
                // use exact date to track against signatures
                $en = $bInfo[2]; //the encounter date which may not be today
                			
                $j = ibh_get_patient_diagnosis($pid);
				
				
                $js = explode(":", $j);
                $code = $js[1];                //Fetch the description for this  ICD9
                $type = $js[0];                //holds the icd type 9 or 10
                
                $desc = icd($code, $type);


				//Set the units for these CPT codes //***IBH HARD CODE
				if(	$c == "T1017" ||
                    $c == "T1016" ||
					$c == "T1014" ||
					$c == "T1017" ||
					$c == "H2017" ||
					$c == "H2011" ||
					$c == "H0038" ||
					$c == "H0046" ||
					$c == "H0031" ||
					$c == "H0032") {
						
					$startTime = $bInfo[3];
					$timeNow = $bInfo[4];
					$time = strtotime($timeNow) - strtotime($startTime);
					$minutes = $time/60;
					$u = round($minutes/15);
					
					if($u < 0) {
						$u = -1 * $u;
					}
					
					//Changes the value of price. 
					// The neg 1 fixes an issue occuring on the production
					// system. The units and price are coming out negative.
					// so to fix it the -1 was added. Could figure out what was causing the 
					// neg numbers.           
					  
				} else {
			         $u = 1;
			    }	
			    // eof setting units
			    	

				$pricet = $u *  $price;
				
				// if there's a diagnosis
				if(!empty($js[0])){
					global $event_date,$appttime,$temp_eid;
					$find = sqlStatement("SELECT * FROM billing WHERE code_text=? AND encounter='$encounter'", array($desc));
					// $res = sqlFetchArray($find);
			
					if (sqlNumRows($find) == 0){ 
						 
					sqlStatement("UPDATE openemr_postcalendar_events SET pc_apptstatus = '>' WHERE encounter='$encounter' AND pc_apptstatus = '@'");

                    //get the appointment info that was just created
                    $mts_sql = "select * from openemr_postcalendar_events where pc_apptstatus = '>' and encounter = '$encounter'";
                    $query = sqlStatement($mts_sql);
                    $ret = sqlFetchArray($query);

                    manage_tracker_status($ret['pc_eventDate'],$ret['pc_startTime'],$ret['pc_eid'],$ret['pc_pid'],$_SESSION["authUser"],">",$_POST['form_room'],$encounter);
						
                    sqlStatement("INSERT INTO billing SET " .
                            "date = '$en' , " .
                            "code_type = 'CPT4', " .
                            "code = '$c', " .
                            "pid = '$pid', " .
                            "provider_id = '$prov', " .
                            "user = '" . $_SESSION['authUserID'] . "', " .
                            "groupname = 'default', " .
                            "authorized = '1', " .
                            "encounter = '$encounter', " .
                            "code_text = ?, " .
							"billed = '0' , ".								
							"modifier = '". $modifier ."', ".
                            "activity = '1', " .
						    "bill_date = '".date("Y-m-d")."', " .								
                            "units = '$u', " .
                            "fee = '$pricet', " .
                            "justify = '" . $js[0]."|".$js[1] .":'", array($t)
                             ); 

                    sqlStatement("INSERT INTO billing SET "
                            . "date = '$en',"
                            . "code_type = '$js[0]',"
                            . "code = '$js[1]',"
                            . "pid = '$pid',"
                            . "provider_id = '$prov',"
                            . "user = '" . $_SESSION['authUserID'] . "',"
                            . "groupname = 'default',"
                            . "authorized = '1',"
                            . "encounter = '$encounter',"
                            . "code_text = ?,"
							. "billed = '0' , "								
                            . "activity = '1',"
							. "bill_date = '".date("Y-m-d")."',"
                            . "units = '1', "
                            . "fee = '0.00' ", array($desc)
                            );
                    
                 
					ibh_check_for_prior_auth_units($pid, $c);
					
				} // end rowCt == 0
					

			   return "C-" . $c . ":: PID-" . $pid . " DIAG:" . $j . ":" . $bInfo[0] . ":" . $price . ":" . $desc;
			   
          } else {
           	return "No Diagnosis has been entered, <br/><br/>Patient cannot be checked out.";
           	exit;
          }
        
        return "EOF";
              
}


## FORMS &  TICKLER

function ibh_form_link($long, $encounter=0, $pid=0) {
	
	$short = "";
	$link = "";

    //***IBH HARD CODE
	switch ($long) {
		case "Comprehensive Diagnostic Assessment": $short = "CDA"; break;
		case "Clinic Tx Plan and Review": $short = "TX Plan"; break;
		case "CBRS/CM Treatment Plan and Review": $short = "CBRS Tx Plan"; break;
		case "CBRS Case Management Assessment": $short = "BCRS CM"; break;
		case "Medication Management Evaluation": $short = "Med Mgmt Eval"; break;
		case "Peer Support Recovery Plan": $short = "Peer S.R. Plan"; break;
		default: $short = $long;
	}
	
	if ($encounter && $pid) {
		$link = "<a href='/openemr/interface/patient_file/encounter/forms.php?set_encounter=" . $encounter . "&pid=" . $pid . "' target='_blank' class='form-link'>open</a>";
	}
	
	return $short . $link;
}



function ibh_get_form_info($form_id, $projections = false) {
	
	if ($form_id == 0) return array("blank"=>1, "nice_date"=>"<span class='choose-form'>CHOOSE FORM...</span>");
	
	$stuff = sqlStatement("SELECT f.* FROM forms f LEFT JOIN esign_signatures es ON f.id=es.tid WHERE es.tid=" . $form_id . " LIMIT 1", array());
	$ret = sqlFetchArray($stuff);
	
	$dt = strtotime($ret['date']);
		
	$ret['nice_date'] = date("Y-m-d", $dt);
	$ret['blank'] = 0;
	
	if ($projections) {
		$d90 = $dt + (86400 * 90);
		$d180 = $dt + (86400 * 180);
		$d270 = $dt + (86400 * 270);
		$d360 = $dt + (86400 * 360);
		
		$ret['d90'] = date("Y-m-d", $d90);
		$ret['d180'] = date("Y-m-d", $d180);
		$ret['d270'] = date("Y-m-d", $d270);
		$ret['d360'] = date("Y-m-d", $d360);
	}
	
	return $ret;
}

