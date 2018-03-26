<?php
/**
 * This form is to update the fee sheet with the CPT codes
 * upon selection of the CPT code the code will be added to the fee sheet.
 * 
 *  Copyright (C) 2015 Sherwin Gaddis 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @package OpenEMR
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org  
 */
 require_once("../../globals.php");
 $w = (filter_input(INPUT_GET, "pop", FILTER_VALIDATE_INT));


if ($w == 3){
	
	$sql = "SELECT username, fname, lname FROM `users` WHERE `info` LIKE '%supervisor%'";
	$res = sqlStatement($sql);
	
	
?>
<!DOCTYPE html>
<?php 
include_once ("../../globals.php");
?>
<html>
<title>Alerting Supervisors</title>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<h3>Select Supervisor(s)</h3>
  <form method = "post" action="../../patient_file/reminder/supervisor_reminder.php" >
         <?php  
		            $i=1;
              while ($row = sqlFetchArray($res)){
				  
				  //echo $row['username']." - ".$row['fname'] ." ". $row['lname'] . "<br>";
				  echo "<input type='radio' name='supervisor' value='".$row['username']."'>" . $row['fname'] ." ". $row['lname'] ."<br>";
				  ++$i;
			  }
			  //var_dump($rows);
		 ?><br>
	 <input type="submit" value="Send Supervisor Alert">
  </form>
<br>
 
</body>
</html>
<?php }?>

<?php 
if($w == 4){
		$sql = "SELECT username, fname, lname FROM `users` WHERE `info` LIKE '%supervisor%' ";
	$res = sqlStatement($sql);
?>
<!DOCTYPE html>
<?php 
include_once ("../../globals.php");
?>
<html>
<title>Alerting Supervisors</title>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<h3>Select Supervisor(s)</h3>
  <form method = "post" action="../reminder/supervisor_reminder.php" >
         <?php  
		            $i=1;
              while ($row = sqlFetchArray($res)){
				  
				  //echo $row['username']." - ".$row['fname'] ." ". $row['lname'] . "<br>";
				  echo "<input type='radio' name='supervisor' value='".$row['username']."'>" . $row['fname'] ." ". $row['lname'] ."<br>";
				  ++$i;
			  }
			  //var_dump($rows);
		 ?><br>
		 <label>Message</label><br>
		 <textarea>  </textarea><br>
	 <input type="submit" value="Select">
  </form>
<br>
 
</body>
</html>
<?php } ?>

<?php 
if($w == 1){
?>
<html>
<title>Interpreter Used
</title>
<head>
	  <script language="JavaScript">
	    function validateForm(){
			var x = document.forms["interp"]["iname"].value;

			if(x == null || x == "") {
				alert("Enter Name Please");
				return false;
			}


	  </script>
</head>
<body>
<h3>Interpreter Used?</h3>
  <form method = "post" action="update_fs.php" name="interp" onsubmit="return validateForm()">
       <label>Units</label><br>
		 <select name="iused">cheecheecheers
         <option value="1"> 1 </option>
		 <option value="2"> 2 </option>
		 <option value="3"> 3 </option>
		 <option value="4"> 4 </option>
		 <option value="5"> 5 </option>
         <option value="6"> 6 </option>
		 <option value="7"> 7 </option>
		 <option value="8"> 8 </option>
		 <option value="9"> 9 </option>
		 <option value="10"> 10 </option>
         <option value="11"> 11 </option>
		 <option value="12"> 12 </option>
		 <option value="13"> 13 </option>
		 <option value="14"> 14 </option>
		 <option value="15"> 15 </option>
         <option value="16"> 16 </option>
		 <option value="17"> 17 </option>
		 <option value="18"> 18 </option>
		 <option value="19"> 19 </option>
		 <option value="20"> 20 </option>		 
         <option value="21"> 21 </option>
		 <option value="22"> 22 </option>
		 <option value="23"> 23 </option>
		 <option value="24"> 24 </option>
		 <option value="25"> 25 </option>		 		 
		 </select><br>
	   <label>Name</label><br/>
	     <input type = "text" name = "iname" size = "15" value= "">
	 <input type="submit" value="Save">
  </form>
  <br>
  <input type="button" value="No, close window" onclick="self.close()">
</body>
</html>

<?php
 }
 
$encounter = $GLOBALS['encounter'];
$pid = $GLOBALS['pid'];

 if ($w == 2){
	 
	 $sql = "SELECT bill_date FROM billing WHERE pid = $pid AND encounter = $encounter";
	 $billed = sqlQuery($sql);
	 $billed_date = explode(" ", $billed['bill_date']);

	 //Checking to see there is an entry in the billing table 
	 //If there is skip billing modifier
	 if(!empty($billed_date[0])){
		 if($billed_date[0] != date("Y-m-d")){
			 echo "<script>window.close();</script>";
			 exit;		 
		 }
	 }
 ?>
 
<html>
<title>Adding Billing & Modifer
</title>
<head>
</head>
<body>
<h3>Please Select Billing Modifier if need?</h3>
  <form method = "post" action="update_fs.php" >
       <label>Modifiers</label><br>
		 <select name="mod">
         <option value="1"> None Needed </option>
		 <option value="76"> 76 </option>
		 <option value="25"> 25 </option>
		 <option value="59"> 59 </option>
		 <option value="GT"> GT </option>
		 <option value="U1"> U1 </option>
		 </select><br><br>

	 <input type="submit" value="Proceed w/Checkout">
  </form>
<br>
  
</body>
</html>
 <?php } ?>