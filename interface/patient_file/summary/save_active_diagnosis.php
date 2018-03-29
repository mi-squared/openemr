<?php
/*******************************************************************************\
 * Copyright 2010 Brady Miller <brady@sparmy.com>                               *
 * Copyright 2011 Rod Roark <rod@sunsetsystems.com>                             *
 *                                                                              *
 * This program is free software; you can redistribute it and/or                *
 * modify it under the terms of the GNU General Public License                  *
 * as published by the Free Software Foundation; either version 2               *
 * of the License, or (at your option) any later version.                       *
 *                                                                              *
 * This program is distributed in the hope that it will be useful,              *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
 * GNU General Public License for more details.                                 *
 *                                                                              *
 * You should have received a copy of the GNU General Public License            *
 * along with this program; if not, write to the Free Software                  *
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.  *
 * @ author Sherwin Gaddis <sherwingaddis@gmail.com>                            *
 ********************************************************************************/

//SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

require_once("../../globals.php");
global $pid;

$d = (filter_input(INPUT_GET, "d", FILTER_VALIDATE_INT));

$sql = sqlQuery("SELECT diag FROM active_diagnosis WHERE pid = $pid AND diag = $d");

  
if(empty($sql)){
	//echo "Insert";
	sqlStatement("INSERT INTO active_diagnosis (pid, diag) VALUES ('$pid', '$d')");
}else {
	//echo "Delete";
	sqlStatement("DELETE FROM `active_diagnosis` WHERE `pid` = $pid AND diag = $d"); 
}


    
	
//print "finished".$pid.$d;
echo "<script>window.close();</script>";
