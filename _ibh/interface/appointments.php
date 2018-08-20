<?php
	
	// ini_set("display_errors", 1);
	
	$authUsers = array("mckenzieb", "admin", "tami", "TamiJ");
	
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

// IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");



?><html>
<title></title>
<head>
<script type="text/javascript" src="<?=  $GLOBALS['webroot'] ?>/_ibh/js/jquery_latest.min.js"></script>

<link rel="stylesheet" href="<?=  $GLOBALS['webroot'] ?>/interface/themes/style_metal.css" type="text/css">
<link rel="stylesheet" href="<?=  $GLOBALS['webroot'] ?>/_ibh/css/encounter.css" type="text/css">
<style>
	.hazard {
		background-color:#ee92a1;
	}
	
	.ajax-status {
		color:#0f990f;
		font-size:12px;
		font-weight:bold;
	}
	#update-status {
		background-color:#f3fcbf;
		color:#333;
		font-size:12px;
		width:76%;
		padding:12px;
		margin:0 auto;
	}
	
	.selected-for-delete {
		background-color:#ffe600;
	}
</style>

</head>
<body class="overview-pane">
<div class="ibh-wrapper">
	
	<?php if (!in_array($_SESSION['authUser'], $authUsers)) {
		 echo "<h2>NO ACCESS</div>"; 
		 exit;  
		 
		 }
	?>
	
	
	<h2>Appointments</h2>
	
	
	<div class="ibh-form-search">
	
	<?php
		

		$search_string = "";
		$data_array = array();
		$where_query = " ope.pc_eventDate>'2001-01-01' ";
		$sel_patient_name = "";
		$sel_auth_num = "";
		$sel_billing_code = "";
		$sel_date = "";
		
		if (isset($_GET['pc_apptstatus']) && $_GET['pc_apptstatus']) {
		
			$apptstatus = $_GET['pc_apptstatus'];
			$where_query .= "AND ope.pc_apptstatus='$apptstatus' ";

		}
		
		if ($_GET['search_string']) {
			$search_string = $ss = trim($_GET['search_string']);
			$search_domain = trim($_GET['search_domain']);
			
			switch($search_domain) {
				case "patient_name":
					$where_query .= " AND (p.lname LIKE '%$search_string%' OR p.fname LIKE '%$search_string%') ";
					$sel_patient_name = "selected";
					break;
			
				case "date":
					$where_query .= " AND ope.pc_eventDate='$search_string' ";
					$sel_date = "selected";
					break;
					
				case "encounter":
					$where_query .= " AND ope.encounter='$search_string' ";
					$sel_encounter = "selected";
					break;
			}
		}		
		
		
		$stamp = time() - (30 * 86400);
		$date_back = date("Y-m-d", $stamp);
		
		$where = $where_query ? $where_query: "pc_EventDate > '$date_back'";

		$filter_checked_in = (isset($_GET['filter']) && $_GET['filter'] == "checked_in");
		if ($filter_checked_in) $where = "pc_apptstatus='@' AND pc_EventDate > '$date_back'";
		
		$sql = "SELECT ope.*, p.fname, p.lname FROM openemr_postcalendar_events ope LEFT JOIN patient_data p ON ope.pc_pid=p.pid WHERE $where ORDER BY ope.pc_eventDate DESC LIMIT 1000";
		
		$url_string = "prior_auths_overview.php?search_domain=" . $search_domain . "&search_string=" . $search_string;

	?>
	<form action="appointments.php" method="get">
		
	<label>search for:</label>
		<input type="text" name="search_string" value="<?=$search_string?>"> 
		<label>in:</label>
		<select name="search_domain">
			<option value="patient_name" <?=$sel_patient_name?>>patient name</option>
			<option value="date" <?=$sel_date?>>date</option>
			<option value="encounter" <?=$sel_encounter?>>encounter</option>
		</select>
		
		&nbsp;&nbsp;&nbsp;AND/OR&nbsp;&nbsp;&nbsp;
		
		<?=ibh_appt_status_picker("apptstatus", $_GET['pc_apptstatus'])?> in status
		
		<input type="submit" value="go">
		
		
		<ul class="ibh-top-buttons">
			<li><a href="appointments.php?filter=checked_in">view @ checked-in</a></li>
			<li><a href="appointments.php">view all</a></li>
		</ul>
	</form>
	
	
</div>


	<div id='update-status'></div>
	
	<table class="prior-auths-table list-table">
	<tr>
		
	<th>patient</th>
	<th>date</th>
		
	<th>title</th>
	
	<th>enc #</th>
	
	<th>&nbsp;</th>

	<th style="width:22%">status</th>
	
	</tr>
	
	
	
	<?php
		
    $appts = sqlStatement($sql);
    
    while($appt = sqlFetchArray($appts)){ 
	    
	    if ($appt['lname']) {
	    $row_class = $appt['pc_apptstatus'] == "@" ? "hazard": "other";
		?>
		
		<tr class='status-row <?=$row_class?>'><td><strong><?=$appt['fname'] . " " . $appt['lname']?></strong></td>
		
		<td><?=$appt['pc_eventDate']?> <?=$appt['pc_startTime']?></td>
		
		<td><?=$appt['pc_title']?></td>
		
		<td><?=$appt['encounter']?></td>
		
		<td><a class='appt_delete' id='<?=$appt['pc_eid']?>' style="cursor:pointer;color:#da7900">delete</a></td>
		
		<td class="editor"><?=ibh_appt_status_picker($appt['pc_eid'], $appt['pc_apptstatus'])?>&nbsp;&nbsp;&nbsp;<span class='ajax-status'>...</span></td>
		</tr>
		
		<?php
	} // end if legit appt
	} // end while
	?>
</div>




<script type="text/javascript">
	$(function(){
		
			
			
			$(".appt_delete").on("click", function() {
				
				var $s = $(this);
				var id = $s.attr("id");
				
				

				var $row = $s.closest(".status-row");
				$row.addClass("selected-for-delete");
				
				
				setTimeout(function() {
					
						var conf = confirm("Are you sure you want to delete this appointment?");
						
						if (conf) {
					
							$("#update-status").html("Aye, Captain! Appointment No. " + id + " deleted from the core database!");
							
							$.ajax({
								url:"<?= $GLOBALS['webroot'] ?>/_ibh/ajax/edit_appointment_status.php",
								data:{pc_eid:id, delete:"true"},
								success: function(data) {
									console.log("delete data: " , data);
									
									$row.fadeOut("slow", function() { $row.remove() });
				
								}
							});
						
						
						}
				
				
						
				}, 30);
				
				
				
				
				
				
			});
			
			
			$(".appt-changer").on("change", function() {
				
				var $s = $(this);
				var id = $s.attr("id");
				var val = $s.val();
				var $status = $s.closest(".editor").find(".ajax-status");
				var $row = $s.closest(".status-row");
				
				$status.text("Updating...");
			
				$.ajax({
					url:"<?= $GLOBALS['webroot'] ?>/_ibh/ajax/edit_appointment_status.php",
					data:{pc_eid:id, pc_apptstatus:val},
					success: function(data) {
						console.log("response: ", data);
						
						setTimeout(function() {
							$status.text("Done!");
						}, 850);
						
						if (val == "@") {
							$row.addClass("hazard");
						} else {
							$row.removeClass("hazard");
						}
				
				
					}
				});
				
				
			});
		
		
	});
</script>




</body>
</html>

