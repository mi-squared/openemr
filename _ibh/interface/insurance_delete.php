<?php
	
	// ini_set("display_errors", 1);
	
	$authUsers = array("mckenzieb", "admin", "tami", "TamiJ");
	
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

// IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");

function ibh_get_insurance_name($id) {
	$cres = sqlStatement("SELECT name FROM insurance_companies WHERE id='$id'");
	return sqlFetchArray($cres)['name'];
}

?><html>
<title></title>
<head>
<script type="text/javascript" src="<?=  $GLOBALS['webroot'] ?>/_ibh/js/jquery_latest.min.js"></script>

<link rel="stylesheet" href="<?=  $GLOBALS['webroot'] ?>/interface/themes/style_metal.css" type="text/css">
<link rel="stylesheet" href="<?=  $GLOBALS['webroot'] ?>/_ibh/css/encounter.css" type="text/css">

<script type="text/javascript" src="<?=  $GLOBALS['webroot'] ?>/_ibh/js/jquery.tablesort.js"></script>


<style>
</style>

</head>
<body class="overview-pane">
<div class="ibh-wrapper">
	
	<?php if (!in_array($_SESSION['authUser'], $authUsers)) {
		 echo "<h2>NO ACCESS</div>"; 
		 exit;  
		 
		 }
		 
		 
		 if (isset($_GET['delete_id'])) {
			 $did = $_GET['delete_id'];
			 echo "ARE YOU SURE YOU WANT TO DELETE " . ibh_get_insurance_name($_GET['delete_id']) . "?<br><br><a href='?confirm_delete_id=$did'>YES, DELETE IT</a>";
		 } else if (isset($_GET['confirm_delete_id'])) {
			 $cdid = $_GET['confirm_delete_id'];
			 $name = ibh_get_insurance_name($_GET['confirm_delete_id']);
			 sqlStatement("DELETE FROM insurance_companies WHERE id='$cdid'");
			 echo "DELETED " . $name . "!<br><br><a href='insurance_delete.php'>BACK TO LIST</a>";
		 } else {
	?>
	
	
	<h2>Delete Insurance</h2>
	<p>Creating and editing can be done at Administration > Practice > Insurance Companies</p>
	
	
	
	<table class="list-table">
	<thead>
		<tr><th>id</th><th>name</th><th>bills</th><th></th></tr>
	</thead>
	<tbody>
	<?php
		
    $insq = sqlStatement("SELECT * FROM insurance_companies ORDER BY name");
    
    while($i = sqlFetchArray($insq)){ 
	   
	   $del = "";
		   
	   $stmt = sqlStatement("SELECT COUNT(*) as ct FROM billing WHERE payer_id=" . $i['id']);
	   $res = sqlFetchArray($stmt);
	   $ct = $res['ct'];
	   if ($ct == 0) {
		   $del = "<a href='?delete_id=" . $i['id'] . "'>delete</a>";
	   }
	 
	 echo "<tr><td>" . $i['id'] . "</td><td>" . $i['name'] . "</td><td>" . $ct . "</td><td>" . $del . "</td></tr>";	 
	} // end while
	?>
	</tbody>
</table>




<script type="text/javascript">
	$(function(){
		
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


		$('.list-table').tablesort();
					
	});
</script>


<?php } ?>

</body>
</html>

