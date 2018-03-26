<?php 
	
// ini_set("display_errors", 1);
		
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

// IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_era.php");





?><html><head>


<style type="text/css">
	
	body {
		font-family:Arial;
		font-size:11px;
	}
	
	.ar-session-row td {
		border-top:44px solid #ccc;
	}
	
	.ar-session-table {
		width:95%;
		border-collapse:collapse;
	}
	
	.ar-session-table td {
		padding:4px;
	}
	
	.ar-session-table th {
		text-align:left;
		background-color:#333;
		color:white;
		padding:4px;
	}
	
	.ar-activity {
		background-color:#f0f7bd;
	}


  
  
</style>
</head>
<body>
	<div class="era-report">
		<table class="ar-session-table">
			<tr><th>session</th><th>payer</th><th>pay total</th><th>type</th><th>created on</th></tr>
<?php
  
	$q1 = sqlStatement("SELECT * FROM ar_session ORDER BY session_id DESC LIMIT 100");
	
	while ($r = sqlFetchArray($q1)) {
		$sess = $r['session_id'];
		echo "<tr class='ar-session-row'><td>SESSON " . $sess . "</td><td>" . $r['payer_id'] . "</td><td>" . $r['pay_total'] . "</td><td>" . $r['payment_type'] . "</td><td>" . $r['created_time'] . " (" . ibh_get_user_names_by_ids($r['user_id'])[0] . ")</td></tr>";
		
		$q2 = sqlStatement("SELECT * FROM ar_activity WHERE session_id='$sess' ORDER BY post_time");
		
		while ($ar = sqlFetchArray($q2)) {
			
			$patient = ibh_get_patient($ar['pid']);
			$enc = $ar['encounter'];
			
			echo "<tr class='ar-activity'><td>&nbsp;</td><td style='padding-left:64px'>" . $patient['lname'] . ", " . $patient['fname'] . "</td><td><span style='color:blue'>" . $ar['encounter'] . "</span>: " . $ar['code'] . "</td><td>" . $ar['pay_amount'] . "</td><td>" . $ar['adj_amount'] . "</td></tr>";


		}
		
		
		
	}
	
  
  
  
?>
		</table>


	
</div>

</body>
</html>