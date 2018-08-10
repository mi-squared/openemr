<?php 
	
// ini_set("display_errors", 1);
		
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

// IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_era.php");

?><html><head>

<script type="text/javascript" src="/openemr/_ibh/js/jquery_latest.min.js"></script>
<link rel="stylesheet" href="/openemr/interface/themes/style_metal.css" type="text/css">
<link rel="stylesheet" href="/openemr/_ibh/css/encounter.css" type="text/css">
<style type="text/css">
	
	
	.era-report {
		width:800px;
		margin:8px auto;
	}
	
	.era-item {
		margin:4px 0;

		width:100%;
		clear:both;	
		border-bottom:1px dashed #ccc;
		padding:4px;
		background-color:#f0f0f0;
	}
	
	.era-name-head {
		background-color:#e4edb3;
	}
	.era-item label {
		display:block;
		float:left;
		width:150px;
		border-right:1px solid #ccc;
		text-align:right;
		color:#888;
		margin-right:12px;
		padding:2px;
	}
	
	.era-item span {
		color:black;
		float:left;
		padding:2px;
	}
	
	.era-chunk {
		margin-bottom:12px;
		border-bottom:3px solid black;
		
	}
	
	.chunk {
		padding:8px;
		border:1px dotted #888;
		margin:2px 0;
	}
	
	.claim-section {
		border:4px solid #323232;
		margin:20px 0;
		background-color:#f0f0f0;
	}
	
	.claim-section table {
		border-collapse:collapse;
		width:100%;
	}
	
	.claim-section table td {
		border:1px dotted #888;
		padding:4px;
	}
	
	.warning-section {
		background-color:#ffd5b0;
		
	}
	
	.warning-section a {
		color:#aa0000;
		text-decoration:none;
	}
	
	.non-warning-section {
		background-color:#e2f3dc;
	}
	
	.non-warning-section a {
		color:#90c383;
		text-decoration:none;
	}
	
	.group:after {
  content: "";
  display: table;
  clear: both;
  }
  
  .era_row {
	  background-color:pink;
  }
  
  .era_row td {
	  background-color:#f0f0f0;
  }
  
  .claim-section table tr.olddetail td {
	  background-color:#bb8282;
  }
  
  .claim-section table tr.newdetail td {
	  background-color:#a7c07d;
  }
  
  .claim-section table tr.errdetail td {
	  background-color:#ef631d;
  }
  
  .claim-section table tr.existing td {
	  background-color:#6dc1df;
  }
  
   .claim-section table tr.error td {
	  background-color:#ff1414;
  }
  
   .claim-section table tr.adjustment td {
	  background-color:#8ba0e5;
  }
  
  .claim-section table tr.ar-records td {
	  background-color:#8cf2c4;
  }

  
  .posting-block {
	  display:none;
	  background-color:#f0f0f0;
	  border-top:4px solid #222;
	  background-color:#cef78d;
  }
  
  .posting-block table {
	  width:100%;
	  
  }
  .posting-block table th td {
	  text-align:left;
	  font-size:12px;
	  
  }
  
   .posting-block table th {
	  font-size:10px;
	  text-align:left;
	  
  }
  
  .posting-block table td {
	  padding:2px;
	  
  }
  
   .posting-block table td input[type=text] {
	 width:100%;
  }
  
   .posting-block table td.sub {
	 text-align:center;
	  
  }
  .posting-block table td.sub input[type=submit] {
	 font-weight: bold;
    font-size: 15px;
    background-color: #18a33f;
    color: white;
    border: none;
	  padding:8px 20px;
  }
  
  .pb-cancel-button {
	float: right;
    padding: 6px;
    color: #ad0000;
    margin: 3px 3px 0 0;
  }
  
</style>
</head>
<body class='overview-pane'>
	
	<div class="ibh-wrapper">
	<div class='nav'>
	
	<a href="era_overview.php">era overview</a>
	</div>	
	<h2>x12 inspector</h2>
<?php

$era_stack = array();
$warning_html = "";

$chunk_id = 1;
$encount = 0;

$paydate = ""; // "2017-08-20";
$last_ptname = '';
$last_invnumber = '';
$last_code = '';
$invoice_total = 0.00;
$InsertionId = 0; 

?>
<div style="margin:12px auto;width:600px;padding:12px; text-align:center;background-color:khaki;border:1px dotted #ccc">
	<h4>Choose an ERA file to inspect (no data is written)</h4>
	<form action="parse_x12.php" method="POST"  enctype="multipart/form-data">
		<input name="form_erafile" id="uploadedfile"  type="file" class="text" size="10"/>
		<input type="submit" name="sub" value="upload">
	</form>
<?php if ($_POST) { ?>
<div>
	<button class='show-hide'>Show/Hide Posted ERA Items</button>
</div>
<?php } ?>
</div>
<div class="era-report">

<?php
	
	function spell_array($val) {
		
		$ret = "";
		
		if (is_array($val)) {
			
			// return str_replace("\n", "<br>", print_r($val, true));
			foreach ($val as $n => $v) {
				if (is_array($v)) {
					$ret .= spell_array($v);
				} else {
					$ret .= $n . ":" . $v . "<br>";
				}
				
			}
			
		} else {
			return $val;
		}
		
		return $ret;
	}

if ($_POST) {
	// echo "SUBMITTED<br>" . print_r($_FILES, true);
	$tmp_name = $_FILES['form_erafile']['tmp_name'];
	
	
	parse_era($tmp_name, 'era_callback'); // ibh_get_era_error_code
	
	
	// echo "<div class='era-item group' style='background-color:GreenYellow'><label>FILE</label><span>" . $_FILES['form_erafile']['name'] . "</span></div>";

	$display_id = 1;

	
	echo $warning_html;
	
	
	foreach($era_stack as $chunk) {
		/*
		echo "<div class='era-chunk'><a name='chunk_" . $display_id++ . "'></a>";
		
		echo "<div class='era-item group era-name-head'><label>PATIENT</label><span>" . $chunk['patient_fname'] . " " . $chunk['patient_lname'] . "</span></div>";
		
		foreach($chunk as $field => $val) {
			echo "<div class='era-item group'><label>" . $field . "</label><span>" . spell_array($val) . "</span></div>";
		}
		echo "</div>";
		*/
	}
	
	
}
	
	?>
	
</div>


<script type="text/javascript">
	$(function(){
		var posted_showing = true;
		
		$(".show-hide").on("click", function() {
			
			if (posted_showing) {
				$(".posted").hide();
				posted_showing = false;
			} else {
				$(".posted").show();
				posted_showing = true;
			}
			
		});
		
		
		
		$(".post-btn").on("click", function() {
			var $bt = $(this);
			var $row = $bt.closest(".post-btn-div");
			var $sec = $bt.closest(".post-btn-div");
			
			
			$.ajax({
					url:"era_overview.php",
					data:{get_session_by_check:$row.data("check")},
					success: function(sess) {
						
						console.log("session:", sess.session_id);
						$bt.hide();
						
						if (sess.session_id) {
							var htm = $("#posting_block").html();
							
							$block = $('<div class="posting-block">' + htm + "</div>").appendTo($sec);
							
							$block.show().find("#session_id").text(sess.session_id);
							
						} else {
							
						}
						
						
					}
			});
			
		});
		
		
		$(document).on("click", ".pb-cancel-button", function(event) {
			var target = $( event.target );
			
			target.closest(".claim-section").find(".post-btn").show();
			
			$(this).parent().remove();			
		});
		
		
		$(document).on("click", ".submit-post", function(event) {
			var target = $( event.target );
			
			var section_table = target.closest(".claim-section").find(".info-table").find("tbody");
			var $table = target.closest(".posting-block");
			
			var $div = target.closest(".post-btn-div");
			var invoice = String($div.data("invoice"));
			
			var inv_arr = invoice.split(".");
			var pid = inv_arr[0];
			var encounter = inv_arr[1];			
			
			var session_id = $table.find("#session_id").text();
			var code_type = $table.find("#code_type").val();
			var code = $table.find("#code").val();
			var mod = $table.find("#mod").val();
			var payer_type = $table.find("#payer_type").val();
			var pay_amount = $table.find("#pay_amount").val();
			var adj_amount = $table.find("#adj_amount").val();
			var memo = $table.find("#memo").val(); 

			if (code && pid && encounter && (pay_amount || adj_amount)) {
				
				
				var data = {
					post_payment:1,
					pid: pid,
					encounter: encounter,
					session_id:session_id,
					code_type: code_type,
					code: code,
					mod: mod,
					payer_type: payer_type,
					pay_amount: pay_amount,
					adj_amount: adj_amount,
					memo: memo				
				};
				
				$.ajax({
					url:"era_overview.php",
					method:"GET",
					data:data,
					success: function(sess) {
						
						if (sess.posted != false) {
							console.log("POSTED session:", sess);
							
							target.closest(".claim-section").find(".post-btn").show();
							target.closest(".posting-block").remove();	
							
							getArRecords(pid, session_id, encounter, section_table)
			
						} else {
							console.log("FAILED TO POST");
						}
						
					}
				});
			
			} else {
				alert("You need to submit a code, and either payment or adjustment.")
			}
			
									
		});
		
		
	
		function getArRecords(pid=0, session=0, encounter=0, table) {
			
			table.find(".ar-records").remove();
			
			$.ajax({
					url:"era_overview.php",
					method:"GET",
					data:{get_ar_records:1, session:session, pid:pid, encounter:encounter},
					success: function(ar_records) {
						
						if (ar_records.posted != false) {
							console.log("ar:", ar_records);
							
							// get records
							table.append("<tr class='ar-records'><td>AR RECORDS</td><td colspan=6>" + ar_records.html + "</td></tr>");
			
						} else {
							console.log("FAILED TO POST");
						}
						
					}
				});
			
			
						
		}
		
		
		
		
	});
	
	
</script>

<div style="display:none">
	<?php include ("includes/ar_editing_block.php"); ?>
</div>

	</div>
</body>
</html>
	