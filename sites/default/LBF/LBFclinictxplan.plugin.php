<?php
// Copyright (C) 2015 Sherwin Gaddis sherwingaddis@gmail.com
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// the purpose of this javascript is to populate the diagnosis for with patient information
//

function billing_info(){
	
		global $pid;
	       $date = date("Y-m-d");
		   
		   $sql = sqlStatement("SELECT pc_title, pc_startTime FROM 
		   openemr_postcalendar_events WHERE pc_pid LIKE '$pid' AND 
		   pc_eventDate = '$date' AND pc_apptstatus LIKE '@' ");
	
	       $res = sqlFetchArray($sql);
		   
		   $title = $res['pc_title'];
		   $time = $res['pc_startTime'];
		   
		   if(!empty($title)){
		   $info = " 
		         
		          var info = ' $title ' + ' $time ' + ' $date ';
				  var testing = 'Done';
				  if(form_info.value == ''){
                   document.getElementById('form_info').value = info;			  
		          }
		   
		         ";
		      }else{
				  $info = "
				  
				     var info = ' Patient not checked into this encounter ';
					 if(form_info.value == ''){
						 document.getElementById('form_info').value = info;
					 }
				  ";
			  }
		   return $info;	
}

function LBFclinictxplan_javascript_onload() {
    global $pid, $encounter;
	
	echo billing_info();
	
	     //GET ORGINAL DIAGNOSIS
		$of_id = sqlStatement("SELECT form_id, encounter FROM forms WHERE form_name = 'Patient Diagnosis' AND pid = $pid ORDER BY encounter ASC LIMIT 1");
		$ores = sqlFetchArray($of_id);
			if(!empty($ores['form_id'])){
				$odiag = sqlStatement("SELECT field_value FROM lbf_data WHERE form_id = " . $ores['form_id'] . "");
				$original_diag = sqlFetchArray($odiag);
		}

$odv = $original_diag['field_value'];

    $fo = explode(":", $odv);
	$fdia = sqlQuery("SELECT short_desc FROM `icd10_dx_order_code` WHERE `formatted_dx_code` = '".$fo[1]."' ");
	$odesc = $fdia['short_desc'];		 
		 
	    //GET THE CURRENT DIAGNOSIS
		$f_id = sqlStatement("SELECT form_id, encounter FROM forms WHERE form_name = 'Patient Diagnosis' AND pid = $pid ORDER BY encounter DESC LIMIT 1 ");
		$res = sqlFetchArray($f_id);
			if(!empty($res['form_id'])){
				$diag = sqlStatement("SELECT field_value FROM lbf_data WHERE form_id = " . $res['form_id'] . "");
				$current_diag = sqlFetchArray($diag);
		}

$cdv = $current_diag['field_value'];

    $cfo = explode(":", $cdv);
	$fcdia = sqlQuery("SELECT short_desc FROM `icd10_dx_order_code` WHERE `formatted_dx_code` = '".$cfo[1]."' ");
	$cdesc = $fcdia['short_desc'];

        //DETERMINE AGE OF PATIENT
		$sql = "SELECT DOB FROM `patient_data` WHERE pid = $pid";
		$get_dob = sqlStatement($sql);
		$res_dob = sqlFetchArray($get_dob);
		$dob = $res_dob['DOB'];
		$eighteen = date('Y-m-d', strtotime('-18 years'));
		if($dob <= $eighteen){
		   echo "document.getElementById('outerdiv_lbf4').style.display = 'none';";
		}		
	echo " var c_diag = ' $cdv ';
	       var o_diag = ' $odv ';
		   var o_desc = ' $odesc ';
		   var c_desc = ' $cdesc ';
	       document.getElementById('form_cur_diag_2').value = c_diag + c_desc;
	       document.getElementById('form_ori_1').value = o_diag + o_desc;
		   
		   
		   
	";

}
