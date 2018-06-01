<html>
<title></title>
<head>
<script type="text/javascript" src="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/js/jquery_latest.min.js"></script>

<link rel="stylesheet" href="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/css/dated_reminders.css" type="text/css">
<style>
	.dr-table {
		width:90%;
		background-color:#f0f0f0;
		border:1px solid #ccc;
		border-collapse:collapse;
	}
	
	
	.dr-table  th{
		background-color:#333;
		color:white;
		text-align:left;
		font-weight:normal;
	}
	
	
	.dr-table  td{
		border:1px dotted #ccc;
		padding:4px;
	}
</style>

</head>
<body>
<div class="ibh-wrapper">


<?php
	
	
	// ini_set("display_errors", 1);
	
	
	require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/interface/globals.php");
	require_once("$srcdir/dated_reminder_functions.php"); 
	
	
	// Matt Johansen ID=52
	
	/*
	$sentBy = $_GET['sentBy'];       
    $sentTo = $_GET['sentTo'];  
	*/
	if(!$isAdmin){ 
      if(empty($_GET['sentBy']) and empty($_GET['sentTo']))
        $_GET['sentTo'] = array(intval($_SESSION['authId'])); 
    }
	
	$reminders = logRemindersArray();
	
	
	
	// print_r($remindersArray);
	
	
	/*
		    [senderID] => 98
            [messageID] => 2724
            [PatientID] => 5553
            [pDate] => 2017-05-15 14:59:43
            [sDate] => 2017-03-16 12:10:53
            [dDate] => 2017-05-15
            [PatientName] => Mr. Elijah  Davies
            [message] => treatment plan review
            [fromName] => Patti  Chromey LCPC
            [ToName] => Matt  Johansen LCPC
            [processedByName] =>  Matt  Johansen LCPC
    */
	?>
	
	<table class="dr-table">
	<tr><th>id</th><th>from</th><th>to</th><th>patient</th><th>due</th><th>message</th></tr>
	<?php
	foreach ($reminders as $r) {
		?>
		<tr><td><?=$r['messageID']?></td><td><?=$r['fromName']?></td><td><?=$r['ToName']?></td><td><?=$r['PatientName']?></td><td><?=$r['dDate']?></td><td><?=$r['message']?></td></tr>
		<?php
			
	}
	
	
	
	
?>
	</table>