<?php
 
	include_once ("../../globals.php");

	$sql = "SELECT username, fname, lname FROM users WHERE info LIKE '%supervisor%'";
	$res = sqlStatement($sql);
	
	
?>
<!DOCTYPE html>
<html>
<title>Alerting Supervisors</title>
<head>
<?php // html_header_show();?>
<link rel="stylesheet" href="/openemr/interface/themes/style_metal.css" type="text/css">

<!-- IBF_DEV add link to stylesheet -->
<link rel="stylesheet" href="/openemr/_ibh/css/encounter.css" type="text/css">




</head>
<body class="body_top">
<h3>Select Supervisor(z)</h3>
  <form method = "post" action="../../patient_file/reminder/supervisor_reminder.php" >
	  
<?php  
	         
		    $i=1;
            while ($row = sqlFetchArray($res)){
				  
				  //echo $row['username']." - ".$row['fname'] ." ". $row['lname'] . "<br>";
				echo "<input type='radio' name='supervisor' value='".$row['username']."'>" . $row['fname'] ." ". $row['lname'] ."<br>";
				++$i;
			}
			
?><div style="text-align:center;padding:14px;">
	 <input class="go-button" type="submit" value="SEND SUPERVISOR ALERT">
</div>
  </form>
<br>
 
</body>
</html>