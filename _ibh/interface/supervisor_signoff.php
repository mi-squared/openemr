<html>
	<head>
		<link rel="stylesheet" href="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/interface/themes/style_metal.css" type="text/css">
		
		<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/js/jquery-1.7.2.min.js"></script>


		<script src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/ESign/js/jquery.esign.js"></script>
		<link rel="stylesheet" type="text/css" href="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/ESign/css/esign.css" />


		
		<style>
			.signoff-wrapper {
				
			}
			
			.signoff-form table {
				width:80%;
				min-width:800px;
				margin:16px auto;
				border-collapse:collapse;
				border:2px solid #555;
				
			}
			
			.signoff-form table td {
				border-left:1px dashed #ccc;
				border-bottom:1px solid #ccc;
				border-right:1px dashed #ccc;
				padding:6px;
				font-family:Arial, Helvetica, sans-serif;
				font-size:14px;
				vertical-align:top;
				line-height:140%;
				
			}
			
			.signoff-form tr th {
				border-bottom:1px solid #333;
				text-align:left;
				background-color:#666;
			}
			
			
			.signoff-form table tr.form-meta td {
				background-color:#f0f0f0;
			}
			
			
			.signoff-form table tr td:first-child {
				text-align:right;	
				width:200px;
				color:#666;
			}
			
			.supervisor-signoff {
				width:80%;
				margin:16px auto;
			}
			
			.supervisor-signoff-section {
				float:left;
				width:48%;
				text-align:center;
				
			}
			.supervisor-signoff-section.signoff-comments {
				border:1px solid red; 
			}
			
			.supervisor-signoff-section.signoff-sign {
				border:1px solid green; 
			}
			
			.signoff-form table td h3 {
				margin:2px 0;
				font-size:11px;
				background-color:#f1dce7;
				padding:3px;
				width:25%;
			}
			
			
		</style>
		
	</head>
	<body>
		
		<?php
	
require_once("../../interface/globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/calendar.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/amc.php");

function strip_number($str) {
	if (is_numeric(substr($str,0,1))) {
		return substr($str,1);
	} else {
		return $str;
	}
	
}

	

$form_id = $_GET['form_id'];
$encounter = $_GET['encounter'];

$data = array($encounter, $form_id);

$sql = "select e.reason, e.date as e_date, pd.pid, pd.fname, pd.lname, f.* from forms f, form_encounter e, patient_data pd WHERE f.encounter=e.encounter AND pd.pid=e.pid AND f.encounter=? AND f.form_id = ?";

$res = sqlStatement($sql,$data);

$main = sqlFetchArray($res);

$pid = $main['pid'];
$formdir = $main['formdir'];
// GET PROVIDER TOO!


// echo print_r($main, true);
	
	
if (isset($_POST['submit_comments'])) {
	
	// generate and send message
	
	echo "<div>Your comments have been sent to the provider</div>";;
}
	
?>


<div class="signoff-wrapper">
	<div class="signoff-form">
	<table>
		<tr class="form-meta"><td>patient id</td><td><?=$pid?></td></tr>
		<tr class="form-meta"><td>encounter id</td><td><?=$encounter?></td></tr>
		<tr class="form-meta"><td>form id</td><td><?=$form_id?></td></tr>
		<tr class="form-meta"><td>form dir</td><td><?=$formdir?></td></tr>
		<tr class="form-meta"><td>date</td><td><?=$main['e_date']?></td></tr>
		<tr class="form-meta"><td>patient</td><td><?=$main['fname']?> <?=$main['lname']?></td></tr>
		<tr class="form-meta"><td>form name</td><td><?=$main['form_name']?></td></tr>
		<tr class="form-meta"><td>reason</td><td><?=$main['reason']?></td></tr>
		
		<tr><th colspan='2'>&nbsp;</th></tr>
<?php		
	
	$fd_sql = "SELECT * FROM lbf_data lbf, layout_options lo WHERE lo.form_id='$formdir' AND lo.field_id=lbf.field_id AND lbf.form_id='$form_id' ORDER BY lo.group_name";
	$fd = sqlStatement($fd_sql);
	$last_group = "";
	
	while($f = sqlFetchArray($fd)) {
		
		$field_val = $f['field_value'];
		
		$la = sqlStatement("SELECT title FROM list_options WHERE option_id='$field_val'");
		$label = sqlFetchArray($la);
		if ($label['title']) {
			$field_val = $label['title'];
		}
		
		$title = "";
		if ($f['title']) {
			$title = "<h3>" . $f['title'] . "</h3>";
		}
		
		$group = strip_number($f['group_name']);
		
		if ($last_group == $group) {
			$group_label = "";
		} else {
			$group_label = $group;
		}
		
		$last_group = $group;
		
		
		
		
		echo "<tr><td>" . $group_label . "</td><td>" . $title . $field_val . "</td></tr>";
		
	}
	
?>
	</table>
	
	<div class="supervisor-signoff">
		
		<div class="signoff-comments supervisor-signoff-section">
			<p>If such and such, add comments here and send back to the provider.</p>
			<form action="supervisor_signoff.php?form_id=<?=$form_id?>&encounter=<?=$encounter?>" method="post">
			<textarea name="supervisor_comments"></textarea>
			<input type="submit" name="submit_comments" value="Send to provider">
			</form>
		</div>
		
		<div class="signoff-sign supervisor-signoff-section">
			
			<a target="RBot" href="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/interface/patient_file/encounter/encounter_top.php?pid=<?=$pid?>&set_encounter=<?=$encounter?>">Sign off on this encounter...</a> (will open in bottom pane)



		</div>
	</div>
</div>

