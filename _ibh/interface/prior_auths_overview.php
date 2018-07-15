<?php
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

// IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");


?><html>
<title></title>
<head>
<script type="text/javascript" src="<?= $GLOBALS['webroot'] ?>/_ibh/js/jquery_latest.min.js"></script>

<link rel="stylesheet" href="<?=  $GLOBALS['webroot'] ?>/interface/themes/style_metal.css" type="text/css">
<link rel="stylesheet" href="<?=  $GLOBALS['webroot'] ?>/_ibh/css/encounter.css" type="text/css">


<style type="text/css">@import url(<?=  $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?= $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<script type="text/javascript" src="<?= $GLOBALS['webroot'] ?>/library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="<?= $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="<?= $GLOBALS['webroot'] ?>/library/textformat.js"></script>



</head>
<body class="overview-pane">
<div class="ibh-wrapper">
	
<h4>Prior Auths Overview</h4>


<div class="ibh-form-search">
	<a href="?run_days_check=1"><button>RUN DAY ALERTS</button></a>
	<?php
		
		if ($_GET['run_days_check'] == "1") {
			ibh_check_for_prior_auth_day_alerts();
		}
		
	?>
	  
	<a href="?run_units_check=1"><button>RUN UNITS ALERTS</button></a>
	<?php
		
		if ($_GET['run_units_check'] == "1") {
			ibh_check_for_prior_auth_units(108, 90853);
		}
		
	?>
	
	
	
</div>


<div class="ibh-form-search">
	
	<?php
		
		$search_string = "";
		$data_array = array();
		$where_query = "";
		$sel_patient_name = "";
		$sel_auth_num = "";
		$sel_billing_code = "";
		$sel_date = "";
		$af_selected = "";
		$at_selected = "";
		
		if ($_GET['search_string']) {
			$search_string = $ss = trim($_GET['search_string']);
			$search_domain = trim($_GET['search_domain']);
			
			switch($search_domain) {
				case "patient_name":
					$where_query = "p.lname LIKE '%$search_string%' OR p.fname LIKE '%$search_string%'";
					$sel_patient_name = "selected";
					break;
				case "prior_auth_number":
					$where_query = "pa.prior_auth_number LIKE '%$search_string%'";
					$sel_auth_num = "selected";
					break;
				case "billing_code":
					$where_query = "(pa.code1='$ss' OR pa.code2='$ss' OR pa.code3='$ss' OR pa.code4='$ss' OR pa.code5='$ss' OR pa.code6='$ss' OR pa.code7='$ss')";
					$sel_billing_code = "selected";
					break;
				case "date":
					$where_query = "date='$ss'";
					$sel_date = "selected";
					break;
					
				case "auth_from":
					$where_query = "auth_from='$ss'";
					$af_date = "selected";
					break;
				
				case "auth_to":
					$where_query = "auth_to='$ss'";
					$at_date = "selected";
					break;
			}
		}
		
		
		if ($_GET['starts']) {
			
		}
		
		$url_string = "prior_auths_overview.php?search_domain=" . $search_domain . "&search_string=" . $search_string;

	?>
	<form action="" method="get">
		
	<label>search for:</label><input type="text" name="search_string" value="<?=$search_string?>"><label>in:</label>
		<select name="search_domain">
			<option value="patient_name" <?=$sel_patient_name?>>patient name</option>
			<option value="prior_auth_number"<?=$sel_auth_num?>>auth code/number</option>
			<option value="billing_code" <?=$sel_billing_code?>>billing codes</option>
			<option value="date" <?=$sel_date?>>date</option>
			<option value="auth_from" <?=$af_date?>>auth from</option>
			<option value="auth_to" <?=$at_date?>>auth to</option>
		</select>
		&nbsp;&nbsp;
		<!-- 
		starts: <select id="auth_from_type" name="auth_from_type">
				<option value="">on</option>
				<option value="">on or after</option>
		</select><input id="auth_from" type="text" name="auth_from">
		&nbsp; ends: <select id="auth_to_type" name="auth_to_type">
				<option value="">before or on</option>
				<option value="">after</option>
		</select>
			<input id="auth_to" type="text" name="auth_to">
		-->
		<input type="submit" value="go">
		
		
		<ul class="ibh-top-buttons">
			<li><a href="prior_auths_overview.php?filter=red_flags">view red flags</a></li>
			<li><a href="prior_auths_overview.php?filter=archived">view all (archived/expired)</a></li>
			<li><a href="prior_auths_overview.php">clear</a></li>
		</ul>

</div>


<table class="prior-auths-table list-table">
	<tr>
		
	<th>patient</th>
	<th>auth #<a href="<?=$url_string?>&order_by=pa.prior_auth_number"><img src="<?= $GLOBALS['webroot'] ?>/_ibh/img/sort_asc.png"></a> <a href="<?=$url_string?>&order_by=pa.prior_auth_number-DESC"><img src="<?= $GLOBALS['webroot'] ?>/_ibh/img/sort_desc.png"></a></th>
		
	<th>starts<a href="<?=$url_string?>&order_by=pa.auth_from"><img src="<?= $GLOBALS['webroot'] ?>/_ibh/img/sort_asc.png"></a> <a href="<?=$url_string?>&order_by=pa.auth_from-DESC"><img src="<?= $GLOBALS['webroot'] ?>/_ibh/img/sort_desc.png"></a></th>
	
	<th>ends<a href="<?=$url_string?>&order_by=pa.auth_to"><img src="<?= $GLOBALS['webroot'] ?>/_ibh/img/sort_asc.png"></a> <a href="<?=$url_string?>&order_by=pa.auth_to-DESC"><img src="<?= $GLOBALS['webroot'] ?>/_ibh/img/sort_desc.png"></a></th>
		
	<th>units<a href="<?=$url_string?>&order_by=pa.units"><img src="<?= $GLOBALS['webroot'] ?>/_ibh/img/sort_asc.png"></a> <a href="<?=$url_string?>&order_by=pa.units-DESC"><img src="<?= $GLOBALS['webroot'] ?>/_ibh/img/sort_desc.png"></a></th>
	<th>entered<a href="<?=$url_string?>&order_by=pa.date"><img src="<?= $GLOBALS['webroot'] ?>/_ibh/img/sort_asc.png"></a> <a href="<?=$url_string?>&order_by=pa.date-DESC"><img src="<?= $GLOBALS['webroot'] ?>/_ibh/img/sort_desc.png"></a></th>
	<th>codes</th></tr>
<?php
	
	$filter_red_flags = (isset($_GET['filter']) && $_GET['filter'] == "red_flags");
	$filter_view_archived = (isset($_GET['filter']) && $_GET['filter'] == "archived");
	
	
	$order_by = $_GET['order_by'] ? str_replace("-", " ", $_GET['order_by']): "pa.date DESC";
	
	$auth_to_today = date("Y-m-d");
	$where = $where_query ? $where_query: "(pa.archived=0 AND pa.auth_to>='" . $auth_to_today . "')";
	
	if ($filter_view_archived) $where = "(pa.archived=0 OR pa.archived=1)";
	
	$sql = "SELECT pa.id, p.fname, p.lname FROM form_prior_auth pa LEFT JOIN patient_data p ON pa.pid=p.pid WHERE $where ORDER BY $order_by LIMIT 1000";
	
	
	// echo "<tr><td colspan='7'>" . $sql . "</td></tr>";
    $prior_auths = sqlStatement($sql);
    
    while($auth = sqlFetchArray($prior_auths)){ 
	    
	    
	    $display = true;
	    /*
	    // get raw codes, but some are blank
		$codes_array_loaded = array($auth['code1'], $auth['code2'], $auth['code3'], $auth['code4'], $auth['code5'], $auth['code6'], $auth['code7']);
		
		$actual_codes = array();
		// clean codes to make smaller array when many are blank
		foreach ($codes_array_loaded as $pa_code) {
			if ($pa_code) $actual_codes[] = $pa_code;
		}
		*/
		$auth2 = ibh_get_prior_auth($auth['id']);
		$auth = array_merge($auth, $auth2);
		
		$bills = ibh_get_prior_auth_bills($auth);
		
		$unit_track_class = "";
		$day_track_class = "";
		
		$red_flag = false;
		
		$editable = true;
		
		if ($auth['units_remaining'] > 0 && $auth['units_remaining'] <= $auth['alert_units']) {
			$unit_track_class = "high-alert";
			$red_flag = true;
		}
		
		if ($auth['days_remaining'] > 0 && $auth['days_remaining'] <= $auth['alert_days']) {
			$day_track_class = "high-alert";
			$red_flag = true;
		}
		
		
		if ($filter_red_flags && !$red_flag) $display = false;
		
		if ($display) {
			
		if (!$auth['prior_auth_number']) {
			$clickable = "(no prior auth #)";
		} else {
			$clickable = $auth['prior_auth_number'];
		}
		
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
		
		if ($editable) {
			$edit_link = '<a href="../../interface/forms/prior_auth/display.php?action=edit&pid=' . $auth['pid'] . '&pa_id=' . $auth['id'] . '">';
		} else {
			$edit_link = "<a>";
		}
		
	?>
	<tr class="<?=$expired_class . $archived_class?>">
		<td><strong><?=$auth['fname']?> <?=$auth['lname']?></strong></td>
		<td><strong><?=$edit_link?><?=$clickable?></a></strong><br><?=$auth['id']?></td>
		<?php
			$auth_from = date("D M j, Y", strtotime($auth['auth_from']));
			$auth_to = date("D M j, Y", strtotime($auth['auth_to']));
		?>
		<td style="background-color:#d5f8ce"><?=$auth_from?></td>
		<td style="background-color:#fdc8c8"><?=$auth_to?><br><small>alert at <?=$auth['alert_days']?> days out</small></td>
		<td><?=$auth['units']?>
		<?php
			if ($auth['unit_adjustment']) {
				echo "<span class='lighter'>(" . $auth['unit_adjustment'] . ")</span>";
			}
		
		?><br><small>alert at <?=$auth['alert_units']?> units</small></td>
		<td><?=substr($auth['date'],0,10)?></td>
		<td><?=ibh_wrap_billing_codes($auth['codes'])?></td>
	</tr>
	<tr  class="<?=$expired_class . $archived_class?>">
		<td class="pa-summary"><h4>remaining:</h4>
		<div class="pa-tracking <?=$unit_track_class?>">units: <?=$auth['units_remaining']?></div>
		<div class="pa-tracking <?=$day_track_class?>">days: <?=$auth['days_remaining']?></div>
		</td>
		<td colspan="6" class="bills-section">
			<?php if ($bills) {
				echo $bills['html'];
				} else {
					echo "<div class='message'>There are no bills for this Prior Auth.</div>";
				}
				
				
			?>
	</tr>	
	<tr><td colspan='7' style="height:0;padding:0;border-top:4px solid black"></td></tr>
	<?php
	
	} // end display filter
	
	}
	
	
	
?>

</table>

<script type="text/javascript">
	$(function(){
		
		/*
			$(".show-hide-bills").on("click", function() {

			if ($(this).data("vis") == false) {
				
				$(this).closest(".bills-section").find(".bills-section-bills").show();
				$(this).data("vis", true);
			} else {
				
				$(this).closest(".bills-section").find(".bills-section-bills").hide();
				$(this).data("vis", false);
			}
		});
		*/
		
		
		
		/*Calendar.setup({inputField:"dob", ifFormat:"%Y-%m-%d", button:"img_dob"});*/
		Calendar.setup({inputField:"auth_from", ifFormat:"%Y-%m-%d", button:"img_auth_from"});
		Calendar.setup({inputField:"auth_to", ifFormat:"%Y-%m-%d", button:"img_auth_to"});


		
	});
</script>


</div>

</body>
</html>