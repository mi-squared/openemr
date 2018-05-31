<?php
	
	
	ini_set("display_errors", true);
	
	
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

	// IBH_DEV_CHG
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");

	$ins_cache = false;
	$ins_co = 0;	
	$search = false;
	$start_date = "";
	$end_date = "";
	
	function ibh_get_insurance_co($id) {
		$fid = sqlStatement("SELECT * FROM insurance_companies WHERE id='$id'");
		return sqlFetchArray($fid);

	}
	
	function getCodePrice($c){
				  
					$sql = "SELECT a.id, b.pr_id, b.pr_price FROM codes AS a, prices AS b WHERE a.code LIKE '$c' AND b.pr_id = a.id";
					$p = sqlStatement($sql);
					$pres = sqlFetchArray($p);
					
					return $pres['pr_price'];
				
				}
				
				
	function getAllowedPrice($c, $ins) {
					$sql = "SELECT * FROM ibh_insurance_est i WHERE i.encounter_code='$c' AND i.ins_id='$ins'";
					$p = sqlStatement($sql);
					$pres = sqlFetchArray($p);
					
					return $pres['amount'];
				
	}
				
	

	function ibh_get_ins_cache() {
		$cres = sqlStatement("SELECT * FROM insurance_companies ORDER BY name");
	
		$arr = array();
		while ($crow = sqlFetchArray($cres)) {
			$arr["i" . $crow['id']] = $crow['name'];
		}
	
		return $arr;
		
	}
	
	
	
		
		// UPDATE `ar_activity` SET modified_time="2001-09-11 08:46:00" WHERE modified_time<"1999-01-01 00:00:00"
		// UPDATE `billing` SET date="2001-09-11 08:46:00" WHERE date<"1999-01-01 00:00:00"
		// UPDATE `billing` SET bill_date="2001-09-11 08:46:00" WHERE bill_date<"1999-01-01 00:00:00"
		
	
		
	if (isset($_GET['insurance_company'])) {
		
		$search = true;
		
		$ins_cache = ibh_get_ins_cache();
				
		$ins_co = $_GET['insurance_company'];
		
		
		
		
		if ($_GET['start_date'] && isset($_GET['start_date'])) {
			$start_date = $_GET['start_date'] . " 00:00:00";
		}
		
		if ($_GET['end_date'] && isset($_GET['end_date'])) {
			$end_date = $_GET['end_date'] . " 00:00:00";
		}
		
		if ($end_date && $start_date) {
			$date_string = " AND b.date >= '" . $start_date . "' AND b.date <= '" . $end_date . "'";
		} else {
			$date_string = " AND b.date > '2016-04-01 00:00:00'";
		}


	}
	
	
	?><html>
	
<head>
	<link rel="stylesheet" type="text/css" href="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/dynarch_calendar.css">
	<script type="text/javascript" src="/openemr/library/dynarch_calendar.js"></script>
	
	<script type="text/javascript" src="/openemr/library/dynarch_calendar_setup.js"></script>
<script src="/openemr/_ibh/js/jquery_latest.min.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>

<style type="text/css">
		.billing-search-table {
			width:85%;
			margin:8px auto;
			border-collapse:collapse;
			border:1px solid #777;
		}
		
		.billing-search-table td {
			padding:4px;
			font-size:12px;
			font-family:Arial;
			border:1px solid #ccc;
		}
		
		.billing-search-table th {
			padding:4px;
			background-color:#ccc;
			font-family:Arial;
			text-align:left;
			color:#333;
			font-size:12px;
		}
</style>
</head>
<body>
<?php


		
	
	// get bills by date range
	
	// FRONT END
	// get insurance companies for pulldown
	// filter by ins company
	// filter by date
	// show what's coming up, including drawing off of expected payments from expected tables
	?>
	
	<form action="" method="GET">
		<?=ibh_get_insurance_company_pulldown($ins_co)?>
		
		
		<br>
		<input type='text' size='10' name='start_date' id='start_date' value='<?=substr($start_date,0,10)?>'/> <img src='/openemr/interface/pic/show_calendar.gif' width='24' height='22' id='img_start_date'>
		<br>
		
		<input type='text' size='10' name='end_date' id='end_date' value='<?=substr($end_date,0,10)?>'/> <img src='/openemr/interface/pic/show_calendar.gif' width='24' height='22' id='img_end_date'>
		
		
		
		
		<br>
		<input type="submit" value="go">
	</form>
	<div class="results">
		<table class="billing-search-table">
			<tr><th>bill date</th><th>insurance</th><th>enc</th><th>type</th><th>code</th><th>type</th><th>billed</th><th>allowed</th><th>pay amt</th><th>adj amt</th></tr>
			
	<?php	
		
		if ($search) {
		
		
	
		$query = "SELECT b.*, a.payer_type, a.pay_amount, a.adj_amount FROM billing b, ar_activity a WHERE b.encounter=a.encounter AND a.code_type=b.code_type " . $date_string . " AND b.payer_id=?";
		
		// echo "<tr><td colspan='5'>" . $query . "</td></tr>";
		
		
		$cres = sqlStatement($query, array($ins_co));
			
		while ($p = sqlFetchArray($cres)) {
			
			
			$ins = $ins_cache["i" . $p['payer_id']];
			
			$billed = getCodePrice($p['code']);
			
			$allowed = getAllowedPrice($p['code'], $p['payer_id']);
			
			//echo $ins . "..." . $p['code_type'] . ":" . $p['code_text'] . "<br>";
			echo "<tr><td>" . $p['date'] . "</td>" 
			. "<td>" . $ins . "</td>" 
			. "<td>" . $p['encounter'] . "</td>" 
			. "<td>" . $p['code_type'] . "</td>" 
			. "<td>" . $p['code'] . "</td>" 
			. "<td>" . $p['code_text'] . "</td>"
			. "<td>" . $billed . "</td>"
			. "<td>" . $allowed . "</td>"
			. "<td>" . $p['pay_amount'] . "</td>"
			. "<td>" . $p['adj_amount'] . "</td>"
			. "</tr>";
		}
	
	}	
	
	
	
	
 ?></table>
	</div>
	
	<script language='JavaScript'>
 Calendar.setup({inputField:"start_date", ifFormat:"%Y-%m-%d", button:"img_start_date"});
 Calendar.setup({inputField:"end_date", ifFormat:"%Y-%m-%d", button:"img_end_date"});
</script>


</body>
</html>
