<?php
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");

// IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");


?><html>
<title></title>
<head>
<script type="text/javascript" src="<?=  $GLOBALS['webroot'] ?>/_ibh/js/jquery_latest.min.js"></script>

<link rel="stylesheet" href="<?=  $GLOBALS['webroot'] ?>/interface/themes/style_metal.css" type="text/css">
<link rel="stylesheet" href="<?=  $GLOBALS['webroot'] ?>/_ibh/css/encounter.css" type="text/css">

</head>
<body class="overview-pane">
<div class="ibh-wrapper">
	
<h4>Billing Fix...</h4>
<style>
	.dupe {
		background-color:#adbd67;
		
	}
	.okay {
		background-color:white;
	}
	
	.void {
		background-color:#222;
	}
</style>

<table class="list-table">
<?php
	
	
	$sql = "SELECT b.*, u.lname, u.fname FROM billing b LEFT JOIN users u ON b.provider_id=u.id WHERE b.date >'2017-07-01 00:00:00' ORDER BY b.encounter DESC, b.code_text";
	
	
	// echo "<tr><td colspan='7'>" . $sql . "</td></tr>";
    $bills = sqlStatement($sql);
    
    while($b = sqlFetchArray($bills)){ 
	    
	    $en = $b['encounter'];
	    $ct = $b['code_text'];
	    $ju = $b['justify'];
	    $bid = $b['id'];
	    
	    
	    if ($last_en == $en && $last_ct == $ct) {
		    $class = "dupe";
		    
		    // sqlStatement("DELETE FROM billing WHERE id='$bid'");
		    
		    
	    } else {
		    $class = "okay";
	    }
	    
	    if ($en != $last_en) {
		    echo "<tr class='void'><td colspan='7'></td></tr>";
	    }
	    
	    ?>
	    
	    <tr class='<?=$class?>'><td><?=$b['date']?></td><td><?=$b['fname'] . " " . $b['lname']?></td><td><?=$b['encounter']?></td><td><?=$b['code_text']?></td><td><?=$b['fee']?></td><td><?=$b['justify']?></td><td><?=$b['id']?></td></tr>
	    <?php
	    
	    
	    $last_en = $en;
	    $last_ct = $ct;
	    $last_ju = $ju;
	
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
		
	});
</script>


</div>

</body>
</html>