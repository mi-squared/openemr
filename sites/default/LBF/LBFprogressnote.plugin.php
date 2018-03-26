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

function LBFprogressnote_javascript_onload(){
   
   echo billing_info();

  }