<?php

/* 
 * This file is to update the fee sheet from the entry of Interactive Complexity popup
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

require_once('../../globals.php');

global $pid, $encounter;
$en = date("Y-m-d");

echo  $pid ." ". $encounter . "<br><font color='red'> Update Form Error!</font><br/>";


function interactive_c($encounter, $pid){

    
            //Get date of the encounter.
            //so that the correct billing entry can be recorded
           $t = date("H:m:s");
            $query = sqlStatement("SELECT date, provider_id FROM form_encounter WHERE encounter = '$encounter'");
            $res = sqlFetchArray($query);
            $e_date = $res['date'];
            $prov = $res['provider_id'];
            $en_date = explode(" ", $e_date);
            $en = $en_date[0]." ".$t;
 
 		       function cdiag($pid){

                       //retrieve the last diagnosis for the patient
                  $f_id = sqlStatement("SELECT form_id, encounter FROM forms WHERE form_name = ".
                                       "'Patient Diagnosis' AND pid = $pid ORDER BY encounter DESC LIMIT 1");

                    $res = sqlFetchArray($f_id);
					 
						$diag = sqlStatement("SELECT field_value FROM lbf_data WHERE form_id = '" . $res['form_id'] . "'");
						$i = 0;
						while($current_diag = sqlFetchArray($diag)){
							if($i == 0){
							$cdi = $current_diag['field_value'];
							
							} else { break; }
							++$i;
						}
						
						$justify = $cdi;

						return $justify; 		
                }
				
		   $justify = cdiag($pid);
		   $just = explode(":", $justify);
		   
                sqlStatement("INSERT INTO billing SET " .
                            "date = '$en' , " .
                            "code_type = 'CPT4', " .
                            "code = '90785', " .
                            "pid = '$pid', " .
                            "provider_id = '$prov', " .
                            "user = '" . $_SESSION['authUserID'] . "', " .
                            "groupname = 'Default', " .
                            "authorized = '1', " .
                            "encounter = '$encounter', " .
                            "code_text = 'Interactive Complexity', " .
							"bill_date = '".date("Y-m-d")."', " .  
                            "activity = '1', " .
                            "units = '1', " .
                            "fee = '20.00', " .
                            "justify = '".$just[0]."|".$just[1] .":'"
                             ); 
            
            echo "<script>window.close();</script>";
       
            
            
            
}
     //Enters Interactive complexity into the billing table
if(filter_input(INPUT_GET, 'com') == 1){
    
    interactive_c($encounter, $pid);
}



    function eSign_checkout($pid, $encounter, $m){

	            //Function No longer needed -- will be deleted at future date.  
             function timeUpdate($pid, $en){
                  
				  $d = explode(" ", $en);
				  $en = $d[0];
				  $sql = "SELECT pc_apptstatus FROM openemr_postcalendar_events WHERE pc_pid LIKE '$pid' AND pc_eventDate LIKE '$en'  LIMIT 1";
				  $ft = sqlStatement($sql);
				  $res = sqlFetchArray($ft);
				  //echo $res['pc_apptstatus']. "App Status.";
				  if(!empty($res['pc_apptstatus']) || $res['pc_apptstatus'] != '>'){
                  $sql = "UPDATE openemr_postcalendar_events SET pc_apptstatus = '>'
				          WHERE pc_pid LIKE '$pid' AND pc_eventDate LIKE '$en' AND pc_apptstatus = '@' LIMIT 1";
                   sqlStatement($sql);
				 }
                 //doesn't return anything just updates the record to the actual exit time.
              }

              function cdiag($pid){

                       //retrieve the last diagnosis for the patient
                  $f_id = sqlStatement("SELECT form_id, encounter FROM forms WHERE form_name = ".
                                       "'Patient Diagnosis' AND pid = $pid ORDER BY encounter DESC LIMIT 1");

                    $res = sqlFetchArray($f_id);
					 
						$diag = sqlStatement("SELECT field_value FROM lbf_data WHERE form_id = '" . $res['form_id'] . "'");
						$i = 0;
						while($current_diag = sqlFetchArray($diag)){
							if($i == 0){
							$cdi = $current_diag['field_value'];
							
							} else { break; }
							++$i;
						}
						
						$justify = $cdi;

						return $justify; 
						
						
              }
			  
			  function getPrice($c){
				  
				  $sql = "SELECT a.id, b.pr_id, b.pr_price FROM codes AS a, prices AS b WHERE a.code LIKE '$c' AND b.pr_id = a.id";
				  $p = sqlStatement($sql);
				  $pres = sqlFetchArray($p);
				  
				  return $pres['pr_price'];
				  
			  }
			  
			             //retrieve the appointment title to get billing code info
			  
			  function bInfo($pid, $encounter){
			              //retrieve encounter date that should match appointment date
			      $sql = "SELECT date, provider_id FROM form_encounter WHERE pid = '$pid' AND encounter = '$encounter'";
				  $enD = sqlStatement($sql);
				  $enDa = sqlFetchArray($enD);
				  $enDate = $enDa['date'];
				  $prov = $enDa['provider_id'];
				  
				          
			      $sql = "SELECT pc_title, pc_startTime, pc_endTime FROM openemr_postcalendar_events WHERE pc_pid = '$pid' AND pc_time >= '$enDate' AND pc_apptstatus = '@'";
				  $ti = sqlStatement($sql);
				  $ttx = sqlFetchArray($ti);
				  $title = $ttx['pc_title'];
				  $stime = $ttx['pc_startTime'];
				  $etime = $ttx['pc_endTime'];
			  
			      return array($title, $prov, $enDate, $stime, $etime);
			  }
			  
			  //retrieve the provider id
                    //retrieve code description
              function icd($code, $type){
                  if($type == ICD9 ){
                  $txt = sqlStatement("SELECT long_desc FROM  icd9_dx_code WHERE formatted_dx_code LIKE '$code' ");
                  $res = sqlFetchArray($txt);
                  $desc = $res['long_desc'];
                  }else {
                  $txt = sqlStatement("SELECT long_desc FROM  icd10_dx_order_code WHERE formatted_dx_code LIKE '$code' ");
                  $res = sqlFetchArray($txt);
                  $desc = $res['long_desc'];    
                  }

                  return $desc;

              }
     /*
	 *  Code that runs and calls functions to gather information for the billing insert.
	 *  
	 */
                //$en = date("Y-m-d");
                $now = date("Y-m-d H:m:s");
                $bInfo = bInfo($pid, $encounter);      //billing info

                $bData = explode(":", $bInfo[0]);
				
                $t = $bData[0];                 // text
                $c = $bData[1];                 // code
				echo $t . $c ."<-C <br>";
				$c = trim($c);
				$price = getPrice($c);
				
                $prov = $bInfo[1];      
                $en = $bInfo[2];               //the encounter date which may not be today
                $j = cdiag($pid);
       
                $js = explode(":", $j);
                $code = $js[1];                //Fetch the description for this  ICD9
                $type = $js[0];                //holds the icd type 9 or 10
                $desc = icd($code, $type);
				
	/*********************************************************************************************/			
	
	//Set the units for these CPT codes
	if(	$c == "T1017" ||
		$c == "T1014" ||
		$c == "T1017" ||
		$c == "H2017" ||
		$c == "H2011" ||
		$c == "H0038" ||
		$c == "H0046" ||
		$c == "H0031" ||
		$c == "H0032") 
		{
          $startTime = $bInfo[3];
		  $timeNow = $bInfo[4];
		  $time = strtotime($timeNow) - strtotime($startTime);
		  $minutes = $time/60;
		  $u = round($minutes/15);
		  if($u < 0)
		  {
			  $u = -1 * $u;
		  }
		   
		  //Change the value of price. 
		  /*The neg 1 is to fix an issue that is occuring on the production
		   * system. The units and price are coming out negative.
           * so to fix it the -1 was added. Could figure out what was causing the 
           * neg numbers.           
		  */
		}else {
         $u = 1;
        }		
    /******************************************************************/
		$pricet = $u *  $price;		
  if(!empty($js[0])){
                //find out if there are any entries in the billing table for today for this patient
                $find = sqlStatement("SELECT pid, encounter FROM billing WHERE pid = '$pid' AND date = '$en'");
                $res = sqlFetchArray($find);
				
                if ($res['encounter'] != $encounter){ $b = "empty"; 
                    timeUpdate($pid, $en); //update the checkout and mark appt status >. 
                        sqlStatement("INSERT INTO billing SET " .
                                "date = '$en' , " .
                                "code_type = 'CPT4', " .
                                "code = '$c', " .
                                "pid = '$pid', " .
                                "provider_id = '$prov', " .
                                "user = '" . $_SESSION['authUserID'] . "', " .
                                "groupname = 'default', " .
                                "authorized = '1', " .
                                "encounter = '$encounter', " .
                                "code_text = '$t', " .
								"billed = '0' , ".								
								"modifier = '". $m ."', ".
                                "activity = '1', " .
							    "bill_date = '".date("Y-m-d")."', " .								
                                "units = '$u', " .
                                "fee = '$pricet', " .
                                "justify = '" . $js[0]."|".$js[1] .":'"
                                 ); 

                        sqlStatement("INSERT INTO billing SET "
                                . "date = '$en',"
                                . "code_type = '$js[0]',"
                                . "code = '$js[1]',"
                                . "pid = '$pid',"
                                . "provider_id = '$prov',"
                                . "user = '" . $_SESSION['authUserID'] . "',"
                                . "groupname = 'default',"
                                . "authorized = '1',"
                                . "encounter = '$encounter',"
                                . "code_text = '$desc',"
								. "billed = '0' , "								
                                . "activity = '1',"
								. "bill_date = '".date("Y-m-d")."',"
                                . "units = '1', "
                                . "fee = '0.00' "
                                );

                   }
				  echo "<script>window.close();</script>";
				  
              }else {echo "<br/><b><font color='red'>No Diagnosis has been entered, <br/><br/>Patient cannot be checked out. </font></b>"; exit;}
	     }
		 
if(filter_input(INPUT_POST, 'mod')){
	$m = filter_input(INPUT_POST, 'mod');
	$checkoutTime = filter_input(INPUT_POST, 'usr_time').":00";
		if($m == 1){
			$m = " ";
			echo "This is the value of M --" . $m;
			eSign_checkout($pid, $encounter, $m, $checkoutTime);
		  } else {
			  //echo $m;
			  eSign_checkout($pid, $encounter, $m, $checkoutTime);
		  }

}
       function inuse($used, $pid, $encounter, $name, $prov){
		   
		       function cdiag($pid){

                       //retrieve the last diagnosis for the patient
                  $f_id = sqlStatement("SELECT form_id, encounter FROM forms WHERE form_name = ".
                                       "'Patient Diagnosis' AND pid = $pid ORDER BY encounter DESC LIMIT 1");

                    $res = sqlFetchArray($f_id);
					 
						$diag = sqlStatement("SELECT field_value FROM lbf_data WHERE form_id = '" . $res['form_id'] . "'");
						$i = 0;
						while($current_diag = sqlFetchArray($diag)){
							if($i == 0){
							$cdi = $current_diag['field_value'];
							
							} else { break; }
							++$i;
						}
						
						$justify = $cdi;

						return $justify; 		
                }
				
		   $justify = cdiag($pid);
		   $just = explode(":", $justify);
		   
		   
          $fee = 20;
		  $total = $fee * $used;
		   $en = date("Y-m-d H:m:s");
		            sqlStatement("INSERT INTO billing SET " .
                                "date = '$en' , " .
                                "code_type = 'CPT4', " .
                                "code = 'T1013', " .
                                "pid = '$pid', " .
                                "provider_id = '$prov', " .
                                "user = '" . $_SESSION['authUserID'] . "', " .
                                "groupname = 'default', " .
                                "authorized = '1', " .
                                "encounter = '$encounter', " .
                                "code_text = 'Interpreter', " .
                                "activity = '1', " .
                                "units = '$used', " .
                                "fee = '$total', " .
                                "justify = '". $just[0] . "|". $just[1] .":', " .
								"notecodes = '".$used ." Units - ". $name."' " 
                                 ); 
	         echo "<script>window.close();</script>";
			
	   }

  if(filter_input(INPUT_POST, "iused")){
      $minutes = filter_input(INPUT_POST, "iused", FILTER_VALIDATE_INT);
	  $name = filter_input(INPUT_POST, "iname", FILTER_SANITIZE_SPECIAL_CHARS);
	 $used = $minutes;
	 $sql = "SELECT provider_id FROM form_encounter WHERE pid = '$pid' AND encounter = '$encounter'";
	 $res = sqlStatement($sql);
	 $provid = sqlFetchArray($res);
	 $prov = $provid['provider_id'];
	 
	 
	 inuse($used, $pid, $encounter, $name, $prov);
	 
  }
          