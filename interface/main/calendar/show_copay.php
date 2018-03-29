<?php
require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/patient.inc');

     $pid = filter_input(INPUT_GET, "pid", FILTER_VALIDATE_INT);
 
	if(!empty($pid)){
	   $sql = "SELECT copay FROM `insurance_data` WHERE pid =" . $pid;
           $res = sqlQuery($sql);
	}else{
		echo "Please select a patient first";
		exit;
	}


   if(!empty($res['copay'])){
	       print "<input type='hidden' name='copayInfo' id='copayInfo' value='2'>";
           print "Collect Copay:  $".$res['copay']."<br>";
	       print "Patient Balance $".get_patient_balance($pid, $with_insurance=false);
   }else{
	       print "<input type='hidden' name='copayInfo' id='copayInfo' value='3'>";	   
           print "No Copay due <br>";
	       print  "Patient Balance $".get_patient_balance($pid, $with_insurance=false);
   }    
?>
<br>


