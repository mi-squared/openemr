<?php
	
		// IBH_DEV
		$saf_provider = "";
		$saf_super = "";
		$admin_view = true;
		$prov_checked = "";
		$super_checked = "";
		$super_sel = "";
		$prov_sel = "";
		$username = $_SESSION{"authUser"};
		$user_is_supervisor = ibh_user_is_supervisor();
		
		if ($user_is_supervisor) { 
			$admin_view = true;
		} 


		if ($admin_view) {
			
			
		if (isset($_GET['supervisor_id']) && $_GET['filter_by'] == "supervisor") {
			 $super_sel = $_GET['supervisor_id'];
			 $super_checked = "checked";
			 $prov_sel = "";
			 
		} else if (isset($_GET['provider_id']) && $_GET['filter_by'] == "provider") {
			 $prov_sel = $_GET['provider_id'];
			 $prov_checked = "checked";
			 $super_sel = "";
		} else {
			$prov_sel = ibh_get_session_user_id();
			$prov_checked = "checked";
		}
		
		$prov_sel = isset($_GET['provider_id'])? $_GET['provider_id']: "";
		
		$url_string = "?supervisor_alert_filter=1&filter_by=" . $_GET['filter_by'] . "&provider_id=" . $_GET['provider_id'] . "&supervisor_id=" . $_GET['supervisor_id']; 
		
	?>
	
	
	<span class="title">Supervisor Alert Report</span>
	<div class="alerts-controls" class="supervisor-report">
	
	
		<table>
			<form id="get_supervisor_alerts" action="messages.php" method="GET">
				
				<input type="hidden" name="filter_by" id="supervisor_filter_by" value="">
				<input type="hidden" name="supervisor_alert_filter" value="1">
			<tr>
				
			<td><label for="by_provider">by provider:</label> <?=ibh_getUserPulldown("provider_id", $prov_sel, false);?></td>
			
			
			<td><label for="by_supervisor"> by supervisor:</label> <?=ibh_getUserPulldown("supervisor_id", $super_sel, true);?></td>
			
			</tr>
			
			</form>
		</table>
	
	<div class="supervisor-report-results">
		<table class="supervisor-messages-table">
			
			<thead>

	
			<tr>
				
			<th>from</th>
			<th>to</th>
			<th>patient</th>
			<th>reason</th>
			<th>enc #</th>
			<th>encounter date</th>
			<th>latest alert</th>
			<th>LBFs</th>
			
			</tr>
			
			</thead>
			<tbody>
	<?php
		
		$encounters = array();
		
		if ($_GET['supervisor_alert_filter'] && $admin_view) {
			
			$filter_by = $_GET['filter_by'];
			if ($filter_by == "supervisor") {
				$saf_super = $_GET['supervisor_id'];
			} else {
				$saf_provider = $_GET['provider_id'];
			}
			
			$sort_by = isset($_GET['sort']) ? $_GET['sort']: "fe.date-DESC";

			// echo $saf_super . "::" . $saf_provider;
			
			$results = ibh_getSupevisorAlertEncounters($saf_super, $saf_provider, $sort_by);
			
			
		} else {
			// current user
			$results = ibh_getSupevisorAlertEncounters($prov_sel, "", $sort_by);
		}
		
		$encounters = array();
		
		while ($row = sqlFetchArray($results)) {			
			$encounters[] = $row['encounter'];
			// using ajax now...
		} // end while
		
		?>
			</tbody>
		</table>
	</div>
	</div>
	
	<script type="text/javascript">
		
		var $ = jQuery;
		var encounters = [<?=implode(",", $encounters)?>];
		
		jQuery(function() {
			
			$(".user-pulldown").on("change", function() {
				var pd = $(this).attr("id");
				var fb = "";
				// alert("pd:" + pd);
				if (pd == "supervisor_id") {
					fb = "supervisor";
				} else {
					fb = "provider";
				}
				
				$("#supervisor_filter_by").val(fb);
				
				$("#get_supervisor_alerts").submit();
				
				
			});
			
			
			$(".encounter-setter").click(function() {
				var datestr = $(this).data("date");
				var enc = $(this).data("enc");
				// console.log("datestr, enc", datestr, enc);
				
				parent.left_nav.setEncounter(datestr, enc, window.name);
				
			});
		});
		
		console.log("enc list", encounters);
		$.each(encounters, function(index,value) {
			console.log("enc", value);
			
			$.ajax({
				url:"<?= $GLOBALS['webroot']?>/_ibh/ajax/get_supervisor_alert.php",
				data:{encounter:value},
				success: function(json) {
				
					$(".supervisor-messages-table").append(json.html);
					
				
					
				}
			});
		
		});
		
		
		$('.supervisor-messages-table').tablesort();
		
		
		
	</script>
	<?php } ?>