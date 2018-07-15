<?php
	
// ini_set("display_errors", true);
	
	
	
// <!-- IBH_DEV_CHG -->
// THIS ENTIRE PAGE HAS BEEN TAKEN OVER BY IBH
// AND HUGELY CHANGED (for the better : )
// DO NOT ALLOW IT TO BE OVER-WRITTEN BY FUTURE
// UPDATES TO OPENEMR

	
// Copyright (C) 2005-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report shows upcoming appointments with filtering and
// sorting by patient, practitioner, appointment type, and date.
// 2012-01-01 - Added display of home and cell phone and fixed header

// srcdir = library



require_once("../globals.php");
require_once("../../library/patient.inc");


require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/appointments.payroll.inc.php");


// IBH_DEV_CHG
require_once("../../_ibh/ibh_functions.php");




function ibh_getPayrollCodePulldown($code) {
	
	$html = "<select id='codes' name='billing_code'><option value=''>All Codes</option>";
	
	$cres = sqlStatement("SELECT pc_catname FROM openemr_postcalendar_categories WHERE pc_catname LIKE '%:%' ORDER BY pc_catname");

	while ($crow = sqlFetchArray($cres)) {
		
		$code_name = $crow['pc_catname'];
		
		$ex_code = trim(explode(":", $code_name)[1]);
  
		$html .= "<option value='" . $ex_code . "'";

	  	if ($ex_code == $code) {
	  		$html .= " selected";
		}
	  	$html .= ">" . text(xl_appt_category($crow['pc_catname'])) . "</option>\n";
	}

	$html .= "</select>";
	
	return $html;


}

function ibh_get_location_totals($assoc, $sep="<br>") {
	$html = array();
	ksort($assoc);
	
	foreach($assoc as $key => $val) {
		$html[] = "<span class='loc-total'>" . str_replace("Idaho Behavioral Health ", "", $key) . ": " . $val . "</span>";
	}
	
	return implode($sep, $html);
	
}






function getTailInfo($code, $ct, $colspan=3, $loc_totals = "") {
	$tail_info = ibh_getEncounterCodeInfo($code);
	$tail_mod = $tail_info['mod'];
	$tail_total = $tail_mod * $ct;
	
	/* OLD WITH MORE SPELLED OUT
	return array("html"=>"<tr class='appt-info appt-tail'><td colspan=4 class='tail-1' style='text-align:right;padding-right:6px'>" .$loc_totals . "<span class='appt-tail-labels'>total:</span></td><td colspan=2 class='tail-2' >"  . $ct . " <span class='appt-tail-labels'>(units)</span> x " . $tail_mod . " <span class='appt-tail-labels'>(mod)</span><span class='total-hours'>" . $tail_total . " hours</span></td></tr>
	<tr style='display:none'><td colspan=" . $colspan . ">&nbsp;</td></tr>", "total_hours"=>$tail_total);
	*/
	
	// new bare bones for excel export
	return array("html"=>"<tr class='noExl'><td colspan=" . $colspan . ">" . $loc_totals . "</td></tr><tr class='appt-info appt-tail'><td></td><td></td><td></td><td></td><td></td><td class='tail-1'><span class='appt-tail-labels'>total:</span></td><td class='tail-2' >"  . $ct . "<td>" . $tail_total . "</td></tr><tr style='display:none'><td colspan=" . $colspan . ">&nbsp;</td></tr>", "total_hours"=>$tail_total);

}

// SELECT b.stuff*** ev.pc_facility FROM billing b, openemr_postcalendar_events ev WHERE b.encounter=ev.pc_eid AND ev.pc_facility='$facility_id' AND b.date*** AND b.date*** 



$alertmsg = ''; // not used yet but maybe later
$patient = $_REQUEST['patient'];

$billing_code = $_REQUEST['billing_code'];


if ($patient && ! $_POST['form_from_date']) {
	// If a specific patient, default to 2 years ago.
	$tmp = date('Y') - 2;
	$from_date = date("$tmp-m-d");
} else {
	$from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
	$to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}

$show_available_times = false;
if ( $_POST['form_show_available'] ) {
	$show_available_times = true;
}

$chk_with_out_provider = false;
if ( $_POST['with_out_provider'] ) {
	$chk_with_out_provider = true;
}

$chk_with_out_facility = false;
if ( $_POST['with_out_facility'] ) {
	$chk_with_out_facility = true;
}

//$to_date   = fixDate($_POST['form_to_date'], '');
$provider  = $_POST['form_provider'];
$facility  = $_POST['form_facility'];  //(CHEMED) facility filter
$form_orderby = getComparisonOrder( $_REQUEST['form_orderby'] ) ?  $_REQUEST['form_orderby'] : 'doctor';


?>

<html>

<head><!-- head start -->
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<title><?php // xl('Payroll Report','e'); ?></title>


<link rel="stylesheet" type="text/css" href="../../_ibh/css/appointments_report_payroll.css">


<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="<?= $GLOBALS['assets_static_relative']?>/jquery-min-2-2-0/index.js"></script>


<script type="text/javascript" src="../../library/js/tableToExcel.js"></script>

<script type="text/javascript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function dosort(orderby) {
    var f = document.forms[0];
    f.form_orderby.value = orderby;
    f.submit();
    return false;
 }

 function oldEvt(eventid) {
    dlgopen('../main/calendar/add_edit_event.php?eid=' + eventid, 'blank', 550, 270);
 }

 function refreshme() {
    // location.reload();
    document.forms[0].submit();
 }


</script>

<style type="text/css">

.nav-table {
	border-collapse:collapse;
	
}

.nav-table td{ 
	border:none;
	
}

.loc-total {

	-webkit-border-radius: 3px;
-moz-border-radius: 3px;
border-radius: 3px;

    float: left;
    font-size: 10px;
    font-weight: normal;
    background-color: #ccc;
    padding: 2px 6px;
    margin-right:8px;
}

.bottom .loc-total {

    float: right;
    clear:both;
    font-size: 12px;
    font-weight: bold;
    background-color: #fff;
	margin:4px 0;
	padding:0;
}


@media print {
        #report_parameters {
                visibility: hidden;
                display: none;
        }
        #report_parameters_daterange {
                visibility: visible;
                display: inline;
        }
        #report_results table {
                margin-top: 0px;
        }
}


@media screen {
	#report_parameters_daterange {
		visibility: hidden;
		display: none;
	}
}
</style>
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv"
	style="position: absolute; visibility: hidden; z-index: 1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Payroll','e'); ?></span>
	

		
<div id="report_parameters_daterange"><?php echo date("d F Y", strtotime($from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='appointments_report_payroll.php'>

<div id="report_parameters">

<table>
	<tr>
		<td width='650px'>
		<div style='float: left'>

		<table class='text nav-table'>
			<tr>
				<td class='label'><?php xl('Facility','e'); ?>:</td>
				<td><?php dropdown_facility(strip_escape_custom($facility), 'form_facility'); ?>
				</td>
				<td class='label'><?php xl('Provider','e'); ?>:</td>
				<td><?php

				// Build a drop-down list of providers.
				//

				$query = "SELECT id, lname, fname FROM users WHERE ".
				  "authorized = 1 $provider_facility_filter and active = 1 ORDER BY lname, fname"; //(CHEMED) facility filter

				$ures = sqlStatement($query);

				echo "   <select name='form_provider'>\n";
				echo "    <option value=''>-- " . xl('All') . " --\n";

				while ($urow = sqlFetchArray($ures)) {
					$provid = $urow['id'];
					echo "    <option value='$provid'";
					if ($provid == $_POST['form_provider']) echo " selected";
					echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
				}

				echo "   </select>\n";

				?></td>
				<!--<td><input type='checkbox' name='form_show_available'
					title='<?php //xl('Show Available Times','e'); ?>'
					<?php // if ( $show_available_times ) echo ' checked'; ?>> <?php  //xl( 'Show Available Times','e' ); ?> 
				</td>-->
			</tr>
			<tr><td class='label'>Code:</td><td colspan=3><?=ibh_getPayrollCodePulldown($_REQUEST['billing_code'])?></td></tr>
			<tr>
				<td class='label'><?php xl('From','e'); ?>:</td>
				<td><input type='text' name='form_from_date' id="form_from_date"
					size='10' value='<?php echo $from_date ?>'
					onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
					title='yyyy-mm-dd'> <img src='../pic/show_calendar.gif'
					align='absbottom' width='24' height='22' id='img_from_date'
					border='0' alt='[?]' style='cursor: pointer'
					title='<?php xl('Click here to choose a date','e'); ?>'></td>
				<td class='label'><?php xl('To','e'); ?>:</td>
				<td><input type='text' name='form_to_date' id="form_to_date"
					size='10' value='<?php echo $to_date ?>'
					onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
					title='yyyy-mm-dd'> <img src='../pic/show_calendar.gif'
					align='absbottom' width='24' height='22' id='img_to_date'
					border='0' alt='[?]' style='cursor: pointer'
					title='<?php xl('Click here to choose a date','e'); ?>'></td>
			</tr>
			<tr><td>&nbsp;</td>
			<?php
			$nb_checked = $_POST['display_nonbillables']=="yes" ? "checked='checked'" : "checked='foo'";	
				?>
				<td colspan=3><input name="display_nonbillables" type="checkbox" value="yes" <?php echo $nb_checked; ?>> Display non-billable encounters?</td>
			
		</table>

		</div>

		</td>
		<td align='left' valign='middle' height="100%">
		<table style='border-left: 1px solid; width: 100%; height: 100%'>
			<tr>
				<td>
				<div style='margin-left: 15px'>
                                <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
				<span> <?php xl('Submit','e'); ?> </span> </a> 
                                <?php if ($_POST['form_refresh'] || $_POST['form_orderby'] ) { ?>
				<a href='#' class='css_button' onclick='window.print()'> 
                                    <span> <?php xl('Print','e'); ?> </span> </a> 
                                    <input type="button" onclick="tableToExcel('testTable', 'W3C Example Table')" value="Export to Excel">
                               <!-- <a href='#' class='css_button' onclick='window.open("../patient_file/printed_fee_sheet.php?fill=2","_blank")'> 
                                    <span> <?php xl('Superbills','e'); ?> </span> </a> -->
                                <?php } ?></div>
				</td>
			</tr>
                      
		</table>
		</td>
	</tr>
</table>

		
</div>
<!-- end of search parameters -->

<?php



if ($_POST['form_refresh'] || $_POST['form_orderby']) { ?>
<div id="report_results">
	<!-- IBH_DEV_CHG -->

	<?php
	

	$appts = ibh_getBillingData($from_date, $to_date, $provider, $facility, $billing_code);
	
	$current_code = "";
	$type_count = 0;
	$show_totals_row = false;
	$colspan = 8;
	$display = false;
	$section = 0;
	$total_hours = 0;
	$gti = array();
	
	$loc_totals = array();
	$code_loc_totals = array();
	
	?>
	<div class="table-wrap group" data-open="0">
		<table class="table2excel expando-table" data-tableName="Test Table 1">
		<tbody>
	
	<?php
	
	$header = true;
		
	foreach ( $appts as $appointment ) {
		
		$patient_id = $appointment['pid'];
		
		// problematic:
		// Peer Support : H0038
		// and
		// Peer Support : H0046
		// both coming in with code_text of "Peer Support"

		
		// THE END
		if ($current_code && $current_code != $appointment['code_text']) {
			// echo the count row, reset the count
			$check_code_tail = ibh_getEncounterCodeInfo($current_code);
		
			
			if ($check_code_tail['exists']) {
				
				$loc_tot = ibh_get_location_totals($code_loc_totals, " &nbsp; ");
				
				$gti = getTailInfo($current_code, $type_ct, $colspan, $loc_tot);
				echo $gti['html'];
				$total_hours += $gti['total_hours'];
			}
			$type_ct = 0;
		} 
		
		$check_code = ibh_getEncounterCodeInfo($appointment['code_text']);
		
		// BEGINNING: code has changed since last record, or from null
		if ($current_code != $appointment['code_text']) {

			$section++;
			
			if ($header) {
				$head_class = "";
			} else {
				// $head_class = "noExl";
			}
			
			if ($check_code["exists"]) {
				
				// HEADER FOR SPECIFIC ENCOUNTERS
				// exclude class .noExl
				echo "<tr id='" . $appointment['code'] . "' class='appt-info appt-head noExl'><td colspan='" . $colspan . "'>"  . $appointment['code_text'] . " (" . $appointment['code'] . ")<div data-open='0' data-section='.section-" . $section . "' class='toggle-details'>show details</div></td></tr>
				<tr class='section-" . $section . " details-row details-header $head_class'><td>code</td><td>date</td><td style='width:90px'>encounter</td><td>provider</td><td>patient</td><td>location</td><td>units</td><td>hours</td></tr>";
				
				$display = true;
				$header = false;
				
			} else {
				
				$display = false;
				
				// other types of billing events that aren't in the
				// official list
				$table_extras .= "<tr id='" . $appointment['code'] . "' class='noExl nonbillable appt-info appt-head'><td colspan='" . $colspan . "'>"  . $appointment['code_text'] . " (" . $appointment['code'] . ")<div data-open='0' data-section='.section-" . $section . "' class='toggle-details'>show details</div></td></tr>
				<tr class='section-" . $section . " details-row details-header'><td>code</td><td>date</td><td>encounter</td><td>provider</td><td>patient</td><td>location</td><td>units</td><td>hours</td></tr>";
				
				
				
			}
	
			$current_code = $appointment['code_text'];
			$new_code = true;
		} else {
			$new_code = false;
		}
		
		

        array_push($pid_list,$appointment['pid']);
        
        $docname  = $appointment['ulname'] . ', ' . $appointment['ufname'] . ' ' . $appointment['umname'];
        $mname = $appointment['mname'] ? " " . $appointment['mname'] . " " : " ";
        $patient_name = $appointment['fname']. $mname . $appointment['lname'];
        
        $actual_appt = ibh_get_encounter_times($appointment['encounter']);
        
        $formatted_date = date("Y-m-d", strtotime($actual_appt['pc_eventDate']));
        
		$code_code = $appointment['code'];
		
		if ($display) {
			
			
			
			$unit_ct = $appointment['units'];
			// count units!
			$appt_hrs = (int) $unit_ct * $check_code['mod'];
			
			if ($new_code) {
				$code_loc_totals = array();
			}
			
			$facil = ibh_get_facility_name($appointment['enc_facility_id']);
			
			if (array_key_exists($facil, $code_loc_totals)) {
				$code_loc_totals[$facil] +=  $appt_hrs;
			} else {
				$code_loc_totals[$facil] = $appt_hrs;
			}
			
			if (array_key_exists($facil, $loc_totals)) {
				$loc_totals[$facil] +=  $appt_hrs;
			} else {
				$loc_totals[$facil] = $appt_hrs;
			}
			
			
			
			
			$type_ct += $unit_ct;
			
			$date_formatted = date("m/d/y", strtotime($appointment['date']));
			
			?>
			<tr class="details-row section-<?=$section?>">
				<td class="detail billing-code"><?=$code_code?></td>
	        	<td class="detail billing-date"><?=$formatted_date?></td>
	        	
	        	<td><a class="encounter-setter" data-date="<?=$date_formatted?>" data-enc="<?=$appointment['encounter']?>" href="<?=  $GLOBALS['webroot'] ?>/interface/patient_file/encounter/forms.php?&pid=<?=$appointment['pid']?>&set_encounter=<?=$appointment['encounter']?>" target="RTop"><?=$appointment['encounter']?></a></td>
	        	
	        	<td class="provider"><?=$docname?></td>
	
				<td class="detail patient-name"><?=$patient_name?></td><td><?=str_replace("Idaho Behavioral Health ", "", $appointment['enc_facility'])?></td>
				<td class="units"><?=$appointment['units']?></td>
				
				<td>&nbsp;</td>
	
			</tr>
			<?php
	 
		$lastdocname = $docname;
	
		} else { 
			if ($appointment['code_text']) {
				$table_extras .= '<tr class="details-row-non-bill">
					<td class="detail">' . $code_code . '</td>
		        	<td class="detail">' . $formatted_date . '</td>
		        	<td>' . $appointment['encounter'] . '</td>
		        	<td>' .  $docname . '</td>
					<td class="detail patient-name">' . $patient_name. '</td><td>' .   $appointment['enc_facility']. '</td>
					<td>' . $appointment['units'] . '</td>
					
					<td>&nbsp;</td>
		
				</tr>';
			}
			
		}

	
	}  //end of foreach loop
	
	
	if ($display) {
		
		$loc_tot = ibh_get_location_totals($code_loc_totals, " &nbsp; ");
		
		$gti = getTailInfo($current_code, $type_ct, $colspan, $loc_tot);
		echo $gti['html'];
		$total_hours += $gti['total_hours'];
				
	}
	
	echo "<tr class='grand-total'><td colspan=" . $colspan . ">GRAND TOTAL: " . $total_hours . " HOURS</td></tr>";
	
	echo "<tr class='grand-total'><td colspan=" . $colspan . "><span class='appt-tail-labels'>BY LOCATION:</span><div class='bottom'>" . ibh_get_location_totals($loc_totals) . "</div></td></tr>";

	// assign the session key with the $pid_list array - note array might be empty -- handle on the printed_fee_sheet.php page.
    $_SESSION['pidList'] = $pid_list;
	
	
	if ($_POST['display_nonbillables'] == "yes") {
		echo "<tr><td colspan=" . $colspan . " class='nonbillables-header'>other encounters:</td></tr>";
		echo $table_extras;
	}
	
	?>
			</tbody>
			</table>
	</div>
</div>

<!-- end of search results --> <?php } else { ?>
<div class='text'><?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>


</div>
	<?php } ?>
	
	
	
	<input type="hidden" name="form_orderby"
	value="<?php echo $form_orderby ?>" /> <input type="hidden"
	name="patient" value="<?php echo $patient ?>" /> <input type='hidden'
	name='form_refresh' id='form_refresh' value='' /></form>

<script type="text/javascript">

	var tableToExcel = 
			(function() {
				$(".table2excel").table2excel({
					exclude: ".noExl",
					name: "Payroll Report",
					filename: "Payroll-Report",
					exclude_img: true,
					exclude_links: true,
					exclude_inputs: true
				});
			});
			
<?php
if ($alertmsg) { echo " alert('$alertmsg');\n"; }
?>


</script>

</body>

<!-- stuff for the popup calendar -->
<style type="text/css">
    @import url(../../library/dynarch_calendar.css);
</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript"
	src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

</script>

<script type="text/javascript">
		var $ = jQuery;
		// console.log("jquery:", $);
		
		$(".toggle-details").on("click", function() {
			var bt = $(this);
			var sec = bt.data("section");
			var ope = bt.data("open");
			
			console.log("section", sec);
						
			if (ope == "1") {
				$(sec).hide();
				bt.data("open", "0");
				bt.text("show details");
			} else {
				$(sec).show();
				bt.data("open", "1");
				bt.text("hide details");
			}
			
			
		});
		
</script>



</html>
