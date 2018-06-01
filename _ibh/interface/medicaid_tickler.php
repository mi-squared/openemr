<?php
	
	
	// ini_set("display_errors", 1);
	
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");


	require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/forms.inc');
require_once($GLOBALS['srcdir'].'/calendar.inc');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/encounter_events.inc.php');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/patient_tracker.inc.php');
	
	
	

	
?><html>
<title></title>
<head>



<style>
	.ibh-tickler-table {
		width:90%;
		background-color:#f0f0f0;
		border:1px solid #ccc;
		border-collapse:collapse;
	}
	
	
	.ibh-tickler-table  th{
		background-color:#333;
		color:white;
		text-align:left;
		font-weight:normal;
	}
	
	
	.ibh-tickler-table  td{
		border:1px dotted #ccc;
		padding:4px;
	}
	.ibh-tickler-table  td.ibh-tick-patient{
		width:150px;
	}
	
</style>


<link rel="stylesheet" type="text/css" href="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/dynarch_calendar.css">
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/topdialog.js?t=<?=time()?>"></script>
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/dynarch_calendar_setup.js"></script>


<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/js/jquery_latest.min.js"></script>


<link rel="stylesheet" href="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/css/tickler.css" type="text/css">


</head>
<body>
<div class="ibh-wrapper">


<table class="ibh-tickler-table">
	<tr><th>patient</th><th>provider</th><th>CDA</th><th>90-day</th><th>90 completed</th><th>180 day</th><th>180 completed</th><th>270 day</th><th>270 completed</th><th>Annual</th><th>360 completed</th><th>prior auth</th><th>auth date start</th><th>auth date end</th></tr>
	
	
	<tr class="ibh-tick-row">
		<td class='ibh-tick-patient'>Select Patient</td>
		<td class='ibh-tick-provider'>...</td>
		<td class='ibh-tick-cda'>...</td>
		<td class='ibh-tick-d90'>...</td>
		<td class='ibh-tick-d90c'>...</td>
		<td class='ibh-tick-d180'>...</td>
		<td class='ibh-tick-d180c'>...</td>
		<td class='ibh-tick-d270'>...</td>
		<td class='ibh-tick-d270c'>...</td>
		<td class='ibh-tick-d360'>...</td>
		<td class='ibh-tick-d360c'>...</td>
		<td class='ibh-tick-pa'>...</td>
		<td class='ibh-tick-pa-start'>...</td>
		<td class='ibh-tick-pa-end'>...</td>
	</tr>
	
</table>


</div>

<?php
	
	$patient_id = 108;
	
	$sql = "SELECT * FROM form_encounter fe, forms f WHERE fe.encounter > 3000 AND fe.pid='$patient_id' AND f.encounter=fe.encounter ORDER BY fe.date LIMIT 50";
	$data = array();
	
	
	$stuff = sqlStatement($sql, $data);
	
	while ($row = sqlFetchArray($stuff)) {
		echo $row['form_name'] . "<br>";
		
	}
	
	
?>

<script type="text/javascript">
	
	
	var $patient_td = {};
	var $provider_td = {};
	
	$(".ibh-tick-patient").click(function() {
		$patient_td = $(this);
		var $parent_row = $patient_td.closest(".ibh-tick-row");
		console.log("parent row", $parent_row);
		
		$provider_td = $parent_row.find(".ibh-tick-provider");
		sel_patient();
		
	});
	
	function getProviderPulldown(pro_id) {
		$.ajax({
					url:"/openemr/_ibh/ajax/get_provider_pulldown.php",
					data:{provider_id:pro_id},
					success: function(d) {
						console.log("pulldown:", d);
						
						$provider_td.html(d[0]);
						
					}
		});
		
	}


	function setpatient(pid) {
		
		$.ajax({
					url:"/openemr/_ibh/ajax/get_patient_data.php",
					data:{pid:pid},
					success: function(patient_data) {

						console.log("patient_data", patient_data);
						$patient_td.text(patient_data.patient.fname + " " + patient_data.patient.lname);
						
						getProviderPulldown(patient_data.patient.providerID);
						
					}
		});
		
		
		
	}
	
	
	function sel_patient() {
		
		 window.open("/openemr/interface/main/calendar/find_patient_popup.php", "patient-chooser", "resizable=1,scrollbars=1,location=0,toolbar=0" + 
	",width=450,height=450,left=150,top=150");
	
 	}
 	
 	

</script>
</body>
</html>
