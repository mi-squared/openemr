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

function pdiagnosis()
{
			global $pid;
		     //GET ORGINAL DIAGNOSIS
		$of_id = sqlStatement("SELECT form_id, encounter FROM forms WHERE form_name = 'Patient Diagnosis' AND pid = $pid ORDER BY encounter ASC LIMIT 1");
		$ores = sqlFetchArray($of_id);
			if(!empty($ores['form_id'])){
				$odiag = sqlStatement("SELECT field_value FROM lbf_data WHERE form_id = " . $ores['form_id'] . "");
                
				//Place the values from the table data into an array
				$dias = array();
				$i = 0;
				while($row = sqlFetchArray($odiag)){
					$dias[$i] = $row;
                   ++$i;
				}
		}
	//Access the data in the array since there are only three diagnosis	
     $d1value = $dias[0]['field_value'];
	 $d2value = $dias[1]['field_value'];
	 $d3value = $dias[2]['field_value'];
	 

    // Fetch the descriptions 
    $fo = explode(":", $d1value);
	$fdia = sqlQuery("SELECT short_desc FROM `icd10_dx_order_code` WHERE `formatted_dx_code` = '".$fo[1]."' ");
	$odesc = $fdia['short_desc'];		 

    $fo2 = explode(":", $d2value);
	$fdia2 = sqlQuery("SELECT short_desc FROM `icd10_dx_order_code` WHERE `formatted_dx_code` = '".$fo2[1]."' ");
	$odesc2 = $fdia2['short_desc'];
	
    $fo3 = explode(":", $d3value);
	$fdia3 = sqlQuery("SELECT short_desc FROM `icd10_dx_order_code` WHERE `formatted_dx_code` = '".$fo3[1]."' ");
	$odesc3 = $fdia3['short_desc'];	
	
	//Send data to form.
	  $text = " var d1 = '$fo[1]';
	            var d2 = '$fo2[1]';
				var d3 = '$fo3[1]';
				
				var box1 = document.getElementById('form_pri_diag_1').value;
				var box2 = document.getElementById('form_sec_diag_2').value;
				var box3 = document.getElementById('form_thir_diag_3').value;
				
				if(d1 != '' && box1 == ''){
	           document.getElementById('form_pri_diag_1').value = d1;
			   document.getElementById('form_cs_diag').value = '$odesc';
			   }
			   if(d2 != '' && box2 == ''){
			   document.getElementById('form_sec_diag_2').value = d2;
			   document.getElementById('form_cs_diag_sec').value = '$odesc2';
			   }
			   if(d3 != ''  && box3 == ''){
			   document.getElementById('form_thir_diag_3').value = d3;
			   document.getElementById('form_cs_diag_1').value = '$odesc3';
			   }
		     ";
    return $text;

}



function LBFcomp_javascript_onload(){
	
	echo billing_info();
	
    // echo pdiagnosis();
	

	
	
  }
