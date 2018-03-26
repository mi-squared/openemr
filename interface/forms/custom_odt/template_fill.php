<?php

/**
 * Document Template Download Module.
 *
 * Copyright (C) 2013-2014 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package OpenEMR
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 */

/**
 * This sample script replaces some content in a template
 */
//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//
require_once('../../globals.php');
require_once($GLOBALS['srcdir'] . '/acl.inc');
require_once($GLOBALS['srcdir'] . '/htmlspecialchars.inc.php');
require_once($GLOBALS['srcdir'] . '/formdata.inc.php');
require_once($GLOBALS['srcdir'] . '/formatting.inc.php');
require_once($GLOBALS['srcdir'] . '/appointments.inc.php');
require_once($GLOBALS['srcdir'] . '/options.inc.php');

require_once '../../../library/docxpresso/CreateDocument.inc';


$u = $_SESSION['authUserID'];

//Find the current encouter if there is one. 
$sql = "SELECT encounter FROM form_encounter WHERE pid = $pid AND date = '$today'";
$qd = sqlQuery($sql);

//auto load the current encounter from database - too many clicks
if(!empty($qd)){
$encounter = $qd['encounter'];
}else{
    echo "Call front desk to have patient checked in.";
    die("no encounter found for patient");
    exit;
}
// Get some info for the currently selected encounter.
if ($encounter) {
  $enrow = sqlQuery("SELECT * FROM form_encounter WHERE pid = ? AND " .
    "encounter = ?", array($pid, $encounter));
}

// Get patient demographic info.
$ptrow = sqlQuery("SELECT pd.*, " .
  "ur.fname AS ur_fname, ur.mname AS ur_mname, ur.lname AS ur_lname " .
  "FROM patient_data AS pd " .
  "LEFT JOIN users AS ur ON ur.id = pd.ref_providerID " .
  "WHERE pd.pid = ?", array($pid));

$hisrow = sqlQuery("SELECT * FROM history_data WHERE pid = ? " .
  "ORDER BY date DESC LIMIT 1", array($pid));

$enrow = array();
//end patient demographic info


$file = filter_input(INPUT_POST, 'template');
$cfile = explode(".", $file);

//House keeping for errors in the process delete temp files
$files = glob('*.odt');
foreach($files as $f){
    unlink($f);
}

echo "Are you sure <b>" . $file . "</b> is the required file. If so, <br>";
$previous = $cfile[0]."-". $pid .".pdf";

if(file_exists($previous)){
    
    unlink($previous);
    
}

$name = $ptrow['fname'] ." ". $ptrow['lname'];
$date = date("Y-m-d");


// Return a string naming all issues for the specified patient and issue type.
function getIssues($type) {
  // global $itemSeparator;
  $tmp = '';
  $lres = sqlStatement("SELECT title, comments FROM lists WHERE " .
    "pid = ? AND type = ? AND enddate IS NULL " .
    "ORDER BY begdate", array($GLOBALS['pid'], $type));
  while ($lrow = sqlFetchArray($lres)) {
    if ($tmp) $tmp .= '; ';
    $tmp .= $lrow['title'];
    if ($lrow['comments']) $tmp .= ' (' . $lrow['comments'] . ')';
  }
  return $tmp;
}

//Get referring doc
      $tmp = empty($ptrow['ur_fname']) ? '' : $ptrow['ur_fname'];
      if (!empty($ptrow['ur_mname'])) {
        if ($tmp) $tmp .= ' ';
        $tmp .= $ptrow['ur_mname'];
      }
      if (!empty($ptrow['ur_lname'])) {
        if ($tmp) $tmp .= ' ';
        $tmp .= $ptrow['ur_lname'];
      }
$therapist = "$tmp";

$pid = $ptrow['pid'];
//Get facility name from calendar info and appointment information
//Not all information has been used
        $findf = sqlStatement("SELECT pc_eid, pc_facility, pc_title, pc_eventDate, pc_startTime, pc_endTime, pc_hometext FROM " .
                              " openemr_postcalendar_events WHERE pc_pid = $pid ORDER BY pc_eid DESC LIMIT 1");
        $fres = sqlFetchArray($findf);
 
        $stime = $fres['pc_startTime'];
        $eTime = $fres['pc_endTime'];
        $cCode  = $fres['pc_title'];
        
//Pull facility information from appointment        
        $fid = $fres['pc_facility'];
        if(!empty($fid)){
        $pfac = sqlStatement("SELECT * FROM facility WHERE id = $fid");
        $facility_info = sqlFetchArray($pfac);
        }else{
            $facility_info['name'] = "none assigned";
        }
$facility = $facility_info['name'];
$facilityID = $facility_info['facility_npi'];
$facility_address = $facility_info['street'];
$facility_city = $facility_info['city'];
$facility_state =$facility_info['state'];
$facility_zip = $facility_info['postal_code'];
$facility_phone = $facility_info['phone'];
$facility_fax = $facility_info['fax'];

$dob = $ptrow['DOB'];

//Calculate age
        $b = $ptrow['DOB'];
        $n = $date;
        $diff = abs(strtotime($n) - strtotime($b));
        $age = floor($diff / (365*60*60*24));  
$pAge = "$age"; 


$pDOS = oeFormatShortDate(substr($enrow['date'], 0, 10));

//Get Chief Complaint
      $cc = $enrow['reason'];
      $patientid = $ptrow['pid'];
      $pDOS = substr($enrow['date'], 0, 10);
      // Prefer appointment comment if one is present.
      $evlist = fetchEvents($pDOS, $pDOS, " AND pc_pid = '$patientid' ");
      foreach ($evlist as $tmp) {
        if ($tmp['pc_pid'] == $pid && !empty($tmp['pc_hometext'])) {
          $cc = $tmp['pc_hometext'];
        }
      }
$complaint = "$cc";

$pSex  = $ptrow['sex'];
$pAddress = $ptrow['street'];
$pCity = $ptrow['city'];
$pState = $ptrow['state'];
$pZip  = $ptrow['postal_code'];

//Get phone number
$ptphone = $ptrow['phone_contact'];
      if (empty($ptphone)) $ptphone = $ptrow['phone_home'];
      if (empty($ptphone)) $ptphone = $ptrow['phone_cell'];
      if (empty($ptphone)) $ptphone = $ptrow['phone_biz'];
      if (preg_match("/([2-9]\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/", $ptphone, $tmp)) {
        $ptphone = '(' . $tmp[1] . ')' . $tmp[2] . '-' . $tmp[3];
      }
$pPhone = "$ptphone";

//Get current diagnosis
        $f_id = sqlStatement("SELECT form_id, encounter FROM forms WHERE form_name = 'Patient Diagnosis' AND pid = $pid ORDER BY encounter DESC LIMIT 1 ");
        $res = sqlFetchArray($f_id);
        if(!empty($res['form_id'])){
        $diag = sqlStatement("SELECT field_value FROM lbf_data WHERE form_id = " . $res['form_id'] );
        }
        $current_diag = sqlFetchArray($diag); 
        
$pDiagnosisC = $current_diag['field_value'];
$cdv = explode(":", $pDiagnosisC);

//Get description of the diagnosis code from database
    if(!empty($cdv) && $cdv[0] == ICD10 ){
       $sql = "SELECT short_desc FROM icd10_dx_order_code WHERE formatted_dx_code LIKE '".$cdv[1]."'";
       $q = sqlQuery($sql);
       $diagDescript = $q['short_desc'];
     }else{
       $sql = "SELECT short_desc FROM icd9_dx_code WHERE formatted_dx_code LIKE '".$cdv[1]."'";
       $q = sqlQuery($sql);
       $diagDescript = $q['short_desc'];
       }

//TODO remove static text
$pDiagnosisF = "Figure this one out";

//Get current person logged in
$sql = "SELECT fname, lname FROM users WHERE id = $u";
$q = sqlStatement($sql);
$qres = sqlFetchArray($q);
$pUserID = $qres['fname']." ".$qres['lname'];

//Get insurance ID
        $findi = sqlStatement("SELECT policy_number FROM insurance_data WHERE pid = '" . $GLOBALS['pid'] ."' AND type = 'primary'" );
        $insnum = sqlFetchArray($findi);
        $pInsur = $insnum['policy_number'];

//Get med info
        $sql = "SELECT a.drug, a.provider_id, a.dosage, a.size, a.unit, b.id, b.fname, b.lname FROM ".
                " prescriptions a, users b WHERE a.patient_id = $pid AND b.id = a.provider_id";
        $res = sqlStatement($sql);
        $medInfo = sqlFetchArray($res);
        $drugName = $medInfo['drug']; $drugDosage = $medInfo['dosage']; $doc = $medInfo['fname']." ".$medInfo['lname']; 
        
$currentMed = $medInfo['drug'];

$pDosage = $medInfo['size'];

$medDoc = "$doc";

//Get allergy list
$pAllergies = generate_plaintext_field(array('data_type'=>'24','list_id'=>''), '');

//Get Medical problem list
$pList = getIssues('medical_problem');

//Calculate total time of appointment
$timeTotal = strtotime($etime) - strtotime($stime);
        $hours = intval($timeTotal / 3600);
        $minutes = intval(($timeTotal % 3600) / 60 );
        $sec  = $timeTotal % 60;
        $answer = str_pad( $hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad( $minutes, 2, '0', STR_PAD_LEFT ) . ':' . str_pad( $sec, 2, '0', STR_PAD_LEFT);
        
//TODO remove static text
//$pastMed = "halidal";
//$eTime   = "15:35";
//$tTime  = "2.5";

//********
// This is where the place holders are replaced with the patient data
// 
//********
$doc = new Docxpresso\createDocument(array('template' => '../../../sites/default/documents/doctemplates/'.$file));
$format = '.pdf';//.pdf, .doc, .docx, .odt, .rtf
//replace single variable
$doc->replace(array('PatientName' => array('value' => $name)));
$doc->replace(array('PatientID' => array('value' => $pid)));
$doc->replace(array('PatientDOB' => array('value' => $dob)));
$doc->replace(array('Age' => array('value' => $pAge)));
$doc->replace(array('oDate' => array('value' => $date)));
$doc->replace(array('ReferringDOC' => array('value' => $therapist)));
$doc->replace(array('StartDOStime' => array('value' => $stime)));
$doc->replace(array('FacilityName' => array('value' => $facility)));
$doc->replace(array('DOS' => array('value' => $pDOS)));
$doc->replace(array('ChiefComplaint' => array('value' => $complaint)));
$doc->replace(array('DiagDescript' => array('value' => $diagDescript)));
$doc->replace(array('Allergies' => array('value' => $pAllergies)));
$doc->replace(array('ProblemList' => array('value' => $pList)));
$doc->replace(array('PatientSex' => array('value' => $pSex)));
$doc->replace(array('Address' => array('value' => $pAddress)));
$doc->replace(array('City' => array('value' => $pCity)));
$doc->replace(array('State' => array('value' => $pState)));
$doc->replace(array('Zip' => array('value' => $pZip)));
$doc->replace(array('PatientPhone' => array('value' => $pPhone)));
$doc->replace(array('CurrentDiagnosis' => array('value' => $pDiagnosisC)));
$doc->replace(array('FirstDiagnosis' => array('value' => $pDiagnosisF)));
$doc->replace(array('psychologist' => array('value' => $pUserID)));
$doc->replace(array('PatientPhone' => array('value' => $pPhone)));
$doc->replace(array('InsuranceID' => array('value' => $pInsur)));
$doc->replace(array('CurrentMedicationName' => array('value' => $currentMed)));
$doc->replace(array('PastMedicationName' => array('value' => $pastMed)));
$doc->replace(array('Dosage' => array('value' => $pDosage)));
$doc->replace(array('PrescribDoc' => array('value' => $medDoc)));
$doc->replace(array('EndDOStime' => array('value' => $eTime)));
$doc->replace(array('TotalDOStime' => array('value' => $answer)));
$doc->replace(array('CategoryCode' => array('value' => $cCode)));
$doc->replace(array('FacilityNPI' => array('value' => $facilityID)));
$doc->replace(array('FacilityAddress' => array('value' => $facility_address)));
$doc->replace(array('FacilityCity' => array('value' => $facility_city)));
$doc->replace(array('FacilityState' => array('value' => $facility_state)));
$doc->replace(array('FacilityZip' => array('value' => $facility_zip)));
$doc->replace(array('FacilityPhone' => array('value' => $facility_phone)));
$doc->replace(array('FacilityFax' => array('value' => $facility_fax)));


//replace natural text
//$doc->replace(array('replace me, please' => array('value' => 'another text')), array('format' => array('','')));
//populate the list
//$doc->replace(array('item' => array('value' => array('first', 'second', 'third'))), array('element' => 'list'));
//populate the table
/**
$vars =array('product' => array('value' => array('Smart phone', 'MP3 player', 'Camera')),
             'price' => array('value' => array('430.00', '49.99', '198,49')),
);
 * 
 */
//$doc->replace($vars, array('element' => 'table'));	
//replace single variable by different values
//$doc->replace(array('test' => array('value' => array('one', 'two', 'three'))));
//and now a variable in the header
//$doc->replace(array('example_header' => array('value' => 'header text')), array('target' => 'header'));
//include in the render method the path where you want your document to be saved
$doc->render($cfile[0]."-".$encounter."-". $pid  . $format); 
//echo a link to the generated document
?>
<html>
    
<head>
    <style type='text/css'>
     .button {
    background-color: #ddcccc;
    border: 1px solid black;
    color: black;
    font-family: Arial;
    font-size: small;
    text-decoration: none;
    padding: 3px;
    }
    </style>
    <script type='text/javascript'>
//<![CDATA[
window.onload=function(){
var downloadButton = document.getElementById("download");
var counter = 4;
var newElement = document.createElement("p");
newElement.innerHTML = "You can download the file in 4 seconds.";
var id;

downloadButton.parentNode.replaceChild(newElement, downloadButton);

id = setInterval(function() {
    counter--;
    if(counter < 0) {
        newElement.parentNode.replaceChild(downloadButton, newElement);
        clearInterval(id);
    } else {
        newElement.innerHTML = "You can download the file in " + counter.toString() + " seconds.";
    }
}, 1000);

}//]]  
    </script>

<body>
<?php
echo 'you may download the generated document from the link below:<br/></br>';
echo '<a href="' . $cfile[0]."-".$encounter."-". $pid . $format . '" id="download" class="button" target = "_blank">Download document</a></br><br>';
echo '<button><a href="template_list.php">Back</a></button>';
?>
</body>
</html>