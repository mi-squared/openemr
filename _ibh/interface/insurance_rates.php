<?php
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

// IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");


$set_ins_co = "";
if (isset($_GET['insurance_company'])) {
	$set_ins_co = $_GET['insurance_company'];
}	
	
?><html>
<title></title>
<head>
<script type="text/javascript" src="<?=  $GLOBALS['webroot'] ?>/_ibh/js/jquery_latest.min.js"></script>

<link rel="stylesheet" href="<?=  $GLOBALS['webroot'] ?>/interface/themes/style_metal.css" type="text/css">
<link rel="stylesheet" href="<?=  $GLOBALS['webroot'] ?>/_ibh/css/encounter.css" type="text/css">
<style type="text/css">
		.code-edit-line {
			margin:4px 0;
			border-bottom:1px solid #777;
			padding:4px;
			width:400px;
			
		}
		
		.code-edit-line .code {
			margin-right:12px;
		}
</style>
</head>
<body class="overview-pane">
<div class="ibh-wrapper">
	
<h4>Set Insurance "Allowed" Rates</h4>

<div class="ibh-wrapper">
	

<div>
	<form id="select_ins_form" action="" method="GET">
	choose insurance company: <?=ibh_get_insurance_company_pulldown($set_ins_co, "<option value=''>SELECT</option><option value='1'>IBH charges</option>")?>
	<!-- <input type="submit" name="get_ins" value="go"> -->
	</form>
</div>

<div id="layout">
<?php 
	if (isset($_POST['submit_ins_rates'])) {
			
			$ins_id = $_POST['ins_co'];
			
			foreach($_POST as $code => $amt) {
				
				if (substr($code, 0,5) == "code_") {
					
					$code = str_replace("code_", "", $code);
					
					$inses = sqlStatement("SELECT * FROM ibh_insurance_est WHERE ins_id=? AND encounter_code=?", array($ins_id,$code));
					if (sqlNumRows($inses) > 0) {
						// if we have a record
						sqlStatement("UPDATE ibh_insurance_est SET amount=? WHERE ins_id=? AND encounter_code=?", array($amt, $ins_id,$code));
					} else {
						// create a new record
						sqlStatement("INSERT INTO ibh_insurance_est (ins_id, encounter_code, amount, type) VALUES (?, ?, ?, 'ins')", array($ins_id,$code, $amt));
					}
			
				} // end if it's a code
				
			}
			
			echo "<div>UPDATED</div>";
		}
		
		
	$cres = sqlStatement("SELECT * FROM openemr_postcalendar_categories WHERE pc_catname LIKE '%:%' ORDER BY pc_catname");	
	
	$codes = array();
	
	while ($crow = sqlFetchArray($cres)) {
		$code = trim(explode(":", $crow['pc_catname'])[1]);
		$codes[] = $code;
	}
	
	asort($codes);
	
	$codes[] = "90785";
	
	
	
	if (isset($_GET['insurance_company'])) {
		
		$code_values = array();
		
		$ins_co = $_GET['insurance_company'];
		
		
		$inses = sqlStatement("SELECT * FROM ibh_insurance_est WHERE ins_id=? ORDER BY encounter_code", array($ins_co));
		
		while ($ins = sqlFetchArray($inses)) {
			// echo $ins['encounter_code'] . ":" . $ins['amount'] . "<br>";
			$code_values[$ins['encounter_code']] = $ins['amount'];
		}
	?>
	<form action="" method="POST">
		<input type="hidden" name="ins_co" value="<?=$ins_co?>">
	<?php
		
		
		foreach ($codes as $code) {
			
			echo "<div class='code-edit-line'><span class='code'>" . $code . "</span><input type='text' name='code_" . $code . "' value='" . $code_values[$code] . "'></div>";
			
		}
		
		?>
		<input type="submit" name="submit_ins_rates" value="go"></form>
		
		<?php } ?>

	

</div>



</div>
<script type="text/javascript">
	$(function(){
		
		$("#insurance_company").on("change", function() {
			$("#select_ins_form").submit();
			
		});
		
	});
</script>
</body>
</html>