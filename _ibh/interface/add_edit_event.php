<?php	

/*
	This stuff is included at the bottom of /interface/main/calendar/add_edit_event.php
*/

require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");



$is_supervisor = ibh_user_is_supervisor() ? "true": "false";

?>
<style type="text/css">
	.pa-prohibited {
		color:orange;
		background-color:black;
		padding:4px;
		font-size:12px;
	}

</style>
<script type="text/javascript">
	
	var eid = Number('<?php echo $eid ?>');
	
	var pa_exceptions = ["00000"];
	
	var is_supervisor = <?=$is_supervisor?>;

		
	function copayComment() {}
	
	
	$(document).ready(function(){
		
		var checkins_html = "";
		var other_checkins = false;
		var other_checkins_populated = false;
		var pa_exceptions_message = "";
		
		
		window.patient_selected = function() {
			
			runPAExceptions();

			runPriorAuthCheck();
			runCheckInCheck();
		}
		
	
		
		function getCatNumber() {
			var category = $("select[name=form_category] option:selected").text();
			var csplit = category.split(":");
			return $.trim(csplit[1]);
		}

		function getApptDate(){

		    var apptDate = $("#form_date").val();
		    return apptDate;

        }
		
		
		function runCheckInCheck() {
			
			var pid = $("#patient_id").val();
			var category = $("select[name=form_category] option:selected").text();			
			var status = $("select[name=form_apptstatus] option:selected").val();
							
			if (pid) {
				
				$.ajax({
					url:"<?= $GLOBALS['webroot'] ?>/_ibh/ajax/get_patient_checkins.php",
					data:{pid:pid},
					success: function(data) {
						
						checkins_html = "";
						other_checkins = false;
						
						var title = "";
						var checkin = "";
						for (var i=0; i<data.checkins.length;i++) {
							
							
							checkin = data.checkins[i];
							
							if (checkin.pc_eid != eid) {
								other_checkins = true;
								checkins_html += "<div>" + checkin.pc_eventDate + " at " + checkin.pc_startTime + " --> " + checkin.pc_title + "</div>";
							}
							
							if (status == "@" && checkin.pc_title == category && !eid) {
								// checked in, has matching titles, isn't being edited
								alert("There's already an appointment with that category checked in! See the list below.");
								$("select[name=form_apptstatus]").val("-");
								return true;
							}
							
						}
						
						if (other_checkins) {
							$("#appt_warning_container").html("<div class='appt-warning'>WARNING: Other appointments are checked in: See below.</div>");
							$("#checkins").html(checkins_html);
						} else {
							other_checkins = false;
							$("#appt_warning_container").html("");
							$("#checkins").html("");
						}
						
						
						return false;							
					} // end success
				}); // end ajax
					
				

			}
							
		}
		
		
		
		function runPAExceptions() {
			
			var pid = $("#patient_id").val();
			var cat_code = "";
				
			$.ajax({
					url:"<?= $GLOBALS['webroot'] ?>/_ibh/ajax/get_patient_pa_exceptions.php",
					data:{pid:pid},
					success: function(data) {

						pa_exceptions = data.pa_cat_exceptions;
						
						// check what's selected
						cat_code = getCatNumber();
						console.log("cat_code" + cat_code)
						if (pid) {
							pa_exceptions_message = "All categories need Prior Auths <em>except</em>: " + pa_exceptions.join(", ");
							$(".prior-auth-reqs").html(pa_exceptions_message);
						}
						
						
						
						runCheckInCheck()
						runPriorAuthCheck();
		
		
					}
				});
			
		}
		
		
		
		function runPriorAuthCheck() {
						
			var prior_auth_okay = false; // starts as false if all is working
			
			var cat = getCatNumber();

			var dates = getApptDate();
			
			console.log("cat:" + cat);
						
						
						
			var pid = $("#patient_id").val();
			
			if (pa_exceptions.indexOf(cat) > -1) {
				prior_auth_okay = true;
			}
			
			if (cat) {
			if (pid) {
				
				$.ajax({
					url:"<?= $GLOBALS['webroot'] ?>/_ibh/ajax/get_patient_prior_auths.php",
					data:{cat:cat, pid:pid, apptdate:dates},
					success: function(data) {
						// console.log("prior auths", data);
						// $("#form_save").removeAttr("disabled");
						$(".pa-warning").hide();
						if (data.prior_auths == 0) {
							$(".prior-auths").hide();
							// $(".prior-auths").text("No prior auths.");
						} else {
							var pas = data.prior_auths;
							var pa = "";
							var html = "<h4>Active Prior Auths</h4><table class='prior-auths-list'>";
							html += "<tr><th>auth #</th><th>days left</th><th>units</th><th>billing codes</th></tr>";
							var codes = "";
							var pc = "";
							var disable_save = false;
							// loop through prior_auths
							
							for (var i=0; i<pas.length; i++) {
								
								pa = pas[i];
								codes = "";
								
								for(var c=0; c < pa.codes.length; c++) {
									pc = pa.codes[c];
									if (pc == cat) {

										if (pa.units_remaining == 0 ) {
											disable_save = true;
											codes += "<span class='pa-code hot-code blink_me'>" + pc + "</span>";
											$(".pa-warning").show();
										} else {
											// WE HAVE A MATCH
											codes += "<span class='pa-code hot-code'>" + pc + "</span>";
											prior_auth_okay = true;											
										}
									} else {
										codes += "<span class='pa-code cold-code'>" + pc + "</span>";
									}
								}
														
					
								html += "<tr><td>" + pa.prior_auth_number + "</td><td>" + pa.days_remaining + "</td><td>" + pa.units_remaining + "/" + pa.units + "</td><td>" + codes + "</tr>";
							}
							html += "</table>";
							
							$(".prior-auths").show().html(html);
							
						}
						
						
						
						
						if (prior_auth_okay == true) {
							
							$(".prior-auth-reqs").html(pa_exceptions_message);
							$("#form_save").removeAttr("disabled");
						} else {
							$(".prior-auth-reqs").html(pa_exceptions_message + "<div class='pa-prohibited'>" + cat + "s require a Prior Auth. Supervisors can set up these without an existing PA.</div>");
							if (!is_supervisor)	$("#form_save").attr("disabled", true);
						}
			
			
					}
				});
			}
			} else {
				$(".prior-auth-reqs").html("");
				$("#form_save").removeAttr("disabled");
			}
			
			
		}
		
		
		// get PA exceptions first
		runPAExceptions();
		runPriorAuthCheck();
		
		
		// patient change action is driven by function in popup
		
		$("select[name=form_apptstatus]").change(function() {
			runCheckInCheck();
		});
	
		
		
		$("select[name=form_category]").change(function() {
						
			runPriorAuthCheck();
			runCheckInCheck();
			
		});
		
		
		
		
		
		
	
	
	});
</script>

