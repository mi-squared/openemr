<?php

require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");


$tallies = array();

/*
// get number of users first
//for ($i=0; $i<170; $i++) {

//	$results = sqlStatement("SELECT * FROM esign_signatures WHERE datetime > '2017-03-21 00:00:00' AND uid=" . $i);
//	$tallies[] = sqlNumRows($results);
	
//}

	foreach($users as $k => $pids) {
		$user_id = explode("-", $k)[1];
		// $tally = $tallies[$user_id];
		// implode(", ", $u)
		// $user = ibh_get_user_by_id($user_id);
		// $perc = round(($tally / 80093) * 100); // percentage of all signatures
		// echo $user['lname'] . ", " . $user['fname'] . "<br>";
		// $perc . "% &nbsp;&nbsp; 
		// echo $user_id . ": " . implode(",", $pids) . "<br><br>";
		
	}
*/

/*

TABLES FOR MYSQL
	
CREATE TABLE `ibh_patients_to_providers` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ibh_patients_to_providers`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `ibh_patients_to_providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  


CREATE TABLE `ibh_tickler_reviews` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `init_cda` int(11) NOT NULL,
  `init_tp` int(11) DEFAULT NULL,
  `r1` int(11) DEFAULT '0',
  `r1_signed` tinyint(1) NOT NULL DEFAULT '0',
  `r2` int(11) DEFAULT '0',
  `r2_signed` tinyint(1) NOT NULL DEFAULT '0',
  `r3` int(11) DEFAULT '0',
  `r3_signed` tinyint(1) NOT NULL DEFAULT '0',
  `r4` int(11) DEFAULT '0',
  `r4_signed` tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `ibh_tickler_reviews`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `ibh_tickler_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


*/

$users = array();
	
	
	// This gets the user_ids on signatures and adds them to an associative array
	// Assess how many records to scan prior to doing this
	$sql = sqlStatement("SELECT DISTINCT f.pid, es.uid FROM forms f, esign_signatures es WHERE f.id = es.tid AND es.datetime > '2017-03-21 00:00:00' ORDER BY es.datetime");
	
	while ($res = sqlFetchArray($sql)){
		
		$uid = $res['uid'];
		$pid = $res['pid'];
		
		//echo $uid . "|" . $pid . "<br>";
		
		if (count($users['u-' . $uid] > 0)) {
			$users['u-' . $uid][] = $pid;
		} else {
			$users['u-' . $uid] = array($pid);
		}
		
	}
	
	
	
	foreach($users as $k => $pids) {
		$user_id = explode("-", $k)[1];

		foreach($pids as $pid) {
			 sqlStatement("INSERT INTO ibh_patients_to_providers (patient_id, provider_id) VALUES ('$pid', '$user_id')");
		}
	}
	
	// echo implode(", ", $tallies);
	echo "INITIAL PROVIDER PATIENTS SET UP! DELETE THIS FILE.";
	
	/*
	$pq = sqlStatement("SELECT p2p.patient_id, p.fname, p.lname, p.providerID FROM ibh_patients_to_providers p2p, patient_data p WHERE p2p.patient_id=p.pid AND p2p.provider_id=$active_user ORDER BY p.lname, p.fname");
	
	while ($patient = sqlFetchArray($pq)){
		echo $patient['lname'] . ", " . $patient['fname'] . "<br>";
	}
	
	*/
	
?>