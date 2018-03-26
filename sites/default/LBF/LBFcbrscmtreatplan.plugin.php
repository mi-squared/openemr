<?php
// Copyright (C) 2015 Sherwin Gaddis sherwingaddis@gmail.com
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// the purpose of this javascript is to populate the diagnosis for with patient information
//

function current_diagnosis(){
	global $pid;
	
	$sql = "SELECT form_id, encounter FROM forms WHERE form_name = 'Patient Diagnosis' AND pid = $pid ORDER BY encounter DESC LIMIT 1";
	$fid = sqlStatement($sql);
	$fres = sqlFetchArray($fid);
	
	$c_diag = sqlStatement("SELECT form_id, field_value FROM lbf_data WHERE form_id = '".$fres['form_id']."' AND field_id LIKE '%diag%'");
	$cdiag = sqlFetchArray($c_diag);
	$see = $cdiag['field_value'];
	
	$gdia = explode(":", $see);
	$fdia = sqlQuery("SELECT short_desc FROM `icd10_dx_order_code` WHERE `formatted_dx_code` = '".$gdia[1]."' ");
	$desc = $fdia['short_desc'];
	
	  $view = " 
	           var cdiag = ' $see ';
			   var desc = ' $desc ';
			   if(form_cur_diag_2.value == ''){
				   document.getElementById('form_cur_diag_2').value = cdiag + desc;
			   }
	          ";
	
	return $view;
	
}

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

function LBFcbrscmtreatplan_javascript_onload(){

       echo billing_info();
	   
	   echo current_diagnosis();

  }