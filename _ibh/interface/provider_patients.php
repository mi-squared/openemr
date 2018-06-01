<?php	
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");

$active_user = $_SESSION['authUserID'];
// $active_user = 22; // Kirsten $_SESSION['authUserID'];



$provider_id = isset($_GET['provider_id']) ? $_GET['provider_id']: 0;

$is_supervisor = ibh_user_is_supervisor();

?><html>
	<head>
		<style>
	</style>


	<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/topdialog.js?t=<?=time()?>"></script>
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/library/dialog.js"></script>


	<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/js/jquery_latest.min.js"></script>
	
	<link rel="stylesheet" href="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/css/tickler.css" type="text/css"/>
	
	</head>
	
	<body>
	
<div class='wrapper'>
<div class="navi">
	<a href="tickler.php">go to tickler</a>
</div>
<h3>provider patients</h3>

<?php 
	
	if ($is_supervisor) {
		echo "change provider: " . ibh_getUserPulldown("user_pulldown", $active_user, false); 
		echo "<div class='new-patient'>add patient</div>";
	}
		
?>
	


<div class='new_patient_row'></div>
<div class='patient_list group'>
		
</div>
<form id='patient-chooser'></form>


<script type='text/javascript'>
	$(function() {
		
		var is_supervisor = <?php echo $is_supervisor == 1 ? "true": "false"; ?>;
		
		var provider_id = <?php echo $provider_id; ?>;
		var $new_patient_row = {};
		
		$("#user_pulldown").on("change", function() {
			
			var $prov_id = $(this).val();
			
			getProviderPatients($prov_id);
			
		});
		
		
		function getProviderPatients(prov_id) {
			
			provider_id = prov_id;
			
			$.ajax({
				url:"<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/ajax/get_provider_patients.php",
				data:{provider_id:prov_id},
				success: function(json) {
					
					
					$(".new-patient").show();
					$(".new_patient_row").text("");
										
					$(".patient_list").html("");
					var patients = json[0];
					
					if (patients.length > 0) {
						
						$.each(patients, function(index, pat) {
						
						var rem = "";
						if (is_supervisor) {
							rem = "<span class='pl-rem'>remove</span>";
						}
						
						$("<div class='pl-item' data-pid='" + pat.id + "'>" + pat.lname + ", " + pat.fname + rem + "<span class='pl-dob'>(" + pat.dob + ")</span></div>").appendTo(".patient_list");
						
						});
					
					} else {
						
						$(".patient_list").html("<div class='pl-item'>no patients listed</div>");
						
					}
		
				}
			});
			
		}
		
		
		$(".patient_list").on("click", ".pl-rem", function() {
			var $row = $(this).closest(".pl-item");
			var pid = $row.data("pid");
			
			// alert("provider" + provider_id + " patient:" + pid);
			
			$.ajax({
				url:"<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/ajax/edit_provider_patients.php",
				data:{action:"remove", provider_id:provider_id, patient_id:pid},
				success: function(json) {
				
					$(".new_patient_row").text("patient removed");
					
					$row.fadeOut();
					
				}
			});



			
		});
		
		
		
		if (provider_id > 0) {
			getProviderPatients(provider_id);
		}
		
	
	
		window.setpatient = function(pid) {
			
			$.ajax({
						url:"<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/ajax/get_patient_data.php",
						data:{pid:pid},
						success: function(patient_data) {
	
							console.log("patient_data", patient_data);
							$(".new_patient_row").text(patient_data.patient.fname + " " + patient_data.patient.lname + " added.");
							
							$.ajax({
								url:"<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/ajax/edit_provider_patients.php",
								data:{action:"add", provider_id:provider_id, patient_id:pid},
								success: function(json) {
								
									console.log("add reply:", json);
									getProviderPatients(provider_id);	
																
								}
							});
			
			
		
						}
			});
			
			
			
		}
		
			
		function sel_patient() {
			
			 window.open("/openemr/interface/main/calendar/find_patient_popup.php", "patient-chooser", "resizable=1,scrollbars=1,location=0,toolbar=0" + 
		",width=450,height=450,left=150,top=150");
		
	 	}
 	
 	
	 	$(".new-patient").click(function() {
			$el = $(this);

			sel_patient();
		
		});
	
 	
		getProviderPatients(<?= $active_user ?>);
 	
		
	});
	
	
	
	
	
</script>
	</div>
	</body>
</html>
