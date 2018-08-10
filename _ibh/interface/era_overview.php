<?php
	
// ini_set("display_errors", true);
	
	
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

// IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_era.php");
// ini_set("display_errors", 1);



function getSessionByCheck($ch) {
		$sql = "SELECT * FROM ar_session WHERE reference LIKE '%$ch'";
	    $arq = sqlStatement($sql);
		$row = sqlFetchArray($arq);
		
		return '{"session_id":"' . $row['session_id'] . '", "pay_total":"' . $row['pay_total'] . '"}';
}



if (isset($_GET['get_session_undistributed'])) {
	header('Content-Type: application/json');
	
	echo ibh_get_era_undistributed($_GET['get_session_undistributed']);
	
	exit;
	
}



if (isset($_GET['get_session_by_check'])) {
	header('Content-Type: application/json');
	
	echo getSessionByCheck($_GET['get_session_by_check']);
	
	exit;
	
}



if (isset($_GET['set_to_pebbles'])) {
	
	$amount = $_GET['amount'];
	$session_id = $_GET['session_id'];
	$date = date("Y-m-d H:i:s");
	$seq = time();
	
	sqlStatement("INSERT INTO ar_activity (pid, encounter, sequence_no, payer_type, post_time, post_user, session_id, pay_amount, adj_amount, modified_time, follow_up_note) VALUES('108', '999', '$seq', '1', '$date', '1', '$session_id', '$amount', '0', '$date', 'RECKONED FROM BEST NOTES')");
	
	echo "<div style='text-align:center; padding:20px; background-color:pink'>BEST-NOTES FLUSH COMPLETED</div>";
}



if (isset($_GET['get_ar_records'])) {
	header('Content-Type: application/json');
	
	$pid = $_GET['pid'];
	$encounter = $_GET['encounter'];
	$session_id = $_GET['session'];
	
	$sql = "SELECT * FROM ar_activity WHERE session_id='$session_id' AND encounter='$encounter'";

    $arq = sqlStatement($sql);
    $html = "";
    
    while($a = sqlFetchArray($arq)){ 
	    $mod = $a['modifier'] ? ":" . $a['modifier']: "";
	      $html .= $a['code'] . $mod . ": Paid: " . $a['pay_amount'] . " Adj: " . $a['adj_amount'] . " " . $a['memo'] . "<br>";
	      
	}
	
	echo '{"html":"' . $html . '"}';
	
	exit;
	
	
}




function getQuestionMarks($arr) {
	$ret = array();
	foreach ($arr as $val) {
		$ret[] = "?";
	}
	return implode(",", $ret);
}

function getInsertString($arr) {
	$ret = array();
	foreach ($arr as $val) {
		$ret[] = "'" . $val . "'";
	}
	return implode(",", $ret);
}


function sqlStatementx($statement, $binds=false )
{
  // Below line is to avoid a nasty bug in windows.
  if (empty($binds)) $binds = false;

  // Use adodb Execute with binding and return a recordset.
  //   Note that the auditSQLEvent function is embedded
  //    in the Execute command.
  $recordset = $GLOBALS['adodb']['db']->Execute( $statement, $binds );
  if ($recordset === FALSE) {
    return "fail " . getSqlLastError();
  }
  return $recordset;
}



if (isset($_GET['post_payment'])) {
	header('Content-Type: application/json');
	/*
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
	*/
	
	$userid = $_SESSION['authUserID'];
	$date = date("Y-m-d H:i:s");
	
	// memo swapped out $_GET['memo']
	// $_GET['sequence_no']
	$sequence_no = "";
	
	$stmt1 = false;
	$stmt2 = true;
	
	$memo = $_GET['memo'] . " (era_insp)";
	
	if ($_GET['pay_amount'] > 0 && $_GET['adj_amount'] > 0) {
		
		// BOTH PAYMENT AND ADJUSTMENT
		$stmt1 = ibh_ar_insert(array($_GET['pid'], $_GET['encounter'], $sequence_no, $_GET['code_type'], $_GET['code'], $_GET['modifier'], $_GET['payer_type'], $date, $userid, $_GET['session_id'], $memo, $_GET['pay_amount'], 0, $date));
		
		$stmt2 = ibh_ar_insert(array($_GET['pid'], $_GET['encounter'], $sequence_no, $_GET['code_type'], $_GET['code'], $_GET['modifier'], $_GET['payer_type'], $date, $userid, $_GET['session_id'], $memo, 0, $_GET['adj_amount'], $date));
		
		
	} else if ($_GET['pay_amount'] > 0) {
		// PAYMENT ONLY
		$stmt1 = ibh_ar_insert(array($_GET['pid'], $_GET['encounter'], $sequence_no, $_GET['code_type'], $_GET['code'], $_GET['modifier'], $_GET['payer_type'], $date, $userid, $_GET['session_id'], $memo, $_GET['pay_amount'], 0, $date));
		
		
	} else if ($_GET['adj_amount'] > 0) {
		// ADJUSTMENT ONLY
		$stmt1 = ibh_ar_insert(array($_GET['pid'], $_GET['encounter'], $sequence_no, $_GET['code_type'], $_GET['code'], $_GET['modifier'], $_GET['payer_type'], $date, $userid, $_GET['session_id'], $memo, 0, $_GET['adj_amount'], $date));
		
	}
	
	
	
	
	if ($stmt1 && $stmt2) {
		echo '{"posted":true}';
	} else {
		echo '{"posted":false}';
	}
	
	
	exit;
	
}





// 817096500000685


	$ins_names = array();
	
	$cres = sqlStatement("SELECT * FROM insurance_companies ORDER BY name");
	
	while ($crow = sqlFetchArray($cres)) {
		$ins_names["id-" . $crow['id']] = $crow['name'];
	}
	
	function getInsName($id) {
		global $ins_names;
		
		if (!$id) {
			return "NO PAYER ID";
		} else if ($id == "id-0") {
			return "PATIENT COPAY";
		} else if (isset($ins_names[$id])) {
			return $ins_names[$id];
		} else {
			return "ID:" . $id;
		}
	}

	
		




?><html>
<title></title>
<head>
<script type="text/javascript" src="/openemr/_ibh/js/jquery_latest.min.js"></script>

<link rel="stylesheet" href="/openemr/interface/themes/style_metal.css" type="text/css">
<link rel="stylesheet" href="/openemr/_ibh/css/encounter.css" type="text/css">

<script type="text/javascript" src="/openemr/_ibh/js/jquery.tablesort.js"></script>

<script src="/openemr/_ibh/js/pikaday.js"></script>
<link rel="stylesheet" href="/openemr/_ibh/js/pikaday.css" type="text/css">

<style type="text/css">
		.odd {
			background-color:#f0f0f0;
			
		}
		.even {
			background-color:#fff;
		}
		.era-info ul {
			list-style:none;
			font-size:14px;
		}
		.era-info label {
			width:100px;
			float:left;
			display:block;
			color:#777;
		}
		
		.bills {
			background-color:#ccc;
			font-weight:bold;
		}
		
		.bills td {
			
		}
		
		.enc-row td {
			background-color:#222;
			color:white;
			font-weight:bold;
			border-top:12px solid #fff;
		}
		
		
		.enc-row td.dk-header {
			color:#666;
		}
		
		.bill-row td {
			background-color:#c6e1f4;
		}
		
		.ar-row td {
			background-color:#fff;
		}
		
		.ar-row td.fade {
			color:#8eb8d6;
		}
		
		tr.spacer {
			background-color:#666;
		}
		
</style>
</head>
<body class="overview-pane">
<div class="ibh-wrapper">
	<div class='nav'>
	
	<a href="parse_x12.php">x12 inspector</a>
	</div>
<?php
	
	// get insurance names
	
	if (isset($_GET['session'])) {
		
		$sess = $_GET['session'];
		$sess_sql = "SELECT * FROM ar_session WHERE session_id='$sess'";
		$sq = sqlStatement($sess_sql);
		$session_info = sqlFetchArray($sq);
		
		$check_total = $session_info['pay_total'];
		
		$encounters = array();
		
		?>
		<a href="era_overview.php">back to era sessions</a>
	<h2>ERA SESSION #<?=$_GET['session']?></h2>
	
	
	<div class="era-info">
		<ul>
			<li><label>payer:</label><?=getInsName("id-" . $session_info['payer_id'])?></li>
			<li><label>reference:</label><?=$session_info['reference']?></li>
			<li><label>check date:</label><?=$session_info['check_date']?></li>
			<li><label>check total:</label><?=$check_total?></li>
		
	<?php 
		
		$table = "<table class='list-table'><thead><tr><th>patient</th><th>encounter</th><th>seq</th><th>code</th><th>fee</th><th>payer</th><th>pay_amt</th><th>adj_amt</th><th>account_code</th><th>memo</th></thead>";
	
		
		// echo "SeSSION:" . $_GET['session'] ;
		$session_id = $_GET['session'];
		
		
		$sql = "SELECT * FROM ar_activity WHERE session_id='$session_id' ORDER BY encounter, sequence_no";
	
	
		// echo "<tr><td colspan='7'>" . $sql . "</td></tr>";
	    $arq = sqlStatement($sql);
	    
	    $enc = "";
	    $ct = 0;
	    $rows = 0;
	    $allocated_total = 0;
	    $class="even";
	    
	    
	    while($a = sqlFetchArray($arq)){ 
		    
		     
		    
		    if ($a['encounter'] != $enc) {
				
			    $class = $ct % 2 == 0 ? "even": "odd";
			    $tr_class="first ";
			    $ct++;
			    $show_encounter = $a['encounter'];
			    $table .= "<tr class='spacer'><td colspan=10></td></tr>";
			    
			    
		    } else {
			    $tr_class = "";
			    $show_encounter = "";
		    }
		    
		    // switch
		    if (!in_array($a['encounter'], $encounters)) {
			    
			    $table .= "<tr class='bills $class' id='" . $a['encounter'] . "'></tr>";
				$encounters[] = $a['encounter'];
		    }
		    
		    
		    $enc = $a['encounter'];
		 	
		    $rows ++;
		    
		    $p = ibh_get_patient($a['pid']);
		    
		    $adj = $a['adj_amount'] == "0.00"? "": $a['adj_amount'];
		    
		    $pay = $a['pay_amount'] == "0.00"? "": $a['pay_amount'];
		    
		    $bills = ibh_get_billing_numbers($a['encounter'], $a['code']);
		    
		    $allocated_total += $pay;
		    
		    
		    $table .= "<tr id='$show_encounter' class='" . $tr_class . $class . "'>";
			$table .= "<td><a href='patient_account.php?pid=" . $a['pid'] . "'>" . $p['fname'] . " " . $p['lname'] . "</a></td>";
			$table .= "<td data-sort-value='" . $enc . "'><a href='#'>" . $show_encounter . "</a></td>";
		    $table .= "<td>" . $a['sequence_no'] . "</td>";
		    $table .= "<td>" . $a['code'] . "</td>";
		    $table .= "<td>" . $bills['fee'] . "</td>";
		    $table .= "<td>" . $a['payer_type'] . "</td>";
		    $table .= "<td>" . $pay . "</td>";
		    $table .= "<td>" . $adj . "</td>";
		    $table .= "<td>" . $a['account_code'] . "</td>";
		    $table .= "<td>" . $a['memo'] . "</td></tr>";
		    
		   
		    
		    
		}
		
		$unadjusted = $check_total - $allocated_total;
		
		$table .= "</table>";
		
		echo "<li><label>rows:</label>" . $rows . "</li>";
		echo "<li><label>pay total</label>" . $allocated_total . "</li>";
		echo "<li><label>unadjusted</label>" . number_format($unadjusted, 2) . "</li>";
		
		?>
		</ul>
	</div>
		<?php
		echo $table;
			
	} else {
		
		
		
		
	
	
	$ar_filter = "";
	$query_arr = array();
	$from_date = "";
	$to_date = "";
	
	
	if (isset($_GET['insurance_company']) && $_GET['insurance_company'] > 0) {
		$ar_filter .= " AND payer_id=? ";
		$query_arr[] = $_GET['insurance_company'];
	}
	
	if (isset($_GET['from_date'])) {
		$ar_filter .= " AND check_date>=?";
		$from_date = $_GET['from_date'];
		$query_arr[] = date("Y-m-d", strtotime($_GET['from_date']));
		
	} else {
		$from_date = date("Y-m-d", time() - (8760 * 60 * 60 * 3));
		$ar_filter .= " AND check_date>=" . $from_date;
	}
	
	if (isset($_GET['to_date'])) {
		$ar_filter .= " AND check_date<=?";
		$to_date = $_GET['to_date'];
		$query_arr[] = date("Y-m-d", strtotime($_GET['to_date']));
	} else {
		$to_date = date("Y-m-d");
		$ar_filter .= " AND check_date<='" . $to_date . "'";
	}
	
	?>
	<h2>AR SESSIONS</h2>
	
	<div class="era-info" style="font-size:.9em; padding:8px; background-color:#ccc;margin-bottom:16px">
	<form action="era_overview.php">
		BEST NOTES FLUSH: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;session id: <input type="text" name="session_id">
		&nbsp;&nbsp;&nbsp;amount: <input type="text" name="amount">
		<input type="submit" name="set_to_pebbles" value='Go'>
	</form>
	</div>
	
	<div class='nav'>
		
	    <form action="era_overview.php" method="GET">
		<?php
			
			$filter_ins_co = $_GET['insurance_company'];
			echo ibh_get_insurance_company_pulldown($filter_ins_co, "<option value=''>choose...</option><option value='0'>patient</option>");
		?>
		from <input name="from_date" type="text" id="picker1" value="<?=$from_date?>">
		to <input name="to_date" type="text" id="picker2" value="<?=$to_date?>">
		
		<input type="submit" value="go">
	    </form>
	</div>
	
	
	<?php
		
	$sql = "SELECT * FROM ar_session WHERE adjustment_code='insurance_payment' AND pay_total > 0 " . $ar_filter . " ORDER BY check_date DESC LIMIT 1000";
	
	//echo $sql;
	//echo "<br>" . implode(",", $query_arr);
	
	// echo "<tr><td colspan='7'>" . $sql . "</td></tr>";
    $arq = sqlStatement($sql, $query_arr);
    
    $table = "<table class='list-table'><thead><tr><th>id</th><th>post date</th><th>payer</th><th>ref</th><th>pay total</th><th>type</th><th>und</th></tr></thead>";
    
    while($ars = sqlFetchArray($arq)){ 
	    
	    
	    $sort_value = floor($ars['pay_total']);
	    
	    $table .= "<tr class='session-line' data-session='" . $ars['session_id'] . "'><td><a href='?session=" . $ars['session_id']. "'>" . $ars['session_id']. "</a></td><td>" . $ars['check_date']. "</td><td><a href='?session=" . $ars['session_id']. "'>" . getInsName("id-" . $ars['payer_id']). "</a></td><td>" . $ars['reference']. "</td><td data-sort-value='" . $sort_value. "'>" . $ars['pay_total']. "</td><td>" . $ars['payment_type']. "</td><td class='undistrib'></td></tr>";
	    
	 
	
	}
	
	$table .= "</table>";
	
	echo $table;
	
	}
	
	
?>



<script type="text/javascript">
	$(function(){
		
		var encounters = [<?=implode(",", $encounters);?>];
		
		/*
		$.each(encounters, function(i,val) {
			
			var row = $("tr#" + val);
			
			$.ajax({
					url:"/openemr/_ibh/ajax/get_encounter_billing.php",
					data:{encounter:val,has_fee:1},
					success: function(bil) {

				        $.each(bil.billing, function(i2, val2) {
					       row.before("<tr class='bills'><td colspan=4></td><td>" + val2.code + "</td><td>" + val2.fee + "</td><td colspan=5>" + val2.process_file + "</td></tr>"); 
				        });
						
					}
			});
		
		
		});
		*/
		
		$.tablesort.defaults = {
			compare: function(a, b) { // Function used to compare values when sorting.
				if (!isNaN(a) && !isNaN(b)) {
					a = Number(a);
					b = Number(b);
				}
				
				
				if (a > b) {
					return 1;
				} else if (a < b) {
					return -1;
				} else {
					return 0;
				}
			}
		};
		
		$(".session-line").each(function(it) {
			
			var $row = $(this);
			var session = $row.data("session");
			var $cell = $row.find(".undistrib");
			
			$.ajax({
					url:"era_overview.php",
					data:{get_session_undistributed:session},
					success: function(bil) {
						
						$cell.text("$" + bil.undistributed);
						
					}
			});
			
			
			
		});
		
		var picker1 = new Pikaday({ field: $('#picker1')[0], format: 'YYYY-MM-DD' });
		var picker2 = new Pikaday({ field: $('#picker2')[0], format: 'YYYY-MM-DD' });

		
		$('.list-table').tablesort();
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
		
	});
</script>


</div>

</body>
</html>