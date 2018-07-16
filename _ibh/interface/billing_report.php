<?php 
	
// ini_set("display_errors", 1);
		
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

// IBH_DEV_CHG
require_once("../../_ibh/ibh_functions.php");




function ibh_get_insurance_expected() {
	$expected = array();
	$cres = sqlStatement("SELECT * FROM ibh_insurance_est WHERE ins_id=34 OR ins_id=79 OR ins_id=10493 ORDER BY ins_id, encounter_code");
	while ($crow = sqlFetchArray($cres)) {
		$expected[$crow['ins_id'] . "-" . $crow['encounter_code']] = $crow['amount'];
	}
	return $expected;
}

$ins_exp = ibh_get_insurance_expected();

function get_expected_amt($ins_id, $code, $billed = 0) {
	global $ins_exp;
	
	if (isset($ins_exp[$ins_id . "-" . $code])) {
		return $ins_exp[$ins_id . "-" . $code];
	} else {
		return 0;
	}
	
}


function ibh_facility_pulldown($sel = "", $name="facility_id") {
	
	$html = "<select name='" . $name . "'>";
	$html .= "<option value=''>All Facilities</option>";
	
	$facs = array(
		"Boise"=>3, // 3
		"Caldwell"=>4, // 4
		"Mountain Home"=>5, // 5
		"Peak"=>6, // 6	
		"NJC"=>7, // 7
		"Riverside"=>8, // 8
		"Nampa"=>9, // 9
	);
	
	foreach ($facs as $n => $v) {
		$sel_str = ($sel == $v) ? " selected": "";
		$html .= "<option value='" . $v . "' $sel_str>" . $n . "</option>";
	}
	
	$html .= "</select>";
	
	return $html;
	
}




	
	if (strlen($_GET['date_end']) > 5) {
		$date_end = $_GET['date_end'] . " 00:00:00";
	} else {
		$date_end = date("2017-06-30 00:00:00");
		
	}
	
	if (strlen($_GET['date_start']) > 5) {
		$date_start = $_GET['date_start'] . " 00:00:00";
	} else {
		$date_start = "2017-01-01 00:00:00";
		
	}
	
	$show_checked = "";
	$show_details = false;
	if (isset($_GET['show_details']) && $_GET['show_details']==1) {
		$show_checked = " checked";
		$show_details = true;
	}
	
	$mcbc_checked = "";
	$mcbc = false;
	if (isset($_GET['mcbc']) && $_GET['mcbc']==1) {
		$mcbc_checked = " checked";
		$mcbc = true;
	}
	
	
	$ins_names = array();
	
	$cres = sqlStatement("SELECT * FROM insurance_companies ORDER BY name");
	
	while ($crow = sqlFetchArray($cres)) {
		$ins_names["id-" . $crow['id']] = $crow['name'];
	}
	
	function getInsName($id) {
		global $ins_names;
		
		if (!$id) {
			return "NO PAYER ID";
		} else if ($id == "id-0") {
			return "PATIENT COPAY";
		} else if (isset($ins_names[$id])) {
			return $ins_names[$id];
		} else {
			return "ID:" . $id;
		}
	}
	
	
	

?><html><head>


<style type="text/css">
	
	body {
		font-family:Monaco, Courier;
		font-size:11px;
	}
	
	.divider td {
		height:8px;
	}
	.divider {
		
		border-top:8px solid #111;
	}
	
	.billing {
		background-color:#f3c5c5;
	}
	
	.ar {
		background-color:#cef0c2;
	}
	
	.ar-debug {
		background-color:#b6def3;
	}
	
	.np {
		background-color:#6b4e43;
	}
	
	.np td {
		text-align:right;
		padding-right:12px;
		color:white;
	}


	.tallies {
		font-size:18px;
	}
	
	.tallies .billed {
		color:#b74646;
	}
	
	.tallies .received {
		color:#34884d;
	}
	
	.tallies .missing {
		color:#fa6036;
	}
	
	
	.filter-note {
		
		color:#799099;
		font-size:18px;
		margin:12px;
	}
	
	
	.ar-session-table, .ins-table {
		border-collapse:collapse;
		width:750px;
		margin:14px auto;
		border:1px solid #333;
	}
	
	.ar-session-table td {
		font-size:13px;
	}
	
	
	.ins-table th {
		background-color:#333;
		color:white;
	}
	
	.ins-table td{
		border:1px dotted #ccc;
		padding:4px;
		background-color:#f0f0f0;
		font-family:Monaco, Courier, Arial;
		font-size:13px;
	}
	
	.ins-table td.numb {
		text-align:right;
		width:25%;
	}
  
  
</style>
</head>
<body>
	<?php 
		
	// 	echo print_r($ins_exp, true);
		
		
	?>
	<div class="filters">
		
		<form action="billing_report.php" method="get">
			
		start: <input type="text" style="width:150px" name="date_start" value="<?=substr($date_start, 0,10)?>">
		&nbsp; &nbsp; end: <input type="text" style="width:150px" name="date_end" value="<?=substr($date_end,0,10)?>">

		<?php
			echo ibh_facility_pulldown($_GET['facility'], "facility")
		?>	
		
		&nbsp;&nbsp;<input id="show_details" type="checkbox" name="show_details" value="1" <?=$show_checked?>><label for="show_details">show details</label>
		
		<!-- 
		&nbsp;&nbsp;<input id="mcbc" type="checkbox" name="mcbc" value="1" <?=$mcbc_checked?>><label for="mcbc">Medicaid & BCBC only</label>
		-->
		
		&nbsp; &nbsp;&nbsp;<input type="submit" value="go">
		</form>
      
      
	</div>
	<div class="era-report">
		<?php
			
			
			$billed = 0;
			$received = 0;

			$table = '<table class="ar-session-table">
			<tr><th>enc</th><th>fac</th><th>payer</th><th>code</th><th>amt</th></tr>';
		
	
	
	echo "<div class='filter-note'>" . substr($date_start,0,10) . " &mdash; " . substr($date_end, 0,10) . "</div>";
	
	
	
		
	$ins_totals = array();
	
	$ins_charges = array();
	$ins_charges["id-0"] = 0;
	
	$ins_tallies = array();
	$ins_tallies["id-0"] = 0;
	
	$encounter_ct = 0;
	
	$cod_totals = array();
	
	
	$copay_total = 0;
  
	$insurance_filter = ""; // $mcbc ? " AND (payer_id=34 OR payer_id=55)": "";

  
	$fac_q = "facility_id > 0";
	$fac = false;
	$fac_totals = array();
	$missing_payments = 0;
	
	if (isset($_GET['facility']) && $_GET['facility'] > 0) {
		echo "<div class='filter-note'>FACILITY: " . str_replace("Idaho Behavioral Health ", "", ibh_get_facility_name($_GET['facility'])) . "</div>";
		
		$fac_q = "facility_id=" . $_GET['facility'] . " ";
		$fac = true;
	}
  
  
  
  //  MAIN ENCOUNTER QUERY
  //
  //
	$q1 = sqlStatement("SELECT * FROM form_encounter WHERE " . $fac_q . " AND date > '$date_start' AND date < '$date_end' ORDER BY date DESC");
	
	while ($r = sqlFetchArray($q1)) {
		
		$html = "";
		$has_payments = false;
		$has_fee = false;
		$this_fee = 0;
	
		$enc = $r['encounter'];
		$facility = $r['facility_id'];
		
		$billing_sql = "SELECT fee, payer_id, code FROM billing WHERE encounter='$enc' AND billed=1 " . $insurance_filter . " ORDER BY id";
		// echo $billing_sql;
		
		$q2 = sqlStatement($billing_sql);
		
		// THE BILLS
		while ($bi = sqlFetchArray($q2)) {
						
			// NEW ENCOUNTER
			if (!$html) {
				$html .= "<tr><td class='divider' colspan=5></td></tr>";
				$has_payments = false;
			}
			
			if ($bi['fee'] > 0) {
				
				$has_fee = true;
				
				// $expected = get_expected_amt($ins_id, $bi['code'], $bi['fee']);
			
				// there's no payer available here??
				$html .= "<tr class='billing'><td class='enc'>" . $enc . "</td><td>" . str_replace("Idaho Behavioral Health ", "", ibh_get_facility_name($facility)) . "</td><td>&nbsp;</td><td>" . $bi['code'] . "</td><td class='num'>" . $bi['fee'] . "</td></tr>";
				
				
				$billed += $bi['fee'];
				$this_fee += $bi['fee'];
				
				
				if (isset($cod_totals[$bi['code']])) {
					$cod_totals[$bi['code']] += 1;
				} else {
					$cod_totals[$bi['code']] = 1;
				}
				
			
				
			}
		} // end first while for bills
		
		
		// sequence_no = 1, 2, 3
		
		// MONEY THAT'S COLLECTED
		//////////////////////////
		$q3 = sqlStatement("SELECT ar.code, ar.payer_type, ar.session_id, ar.pay_amount, ass.payer_id FROM ar_activity ar LEFT JOIN ar_session ass ON ar.session_id=ass.session_id WHERE ar.encounter='$enc' ORDER BY ar.post_time");

		$payment_happened = false;
		while ($ar = sqlFetchArray($q3)) {
			
			$code = $ar['code'];
			
			// gray
			if ($html) {
				if ($ar['pay_amount'] > 0) {
					$has_payments = true;
					
					
					if ($ar['payer_type'] == 1) {
						
						$session = $ar['session_id'];
				
						$payer_id = $ar['payer_id'];
						$payer_assoc = "id-" . $payer_id;
						$payer = getInsName($payer_assoc);
						
						$expected = get_expected_amt($payer_id, $ar['code'], 0);
						// echo " :EXP-" . $expected;
					} else {
						$payer_id = 0;
						$payer = "PATIENT/COPAY";
						$payer_assoc = "id-0";
						
						// $ins_charges["id-0"] += $ar['pay_amount'];
						$ins_tallies["id-0"] += 1;
						
						$expected = 0;
					}
					
						
					if ($payer_id == 0) {
						$copay_total += $ar['pay_amount'];
					}
					
					$expected_str = "";
					if ($expected) {
						$expected_str = " (" . $expected . ")";
					}
					
					$html .= "<tr class='ar'><td>&nbsp;</td><td>&nbsp;</td><td>" . $payer . ": " . $ar['code'] . "</td><td>&nbsp;</td><td>" . $ar['pay_amount'] . $expected_str . "</td></tr>";
					
					if (isset($ins_totals[$payer_assoc])){ 
						$ins_totals[$payer_assoc] += $ar['pay_amount'];
					} else {
						$ins_totals[$payer_assoc] = $ar['pay_amount'];
					}
			
					
					// this happens once for each PAYMENT
					if (!$payment_happened && $payer_id != 0) {
							// we're doing this once here because we've tallied
							// up the encounter fees into a single var
							if (isset($ins_charges[$payer_assoc])){ 
								$ins_charges[$payer_assoc] += (int) $this_fee;
								$ins_tallies[$payer_assoc] += 1;
								
							} else {
								
								$ins_charges[$payer_assoc] = (int) $this_fee;
								$ins_tallies[$payer_assoc] = 1;
								
							}
						
						$payment_happened = true;
					}
					
	
					
				
					
					if (!$fac) {
						
						if (isset($fac_totals[$facility])) {
							$fac_totals[$facility]+= $ar['pay_amount'];
						} else {
							$fac_totals[$facility] = $ar['pay_amount'];
						}
						
					}
	
					$received += $ar['pay_amount'];
					
					
				} // end if there's a payment
				
			} // end if html
			
				
		}
		
		// MISSING PAYMENT
		if ($html && $has_fee && !$has_payments) {
			$missing_payments += $this_fee;
			$html .= "<tr class='np'><td colspan='5'>NO PAYMENT: $" . $this_fee . "</td></tr>";
		}
		
		$table .= $html;
		
	}
	
	$table .= "</table>";
  
 	$dc_total = 0;
	
	
	arsort($ins_totals, 1);
	arsort($fac_totals, 1);
	arsort($cod_totals, 1);
	
	$cod_total = 0;
		foreach ($cod_totals as $key => $value) {
		$cod_total += $value;
	}
	
  
	
	foreach ($ins_totals as $key => $value) {
		$dc_total += $value;
	}
	
	$ins_string = "";
	
	foreach ($ins_totals as $key => $value) {
		
		// ALSO DISPLAY CHARGES
		$ins_charges_total = $ins_charges[$key];
		$tally = $ins_tallies[$key];
		
		$raw = $value / $dc_total * 100;
		$i_percent = number_format($raw, 2, '.', ',');
		$ins_string .= "<tr><td>". getInsName($key) . "</td><td class='numb'>" . $tally . "</td><td class='numb'>" . number_format($ins_charges_total, 2, '.', ',') . "</td><td class='numb'>" . number_format($value, 2, '.', ',') . "</td><td class='numb'>" . $i_percent . "</td></tr>";
	}
	
	$fac_string = "";
	foreach ($fac_totals as $key => $value) {
		
		$raw = $value / $dc_total * 100;
		$f_percent = number_format($raw, 2, '.', ',');
		$fac_string .= "<tr><td>" . ibh_get_facility_name($key) . "</td><td class='numb'>" . number_format($value, 2, '.', ',') . "</td><td class='numb'>" . $f_percent . "</td></tr>";
	}
	
  
  
  ?>
  <div class="tallies">
	 <?php
		 
  echo "<div class='billed'>TOTAL ENCOUNTERS: " . number_format($cod_total, 0, '', ',') . "</div>";
  echo "<div class='billed'>BILLED: $" . number_format($billed, 2, '.', ',') . "</div>";
  echo "<div class='received'>RECEIVED: $" . number_format($received, 2, '.', ',') . "</div>";
  
  
  echo "<div class='received'>COPAYS: $" . number_format($copay_total, 2, '.', ',') . "</div>";
  
  
  echo "<div class='missing'>UNPAID: $" . number_format($missing_payments, 2, '.', ',') . "</div>";


  echo "<table class='ins-table'>";
  echo "<tr><th>insurance co</th><th>appts</th><th>charged</th><th>received</th><th>%</th>";
  echo $ins_string;
  echo "</table>";
  
  
  if (!$fac) {
	  echo "<table class='ins-table'>";
	  echo "<tr><th>facility</th><th>received</th><th>%</th>";
	  echo $fac_string;
	  echo "</table>"; 
  }
  
 
   echo "<table class='ins-table'>";
   echo "<tr><th>code</th><th>total</th><th>%</th>";
   foreach ($cod_totals as $key => $value) {
	   $cod_raw = $value / $cod_total * 100;
	   $cod_percent = number_format($cod_raw, 2, '.', ',');
	   echo "<tr><td>" . $key . "</td><td class='numb'>" . $value . "</td><td class='numb'>" . $cod_percent . "</td></tr>";
   }
   echo "</table>";
  
  
  // echo "<br>double-check: $" . floor($dc_total);
  ?>
  </div>
  
  <?php
  if (isset($_GET['show_details']) && $_GET['show_details']==1) echo $table;
  
?>
		


	
</div>

</body>
</html>