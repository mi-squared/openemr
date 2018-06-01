<?php
	
// ini_set("display_errors", true);
	
	
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

// IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");

// ini_set("display_errors", 1);


?><html>
<title></title>
<head>
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/js/jquery_latest.min.js"></script>

<link rel="stylesheet" href="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/interface/themes/style_metal.css" type="text/css">
<link rel="stylesheet" href="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/css/encounter.css" type="text/css">

<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/js/jquery.tablesort.js"></script>

<style type="text/css">
		.odd {
			background-color:#f0f0f0;
			
		}
		.even {
			background-color:#fff;
		}
		.era-info ul {
			list-style:none;
			font-size:14px;
		}
		.era-info label {
			width:100px;
			float:left;
			display:block;
			color:#777;
		}
		
		.bills {
			background-color:#ccc;
			font-weight:bold;
		}
		
		.bills td {
			
		}
		
		.enc-row td {
			background-color:#222;
			color:white;
			font-weight:bold;
			border-top:12px solid #fff;
		}
		
		
		.enc-row td.dk-header {
			color:#666;
		}
		
		.bill-row td {
			background-color:#c6e1f4;
		}
		
		.ar-row td {
			background-color:#fff;
		}
		
		.ar-row td.fade {
			color:#8eb8d6;
		}
		
		.add-bill {
			float:right;
			color:#eb1b33;
			cursor:pointer;
		}
		
		.add-bill:hover {
			color:orange;
		}
		
		.patient-name {
			position:fixed;
			background-color:#81009d;
			color:white;
			padding:14px;
			font-size:14px;
		}
</style>
</head>
<body class="overview-pane">
<div class="ibh-wrapper">
	<div class='nav'>
	
	<a href="parse_x12.php">x12 inspector</a>
	<a href="era_overview.php">era overview</a>
	</div>
<?php
	
	// get insurance names
	
	if (isset($_GET['pid'])) {
		
		$pid = $_GET['pid'];
		$p = ibh_get_patient($pid);
		
		echo "<h2 class='patient-name'>" . $p['fname'] . " " . $p['lname'] . "</h2>";
		
		$table = "<table class='list-table'><thead><tr><th>session</th><th>date</th><th>seq</th><th>code</th><th>fee</th><th>payer</th><th>paid</th><th>adj</th><th>account_code</th><th>memo</th></thead>";
		
		// GET ALL ENCOUNTERS
		$encq = "SELECT fe.*, ev.pc_title, ev.pc_startTime, ev.pc_eventDate, ev.pc_apptstatus FROM form_encounter fe, openemr_postcalendar_events ev WHERE ev.encounter = fe.encounter AND fe.pid ='$pid' ORDER BY ev.pc_eventDate DESC, fe.encounter DESC";
		
		$encq_stmt = sqlStatement($encq);
		
		
		while($e = sqlFetchArray($encq_stmt)){ 
			
			$enc = $e['encounter'];
			
		    $ct = 0;
		    $row_ct = 0;
		    $rows = 0;
		    $allocated_total = 0;
		    $class="even";
		    
		    
		    $table .= "<tr class='enc-row'>";
				
				$table .= "<td><a name='ENC_" . $enc . "'></a><strong>" . $enc . "</strong></td>";
				$table .= "<td>" . $e['pc_eventDate'] . "</td>";
				$table .= "<td></td>";
				$table .= "<td></td>";
			    $table .= "<td class='dk-header'>$</td>";
			    $table .= "<td class='dk-header'>payer</td>";
			    $table .= "<td class='dk-header'>paid</td>";
			    $table .= "<td class='dk-header'>adj</td>";
			    $table .= "<td></td>";
			    $table .= "<td>" . $e['pc_apptstatus'] . "</td></tr>";

			 
			// BILLING
		    
		    $bill_sql = "SELECT * FROM billing WHERE encounter='$enc' ORDER BY id";
			
		    $bill_q = sqlStatement($bill_sql);
		    
		    while($b = sqlFetchArray($bill_q)){ 
			    
			    $billing_code = $b['code'];
			    $bmod = $b['modifier'];
			    $bmod_str = $bmod ? ":" . $bmod: "";
			    
			    
			    if ($b['fee'] > 0) {
			        $table .= "<tr class='bill-row' data-encounter='$enc' data-code='$billing_code' data-modifier='$bmod'>";
				
				$table .= "<td></td>";
				$table .= "<td>" . $b['code_type'] . "</td>";
				$table .= "<td>" . $b['code'] . $bmod_str . "</td>";
				$table .= "<td>" . $b['code_text'] . "</td>";
			    $table .= "<td>" . $b['fee'] . "</td>";
			    $table .= "<td colspan=3></td>";
			    //$table .= "<td></td>";
			    //$table .= "<td></td>";
			    $table .= "<td colspan=2>" . $b['process_file'] . "<span class='add-bill'>+ item</span></td>";
			    $table .= "</tr>";
			    }
			    
			
			 
			$sql = "SELECT ar.*, fe.date as fe_date FROM ar_activity ar, form_encounter fe WHERE ar.code='$billing_code' AND ar.pid='$pid' AND ar.encounter=fe.encounter AND ar.encounter='$enc' ORDER BY encounter DESC, sequence_no";
			
		    $arq = sqlStatement($sql);
		    
		    while($a = sqlFetchArray($arq)){ 
			   
			    $enc = $a['encounter'];
			    $session = $a['session_id'];
			 	
			    $rows ++;
			    			    
			    $adj = $a['adj_amount'] == "0.00"? "": $a['adj_amount'];
			    
			    $pay = $a['pay_amount'] == "0.00"? "": $a['pay_amount'];
			    
			    $bills = ibh_get_billing_numbers($a['encounter'], $a['code']);
			    
			    $allocated_total += $pay;
			    
			    $row_ct++;
			    
			    $table .= "<tr class='ar-row'>";
				
				$table .= "<td data-sort-value='" . $row_ct . "'><a href='era_overview.php?session=" . $a['session_id'] . "'>" . $a['session_id'] . "</a></td>";
				$table .= "<td>" . substr($a['post_time'],0,10) . "</td>";
			    $table .= "<td>" . $a['sequence_no'] . "</td>";
			    $table .= "<td>" . $a['code'] . "</td>";
			    $table .= "<td class='fade'>" . $bills['fee'] . "</td>";
			    $table .= "<td>" . $a['payer_type'] . "</td>";
			    $table .= "<td>" . $pay . "</td>";
			    $table .= "<td>" . $adj . "</td>";
			    $table .= "<td>" . $a['account_code'] . "</td>";
			    $table .= "<td>" . $a['memo'] . "</td></tr>";
				
				
			} // end while for ar records
			
			} // end billing
		
		} // end while for encounters
		
		
			$table .= "</table>";
			
			
			echo $table;
			
			echo "END";
			
				
		
	} else {
		echo "NO PATIENT ID SELECTED";
	}
	
	
?>
<div id="posting_block">
	<div class="pb-cancel-button">cancel</div>
	<form action="patient_account">
		<input type="hidden" name="pid" value="<?=$pid?>">
<table class="list-table">
	<tr><th>session</th><th>code type</th><th>code</th><th>mod</th><th>payer type</th><th>pay amt</th><th>adj amt</th><th>memo</th><th>account code</th></tr>
	
	<tr>
		<td><input id="session_id" name="session_id" type="text"></td>
		<td><input id="code_type" type="text" name="code_type"></td>
		<td><input id="code" type="text" name="code"></td>
		<td><input id="modifier" type="text" name="modifier"></td>
		<td><select id="payer_type"><option value='1'>Ins 1</option><option value='2'>Ins 2</option><option value='0'>patient</option></select></td>
		<td><input id="pay_amount" type="text" name="pay_amount"></td>
		<td><input id="adj_amount" type="text" name="adj_amount"></td>
		<td><input id="memo" type="text" name="memo"></td>
		<td><input id="account_code" type="text" name="account_code"></td>
	</tr>
	
	<tr>
		<td>payment type</td>
		<td>
			<select name="payment_type">
			<option value="">choose</option>
			<option value="patient_payment"> patient</option>
			<option value="insurance_payment">insurance</option>
			</select>
		</td>
		
		<td>payment method</td>
		<td>
			<select name="payment_method">
			<option value="">choose...</option>
			<option value="cash">cash</option>
			<option value="check_payment">check</option>
			<option value="credit_card">credit card</option>
			<option value="electronic">electronic</option>
			</select>
		</td>
		<td>description:</td>
		<td><input type="text" name="description">
		
		<td colspan=3></td>
	</tr>	
			
	<tr><td colspan='9' class='sub'><input class="submit-post" type="submit" value="submit payment/adjustment"></td></tr>
	
</table>
	</form>
</div>


<script type="text/javascript">
	$(function(){
		
		
		$(".add-bill").on("click", function() {
			var $bt = $(this);
			var $row = $bt.closest(".bill-row");
			var enc = $row.data("encounter");
			var cod = $row.data("code");
			var mod = $row.data("modifier");
			
			// alert(enc + ":" + cod + ":" + mod);
			$(".add-record-row").remove();
			
			$new_row = $row.after("<tr class='add-record-row'><td colspan=10>foo</td></tr>");
			
			var form_table = $("#posting_block").html();
			
			$(".add-record-row").find("td").html(form_table);
		});
		
		
	});
</script>


</div>

</body>
</html>