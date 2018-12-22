<?php
/**
 * library/ajax/enhanced_appointment_report.php: handles ajax calls from enhanced_appointment_report.php
 * file adapted to present user activity log
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/mpl-license.php>;.
 * Copyright (c) 2018 Growlingflea Software <daniel@growlingflea.com>
 * File adapted for user activity log.
 * @package OpenEMR
 * @author  Daniel Pflieger daniel@growlingflea.com daniel@mi-squared.com
 */
$fake_register_globals=false;
$sanitize_all_escapes=true;
$testing = true;


require_once("../../interface/globals.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/patient.inc");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";
require_once "$srcdir/appointments.inc.php";
require_once "$srcdir/clinical_rules.php";

// IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");


//Enter false if searching for users that aren't active
function getUsersArray($active = true){

    $users = array();
    $query = "Select distinct(username) from users ";
    $query .= $active = true ? "where active = 1" : " ";
    $query .= " AND username != ''";
    $result = sqlStatement($query);
    while ($row = sqlFetchArray($result)) {
        array_push($users, $row['username']);

    }
    return $users;
}

function ifTestingTrue($testing){

    if($testing)
        return " Limit 0,100";
    else return "";

}

function fetchEncounterFromAppointment($pc_eid){

    $query = "select encounter from openemr_postcalendar_events where pc_eid = ?";
    $response = sqlQuery($query, array($pc_eid));
    return $response;

}


if($_POST['func']=="get_all_diags_data")
{
    $response['data'] = array();
    $queryArray = array();
    $query = " SELECT type, l.title, diagnosis, l.activity, l.pid, dob, status, sex, list_options.title as ethnicity, monthly_income " .
        " FROM `lists` l join patient_data pd on pd.pid = l.pid " .
        " join list_options on option_id = ethnicity and list_id = 'ethnicity' where type = 'medical_problem'  ";

    if($_POST['diag'] != ''){
        array_push($queryArray, $_POST['diag'] );
        $query .= ' AND diagnosis = ?';
    }





    $query .= " AND ? "; //handles the case where nothing is being queried
    array_push($queryArray, 1 );
    $result = sqlStatement($query, $queryArray);

    while ($row = sqlFetchArray($result)) {
        $row['sex'] = ($row['sex'] == 'Male') ? 'M' : 'F';
        array_push($response['data'], $row);

    }

    $test = json_encode($response);
    echo json_encode($response);



}


if($_POST['func']=="get_all_lab_data")
{
    $response['data'] = array();

    $query  = "select pd.pid as pid, pd.sex as gender, pd.dob, pd.ethnicity, pres.result_text, pres.result, pres.abnormal from procedure_result pres ";
    $query .= "join procedure_report prep on pres.procedure_report_id = prep.procedure_report_id " ;
    $query .= "join procedure_order prord on prep.procedure_order_id = prord.procedure_order_id " ;
    $query .= "join patient_data pd on pd.pid = prord.patient_id ";
    $query .= "where  pres.result_text != '' and pres.abnormal != '' ";




    ini_set('memory_limit', '1000M');
    $result = sqlStatement($query);

    while ($row = sqlFetchArray($result)) {
        $row['gender'] = ($row['gender'] == 'Male') ? 'M' : 'F';
        array_push($response['data'], $row);
    }
    $test = json_encode($response);
    echo json_encode($response);


}

if($_POST['func'] == "show_appointments"){
    //todo: complete the search critera
    $response['data'] = array();

    $from_date = $_POST['from_date'];
    $to_date   = $_POST['to_date'];

    //function fetchAppointments( $from_date, $to_date, $patient_id = null, $provider_id = null, $facility_id = null, $pc_appstatus = null, $with_out_provider = null, $with_out_facility = null, $pc_catid = null, $tracker_board = false, $nextX = 0 )
    $query = "select pc_eid, pd.pid, pd.lname, pd.fname, pc_eventDate, pc_startTime, pc_endTime, pc_title, pc_facility, ope.encounter , " .
            " pc_apptstatus, lo.title as status, " .
            " ope.encounter, u.lname as ulname, u.fname as ufname  " .
            "from openemr_postcalendar_events ope ".
            "join patient_data pd on pc_pid = pd.pid " .
            "JOIN users AS u ON u.id = ope.pc_aid " .
            "join list_options as lo on lo.option_id = pc_apptstatus " .

            "where pc_eventDate >= ? and pc_eventDate <= ?   ";


    if($_POST['category'] != "ALL" && !empty($_POST['category'])){

        $query .= " AND pc_catid = {$_POST['category']}";

    }

    if($_POST['provider'] != "ALL" && !empty($_POST['provider'])){

        $query .= " AND pc_aid = {$_POST['provider']}";

    }

    if($_POST['facility'] != "ALL" && !empty($_POST['facility'])){

        $query .= " AND pc_facility = {$_POST['facility']}";

    }

    $result = sqlStatement($query, array($from_date, $to_date));

    while ($appointment = sqlFetchArray($result)) {




        $row = array();
        $row['pc_eid']      = $appointment['pc_eid'];
        $row['pc_title']    = $appointment['pc_title'];
        $row['provider']    = $appointment['ulname']. " , " . $appointment['ufname'];
        $row['client_name'] = $appointment['lname'] . " , " . $appointment['fname'];
        $row['appt_date']   = $appointment['pc_eventDate'];
        $row['appt_title']  = $appointment['pc_title'];
        $row['pc_startTime'] = $appointment['pc_startTime'];
        $row['pc_endTime'] = $appointment['pc_endTime'];
        $row['status'] = $appointment['status'];
        $row['encounter']   = $appointment['encounter'];
        $row['pid']   = $appointment['pid'];

        //get the  minutes of the appointments

        $row['appt_lenth_hours'] = round(abs(strtotime($appointment['pc_startTime']) - strtotime($appointment['pc_endTime'])) / 3600,2);
        $row['appt_lenth_minutes'] = round(abs(strtotime($appointment['pc_startTime']) - strtotime($appointment['pc_endTime'])) / 60,2);

        //query for the subreport
        //get the Procedure codes:
        if(!empty($appointment['encounter'])) {
            $sub1sql = "select concat(code, modifier) as code, code_text, activity, units, justify from billing " .
                "where encounter = '{$appointment['encounter']}' and code_type = 'CPT4'";

            $icd10_result = sqlStatement($sub1sql);
            $row['encounter_details'] = array();
            while ($code = sqlFetchArray($icd10_result)) {


                //get the justify code
                $code['justify'] = str_replace("ICD10|", '', $code['justify']);
                $code['justify'] = str_replace(":", '', $code['justify']);

                $jsql = "select code_text from codes where code like '%".$code['justify']."%' AND active = 1";
                $justify = sqlStatement($jsql);
                $jred = sqlFetchArray($justify);
                $code['justify_text'] = $jred['code_text'];
                $code['pc_startTime'] = $appointment['pc_startTime'];
                $code['pc_endTime'] = $appointment['pc_endTime'];
                $code['unit_time'] = $row['appt_lenth_minutes'] . " \ " . $code['units'];
                array_push($row['encounter_details'], $code);

            }
        }

        array_push($response['data'], $row);

    }


    echo json_encode($response);

}



?>
