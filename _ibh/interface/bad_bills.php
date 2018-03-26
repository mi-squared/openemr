<?php 
	
// ini_set("display_errors", 1);
		
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

// IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");




?><html><head>


<style type="text/css">
	
	body {
		font-family:Monaco, Courier;
		font-size:11px;
		background:#ccc;
	}
	
	table {
		width:50%;
		margin:8px auto;
		border:1px solid #999;
		border-collapse:collapse;
	}
	
	td {
		font-size:11px;
		border:1px dotted #ccc;
		padding:4px;
	}
	
	table tr.main-row td {
		border-top:2px solid black !important;
		background-color:#fff;
	}
	</style>
</head>
<body>
	<p style="width:400px">White rows show billing records where the patient ID is listed as different than the patient ID in the encounter. Rows immediately below are billing records existing for the PROPER patient (for that encounter), some are marked as billed -- in green, others not -- in pink.</p>
	<table>
		<tr><th>date</th><th>enc</th><th>encounter patient</th><th>bill patient</th><th>code</th><th>user</th><th>fee</th><th>processed</th></tr>
		
<?php 
					
		
	// 	echo print_r($ins_exp, true);
		
		$ct = 0;
		
		$q3 = sqlStatement("SELECT fe.date as enc_date, b.code, b.fee, b.process_date, b.user, b.encounter as b_encounter, b.pid as b_pid, fe.pid as fe_pid, fe.reason FROM billing b INNER JOIN form_encounter fe ON b.encounter=fe.encounter WHERE b.code_type='CPT4' AND b.bill_date > '2017-07-01 00:00:00' ORDER BY b.bill_date DESC");

		while ($b = sqlFetchArray($q3)) {
			
			$encounter = $b['b_encounter'];
			$billing_pid = $b['b_pid'];
			$billing_patient = ibh_get_patient($billing_pid);
			$enc_pid = $b['fe_pid'];
			$encounter_patient = ibh_get_patient($enc_pid);
			// $user = ibh_get_user_by_id($b['user']);
			$user = $b['user'];
			
			$reason = $b['reason'];
			$code = $b['code'];
			$date = $b['enc_date'];

			if ($enc_pid != $billing_pid) {
				$ct++;
				echo "<tr class='main-row'><td>" . substr($date,0,10) . "</td><td>" . $encounter . "</td><td><span style='color:blue'>" . $encounter_patient['fname'] . " " . $encounter_patient['lname'] . "</span></td><td>" . $billing_patient['lname'] . "</td><td>" . $code . " </td><td>" . $user . "</td><td>" . $b['fee'] . "</td><td>" . $b['process_date'] . "</td><tr>";
			
			
			/// GET GOOD BILLING RECORDS
			
			
			$q4 = sqlStatement("SELECT * FROM billing WHERE code_type='CPT4' AND encounter='$encounter' AND pid='$enc_pid'");
			
			while ($g = sqlFetchArray($q4)) {
				
				if ($g['billed'] == 1) {
					$bg = '#cfe5bd';
					$billed = "yes";
				} else {
					$billed = "no";
					$bg = "pink";
				}
				
				
				echo "<tr style='background-color:" . $bg . "'><td>" . $g['date'] . "</td><td>" . $encounter . "</td><td><span style='color:blue'>" . $encounter_patient['fname'] . " " . $encounter_patient['lname'] . "</span></td><td>billed: " . $billed . "</td><td>" . $g['code'] . " </td><td>" . $g['user'] . "</td><td>" . $g['fee'] . "</td><td>" . $g['process_date'] . "</td><tr>";
			}
			
			
			}
			
			/////
						
			
		}
			
		
		
		// $q2 = sqlStatement("SELECT * FROM form_encounter WHERE encounter='$encounter' LIMIT 1");
		


?>
		
	</table>

	<?php echo "COUNT: " . $ct; ?>
</div>

</body>
</html>