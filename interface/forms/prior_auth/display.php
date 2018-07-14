<?php

// Copyright (C) 2016 by following authors:
//  Sherwin Gaddis <sherwingaddis@gmail.com>
//  
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
// forms/prior_auth/display.php?pid=&pa_id=

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once("../../globals.php");
require_once("$srcdir/forms.inc");

// IBH_DEV_CHG
require_once("../../../_ibh/ibh_functions.php");

// global $pid;
// We're NOT using the global $pid variable here
// but bypassing it with a local $_pid var that 
// can be set with the $_GET url argument

$top_mssg = "";

$authUser = $_SESSION['authUser'];


if (isset($_GET['pid'])) {
  $_pid = $_GET['pid'];
}

if (isset($_POST['pid'])) {
  $_pid = $_POST['pid'];
}
 
$patient = ibh_get_patient($_pid);



if ($_GET['action'] == "delete") { 
	$pa_num = $_GET['pa'];
	
	// $sql = "UPDATE form_prior_auth SET archived=1 WHERE prior_auth_number=?";
	
	// $data = array($pa_num);
	
	// $stmt = sqlStatement($sql, $data);
	
	$top_mssg .= "NO DELETING FOR NOW"; // "That Prior Auth (" . $pa_num . ") was 'deleted' (archived)";
	
}


if (isset($_POST['editing'])) {
	
	$pa_id = $_POST['id'];
	$date = date("Y-m-d H:i:s");
	
	$prior_auth_number = $_POST['prior_auth_number'];
	$units = $_POST['units'];
	$auth_contact = $_POST['auth_contact'];
	$auth_phone = $_POST['auth_phone'];
	$code1 = $_POST['code1'];
	$code2 = $_POST['code2'];
	$code3 = $_POST['code3'];
	$code4 = $_POST['code4'];
	$code5 = $_POST['code5'];
	$code6 = $_POST['code6'];
	$code7 = $_POST['code7'];
	
	$alert_days = $_POST['alert_days'];
	$alert_units = $_POST['alert_units'];
	
	// DEFAULTS
	$activity = 1;
	$auth_length = 0;
	$dollar = 0;
	$auth_for = 333;
	$posted_pid = $_POST['pid'];
	
	$override = 1; // $_POST['override'] == "1"? 1:0;
	$archived = 0; // $_POST['archived'] == "1"? 1:0;
	$auth_number_required = $_POST['auth_number_required'] == "1"? 1:0;
	
	$auth_from = $_POST['auth_from'];
	$auth_to = $_POST['auth_to'];
	// $auth_for = $_POST['auth_for']; // days
	
	$desc = $_POST['desc'];
	$comments = $_POST['comments'];
	
	$unit_adjustment = $_POST['unit_adjustment'];
	
	
	$alerts_to = implode(",", $_POST['alerts_to']);
	// $top_mssg .= "<br>" . $alerts_to . "<br>";
	
	
	if ($_POST['id'] == "new") {
		$sql = "INSERT INTO form_prior_auth (pid, activity, date, prior_auth_number, auth_number_required, comments, description, auth_for, auth_from, auth_to, units, auth_length, dollar, auth_contact, auth_phone, code1, code2, code3, code4, code5, code6, code7, archived, override, alerts_to, alert_units, alert_days, unit_adjustment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		
		$data = array($posted_pid, $activity, $date, $prior_auth_number, $auth_number_required, $comments, $desc, $auth_for, $auth_from, $auth_to, $units, $auth_length, $dollar, $auth_contact, $auth_phone, $code1, $code2, $code3, $code4, $code5, $code6, $code7, $archived, $override, $alerts_to, $alert_units, $alert_days, $unit_adjustment);
		
		$stmt = sqlStatement($sql, $data);
		
		$top_mssg .= "New Prior Auth Created: $prior_auth_number";
		
		
	} else {

	$sql = "UPDATE form_prior_auth SET auth_from=?, auth_to=?, auth_for=?, auth_phone=?, auth_contact=?, description=?, comments=?, code1=?, code2=?, code3=?, code4=?, code5=?, code6=?, code7=?, prior_auth_number=?, units=?, override='$override', archived='$archived', alerts_to='$alerts_to', auth_number_required='$auth_number_required', alert_units=?, alert_days=?, unit_adjustment=? WHERE id=?";
	
	$data = array($auth_from, $auth_to, $auth_for, $auth_phone, $auth_contact, $desc, $comments, $code1, $code2, $code3, $code4, $code5, $code6, $code7, $prior_auth_number, $units, $alert_units, $alert_days, $unit_adjustment, $pa_id);
	
	$stmt = sqlStatement($sql, $data);
	
	$top_mssg .= "Prior Auth Updated: $prior_auth_number";
	
	
	}
	
}

$pan_filter = isset($_GET['prior_auth_number']) ? $_GET['prior_auth_number'] : "";

$show_archived = isset($_GET['show_archived']) ? false : true; // reverses logic: false here means "show only valid ones"

$prior_auths = ibh_get_patient_prior_auths($_pid, $show_archived, $pan_filter);

?><html>
<head>
<title>Prior Auths Editing</title>

<?php html_header_show();?>'
	<script type="text/javascript" src="/library/js/jquery-1.10.1.js"></script>
	<script type="text/javascript" src="/library/js/ajtooltip.js"></script>

	<style type="text/css">@import url( /library/dynarch_calendar.css);</style>
	<script type="text/javascript" src=" /library/dynarch_calendar.js"></script>
	<script type="text/javascript" src=" /library/dynarch_calendar_en.js"></script>
	<script type="text/javascript" src=" /library/dynarch_calendar_setup.js"></script>
	<script type="text/javascript" src=" /library/textformat.js"></script>


<link rel="stylesheet" href="/interface/themes/style_metal.css" type="text/css">
<link rel="stylesheet" href="/_ibh/css/encounter.css" type="text/css">

</head>
<body class="overview-pane">
<div class="ibh-wrapper">
		
<h4>Prior Auth for <?=$patient['fname'] . " " . $patient['lname']?></h4>
<?php if ($top_mssg) { ?>
		<div class='top-message'><?=$top_mssg?></div>
<?php	} ?>
<ul class="ibh-top-buttons">
	<li><a href="../../../_ibh/interface/prior_auths_overview.php">ALL PRIOR AUTHS</a></li>
	<li><a href="display.php?pid=<?=$_pid?>">PATIENT LIST (active)</a> &nbsp;&nbsp;&nbsp;&nbsp; <a href="display.php?pid=<?=$_pid?>&show_archived=true" style='color:#777'>(include archived)</a></li>
	<li><a href="display.php?action=new&pid=<?=$_pid?>">CREATE A NEW PA FOR <?=$patient['fname'] . " " . $patient['lname']?></a></li>
	
</ul>

<?php if ($_GET['action'] == "edit" || $_GET['action'] == "new") { 
	
	$pa = array();
	
	
	if ($_GET['action'] == "edit") {
		// we need to get the data on the Prior Auth
		$pa_id = $_GET['pa_id'];
		$pa = ibh_get_prior_auth($pa_id);
	} else {
		// it's new, things might be default...
		$pa_id = "new";
	}
	
	
	$an_placeholder = "";
	
	if ($pa['auth_number_required'] == 1 || $pa['prior_auth_number']) {
		$an_checked = "checked";
		$an_no_checked = "";
		$an_no_style = "";
		$an_placeholder = $pa['prior_auth_number'];
		
	} else {
		$an_checked = "";
		$an_no_checked = "checked";
		$an_no_style = "display:none";
	}

	$so_checked = $pa['override'] != 1 ? "": "checked";
	$ar_checked = $pa['archived'] != 1 ? "": "checked";
	
	if ($pa_id == "new") { 
		$an_checked = "checked";
		?><h4 class="new-item">New!</h4><?php
	} else { 
		// editing, if there's an existing code, call it required
		if ($pa['prior_auth_number']) $an_checked = "checked";
	}
	
	?>
	<br>

	<form name="prior_auth" method="post" action="display.php">
		<input type="hidden" name="id" value="<?=$pa_id?>">
		<input type="hidden" name="pid" value="<?=$_pid?>">
		<input type="hidden" name="editing" value="1">
<table class="ibh-form-table">

<tr>
    <td>Is the Auth ID # Required?</td>
    <td>
	    <input type="radio" name="auth_number_required" value="1" id="authReq" <?=$an_checked?>>
		<label for="authReq">Yes</label>
		
		<input type="radio" name="auth_number_required" value="0" id="noAuthReq" <?=$an_no_checked?>>
		<label for="noAuthReq">No</label>
	</td>
</tr>

<tr id="prior_auth_number_row" style="<?=$an_no_style?>">
	<td>Auth ID #:</td>
	<td><input type="text" size="35" name="prior_auth_number" id="prior_auth_number" placeholder="<?=$an_placeholder?>" value="<?=$pa['prior_auth_number']?>"></td>
</tr>


<tr class="auth-units">
	
    <td>Initial Units/Sessions:</td><td><input type="text" size="5" name="units" id="units" value="<?=$pa['units']?>"> <small>(used: -<?=$pa['bills'] . " billed units and " . $pa['unit_adjustment'] . " adj. = " . ((-1 * $pa['bills']) + $pa['unit_adjustment'])?>) remaining: <?=$pa['units_remaining']?></small></td>
</tr>
<tr class="auth-units">
	<td>Unit Adjustment:</td><td>
		<input type="text" size="5" name="unit_adjustment"  value="<?=$pa['unit_adjustment']?>"/> <small>...</small>
    </td>
</tr>
<tr class="auth-units">
	<td>Alert @ Units Used:</td><td>
		<input type="text" size="5" name="alert_units"  value="<?=$pa['alert_units']?>"/> <small>(@ units remaining)</small>
    </td>
</tr>


<tr class="auth-days">
    <td>Auth Length:</td>
    <td><label>From: </label>
	   <input type='text' size='10' name="auth_from" id="auth_from"
    value="<?=$pa['auth_from']?>"
	title="yyyy-mm-dd"/>
   <img src="../../pic/show_calendar.gif" align="absbottom" width="24" height="22"
    id="img_auth_from" border="0" alt="[?]" style="cursor:pointer;cursor:hand"
	title="Click here to choose a date"/>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<label>To: </label>
	   <input type='text' size='10' name='auth_to' id="auth_to"
    value="<?=$pa['auth_to']?>"
	title="yyyy-mm-dd"/>
   <img src="../../pic/show_calendar.gif" align="absbottom" width="24" height="22"
    id="img_auth_to" border="0" alt="[?]" style="cursor:pointer;cursor:hand"
	title="Click here to choose a date"/>

    </td>
</tr>
<tr class="auth-days">
	<td>Alert @ Days Out:</td><td>
		<input type="text" size="5" name="alert_days" value="<?=$pa['alert_days']?>" placeholder="days"/> <small>(days prior to end-date)</small>
    </td>
</tr>


<tr>
    <td>Description:</td>
    <td><input type="text" size="55" name="desc" id="description" value="<?=$pa['description']?>"></td>
</tr>

<tr>
    <td>Auth Contact:</td>
    <td><input type="text" size="25" name="auth_contact" value="<?=$pa['auth_contact']?>"></td>
</tr>


<tr>
    <td>Auth Phone:</td><td> <input type="text" size="15" name="auth_phone" value="<?=$pa['auth_phone']?>">  </td>
</tr>
<tr>
    <td>Code:</td><td> 
	<input type="text" size="5" name="code1" value="<?=$pa['code1']?>">
	<input type="text" size="5" name="code2" value="<?=$pa['code2']?>">
	<input type="text" size="5" name="code3" value="<?=$pa['code3']?>">
	<input type="text" size="5" name="code4" value="<?=$pa['code4']?>">
	<input type="text" size="5" name="code5" value="<?=$pa['code5']?>">
	<input type="text" size="5" name="code6" value="<?=$pa['code6']?>">
	<input type="text" size="5" name="code7" value="<?=$pa['code7']?>">
	</td>

</tr>


<tr>
	<td>Comments:</td><td colspan="2"><textarea name="comments" value="" cols="75" rows="8"><?=$pa['comments']?></textarea></td>
</tr>

<tr>
	<td>Send Alerts To:</td><td>
		
		<?php 
			$alerts_to_arr = explode(",", $pa['alerts_to']);
			
			$my_sel = in_array($_SESSION['authId'], $alerts_to_arr) ? "selected": "";
			//echo "<br>in db alerts to: " . $pa['alerts_to'];
			//echo "<br>session authId: " . $_SESSION['authId'];
			?>


		<select style="width:100%; height:200px;" id="sendTo" name="alerts_to[]" multiple="multiple">
			
                <option value="<?php echo attr(intval($_SESSION['authId'])); ?>" <?=$my_sel?>><?php echo xlt('Myself') ?></option>
            <?php   
 
			$uSQL = sqlStatement('SELECT id, fname,	mname, lname  FROM  `users` WHERE  `authorized` = 1 AND `facility_id` > 0 AND id != ? ORDER BY lname, fname', array(intval($_SESSION['authId'])));

		    for($i=2; $uRow=sqlFetchArray($uSQL); $i++){ 
			    $sel = in_array($uRow['id'], $alerts_to_arr) ? "selected": "";
		        echo '<option value="',attr($uRow['id']),'" ' . $sel . '>',text($uRow['lname'].', '.$uRow['fname'].' '.$uRow['mname']),'</option>';  
		    }
?>      
              </select>
    </td>
</tr>
<!-- 
<tr>
	<td>Supervisor Override:</td><td><input type="checkbox" id="override_cb" name="override" value="1" <?=$so_checked?>/><label for="override_cb">Yes</label></td>
</tr>
<tr>
	<td>Archive this Prior Auth?</td><td><input type="checkbox" id="archive_cb" name="archived" value="1" <?=$ar_checked?>/><label for="archive_cb">Yes</label></td>
</tr>
-->
<tr>
	<td>&nbsp;</td>
	<td class="field-display-submit">
		
		<input type="submit" name="Submit" value="Save" id="save_pa">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<a href="display.php?pid=<?=$_pid?>">cancel</a>
		
		
		
		<?php if(getSupervisor($authUser) == 'Supervisor' || getSupervisor($authUser) == 'Supervisor:'){ ?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<a id="deleter" href="display.php?pid=<?=$_pid?>&pa=<?=$pa['prior_auth_number']?>&action=delete">delete</a>
		<?php } ?>
		
		</td>
		
</tr>
</table>
		
		

</form>



<?php } else { 
	
	// NOT EDITING:: VIEWING
	
	$bills_claimed = array();
	$expired = false;
	
	if (count($prior_auths) == 0) {  ?>
		<div class="top-message">There are no active prior auths for this patient</div>
		
	<?php 
	}

	foreach ($prior_auths as $auth){ 
		
		
	
		$auth_required_word = "";
		
		if ($auth['auth_number_required'] == 1 || $auth['prior_auth_number']) {
			$auth_required_word = "Yes";
		} else {
			$auth_required_word = "No";
		}
		
		$override_word = $auth['override'] == 1 ? "Yes": "No";
		// $archived_word = $auth['archived'] == 1 ? "Yes": "No";
		
		$codes_array_loaded = array($auth['code1'], $auth['code2'], $auth['code3'], $auth['code4'], $auth['code5'], $auth['code6'],$auth['code7']);
		
		$actual_codes = array();
		foreach ($codes_array_loaded as $pa_code) {
			if ($pa_code) $actual_codes[] = $pa_code;
		}
		
		$auth_date = date("F j, Y", strtotime($auth['date']));
		
		$editable = true;
		
		if (strtotime($auth['auth_to'] . " 23:59:59") < time()) {
			$expired_class = " auth-expired";
			$arch_label = "--EXPIRED--";
			$editable = false;
		} else {
			$expired_class = "";
			$arch_label = "";
		}
		
		if ($auth['archived'] == 1) {
			$archived_class = " auth-archived";
			$arch_label .= "--ARCHIVED--";
			$editable = false;
		} else {
			$archived_class = "";
			
		}
		
?>
	<div class="prior-auth-wrapper<?=$expired_class?><?=$archived_class?>">
		<h2><?=$arch_label?> Prior Auth #: <?=$auth['prior_auth_number']?> &nbsp; created: <?=$auth_date?></h2>
	<div class="prior-auth-display fields">
		<div class="field-display">
			<label>Date Created:</label>
			<div class="field-display-value"><?=$auth['date']?></div>
		</div>
		<div class="field-display">
			<label>Auth # Required?:</label><div class="field-display-value"><?= $auth_required_word?></div>
		</div>
		
		
		<?php if ($auth_required_word == "Yes") { ?>
		<div class="field-display">
			<label>Auth #:</label>
			<div class="field-display-value"><?=$auth['prior_auth_number']?></div>
		</div>
		<?php } ?>
		
		
		<div class="field-display auth-units">
			<label>Allotted Units:</label>
			<div class="field-display-value"><?php echo $auth['units']; ?> <span class='lighter'>(<?=$auth['units_remaining']; ?> remaining)</span></div>
		</div>
		
		<div class="field-display auth-units">
			<label>Unit Adjustment</label>
			<div class="field-display-value"><?php echo $auth['unit_adjustment']; ?></div>
		</div>
		
		<div class="field-display auth-units">
			<label>Alert @ Units Left:</label>
			<div class="field-display-value"><?php echo $auth['alert_units']; ?></div>
		</div>
		
		
		
		<div class="field-display auth-days">
			<label>Date Range:</label>
			<div class="field-display-value"><?php echo $auth['auth_from']." &nbsp;&ndash;&nbsp; ".$auth['auth_to']; ?><br></div>
		</div>
		<div class="field-display auth-days">
			<label>Days Remaining:</label>
			<div class="field-display-value"><?=$auth['days_remaining']?> days</div>
		</div>
		
		
		<div class="field-display auth-days">
			<label>Alert @ Days Out:</label>
			<div class="field-display-value"><?php echo $auth['alert_days']; ?></div>
		</div>
		
		
		
		
		<div class="field-display">
			<label>Description:</label>
			<div class="field-display-value"><?php echo $auth['description']; ?></div>
		</div>
		
		<div class="field-display">
			<label>Comments:</label>
			<div class="field-display-value"><?php echo $auth['comments']; ?></div>
		</div>
		
		
		
		
		<div class="field-display">
			<label>Auth Contact:</label>
			<div class="field-display-value"><?php echo $auth['auth_contact']; ?></div>
		</div>
		<div class="field-display">
			<label>Auth Phone:</label>
			<div class="field-display-value"><?php echo $auth['auth_phone']; ?></div>
		</div>
		
		<div class="field-display">
			<label>Codes:</label>
			<div class="field-display-value"><?php echo $auth['code1']. " " . $auth['code2']. " " .$auth['code3']. " ". $auth['code4']. " " . $auth['code5']. " ". $auth['code6']. " ". $auth['code7']; ?></div>
		</div>
		
		<div class="field-display">
			<label>Auth Alerts:</label>
			<div class="field-display-value">
				<?php echo implode(", ", ibh_get_user_names_by_ids($auth['alerts_to'])); ?>
			</div>
		</div>
		<!-- 
		<div class="field-display">
			<label>Supervisor Override?:</label>
			<div class="field-display-value"><?=$override_word?></div>
		</div>
		
		<div class="field-display">
			<label>Archived?:</label>
			<div class="field-display-value"><?=$archived_word?></div>
		</div>
		-->
		<?php 
		if(getSupervisor($authUser) == "Supervisor" || getSupervisor($authUser) == "Supervisor:"){
			$editable = true;
		}	
			
		if($editable){ ?>
		<div class="field-display field-display-submit">
<a href="display.php?action=edit&pid=<?=$_pid?>&pa_id=<?php echo $auth['id']; ?>" id="edit"><button>Edit</button></a>
		</div>
		<?php } ?>

		<div class="prior-auth-billing group"><h4>applicable bills</h4>
		<?php
		foreach ($auth['codes'] as $billing_code) {
				
				$auth_from_adj = $auth['auth_from'] . " 00:00:00";
				$auth_to_adj = $auth['auth_to'] . " 23:59:59";
		        $billsq = sqlStatement("SELECT * FROM billing WHERE pid='$_pid' AND code='$billing_code' AND (date >='$auth_from_adj' AND date < '$auth_to_adj')");
		        while($bill = sqlFetchArray($billsq)) {
	
				    if (!in_array($bill['id'], $bills_claimed)) {
					    echo "<div class='pa-billing-bill'><small>" . $bill['date'] . "</small><br><em>" . $bill['code_text'] . "</em> <br>code: " . $bill['code'] . "<br>encounter: " . $bill['encounter'] . "<br>units: " . $bill['units'] . "</div>";
					    
			        } // end if it's NOT claimed
		        
		        } 	
				
			}	
		?>
		</div>
		
	
	</div>
	</div>
		
	
	
</br>



<?php } ?>


<?php } // end if=else edit/new ?>

</div><!-- end ibh-wrapper -->


<script language="javascript">
/* required for popup calendar */


$(document).ready(function(){
	
	function no_dash(n) {
		return n.replace(/-/g, "");
	}
	
	function validDate(d) {
		
		var darr = d.split("-");
		var today = new Date();
		var this_year = today.getFullYear();
		var too_far = this_year + 2;
		var field_yr = darr[0];
		var field_mo = darr[1];
		
		console.log(too_far);
		
		if (!field_mo || field_mo > 12 || field_mo < 1) {
			return false;
		}
		
		if (!field_yr || field_yr > too_far || field_yr < this_year-2 ) {
			return false;
		}
		
		
		return true;
	}
	
	$("#save_pa").click(function() {
		
		var mssg = "";
	
		var auth_from_date = $("#auth_from").val();
		var auth_to_date = $("#auth_to").val();
		
		
	    var auth_from = Number(no_dash(auth_from_date)); // get the auth from date
	    var auth_to = Number(no_dash(auth_to_date));
		
		if (!validDate(auth_from_date) || !validDate(auth_to_date) ) {
			mssg += "Dates are invalid!\n";
		}
		
	    if (auth_from > auth_to) {
	        mssg += "The FROM date is after the TO date\n";
	    }
	    
	    var units = $("#units").val();
	    if (!units || units < 1) {
		    mssg += "We need some units! It ain't a prior auth without some units!\n";
	    }
	    
	    
	    var req = $(':radio[name="auth_number_required"]:checked').val();
	    if (req == 1) {
		    pan = $("#prior_auth_number").val();
		    if (!pan || pan.length < 5) {
			    mssg += "The Auth # is required, so we'll need that.\n";
		    }
	    } else {
		    var desc = $("#description").val();
		    if (!desc) {
			    mssg += "Since there's no Auth #, how about a short description?";
		    }
	    }
	    	    
	    if (mssg) {
		    
		    alert("Whoops!\n" + mssg);
		    return false;
	    }
		
	});


	$("#deleter").click(function() {
		var conf = confirm("Are you sure you want to delete this Prior Auth? Data will be archived. To fully permanently delete it from the database, have an administrator speak to a developer.");
		
		if (!conf) return false;
		
	});

     
     $(':radio[name="auth_number_required"]').change(function() {
	 	var req = $(this).filter(':checked').val();
	 	
	 		if (req == 1) {
		 		// show field
		 		$("#prior_auth_number_row").show();
	 		} else {
		 		// hide it, clear it
		 		$("#prior_auth_number").val("");
		 		$("#prior_auth_number_row").hide();
	 		}
		});
		
		
		/*Calendar.setup({inputField:"dob", ifFormat:"%Y-%m-%d", button:"img_dob"});*/
		Calendar.setup({inputField:"auth_from", ifFormat:"%Y-%m-%d", button:"img_auth_from"});
		Calendar.setup({inputField:"auth_to", ifFormat:"%Y-%m-%d", button:"img_auth_to"});




});



</script>

</body>

</html>