<?php
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

// IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");


$encounter_id = "";
if (isset($_GET['encounter_id'])) {
	$encounter_id = $_GET['encounter_id'];
}	
	
?><html>
<title></title>
<head>
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/js/jquery_latest.min.js"></script>

<link rel="stylesheet" href="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/interface/themes/style_metal.css" type="text/css">
<link rel="stylesheet" href="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/css/encounter.css" type="text/css">

<style>
		.warning {
			font-size:24px;
			color:orange;
			text-align:center;
			border:4px dotted yellow;
			background-color:white;
			padding:16px;
		}
		
		.warning a {
			color:red;
		}
		
		.recents div {
			clear:both;
		}
			
		.recents i {
			display:block;
			float:left;
			width:30%;
		}
</style>
</head>
<body class="overview-pane">
<div class="ibh-wrapper">
	
<h4>Encounter Inspector</h4>

<div class="ibh-wrapper">
	
	<div>
	<form action="encounter_inspector.php" method="get">
		search encounter by id: <input type="text" name="encounter_id" value='<?=$encounter_id?>'><input type="submit" name="sub" value="go">
	</form>
	</div>
	
	
	
	<?php
		
		
		if ($_GET['delete'] == "confirmed") {
			echo "<div class='warning'>THAT ENCOUNTER IS GONE NOW.</div>";
			$encounter_id = false;
			
			
			// DELETE IT!
			
			
		}
		
			
			
		if ($encounter_id) {
						
			if ($_GET['delete'] == "start") {
				echo "<div class='warning'>ARE YOU SURE YOU WANT TO DELETE THIS ENCOUNTER??<br><br><a href='encounter_inspector.php?encounter_id=" . $encounter_id . "&delete=confirmed'>YES</a></div>";
				
			}
			
			$enc = sqlStatement("SELECT * FROM form_encounter fe, forms f WHERE fe.encounter='$encounter_id' AND fe.encounter=f.encounter AND form_name != 'New Patient Encounter'");
			$e = sqlFetchArray($enc);
			
			$appt = ibh_get_appointment_info($encounter_id);
			
			$patient = ibh_get_patient($e['pid']);
			
			$pid = $e['pid'];
			
			$form_id = $e['form_id'];
			
			$bill = false;
			$bs = "SELECT * FROM billing WHERE encounter='$encounter_id'";
			$b = sqlStatement($bs);
			// $bill = sqlFetchArray($b);
		
			$allow_delete = true;
			
			$note = false;
			$fs = "SELECT * FROM lbf_data WHERE form_id='$form_id'";
			$note_data = sqlStatement($fs);
			// $bill = sqlFetchArray($b);
			
			
			?>
			<table class='billing'>
				<tr><th colspan='2'>encounter</th></tr>
				<tr><td>patient</td><td><?=$patient['lname']?>, <?=$patient['fname']?></td></tr>
				<tr><td>other recent encounters</td><td class='recents'>
					
				<?php
					$stmt = sqlStatement("SELECT fe.encounter, e.pc_title, e.pc_eventDate FROM form_encounter fe, openemr_postcalendar_events e WHERE fe.pid='$pid' AND fe.encounter=e.encounter AND fe.encounter !='$encounter_id' ORDER BY e.pc_eventDate DESC LIMIT 10");
					while ($arr = sqlFetchArray($stmt)) {
						echo "<div><i>enc-" . $arr['encounter'] . "</i><i>" . $arr['pc_eventDate'] . "</i><i>" . $arr['pc_title'] . "</i></div>";
					}
					
				?>
				</td></tr>
				<tr><td>date</td><td><?=$e['date']?></td></tr>
				<tr><td>reason</td><td><?=$e['reason']?></td></tr>
				<tr><td>facility</td><td><?=$e['facility']?></td></tr>
				<tr><td>form</td><td><?php echo $e['form_name'] . " (" . $e['form_id'] . ")"; ?></td></tr>
				
			</table>
			
			
			<table class='billing'>
				<tr><th colspan='2'>appointment</th></tr>
				<tr><td>title</td><td><?=$appt['pc_title']?></td></tr>
				<tr><td>date</td><td><?=$appt['pc_eventDate']?></td></tr>
				<tr><td>time</td><td><?=$appt['pc_startTime']?></td></tr>
				
			</table>
			
			
			<table class='billing'>
				<tr><th colspan='3'>billing</th></tr>
				<tr class='header'><td>code</td><td>fee</td><td>processed?</td></tr>
				<?php 
					
				if ($b) { 
					
					while ($bill = sqlFetchArray($b)) {
						echo "<tr><td>" . $bill['code'] . "</td><td>" . $bill['fee'] . "</td><td>" . $bill['bill_process'] . "</td></tr>";	
						if ($bill['bill_process'] > 0) {
							$allow_delete = false;
						}
					}
					
				}
				?>
				
				
			</table>
			
				<table class='billing'>
				<tr><th colspan='2'>note</th></tr>
		
				<?php 
					
				if ($note_data) { 
					
					while ($n = sqlFetchArray($note_data)) {
						echo "<tr><td>" . $n['field_id'] . "</td><td>" . $n['field_value'] . "</td></tr>";
					}
					
				}
				?>
				
				
			</table>
			
			
			
			
</div>
<?php
	if ($allow_delete) {  ?>
		
	
<div style="text-align:center; font-size:24px"><a href="encounter_inspector.php?encounter_id=<?=$encounter_id?>&delete=start">DELETE THIS ENCOUNTER</a></div>


<?php

	} else { 
		
?>
<div style="text-align:center; font-size:24px">This encounter cannot be deleted, because billing has been processed.</div>
<?php
	} // end if/else allow delete
		}	// end if there's an encounter id
			
	
	?>

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