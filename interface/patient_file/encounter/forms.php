<?php
use ESign\Api;
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/calendar.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/amc.php");
require_once $GLOBALS['srcdir'].'/ESign/Api.php';
require_once("$srcdir/../controllers/C_Document.class.php");
require_once("forms_review_header.php"); //added

// IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");



$hide_alert = filter_input(INPUT_GET, 'hi', FILTER_VALIDATE_INT);
$ct = filter_input(INPUT_GET, 'ct', FILTER_VALIDATE_INT);
$sup_alerted1 = filter_input(INPUT_GET, 's1');

$authUser = $_SESSION['authUser'];


$user_acl_names=acl_get_group_titles($authUser);
$user_is_physician = in_array("Physicians", $user_acl_names) ? true: false;




// IBF_DEV
// ADD SECTION TO ALLOW GET URLS
if (isset($_GET['pid'])) {
    $pid = $_GET['pid'];
}

$encounter = $_SESSION['encounter'];


if (isset($_GET['set_encounter'])) {
    $_SESSION['encounter'] = $encounter = $_GET['set_encounter'];
}

$printing = false;
if (isset($_GET['printing'])) {
    $printing = true;
}


$comments_submitted = false;
$encounter_info = ibh_get_encounter_info($encounter);
$encounter_forms = ibh_getEncounterForms($encounter);
$num_forms = $encounter_forms['lbf_count'];

$provider_id = $encounter_info['provider_id'];
$provider_username = $encounter_info['username'];
$user_is_supervisor = $encounter_info['supervisor_username'] == $_SESSION['authUser'];
$user_id = ibh_get_session_user_id();
$einfo = $encounter_info;


if (isset($_POST['supervisor-comments'])) {

    $comments_submitted = true;
    $form_id = $_POST['form_id'];
    $encounter = $_SESSION['encounter'];

    // get form user
    $form_q = sqlStatement("SELECT * FROM forms WHERE encounter='$encounter' AND form_id='$form_id'");
    $form = sqlFetchArray($form_q);

    // WHOOPS
    $uname = $form['user']; // provider who created the form

    // esign_signatures uses forms.id raw ID rather than forms.form_id
    $form_id = $form['id'];

    $supervisor = $_SESSION['authUser'];

    $comments = $_POST['supervisor-comments'];

    // delete provider signature


    // from supervisor reminder
    $date = date("Y-m-d H:m:s");



    $slash_date = $einfo['slash_date'];

    $encounter_link = "<a class='encounter-setter' data-date='" . $slash_date . "' data-enc='" . $encounter . "' href='/openemr/interface/patient_file/encounter/forms.php?pid=" . $einfo['pid'] . "&set_encounter=" . $encounter . "' target='RTop'>Click here to edit the LBF for this encounter.</a>";

    $encounter_message = $date . " (" . $supervisor . " to " . $uname . ")<br>Encounter: " . $encounter . "<br>Comments: " . $comments . "<br>";

    $data = array($encounter_message . "<br>" . $encounter_link);

    $sql = "INSERT INTO `pnotes` (`date`, `body`, `pid`, `user`, `groupname`, `activity`, `authorized`, `title`, `assigned_to`, `message_status`, `encounter`) 
            VALUES ('$date', ?, '$pid', '$supervisor', 'IBH', '1', '1', 'Supervisor Comment', '$uname', 'New', $encounter)";

    sqlStatement($sql, $data);


    $signature_deleted = ibh_delete_encounter_signature($form_id, $encounter, true);

    $sig_deleted_note = $signature_deleted ? "All signatures were deleted for that form.<br>" : "(There was an error: Signatures not deleted.)";

    $sig_deleted_note .= $encounter_message;
}

// END IBH_DEV



?>
<html>

<head>

<?php require $GLOBALS['srcdir'] . '/js/xl/dygraphs.js.php'; ?>

<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="../../../library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.css" media="screen" />
<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>

<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/ESign/css/esign.css" /><!-- added -->
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/modified/dygraphs-2-0-0/dygraph.css" type="text/css"></script><!-- added -->
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/_ibh/css/encounter.css" type="text/css">
<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-7-2/index.js"></script><!-- changed -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js?v=<?php echo $v_js_includes; ?>"></script><!-- changed-->
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>
<script type="text/javascript" src="../../../library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.js"></script>
<script src="<?php echo $GLOBALS['webroot'] ?>/library/ESign/js/jquery.esign.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/modified/dygraphs-2-0-0/dygraph.js?v=<?php echo $v_js_includes; ?>"></script>
</head> <!--this was missing -->
<?php 
$esignApi = new Api();
?>

<?php // if the track_anything form exists, then include the styling and js functions for graphing
if (file_exists(dirname(__FILE__) . "/../../forms/track_anything/style.css")) { ?>
 <script type="text/javascript" src="<?php echo $GLOBALS['web_root']?>/interface/forms/track_anything/report.js"></script>
 <link rel="stylesheet" href="<?php echo $GLOBALS['web_root']?>/interface/forms/track_anything/style.css" type="text/css">
<?php } ?>

<?php //added
// If the user requested attachment of any orphaned procedure orders, do it.
if (!empty($_GET['attachid'])) {
  $attachid = explode(',', $_GET['attachid']);
  foreach ($attachid as $aid) {
    $aid = intval($aid);
    if (!$aid) continue;
    $tmp = sqlQuery("SELECT COUNT(*) AS count FROM procedure_order WHERE " .
      "procedure_order_id = ? AND patient_id = ? AND encounter_id = 0 AND activity = 1",
      array($aid, $pid));
    if (!empty($tmp['count'])) {
      sqlStatement("UPDATE procedure_order SET encounter_id = ? WHERE " .
        "procedure_order_id = ? AND patient_id = ? AND encounter_id = 0 AND activity = 1",
        array($encounter, $aid, $pid));
      addForm($encounter, "Procedure Order", $aid, "procedure_order", $pid, $userauthorized);
    }
  }
}
//end added
?>

<script type="text/javascript">
$.noConflict();
jQuery(document).ready( function($) {

	//Javascript IBH
	function ibhAlert() {

		var alert = $("<div class='alert-box'>This encounter already has 1+ LBF associated with it.<br><div class='alert-close'>Got it!</div></div>").appendTo("body");

	}



	$(".form-picker").click(function() {
		var num_forms = Number(<?php echo $num_forms; ?>);
		if (num_forms == 0) {
			// allow the creation of an LBF
			return true;
		} else {
			// alert("This encounter already has 1+ LBF form associated with it.");

			ibhAlert()


			return false;
		}
	});

	$(".alert-close").live("click", function() {
		$(".alert-box").remove();
	});

	//End Javascript IBH
	var formConfig = <?php echo $esignApi->formConfigToJson(); ?>;
    $(".esign-button-form").esign( 
    	formConfig,
        { 	    
            afterFormSuccess : function( response ) {
                if ( response.locked ) {
                	var editButtonId = "form-edit-button-"+response.formDir+"-"+response.formId;
                    $("#"+editButtonId).replaceWith( response.editButtonHtml );
                }
                
                var logId = "esign-signature-log-"+response.formDir+"-"+response.formId;
                $.post( formConfig.logViewAction, response, function( html ) {
                    $("#"+logId).replaceWith( html );  
                });
            }
		}
    );

    var encounterConfig = <?php echo $esignApi->encounterConfigToJson(); ?>;
    $(".esign-button-encounter").esign( 
    	encounterConfig,
        { 	    
            afterFormSuccess : function( response ) {
                // If the response indicates a locked encounter, replace all 
                // form edit buttons with a "disabled" button, and "disable" left
                // nav visit form links
                if ( response.locked ) {
                    // Lock the form edit buttons
                	$(".form-edit-button").replaceWith( response.editButtonHtml );
                	// Disable the new-form capabilities in left nav
                	top.window.parent.left_nav.syncRadios();
                    // Disable the new-form capabilities in top nav of the encounter
                	$(".encounter-form-category-li").remove();
                }
                
                var logId = "esign-signature-log-encounter-"+response.encounterId;
                $.post( encounterConfig.logViewAction, response, function( html ) {
                    $("#"+logId).replaceWith( html );
                });
            }
		}
    );
    $("a#supervisor").fancybox(); //added by IBH
    $(".onerow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".onerow").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".onerow").click(function() { GotoForm(this); });

    $("#prov_edu_res").click(function() {
        if ( $('#prov_edu_res').attr('checked') ) {
            var mode = "add";
        }
        else {
            var mode = "remove";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "patient_edu_amc",
              complete: true,
              mode: mode,
              patient_id: <?php echo htmlspecialchars($pid,ENT_NOQUOTES); ?>,
              object_category: "form_encounter",
              object_id: <?php echo htmlspecialchars($encounter,ENT_NOQUOTES); ?>
            }
        );
    });

    $("#provide_sum_pat_flag").click(function() {
        if ( $('#provide_sum_pat_flag').attr('checked') ) {
            var mode = "add";
        }
        else {
            var mode = "remove";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "provide_sum_pat_amc",
              complete: true,
              mode: mode,
              patient_id: <?php echo htmlspecialchars($pid,ENT_NOQUOTES); ?>,
              object_category: "form_encounter",
              object_id: <?php echo htmlspecialchars($encounter,ENT_NOQUOTES); ?>
            }
        );
    });

    $("#trans_trand_care").click(function() {
        if ( $('#trans_trand_care').attr('checked') ) {
            var mode = "add";
            // Enable the reconciliation checkbox
            $("#med_reconc_perf").removeAttr("disabled");
	    $("#soc_provided").removeAttr("disabled"); //added
        }
        else {
            var mode = "remove";
            //Disable the reconciliation checkbox (also uncheck it if applicable)
            $("#med_reconc_perf").attr("disabled", true);
            $("#med_reconc_perf").removeAttr("checked");
	    $("#soc_provided").attr("disabled",true);//added
	    $("#soc_provided").removeAttr("checked");//added
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "med_reconc_amc",
              complete: false,
              mode: mode,
              patient_id: <?php echo htmlspecialchars($pid,ENT_NOQUOTES); ?>,
              object_category: "form_encounter",
              object_id: <?php echo htmlspecialchars($encounter,ENT_NOQUOTES); ?>
            }
        );
    });

    $("#med_reconc_perf").click(function() {
        if ( $('#med_reconc_perf').attr('checked') ) {
            var mode = "complete";
        }
        else {
            var mode = "uncomplete";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "med_reconc_amc",
              complete: true,
              mode: mode,
              patient_id: <?php echo htmlspecialchars($pid,ENT_NOQUOTES); ?>,
              object_category: "form_encounter",
              object_id: <?php echo htmlspecialchars($encounter,ENT_NOQUOTES); ?>
            }
        );
    });

    //added
    $("#soc_provided").click(function(){
        if($('#soc_provided').attr('checked')){
                var mode = "soc_provided";
        }
        else{
                var mode = "no_soc_provided";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
                { amc_id: "med_reconc_amc",
                complete: true,
                mode: mode,
                patient_id: <?php echo htmlspecialchars($pid,ENT_NOQUOTES); ?>,
                object_category: "form_encounter",
                object_id: <?php echo htmlspecialchars($encounter,ENT_NOQUOTES); ?>
                }
        );
    });
//end added
    // $(".deleteme").click(function(evt) { deleteme(); evt.stopPropogation(); });

    var GotoForm = function(obj) { //function changed
        var parts = $(obj).attr("id").split("~");
        top.restoreSession();
        parent.location.href = "<?php echo $rootdir; ?>/patient_file/encounter/view_form.php?formname="+parts[0]+"&id="+parts[1];
    }
//added
<?php
  // If the user was not just asked about orphaned orders, build javascript for that.
  if (!isset($_GET['attachid'])) {
    $ares = sqlStatement("SELECT procedure_order_id, date_ordered " .
      "FROM procedure_order WHERE " .
      "patient_id = ? AND encounter_id = 0 AND activity = 1 " .
      "ORDER BY procedure_order_id",
      array($pid));
    echo "  // Ask about attaching orphaned orders to this encounter.\n";
    echo "  var attachid = '';\n";
    while ($arow = sqlFetchArray($ares)) {
      $orderid   = $arow['procedure_order_id'];
      $orderdate = $arow['date_ordered'];
      echo "  if (confirm('" . xls('There is a lab order') . " $orderid " .
        xls('dated') . " $orderdate " .
        xls('for this patient not yet assigned to any encounter.') . " " .
        xls('Assign it to this one?') . "')) attachid += '$orderid,';\n";
    }
    echo "  if (attachid) location.href = 'forms.php?attachid=' + attachid;\n";
  }
?>
//end added
});

 // Process click on Delete link.
 function deleteme() {
  dlgopen('../deleter.php?encounterid=<?php echo $encounter; ?>', '_blank', 500, 450);
  return false;
 }

 // Called by the deleter.php window on a successful delete.
 function imdeleted(EncounterId) { //function changed
  top.window.parent.left_nav.removeOptionSelected(EncounterId);
  top.window.parent.left_nav.clearEncounter();
 }

</script>

<script language="javascript">
function expandcollapse(atr){
	if(atr == "expand") {
		for(i=1;i<15;i++){
			var mydivid="divid_"+i;var myspanid="spanid_"+i;
				var ele = document.getElementById(mydivid);	var text = document.getElementById(myspanid);
				if (typeof(ele) != 'undefined' && ele != null)
					ele.style.display = "block";
				if (typeof(text) != 'undefined' && text != null)
					text.innerHTML = "<?php xl('Collapse','e'); ?>";
		}
  	}
	else {
		for(i=1;i<15;i++){
			var mydivid="divid_"+i;var myspanid="spanid_"+i;
				var ele = document.getElementById(mydivid);	var text = document.getElementById(myspanid);
				if (typeof(ele) != 'undefined' && ele != null)
					ele.style.display = "none";	
				if (typeof(text) != 'undefined' && text != null)
					text.innerHTML = "<?php xl('Expand','e'); ?>";
		}
	}

}

function divtoggle(spanid, divid) {
	var ele = document.getElementById(divid);
	var text = document.getElementById(spanid);
	if(ele.style.display == "block") {
		ele.style.display = "none";
		text.innerHTML = "<?php xl('Expand','e'); ?>";
  	}
	else {
		ele.style.display = "block";
		text.innerHTML = "<?php xl('Collapse','e'); ?>";
	}
}
</script>

<style type="text/css">
#sddm {
	margin-top:-10px;
    }
</style>
<!-- head removed -->
<?php

// This was being included with its own body/html, adding to
// the weird framey freak-fest. We can remove this entirely if
// everything's working.
// require_once("$incdir/patient_file/encounter/new_form.php");
?>
<body class="body_top">
<!--removed the encounter forms div-->
<?php

	// IBH_DEV
	?><div class="hide-from-print"><?php
	require_once("$incdir/patient_file/encounter/encounter_menu.php");
	?></div><?php


	if ($comments_submitted) { ?>

		<div class="top-message">
			<p>Thank you for submitting your comments.<br><?=$sig_deleted_note ?></p>
		</div>

<?php	} else if (isset($_GET['supervisor_review']) && $user_is_supervisor) { ?>
		<!--
		<div class="top-message">
			<p>You are assigned as the supervisor for this encounter: Scroll down to review LBF form for this encounter, and sign if it meets our criteria. If it should not be signed yet, please comment below to send a message to the provider.</p>
		</div>
		-->
<?php	}


?>




<?php
// END IBH_DEV
$dateres = getEncounterDateByEncounter($encounter);
$encounter_date = date("Y-m-d",strtotime($dateres["date"]));
$providerIDres = getProviderIdOfEncounter($encounter);
$providerNameRes = getProviderName($providerIDres);
//<div class='encounter-summary-container'> removed by IBH
//<div class='encounter-summary-column'>
//<div>
//<span class="title"><?php echo oeFormatShortDate($encounter_date) . " " . xl("Encounter"); ?><!-- </span>-->
<?php
//$auth_notes_a  = acl_check('encounters', 'notes_a');
//$auth_notes    = acl_check('encounters', 'notes');
//$auth_relaxed  = acl_check('encounters', 'relaxed') //Removed by IBH

if (is_numeric($pid)) {
    // Check for no access to the patient's squad.

    $patient = getPatientData($pid, "fname,lname,squad");//changed $reult to $patient ??WHY??
    // echo "<br>".$title ." ". htmlspecialchars( xl('-for','',' ',' ') . $result['fname'] . " " . $result['lname'] );
    if ($patient['squad'] && ! acl_check('squads', $patient['squad'])) {
        $auth_notes_a = $auth_notes = $auth_relaxed = 0;
    }
    // Check for no access to the encounter's sensitivity level.
    $result = sqlQuery("SELECT sensitivity FROM form_encounter WHERE " .
                        "pid = '$pid' AND encounter = '$encounter' LIMIT 1");
    if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
        $auth_notes_a = $auth_notes = $auth_relaxed = 0;
    }
}

//Added by IBH
$event = ibh_get_appointment_info($encounter);

	 $format_stamp = strtotime($event['pc_eventDate'] . " " . $event['pc_startTime']);
	 $format_date = date("n/j/Y g:i a", $format_stamp);


if ($event) {
	$date = $format_date;
} else {
	$date = $encounter_info['slash_date'];

}

if (isset($_POST['edit_title'])) {
	echo "<h2 style='text-align:center;color:green'>Title edited!</h2>";

	$post_cat = $_POST['pc_catid'];
	$post_encounter = $_POST['encounter_id'];
	$post_old_billing_code = $_POST['billing_code'];
	$post_old_code_text = $_POST['code_text'];

	sqlStatement("UPDATE form_encounter SET pc_catid=? WHERE encounter=?", array($post_cat, $_POST['encounter_id']));


	$catq = sqlStatement("SELECT pc_catname FROM openemr_postcalendar_categories WHERE pc_catid='$post_cat'");
	$cat_name = sqlFetchArray($catq);
	$encounter_cat_name = $cat_name['pc_catname'];

	sqlStatement("UPDATE openemr_postcalendar_events SET pc_title=? WHERE encounter=?", array($encounter_cat_name, $post_encounter));

    $bData = explode(":", $encounter_cat_name);
        $t = $bData[0];
        $c = $bData[1];
		$new_code = trim($c);


    // if any billing:
    $billq = sqlStatement("SELECT id FROM billing WHERE encounter=? AND code_type='CPT4'", array($post_encounter));

	// if it's been checked out
	if (sqlNumRows($billq) != 0) {

		// echo "NEW CODE:" . $new_code . "<br>" .
		//"new code text: " . $encounter_cat_name .
		//"old code: " . $post_old_billing_code;

		sqlStatement("UPDATE billing SET code='$new_code', code_text='$encounter_cat_name' WHERE code_type='CPT4' AND encounter='$post_encounter' AND code='$post_old_billing_code'");

	}


}

?>

<div class='encounter-summary-container'>


<div class='encounter-summary-column column-1'>

<h2>Encounter</h2>
<h3><label>patient:</label><span><?= $patient['fname'] . " " . $patient['lname'] ?></span></h3>
<h3><label>provider:</label><span><?= $encounter_info['fname'] . " " . $encounter_info['lname'] ?></span></h3>

<!-- _work -->
<?php

	$title = getTitle($encounter);

	$bData = explode(":", $title);
        $t = $bData[0];
        $c = $bData[1];
		$title_code = trim($c);


	if ($user_is_physician || acl_check('admin', 'super')) {

		$title_html = "<span id='encounter_title'>" . $title . "</span><span id='init_edit_encounter_title'> (edit)</span><div id='edit_encounter_title'><form method='POST' action=''>" . ibh_encounter_title_pulldown($title) . "<input type='hidden' name='edit_title' value='1'><input type='hidden' name='code_text' value='" . $t . "'><input type='hidden' name='billing_code' value='" . $title_code . "'><input type='hidden' name='encounter_id' value='" . $encounter . "'><input type='submit' value='set'></form>&nbsp;&nbsp; <span id='cancel_edit_encounter_title'>cancel</span></div>";

	} else {

		$title_html = "<span id='encounter_title'>" . $title . "</span>";

	}


?>
<h3><label>title:</label><div class="encounter-title"><?=$title_html?></div></h3>
<h3><label>date:</label><span><?=$date?></span></h3>
<h3><label>supervisor:</label><span><?= $encounter_info['supervisor_fname'] . " " . $encounter_info['supervisor_lname'] ?></span></h3>

<h3><label>ID:</label><span><?=$encounter?></span></h3>
<h3><label>status: </label><span style="font-size:1.5em"><?=$event['pc_apptstatus']?></span></h3>

<?php
	$diag = ibh_get_diagnosis($pid);

if ($diag) { ?>
<h3><label>diagnosis: </label><?=implode('<br>', $diag)?></h3>
<?php } else { ?>
<h3 class="no-diagnosis warning">THIS PATIENT HAS NO DIAGNOSIS</h3>
<?php } ?>

<!-- End of Added by IBH -->
</div>

<?php  //this is the code that is commented out above
$auth_notes_a  = acl_check('encounters', 'notes_a');
$auth_notes    = acl_check('encounters', 'notes');
$auth_relaxed  = acl_check('encounters', 'relaxed');


?>

<div class="encounter-summary-column hide-from-print column-2"><!-- End of Added by IBH -->
<?php 
// ESign for entire encounter
$esign = $esignApi->createEncounterESign( $encounter );
if ( $esign->isButtonViewable() ) {
    echo $esign->buttonHtml();
}
?>
<?php if (acl_check('admin', 'super')) { ?><!-- full replace -->
<div class="forms-button hide-from-print">
    <a href='toggledivs(this.id,this.id);' target='_self' onclick='return deleteme()'><button class='go-button delete'>Delete</button></a>
    </div>
<?php } ?>


<div class="forms-button">
     <a href="supervisor_alert_popup.php" id="supervisor"><button class="go-button" id="super">Supervisor Alert</button></a>
    <!-- End of full replace of Added by IBH -->
</div>

    <!-- start of Added by IBH -->
	 <?php if(ibh_user_is_supervisor()){
	       $reject = filter_input(INPUT_POST, 'rejected');
		   if($reject == 1){ deleteSignature($encounter); }
	          ?>
	     <div class="forms-button">
		 <form method="post" name="reject">
		    <input type="hidden" name="rejected" value="1">
			<input class="go-button delete" type="submit" value="Remove eSignature" title="Click here to remove the clinician eSignature from this encounter.">
		</form>
             <!-- End of Added by IBH -->
</div>
		<?php } ?><!--  Added by IBH -->

		<div class="forms-button">
     <a target="_blank" href="forms.php?printing=1&set_encounter=<?=$encounter?>" id="print_button"><button class="go-button" id="print">Print</button></a>
</div>


    <div class="expand-collapse">

		<a href="#" onClick='expandcollapse("expand");' style="font-size:80%;"><?php xl('Expand All','e'); ?></a>
 | <a  style="font-size:80%;" href="#" onClick='expandcollapse("collapse");'><?php xl('Collapse All','e'); ?></a>
		</div>



</div>




<!-- replaced encounter-summary-column column-3 -->
<div class='encounter-summary-column column-3'>
<?php if ($GLOBALS['enable_amc_prompting']) { ?>
    <div class="encounter-checkers"> <!-- replaced div usinf encoutner-checkers class -->

            <table>
            <tr>
            <td>
            <?php // Display the education resource checkbox (AMC prompting)
                $itemAMC = amcCollect("patient_edu_amc", $pid, 'form_encounter', $encounter);
            ?>
            <?php if (!(empty($itemAMC))) { ?>
                <input type="checkbox" id="prov_edu_res" checked>
            <?php } else { ?>
                <input type="checkbox" id="prov_edu_res">
            <?php } ?>
            </td>
            <td>
            <span class="text"><?php echo xl('Provided Education Resource(s)?') ?></span>
            </td>
            </tr>
            <tr>
            <td>
            <?php // Display the Provided Clinical Summary checkbox (AMC prompting)
                $itemAMC = amcCollect("provide_sum_pat_amc", $pid, 'form_encounter', $encounter);
            ?>
            <?php if (!(empty($itemAMC))) { ?>
                <input type="checkbox" id="provide_sum_pat_flag" checked>
            <?php } else { ?>
                <input type="checkbox" id="provide_sum_pat_flag">
            <?php } ?>
            </td>
            <td>
            <span class="text"><?php echo xl('Provided Clinical Summary?') ?></span>
            </td>
            </tr>
            <?php // Display the medication reconciliation checkboxes (AMC prompting)
                $itemAMC = amcCollect("med_reconc_amc", $pid, 'form_encounter', $encounter);
            ?>
            <?php if (!(empty($itemAMC))) { ?>
                <tr>
                <td>
                <input type="checkbox" id="trans_trand_care" checked>
                </td>
                <td>
                <span class="text"><?php echo xl('Transition/Transfer of Care?') ?></span>
                </td>
                </tr>
                </table>
                <table style="margin-left:2em;">
                <tr>
                <td>
                <?php if (!(empty($itemAMC['date_completed']))) { ?>
                    <input type="checkbox" id="med_reconc_perf" checked>
                <?php } else { ?>
                    <input type="checkbox" id="med_reconc_perf">
                <?php } ?>
                </td>
                <td>
                <span class="text"><?php echo xl('Medication Reconciliation Performed?') ?></span>
                </td>
                </tr>
		<tr><!-- table row added -->
                <td>
                <?php if (!(empty($itemAMC['soc_provided']))) { ?>
                    <input type="checkbox" id="soc_provided" checked>
                <?php } else { ?>
                    <input type="checkbox" id="soc_provided">
                <?php } ?>
                </td>
                <td>
                <span class="text"><?php echo xl('Summary Of Care Provided?') ?></span>
                </td>
                </tr>
                </table>
            <?php } else { ?>
                <tr>
                <td>
                <input type="checkbox" id="trans_trand_care">
                </td>
                <td>
                <span class="text"><?php echo xl('Transition/Transfer of Care?') ?></span>
                </td>
                </tr>
                </table>
                <table style="margin-left:2em;">
                <tr>
                <td>
                <input type="checkbox" id="med_reconc_perf" DISABLED>
                </td>
                <td>
                <span class="text"><?php echo xl('Medication Reconciliation Performed?') ?></span>
                </td>
                </tr>
                <tr><!-- table row added -->
                <td>
                <input type="checkbox" id="soc_provided" DISABLED>
                </td>
                <td>
                <span class="text"><?php echo xl('Summary of Care Provided?') ?></span>
                </td>
                </tr>
                </table>
            <?php } ?>
        </div>
    </div>
 <?php //This was moved from 341-44 in rel-422


 ?>

 <?php if ( $esign->isLogViewable() ) {
echo "<div class='encounter-log'>";

    $esign->renderLog();

echo "</div>";
 } ?>

<?php } ?>
</div>

</div>

<br style="clear:both"/> <!--added by IBH -->


<?php $info = ibh_get_encounter_billing($pid, $encounter); //Billing info added to the forms page 5-18-2016
      if(!empty($info)){
	  ?>
<div class="info-wrapper">
<p>Billing Info:</p>


			<table  class="billing" width=849px border=1  >
			<tr>
			  <th>Type</th>
			  <th>Code</th>
			  <th>Modifiers</th>
			  <th>Justify</th>
			  <th>Units</th>
			 <!--  <th>Price</th> -->
			  <th>Note Codes</th>
			  <th>Desc</th>
			</tr>

			<?php
			foreach($info as $v){
				// removed from after units
				// $v['fee']."</td><td align='center'>" .
				 echo "<tr><td align='center'>".$v['code_type']."</td><td align='center'>" .
												 $v['code']."</td><td align='center'>" .
												 $v['modifier']."</td><td align='center'>" .
												 $v['justify']."</td><td align='center'>" .
												 $v['units']."</td><td align='center'>" .

												 $v['notecodes']."</td><td align='center'>" .
												 $v['code_text']."</td></tr>";
			}
			?>

			</table>

			<?php
				$date = $encounter_date; // $date = explode(" ", $date); $date = $date[0];

				$duration = ibh_get_encounter_times($encounter);

				$no_duration_codes = array("99211", "99212", "99213", "99214", "99215", "99201", "99202", "99203", "99204", "99205");


				if (!$duration['message']) {

					$total = (strtotime($duration['pc_endTime']) - strtotime($duration['pc_startTime']))/60;

					if (!in_array($title_code, $no_duration_codes)) {


			?>

			<table class="billing">
			<tr>
			    <td><?php echo "Appt. Started: " . $duration['pc_startTime']; ?>    </td>
				<td><?php echo "Ended: " . $duration['pc_endTime']; ?> </td>
				<td><?php echo "Total Minutes: " . $total; ?> </td>
			</tr>
			</table>

		<?php  } else { ?>

		      <table class="billing">
			<tr>
			    <td><?php echo "Appt. Time: " . $duration['pc_startTime']; ?>    </td>

			</tr>
			</table>


	    <?php }
	      	} else {
		      	// We can deprecate this summer of 2017
		      	// echo "<div class='no-billing'>" . $duration['message'] . " However...</div>";

		      	$duration = getTimeDuration($pid, $date, $encounter);
		      	$total = (strtotime($duration['pc_endTime']) - strtotime($duration['pc_startTime']))/60;

		      	?>
		      	<p>Duration:</p>
			<table class="billing" width=400px border=1 >
			<tr>
			    <td><?php echo "Start time: " . $duration['pc_startTime']; ?></td>
				<td><?php echo "End time: " . $duration['pc_endTime']; ?> </td>
				<td><?php echo "Total: " . $total; ?> </td>
			</tr>
			</table>

		      	<?php

	      	}


	      } else { echo "<div class='no-billing'>There is no billing information yet.</div>"; } ?>
</div>

<!-- end ofadded by IBH -->
<!-- Get the documents tagged to this encounter and display the links and notes as the tooltip -->
<?php 
	$docs_list = getDocumentsByEncounter($pid,$_SESSION['encounter']);
	if(count($docs_list) > 0 ) {
?>
<div class='enc_docs'>
<span class="bold"><?php echo xlt("Document(s)"); ?>:</span>
<?php
	$doc = new C_Document();
	foreach ($docs_list as $doc_iter) {
		$doc_url = $doc->_tpl_vars[CURRENT_ACTION]. "&view&patient_id=".attr($pid)."&document_id=" . attr($doc_iter[id]) . "&";
		// Get notes for this document.
		$queryString = "SELECT GROUP_CONCAT(note ORDER BY date DESC SEPARATOR '|') AS docNotes, GROUP_CONCAT(date ORDER BY date DESC SEPARATOR '|') AS docDates
			FROM notes WHERE foreign_id = ? GROUP BY foreign_id";
		$noteData = sqlQuery($queryString,array($doc_iter[id]));
		$note = '';
		if ( $noteData ) {
			$notes = array();
			$notes = explode("|",$noteData['docNotes']);
			$dates = explode("|", $noteData['docDates']);
			for ( $i = 0 ; $i < count($notes) ; $i++ )
				$note .= oeFormatShortDate(date('Y-m-d', strtotime($dates[$i]))) . " : " . $notes[$i] . "\n";
		}
?>
	<br><!-- edited -->
	<a href="<?php echo $doc_url;?>" style="font-size:small;" onsubmit="return top.restoreSession()"><?php echo oeFormatShortDate($doc_iter[docdate]) . ": " . text(basename($doc_iter[url]));?></a>
	<?php if($note != '') {?> 
			<a href="javascript:void(0);" title="<?php echo attr($note);?>"><img src="../../../images/info.png"/></a>
	<?php }?>
<?php } ?>
</div>
<?php } ?>
<br/>

<?php
//ADDED BY ibh
   if($hide_alert == 1){

      if(empty($ct)){
	  	echo "<div class='alert-box'>Alert Sent to ".$sup_alerted1."</div>";
	  }

   }

   ?>


 <div class="info-wrapper">

<?php

//END OF ADDED BU IBH
  if ($result = getFormByEncounter($pid, $encounter, "id, date, form_id, form_name, formdir, user, deleted")) {
    echo "<table class='encounter-form-table' id='partable'>"; //ibh CHANGED CLASS
	$divnos=1;
    foreach ($result as $iter) {
	    $formid = $iter['form_id']; //ADDED BY IBH
        $formdir = $iter['formdir'];

        if ($formdir == 'newpatient') continue; //ADDED BY ibh

        // skip forms whose 'deleted' flag is set to 1
        if ($iter['deleted'] == 1) continue;

        // Skip forms that we are not authorized to see.
        if (($auth_notes_a) ||
            ($auth_notes && $iter['user'] == $_SESSION['authUser']) ||
            ($auth_relaxed && ($formdir == 'sports_fitness' || $formdir == 'podiatry'))) ;
        else continue;

        // $form_info = getFormInfoById($iter['id']);
        if (strtolower(substr($iter['form_name'],0,5)) == 'camos') {
            //CAMOS generates links from report.php and these links should
            //be clickable without causing view.php to come up unexpectedly.
            //I feel that the JQuery code in this file leading to a click
            //on the report.php content to bring up view.php steps on a
            //form's autonomy to generate it's own html content in it's report
            //but until any other form has a problem with this, I will just
            //make an exception here for CAMOS and allow it to carry out this
            //functionality for all other forms.  --Mark
	        echo '<tr title="' . xl('Edit form') . '" '.
       		      'id="'.$formdir.'~'.$iter['form_id'].'">';
        } else {
            echo '<tr title="' . xl('Edit form') . '" '.
                  'id="'.$formdir.'~'.$iter['form_id'].'" class="text onerow">';
        }
        $user = getNameFromUsername($iter['user']);

        $form_name = ($formdir == 'newpatient') ? xl('Patient Encounter') : xl_form_title($iter['form_name']);

        // Create the ESign instance for this form
        $esign = $esignApi->createFormESign( $iter['id'], $formdir, $encounter );
        //edited by ibh
        echo "<tr data-foo='x'>";

        echo "<td class='encounter-cell'>"; //end of edited by ibh
        // a link to edit the form
        echo "<div class='form_header_controls'>";

        
        // WHERE FORM BUTTONS WERE BEFORE!!!

        echo "<div class='form_header'>";

        // Figure out the correct author (encounter authors are the '$providerNameRes', while other
        // form authors are the '$user['fname'] . "  " . $user['lname']').
        if ($formdir == 'newpatient') {
          $form_author = $providerNameRes;
        }
        else {
          $form_author = $user['fname'] . "  " . $user['lname'];
        }

        echo "<a href='#' onclick='divtoggle(\"spanid_$divnos\",\"divid_$divnos\");' class='small' id='aid_$divnos'><b>$form_name</b> <span class='text'>by " . htmlspecialchars( $form_author ) . "</span> <span id=spanid_$divnos class=\"indicator collapse\">(" . xl('Collapse') . ")</span></a></div>";

       // echo "</td>\n";
       // echo "</tr>";
       // echo "<tr data-foo='1'>";
       // echo "<td valign='top' class='formrow'>";
        echo "<div class='tab' id='divid_$divnos' style='display:block'>";

        // Use the form's report.php for display.  Forms with names starting with LBF
        // are list-based forms sharing a single collection of code.
        //
        if (substr($formdir,0,3) == 'LBF') {
          include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
          call_user_func("lbf_report", $pid, $encounter, 2, $iter['form_id'], $formdir, true); //edited
        }
        else  {
          include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
          call_user_func($formdir . "_report", $pid, $encounter, 2, $iter['form_id']);
        }
        echo "<div class='sig-log'>";
        if ( $esign->isLogViewable() ) {
            $esign->renderLog();
        }
        echo "</div>"; //closing div


        echo "</div>"; //closing div
		$divnos=$divnos+1;
		//This stuff is moved
		if ($user_is_supervisor) { ?>

			<form action="" method="post">
				<input type="hidden" name="form_id" value="<?php echo $formid; ?>">

			<div class='supervisor-comments'><h4>Supervisor Comments</h4><p>You are listed as the supervisor for this encounter. Submit comments in order to have the practitioner review/revisit the form. This will be sent via the messaging system.</p><form action='forms.php' method='post'><div class='form-element'><textarea name='supervisor-comments'></textarea></div><div class='form-element'><input class="go-button" type='submit' name='submit_comments' value='Submit Comments for Review/Changes'></div></div>
			</form>

		<?php

    }

			$signable = true;
		if ($esign->isLocked()) {
			$signable = false;
		}

		      // FORM BUTTONS
        echo "<div class='form_header_buttons hide-from-print'>";
        // If the form is locked, it is no longer editable
        if ( !$signable ) {
            echo "<span class='locked'>".xlt('Editing Locked')."</span>";
        } else {


	        $target = "_self";
	        //$target = $GLOBALS['concurrent_layout'] ? "_parent" : "Main";

            echo "<a class='css_button_small form-edit-button' id='form-edit-button-".attr($formdir)."-".attr($iter['id'])."' target='".
                    $target .
                    "' href='$rootdir/patient_file/encounter/view_form.php?" .
                    "formname=" . attr($formdir) . "&id=" . attr($iter['form_id']) .
                    "' onclick='top.restoreSession()'>Edit</a> ";


        }


       $acl_names=acl_get_group_titles($iter["user"]);
       $is_physician = in_array("Physicians", $acl_names) ? true: false;

        if ($diag) {
	        if ( $esign->isButtonViewable() ) {

		        if (

		        $signable &&
		        (($provider_username == $authUser && $encounter_info['supervisor_id']) || ibh_user_is_supervisor()) || $is_physician){
	           		echo $esign->buttonHtml();
            	}

			}
        } else {
	        ?><span class="warning">Patient must have diagnosis for LBF to be signed</span><?php
        }


		// delete button should only be rendered if it's admin
        if (acl_check('admin', 'super') ) {
            if ( $formdir != 'newpatient') {
                // a link to delete the form from the encounter
                echo "<a target='_self' " .
                    // ($GLOBALS['concurrent_layout'] ? "_parent" : "Main") . "'" .
                    " href='$rootdir/patient_file/encounter/delete_form.php?" .
                    "formname=" . $formdir .
                    "&id=" . $iter['id'] .
                    "&encounter=". $encounter.
                    "&pid=".$pid.
                    "' class='delete css_button_small' title='" . xl('delete this form') . "' onclick='top.restoreSession()'>" . xl('Delete') . "</a>";
            } else {
                ?><a href='javascript:;' target='_self' class='css_button_small delete'><?php xl('Delete','e'); ?></a><?php
            }
        }
        echo "</div></td></tr><tr><td>&nbsp;</td></tr>";
        // END FORM HEADER BUTTONS


    }// end foreach on encounter form


    echo "</table>"; //end of table


}
?>
 </div>

<!-- JS added by IBH-->
<script language='JavaScript'>
	jQuery(document).on("ready", function() {
		var $ = jQuery;


		$("#init_edit_encounter_title").click(function() {
			$("#edit_encounter_title").show();
			$("#encounter_title").hide();
			$("#init_edit_encounter_title").hide();
		});

		$("#cancel_edit_encounter_title").click(function() {
			$("#edit_encounter_title").hide();
			$("#encounter_title").show();
			$("#init_edit_encounter_title").show();
		});

		<?php if($printing) { ?>

			$("#print_button").click(function() {

				window.print();

				return false;

			});
		<?php }		?>



	});

</script>

<?php if ($GLOBALS['athletic_team'] && $GLOBALS['concurrent_layout'] == 2) { ?>
<script language='JavaScript'>
 // If this is the top frame then show the encounters list in the bottom frame.
 // var n  = parent.parent.left_nav;
 var n  = top.left_nav;
 var nf = n.document.forms[0];
 if (parent.window.name == 'RTop' && nf.cb_bot.checked) {
  var othername = 'RBot';
  n.setRadio(othername, 'ens');
  n.loadFrame('ens1', othername, 'patient_file/history/encounters.php');
 }
</script>
<?php } ?>

<!-- </div>  end large encounter_forms DIV -->
</body>
<?php require_once("forms_review_footer.php"); ?><!-- added -->
</html>
