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

$en = $GLOBALS['encounter'];
$pid = $GLOBALS['pid'];
$fid = sqlStatement("SELECT form_id, encounter FROM forms WHERE form_name = 'Patient Diagnosis' AND pid = $pid ORDER BY encounter DESC LIMIT 1");
$pdi = sqlFetchArray($fid);

$f_id = sqlStatement("SELECT field_value FROM lbf_data WHERE form_id = '".$pdi['form_id']."' AND field_id LIKE '%diag%'");
      $i=0;
	  
$aSql = "SELECT diag FROM active_diagnosis WHERE pid = $pid";
$aRes = sqlStatement($aSql);
$active = array();

	while($a = sqlFetchArray($aRes)){
	   $active[] = $a;
    }
	
//var_dump($active);

$t = $active[0][diag];
$u = $active[1][diag];
$v = $active[2][diag];
$w = $active[3][diag];
$x = $active[5][diag];

?>

<table>
<?php	  
      $l = 1;
while ($res = sqlFetchArray($f_id)){
	      
			$dc = $res['field_value'];
    		$code[$i] = "<font color='blue'>".$dc."</font><br>";
            $dc = explode(":", $dc);
			
			if($dc[0] == 'ICD10'){
			$sql = "SELECT short_desc FROM icd10_dx_order_code WHERE formatted_dx_code LIKE '".$dc[1]."'";
			$q = sqlQuery($sql);
            $desc[$i] = "Description: ".$q['short_desc'] . " <br>";
			}
			if($dc[0] == 'DSM5'){
			$sql = "SELECT code_text FROM codes WHERE code LIKE '".$dc[1]."'";
			$q = sqlQuery($sql);
			$desc[$i] = "Description: ".$q['code_text'] . " <br>";	
			}
			print "<tr><td><input type='checkbox' name='checkbox' id='checkbox".$i."' 
			      value='".$i."' onclick='activeDiag".$i."()' ";
				  if( $t == $l ||
				      $u == $l ||
                      $v == $l ||
					  $w == $l ||
					  $x == $l ){print "checked";}
			print " title = 'Set as active diagnosis'></td><td>".$desc[$i].$code[$i]."</td></tr>";
			$l++;
			$i++;
	}
?>
</table>	

<script>
function activeDiag0(){
	dlgopen('save_active_diagnosis.php?d=1', '_blank', 200, 250);	
	//alert("set active");
}
function activeDiag1(){
	dlgopen('save_active_diagnosis.php?d=2', '_blank', 200, 250);
	//alert("set active");
}
function activeDiag2(){
	dlgopen('save_active_diagnosis.php?d=3', '_blank', 200, 250);
	//alert("set active");
}
function activeDiag3(){
	dlgopen('save_active_diagnosis.php?d=5', '_blank', 200, 250);
	//alert("set active");
}
function activeDiag4(){
	dlgopen('save_active_diagnosis.php?d=5', '_blank', 200, 250);
	//alert("set active");
}

</script>


