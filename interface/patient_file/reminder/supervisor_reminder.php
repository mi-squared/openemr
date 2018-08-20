<?php
// Copyright (C) 2011 by following authors:
//  Sherwin Gaddis <sherwingaddis@gmail.com>
//  
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once("../../globals.php");
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");


// 25394
$pid = $GLOBALS['pid'];  
$userid = $_SESSION['authUserID'];
$uname = $_SESSION['authUser'];

$landed = implode(",", $_POST);

$ea_supervisor = explode(",", $landed);


/*
 *  This is to insert message into the message table to alert supervisors
 */

$date = date("Y-m-d H:m:s");
$nice_date = date("F j, Y, g:i a");

 
function pass_note($supervisor, $uname, $pid, $date, $encounter){
	
	
	// get encounter info
	$einfo = ibh_get_encounter_info($encounter);
	$slash_date = $einfo['slash_date'];

    // get supervisor user id
    $sup = sqlStatement("SELECT id FROM users WHERE username='$supervisor'");
    $sup_arr = sqlFetchArray($sup);
    $supervisor_user_id = $sup_arr['id'];
    
    // old records had subject (`title`) as "New Document" (eyeroll)
    // now uses "Supervisor Alert"
   $datetime = date("Y-m-d H:i:s");
   
   $sql2 = "UPDATE form_encounter SET supervisor_id=?, last_supervisor_alert='$datetime' WHERE encounter=?";

   sqlStatement($sql2, array($supervisor_user_id, $encounter)); 
    
    
}

/*
 *  check to see who is the supervisor
 */
 
$encounter = $GLOBALS['encounter'];


//echo $supervisor;
//echo $encounter;
//$i = 0;
//echo $ea_supervisor[0] . "<br>";
if($ea_supervisor[0]){
        $supervisor = $ea_supervisor[0];
        pass_note($supervisor, $uname, $pid, $date, $encounter);
  }
// echo $ea_supervisor[1] . "<br>";

/*
if($ea_supervisor[1]){
	          ++$i;
              $supervisor = $ea_supervisor[1];
        pass_note($supervisor, $uname, $pid, $date, $encounter);
  }
echo $ea_supervisor[2] . "<br>";
if($ea_supervisor[2]){
	          ++$i;	
              $supervisor = $ea_supervisor[2];
        pass_note($supervisor, $uname, $pid, $date, $encounter);
  }
echo $ea_supervisor[3] . "<br>";
if($ea_supervisor[3]){
	          ++$i;	
              $supervisor = $ea_supervisor[3];
        pass_note($supervisor, $uname, $pid, $date, $encounter);
  }
echo $ea_supervisor[4] . "<br>";
if($ea_supervisor[4]){
	          ++$i;	
              $supervisor = $ea_supervisor[4];
        pass_note($supervisor, $uname, $pid, $date, $encounter);
  }
echo $ea_supervisor[5] . "<br>";

echo $ea_supervisor[6] . "<br>";
*/


header("Location: ../encounter/forms.php?hi=1&s1=$ea_supervisor[0]");

// supervisor_review=1&pid=108&set_encounter=19071

?>