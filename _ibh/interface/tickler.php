<?php
	
	
// ini_set("display_errors", 1);
	
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

$active_user = $access_provider = $_SESSION['authUserID'];

if (isset($_GET['access_provider']) && $_GET['access_provider'] > 0) {
	$access_provider = $_GET['access_provider'];
}

require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/forms.inc');
require_once($GLOBALS['srcdir'].'/calendar.inc');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/encounter_events.inc.php');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/patient_tracker.inc.php');


require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");


$is_supervisor = ibh_user_is_supervisor();





function provider_patient_pulldown($active_user) {
	
	$patients = sqlStatement("SELECT pd.pid, pd.DOB, pd.fname, pd.lname FROM patient_data pd, ibh_patients_to_providers p2p WHERE pd.pid=p2p.patient_id AND p2p.provider_id=? ORDER BY pd.lname, pd.fname", array($active_user));

	$html = "<select id='provider_patient_pulldown'><option>Choose...</option>";
	
	while ($p = sqlFetchArray($patients)) {
		$html .= "<option data-dob='" . $p['DOB'] . "' value='" . $p['pid'] . "'>" . $p['lname'] . ", " . $p['fname'] . "</option>";
	}
	
	$html .= "</select>";
	
	return $html;
		
}


function nodash($str) {
	if (substr($str,0,2) != "20") {
		return "";
	} else {
		return str_replace("-", "", $str);
	}
}

function get_cb($id, $signed=0, $blank) {
	
	if ($id && $blank == "0") {
		if ($signed == 0) {
			return "<div class='f-sign unsigned'></div>";
		} else {
			return "<div class='f-sign signed'></div>";
		}
		
	} else {
		return "";
	}
}
	
function get_plan_row($patient) {
	
		$blank_row_ct = 0;	
		
		$pid = $patient['pid'];
		$name = $patient['lname'] . ", " . $patient['fname'];
		$dob = $patient['DOB'];
		
		$stuff = sqlStatement("SELECT * FROM ibh_tickler_reviews WHERE patient_id=?", array($pid));
	
		$html = "";
		
		if (sqlNumRows($stuff) > 0){ 
	
			while ($row = sqlFetchArray($stuff)) {
				
				$tick_id = $row['id'];

				$init_cda = $row['init_cda'];
				$init_cda_info = ibh_get_form_info($init_cda);
				
				$init_tp = $row['init_tp'];
				$init_tp_info = ibh_get_form_info($init_tp, true); // includes projections
	
				$r1 = $row['r1'];
				$r1_info = ibh_get_form_info($r1); 
				
				$r2 = $row['r2'];
				$r2_info = ibh_get_form_info($r2); 
				
				$r3 = $row['r3'];
				$r3_info = ibh_get_form_info($r3); 
				
				$r4 = $row['r4'];
				$r4_info = ibh_get_form_info($r4); 
				
				
				$html = "<tr class='ibh-tick-row' id='active_" . $pid . "_" . $tick_id . "'>
			<td class='ibh-tick-patient'>" . $name . "<div class='patient-dob'>" . $dob . "</div></td>
	
			<td class='ibh-choose-cda active' data-type='cda' data-form_id='" . $init_cda_info['id'] . "' data-sort-value='" . nodash($init_cda_info['nice_date']) . "'><div class='mini-form-name'>" . ibh_form_link($init_cda_info['form_name'], $init_cda_info['encounter'], $pid) . "</div><div class='comp-date'>" . $init_cda_info['nice_date'] . "</div></td>
			
			<td class='ibh-choose-tx0 active' data-type='tx' data-form_id='" . $init_tp_info['id'] . "' data-sort-value='" . nodash($init_tp_info['nice_date']) . "'><div class='mini-form-name'>" . ibh_form_link($init_tp_info['form_name'], $init_tp_info['encounter'], $pid). "</div><div class='comp-date'>" . $init_tp_info['nice_date'] . "</div></td>
			
			
			<td class='ibh-tick-d90 ibh-tick-target' data-sort-value='" . nodash($init_tp_info['d90']) . "'>" . $init_tp_info['d90'] . "</td>
			
			<td class='ibh-choose-d90-comp'data-form_id='" . $r1_info['id'] . "' data-sort-value='" . nodash($r1_info['nice_date']) . "'><div class='mini-form-name'>" . ibh_form_link($r1_info['form_name'], $r1_info['encounter'], $pid). "</div><div class='comp-date'>" . $r1_info['nice_date'] . "</div>" . get_cb($r1_info['id'], $row['r1_signed'], $r1_info['blank']) . "</td>
			
			<td class='ibh-tick-d180 ibh-tick-target' data-sort-value='" . nodash($init_tp_info['d180']) . "'>" . $init_tp_info['d180'] . "</td>
			
			<td class='ibh-choose-d180-comp' data-form_id='" . $r2_info['id'] . "' data-sort-value='" . nodash($r2_info['nice_date']) . "'><div class='mini-form-name'>" . ibh_form_link($r2_info['form_name'], $r2_info['encounter'], $pid). "</div><div class='comp-date'>" . $r2_info['nice_date'] . "</div>" . get_cb($r2_info['id'], $row['r2_signed'], $r2_info['blank']) . "</td>
			
			<td class='ibh-tick-d270 ibh-tick-target' data-sort-value='" . nodash($init_tp_info['d270']) . "'>" . $init_tp_info['d270'] . "</td>
			
			<td class='ibh-choose-d270-comp' data-form_id='" . $r3_info['id'] . "' data-sort-value='" . nodash($r3_info['nice_date']) . "'><div class='mini-form-name'>" . ibh_form_link($r3_info['form_name'], $r3_info['encounter'], $pid). "</div><div class='comp-date'>" . $r3_info['nice_date'] . "</div>" . get_cb($r1_info['id'], $row['r3_signed'], $r3_info['blank']) . "</td>
			
			<td class='ibh-tick-d360 ibh-tick-target' data-sort-value='" . nodash($init_tp_info['d360']) . "'>" . $init_tp_info['d360'] . "</td>
			
			<td class='ibh-choose-d360-comp' data-form_id='" . $r4_info['id'] . "' data-sort-value='" . nodash($r4_info['nice_date']) . "'><div class='mini-form-name'>" . ibh_form_link($r4_info['form_name'], $r4_info['encounter'], $pid). "</div><div class='comp-date'>" . $r4_info['nice_date'] . "</div>" . get_cb($r1_info['id'], $row['r4_signed'], $r4_info['blank']) . "</td>
			
			</tr>";
		
				
			}
		
		} else {
			// nothing else here; only existing data is loaded
		}
		return $html;
}



	
?><html>
<title></title>
<head>

<link rel="stylesheet" type="text/css" href="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/dynarch_calendar.css">
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/topdialog.js?t=<?=time()?>"></script>
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/js/jquery_latest.min.js"></script>
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/js/moment.js"></script>
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/js/jquery.tablesort.js"></script>

<link rel="stylesheet" href="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/css/tickler.css" type="text/css">

<?php if ($is_supervisor != 1) { ?>
<style type="text/css">
	.choose-form { display:none }
</style>
<?php } ?>

</head>
<body>
<div class="ibh-wrapper">

<div class='date_selector'><div class='ds_cancel'>cancel</div><div class='ds_clear'>clear</div><div class='form-list-items'></div></div>
<div class="wrapper">
	<div class="navi">
	<a href="provider_patients.php">go to provider-patient list</a>
</div>

	<h3 style="margin-top:0">Tickler</h3>


<?php if ($is_supervisor) { ?>
<div class='controls'>
Create a new row with patient: <?php echo provider_patient_pulldown($access_provider); ?>
</div>
<?php } ?>


<?php
	
 echo "<div class='controls'>Change provider:" . ibh_getUserPulldown("user_pulldown", $access_provider, false) . "</div>"; 

?>



</div>

<table class="ibh-tickler-table">
	<thead><tr><th>patient</th><th>CDA</th><th>TX</th><th>90-day</th><th>90 completed</th><th>180 day</th><th>180 completed</th><th>270 day</th><th>270 completed</th><th>Annual</th><th>360 completed</th></tr></thead>
	<tbody>

<?php
	// ibh_patients_to_providers
	
	$patients = sqlStatement("SELECT pd.pid, pd.DOB, pd.fname, pd.lname FROM patient_data pd, ibh_patients_to_providers p2p, ibh_tickler_reviews tr WHERE pd.pid=p2p.patient_id AND p2p.patient_id=tr.patient_id AND p2p.provider_id=? ORDER BY pd.lname, pd.fname", array($access_provider));

	
	while ($patient = sqlFetchArray($patients)) {
			
		echo get_plan_row($patient);

	}

?>	
	</tbody>
</table>


</div>
<script type="text/javascript">
	
	var is_supervisor = <?php echo $is_supervisor == 1 ? "true": "false"; ?>;
	
	$(function() {
		
		
	$('.ibh-tickler-table').tablesort();
	
	var $active_td = {}, active_id = "", reset_tx = false;
	
	
	// CLICKING ON A REGULAR CELL
	if (is_supervisor) {
		
		// trigger the selection pulldown to appear
		$("body").on("click", ".ibh-tick-row td", function(event) {
					
			if (event.target.className != "form-link" && event.target.className.substring(0,6) != "f-sign") {
				
				var $this_td = $(this);
				
				var parent_row_id = $this_td.closest(".ibh-tick-row").attr("id");
				var class_name = $this_td.attr("class");
				var this_id = active_id = parent_row_id + class_name;		
				var cda = $this_td.closest(".ibh-tick-row").find(".ibh-choose-cda").data("form_id");
				var pid = parent_row_id.split("_")[1];
				var cell_class_arr = class_name.split("-")
				var cell_type = cell_class_arr[1];
				var complete_chooser = cell_class_arr[3] == "comp" ? true: false;
				var clearing_tx = false;
				
				if (!cda && !$this_td.hasClass("ibh-choose-cda")) {
					alert("Please choose a CDA first.");
					return false;
				}
				
				if (pid && cell_type == "choose") {
					
					$active_td = $this_td;
					
					var is_active = $this_td.hasClass("active");
					
					var is_comp = $this_td.hasClass("completed");
						
					var pos = $active_td.offset();
					var wid = $active_td.width();
					var el_left = pos.left - (340 - wid);
					var el_top = pos.top + 52;
								
					$(".date_selector").css({left:el_left, top:el_top});
					
					if (is_active || is_comp) {
						// show clear
						$(".date_selector").find(".ds_clear").show();
					} else {
						// hide clear
						$(".date_selector").find(".ds_clear").hide();
					}
					
					getEncounterPulldown(pid, $active_td);			
							
				} else if (cell_type == 'blank') {
					alert("Please choose CDA and Treatment Plan.");
				} 
			
			}
					
		});
	
	}
	
	function get_phase($td) {
		var class_list = $td.attr("class");
		var first_class = class_list.split(" ")[0];
		var phase_days = first_class.split("-")[2];
		
		
		var phase_translation = {"d90":"r1_signed", "d180":"r2_signed","d270":"r3_signed", "d360":"r4_signed"}
		
		return phase_translation[phase_days];
		
	}
	
	

	// Once the user selects an appt. from the pulldown
	// this injects the information into the tickler row
	$(".date_selector").on("click", ".form-list-item", function() {
		
		var $el = $(this);
		var dat = $el.data("date");
		var tit = $el.data("title");
		var fid = $el.data("form_id");
		var enc = $el.data("encounter");
		
		var existing_id = $active_td.data("form_id");
		
		var row_id = $active_td.closest(".ibh-tick-row").attr("id");
		var pid = row_id.split("_")[1];
		
		var cell_info = $active_td.attr("class").replace(" ", "-").split("-");

		$active_td.addClass("active").html("<div class='mini-form-name'>" + tit + "<a href='/openemr/interface/patient_file/encounter/forms.php?set_encounter=" + enc + "&pid=" + pid + "' target='_blank' class='form-link'>open</a></div><div class='comp-date'>" + dat + "</div><div class='f-sign unsigned'></div>").attr("data-form_id", fid);

		
		if (cell_info[3] == "comp" || cell_info[2] == "cda") {			
			setGoalComplete(fid, row_id, cell_info);
			setRowColors($("#" + row_id));
		}
		
		
		
		if ($active_td.hasClass("ibh-choose-tx0")) {
			// has a form id, is existing TX
			
			
			var cda = $active_td.closest(".ibh-tick-row").find(".ibh-choose-cda").data("form_id");
			
			if (existing_id > 0) {
				
				// existing, clear and recreate then refresh?
				setRowFromTX($active_td, row_id, fid, cda, "update"); // non-clear creates new record
				
				
			} else if (fid && cda) {	
				
							
				setRowFromTX($active_td, row_id, fid, cda, "set"); // non-clear creates new record
				setRowColors($("#" + row_id));
				
			}
		}
		
	
		$(".date_selector").find("stuff").html("");

		cancelSelector();
				
	});
	
	
	
		
	function setGoalComplete(form_id, row_id, cell_info) {
		
		var row_info = row_id.split("_");		
		var tickler_id = row_info[2];		
		var column_id = cell_info[2];
		
		$.ajax({
					url:"/openemr/_ibh/ajax/tickler_set_complete_date.php",
					data:{form_id:form_id, tickler_id:tickler_id, target:column_id, clear:0},
					success: function(reply) {
					
						// console.log("setGoalComplete success:", reply);
						
					}
		});
		
		
	}
	
	/*
	function getCellInfo($cell) {
		var form_id = $cell.data("form_id");
	}
	*/
	
	
	if (is_supervisor) {
		
		$("body").on("click", ".f-sign", function(event) {
			
			
			var $bt = $(this);
			

			var new_state = $bt.hasClass("signed") ? 0:1;
			var $cell = $bt.closest("td");
			var row_id = $cell.closest(".ibh-tick-row").attr("id");
			var tickler_id = row_id.split("_")[2];	
			
			var phase = get_phase($cell);
			console.log("phase", phase);
			

			$bt.toggleClass("signed unsigned");

			$.ajax({
					url:"/openemr/_ibh/ajax/tickler_set_signed.php",
					data:{phase:phase, tickler_id:tickler_id, signed:new_state},
					success: function(reply) {
					
						console.log("signed/unsigned");
						
					}
			});
		
		})
	
	}
	
	$("#user_pulldown").on("change", function() {
		var prov_id = $(this).val();
		window.location.href = "tickler.php?access_provider=" + prov_id;
		// set the provider in the URL
	});
		
	
	
	
	function getEncounterPulldown(pid, $el) {
		
		$.ajax({
					url:"/openemr/_ibh/ajax/tickler_encounter_pulldown.php",
					data:{pid:pid},
					success: function(json) {
						if (!json.html) {
							$(".date_selector").find(".form-list-items").html("no forms");
							return false;
						} else {
							$(".date_selector").find(".form-list-items").html(json.html);
						}
						
					}
		});
		
	}
	
	
	
	function setupCell($row, phase) {
		$row.find(".ibh-blank-" + phase + "-comp").removeClass("ibh-blank-" + phase + "-comp").addClass("ibh-choose-" + phase + "-comp").html("<div class='choose-form'>CHOOSE FORM...</div>");
	}
	
	
	
	function setupRowForTargets(json, row_id) {
		
		$row = $("#" + row_id);
		
		$row.find(".ibh-blank-d90").html(json.row.d90).addClass("ibh-tick-target");
		$row.find(".ibh-blank-d180").html(json.row.d180).addClass("ibh-tick-target");
		$row.find(".ibh-blank-d270").html(json.row.d270).addClass("ibh-tick-target");
		$row.find(".ibh-blank-d360").html(json.row.d360).addClass("ibh-tick-target");
		
		setupCell($row, "d90");
		setupCell($row, "d180");
		setupCell($row, "d270");
		setupCell($row, "d360");
	
	}
	
	
	
	function setRowFromTX($td, row_id, tx_id, cda_id, action) {
		
		// active_2069_1
		var patient_id = row_id.split("_")[1];
			
		if (action == "clear") {

			var tickler_id = row_id.split("_")[2];	
			
			$.ajax({
					url:"/openemr/_ibh/ajax/tickler_set_row.php",
					data:{action:"clear", clear_id:tickler_id, tx_form_id:tx_id, cda_form_id:cda_id},
					success: function(json) {
						console.log("clear??");
											
						var $row = $td.closest(".ibh-tick-row")
						$row.find("td").each(function() {
							var $cell = $(this);
						
							if ($cell.hasClass("ibh-tick-patient")) {
								// do nothing
							} else if ($cell.hasClass("ibh-choose-tx0")) {
								
								
								$cell.html("<span class='choose-cda lg'>CHOOSE TX</span>").removeClass("active");
								
								
							} else if ($cell.hasClass("ibh-choose-cda")) {
								$cell.html("<span class='choose-cda lg'>CHOOSE CDA</span>").removeClass("active");
							} else {
								
								var classes = $cell.attr("class").split(" ");
								var orig = classes[0].replace("-comp", "");
								
								$cell.html("").attr("class", orig);
							}
						}); // end each
						
					
					} // end success on ajax req
			});
			
		} else if (action == "set") {
			
			// so far, it has no tickler_id; this wll be sent back in json var as json.tickler_id
			
			$.ajax({
					url:"/openemr/_ibh/ajax/tickler_set_row.php",
					data:{action:"set", tx_form_id:tx_id, cda_form_id:cda_id, patient_id:patient_id},
					success: function(json) {
						
						
						var tickler_id = json.tickler_id;
						var r = row_id.split("_");
						var new_id = r[0] + "_" + r[1] + "_" + tickler_id;
						
						$("#" + row_id).attr("id", new_id);
						
						
						console.log("setting up row...", json, new_id);
						// THis is creating a new record, when it ought to edit...
						setupRowForTargets(json, new_id);
						
						
					}
			});
		} else if (action == "update") {
			
			
			
			var tickler_id = row_id.split("_")[2];
			
			console.log("updating tx", tickler_id, tx_id);
			
			
			$.ajax({
					url:"/openemr/_ibh/ajax/tickler_set_row.php",
					data:{action:"update", tickler_id:tickler_id, tx_form_id:tx_id, cda_form_id:"", patient_id:patient_id},
					success: function(json) {
						
						// THis is creating a new record, when it ought to edit...
						// setupRowForTargets(json, row_id);
						setTimeout(function() {
							location.reload();
						}, 500)
						
					}
			});



			
		}
	}
	
	
	
	
	function get_days_from_ms(ms) {
		var secs = 86400;
		var day_ms = secs * 1000;
		return Math.round(ms/day_ms);
		
	}
	
	
	function color_dates($targ, $comp) {
		
		var now = moment();
		
		var targ_date = $targ.text();
		var targ_moment = moment(targ_date);
		var early_late = "";
		var comp_date = $comp.find(".comp-date").text();
		
		var time_to_date_str = moment().to(targ_date);
		var time_to_date_diff = moment().diff(targ_date);
		
		var td_class = "";
		
		$comp.find("small").remove();
		
		if (comp_date.substring(0,2) == "20") {
			// we have a completed date
			var comp_moment = moment(comp_date);
			
			var ago = get_days_from_ms(targ_moment.diff(comp_moment)) ;
			
			early_late = ago < 0 ? "after":"before";
						
			$comp.append("<small>" + Math.abs(ago) + " days " + early_late + "</small>");
			$comp.addClass("completed");
			
		} else {
			// NOT COMPLETE
					
			var days_to_go = get_days_from_ms(time_to_date_diff);

			$comp.append("<small>due " + time_to_date_str + "</small>");
			
			if (days_to_go < 0) {
				if (days_to_go <= -15) {
				    // 2+ weeks
				    td_class = "upcoming_15_or_more";
				} else if (days_to_go > -15 && days_to_go < -1) {
					// 2 week window UPCOMING
					td_class = "upcoming_two_week_window";
				} else if (days_to_go == -1) {
					// TODAY
					td_class = "upcoming_today";
				} else {
					// wtf
				}
				
				$comp.addClass(td_class);
			} else {
				// OVERDUE
				
				if (days_to_go > 1 && days_to_go <= 15) {
				    // 2+ weeks
				    td_class = "overdue_1_to_15";
				} else if (days_to_go > 15 && days_to_go < 30) {
					// 2 week window UPCOMING
					td_class = "overdue_16_to_30";
				} else if (days_to_go >= 30) {
					// TODAY
					td_class = "overdue_31";
				} else {
					// wtf
				}
				$comp.addClass(td_class);
			}
		}
	}
	
	
	function setColors() {
		var $td = {}, tf = '', fc_info = [], first_class="", type="", has_class=false, $comp;
		
		$(".ibh-tick-row").each(function() {
			setRowColors($(this));
		});
		
	}
	
	
	function setRowColors($row) {
		var $tds = $row.find("td");
			
			$tds.each(function() {
				$td = $(this);
				first_class = $td.attr("class").split(/\s+/)[0];
				has_class = $td.hasClass("ibh-tick-target");
				
				fc_info = first_class.split("-");
				tf = fc_info[2];
				type = fc_info[1];
				
				if (has_class && (tf == "d90" || tf == "d180" || tf == "d270" || tf == "d360" )) {
					// console.log("tf:", tf);
					$comp = $td.next();
					
					color_dates($td, $comp);
					
				}
				
			});
		
	}
	
	
	
	function cancelSelector() {
		$(".date_selector").css({left:"-600px"}).find(".form-list-items").html("");
		active_id = "";
		$active_td = {};
	}
	
	
	function clearCell() {

		var row_id = $active_td.closest(".ibh-tick-row").attr("id");
		var tx_id = $active_td.data("form_id");
		var class_str = $active_td.attr("class")
		var class_list = class_str.split(" ");
		var class_info = class_str.replace(" ","-").split('-');
		var $td = $active_td;
		
		
		if (class_info[2] == "cda") {
			
			$active_td.html("<span class='choose-cda lg'>CHOOSE CDA</span>");
		
		} else if (class_info[2] == "tx0") {
			// We've got to clear the whole row
			// wipe tx and other cells
			var conf = confirm("This will clear the entire row. Okay?");
			if (conf) {
				setRowFromTX($active_td, row_id, tx_id, "", "clear");
				cancelSelector();	
			}
			
			
		} else {
			
			var tickler_id = row_id.split("_")[2];
			
			$.ajax({
					url:"/openemr/_ibh/ajax/tickler_set_complete_date.php",
					data:{clear:1, tickler_id:tickler_id, target:class_info[2]},
					success: function(reply) {
						
						$td.attr("class", class_list[0]).find("small").remove();
						$td.html("<div class='choose-form'>CHOOSE FORM...</div>");
						
						cancelSelector();
						
						setRowColors($("#" + row_id));
						
						
					}
			});
		
		}
		$td.removeClass("active");
		cancelSelector();
	}
	
	
	$(".ds_clear").click(function() {
		clearCell();
	});


	$(".ds_cancel").click(function() {
		cancelSelector();
	});


	function getProviderPulldown(pro_id) {
		$.ajax({
			url:"/openemr/_ibh/ajax/get_provider_pulldown.php",
			data:{provider_id:pro_id},
			success: function(d) {						
				
				$provider_td.html(d[0]);
				
			}
		});
	}
	
	
	$("#provider_patient_pulldown").on("change", function() {
		
		var pid = $(this).val();
		var pname = $("#provider_patient_pulldown option:selected").text();
		var dob = $("#provider_patient_pulldown option:selected").data("dob");
		
		var htm = "<tr class='ibh-tick-row' id='blank_" + pid + "_1'>" +
			"<td class='ibh-tick-patient'>" + pname + "<div class='patient-dob'>" + dob + "</div></td>" +
			"<td class='ibh-choose-cda' data-sort-value='0' data-type='cda'><span class='choose-cda lg'>CHOOSE CDA</span></td>" +
			"<td class='ibh-choose-tx0' data-sort-value='0' data-type='tx'><span class='choose-tx lg'>CHOOSE TX</span></td>" +
			"<td class='ibh-blank-d90' data-sort-value='0' ></td>" +
			"<td class='ibh-blank-d90-comp' data-sort-value='0' ></td>" +
			"<td class='ibh-blank-d180' data-sort-value='0' ></td>" +
			"<td class='ibh-blank-d180-comp' data-sort-value='0' ></td>" +
			"<td class='ibh-blank-d270' data-sort-value='0' ></td>" +
			"<td class='ibh-blank-d270-comp' data-sort-value='0' ></td>" +
			"<td class='ibh-blank-d360' data-sort-value='0' ></td>" +
			"<td class='ibh-blank-d360-comp' data-sort-value='0' ></td>" +
			"</tr>";
		
		$(".ibh-tickler-table").find("tbody").prepend(htm);
		
	});


	function setpatient(pid) {
		
		var $patient_td = $active_td; // BOGUS!!
		
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
 	
 	setColors();
 	
 	
 	
 	});
 	
 	

</script>


</body>
</html>
