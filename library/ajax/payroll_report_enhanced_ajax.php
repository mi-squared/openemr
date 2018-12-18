<?php




require_once("../../interface/globals.php");
require_once("../../library/patient.inc");


require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/appointments.payroll.inc.php");


// IBH_DEV_CHG
require_once("../../_ibh/ibh_functions.php");




//get the payroll information

if($_POST['func'] == 'display_payroll'){

    $tableData['data'] = array();


    $topsql = "select pc_catid, pc_catname from openemr_postcalendar_categories where pc_catname like '%:%' ";
    $topresult = sqlStatement($topsql);
    $index = 0;
    while($toprow = sqlFetchArray($topresult)){
        $catname = $toprow['pc_catname'];
        array_push($tableData['data'],  $toprow);


        $sql = "select pc_eid, pc_aid, concat(u.fname, ' ', u.lname) as provider , pc_eventDate, pc_startTime, pc_endTime, b.code_type, b.code, code_text, b.pid, " .
            "concat(pd.lname, ', ', pd.fname ) as patientName, b.encounter, pc_title, f.name as facility, units from billing b " .
            "join openemr_postcalendar_events ope on b.encounter = ope.encounter " .
            " join users u on u.id = ope.pc_aid ".
            " join patient_data pd on pd.pid = ope.pc_pid " .
            " join facility f on pc_facility = f.id " .
            " where b.encounter != 0 " .
            " AND pc_eventDate >= ? AND pc_eventDate <= ? AND pc_catid = {$toprow['pc_catid']} and code_type = 'CPT4' ";

                if(isset($_POST['form_facility']) && strlen($_POST['form_facility']) > 0){

            $sql .= "AND pc_facility = {$_POST['form_facility']} ";

        }

        if(isset($_POST['form_provider']) && strlen($_POST['form_provider']) > 0){

            $sql .= "AND pc_aid = {$_POST['form_provider']} ";

        }

        if(isset($_POST['billing_code']) && strlen($_POST['billing_code']) > 0){

            $sql .= "AND code = {$_POST['code']} ";

        }


        $result = sqlStatement($sql, array($_POST['form_from_date'], $_POST['form_to_date']));
        $tableData['data'][$index]['details'] = array();
        while ($encounter = sqlFetchArray($result)) {


           array_push($tableData['data'][$index]['details'], $encounter) ;



        }


        $index++;



    }











    echo json_encode($tableData);




}