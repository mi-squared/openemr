<?php
// library/ajax/display2_ajax.php
// Copyright (C) 2018 -Daniel Pflieger
// daniel@growlingflea.com daniel@mi-squared.com
//
// This program was written for Idaho Behavioral Health.
//
//


$fake_register_globals=false;
$sanitize_all_escapes=true;
$testing = false;

require_once("../../interface/globals.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/patient.inc");
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
$DateFormat = DateFormatRead();
//make sure to get the dates

$top_mssg = "";

$authUser = $_SESSION['authUser'];


if (isset($_GET['pid'])) {
    $_pid = $_GET['pid'];
}

if (isset($_POST['pid'])) {
    $_pid = $_POST['pid'];
}

$patient = ibh_get_patient($_pid);


if ($_GET['action'] == "delete") {
    $pa_num = $_GET['pa'];



}

if ($_POST['func'] === 'add_auth'){



    $pa = array();
    $_POST['alerts_to'] = array();
    foreach($_POST['fdata'] as $value){
        if($value['name'] == "alerts_to[]"){

            array_push($_POST['alerts_to'], $value['value']);
        }else {

            $_POST[$value['name']] = $value['value'];
        }
    }


    $pa_id = 'new';
    $date = date("Y-m-d H:i:s");

    $prior_auth_number = $_POST['prior_auth_number'];
    $units = $_POST['units'];
    $auth_contact = $_POST['auth_contact'];
    $auth_phone = $_POST['auth_phone'];
    $code1 = $_POST['code1'];
    $code2 = $_POST['code2'];
    $code3 = $_POST['code3'];
    $code4 = $_POST['code4'];
    $code5 = $_POST['code5'];
    $code6 = $_POST['code6'];
    $code7 = $_POST['code7'];

    $alert_days = $_POST['alert_days'];
    $alert_units = $_POST['alert_units'];

    // DEFAULTS
    $activity = 1;
    $auth_length = 0;
    $dollar = 0;
    $auth_for = 333;
    $posted_pid = $_POST['pid'];

    $override = 1; // $_POST['override'] == "1"? 1:0;
    $archived = 0; // $_POST['archived'] == "1"? 1:0;
    $auth_number_required = $_POST['auth_number_required'] == "1"? 1:0;

    $auth_from = $_POST['auth_from'];
    $auth_to = $_POST['auth_to'];
    // $auth_for = $_POST['auth_for']; // days

    $desc = $_POST['description'];
    $comments = $_POST['comments'];

    $unit_adjustment = $_POST['unit_adjustment'];


    $alerts_to = implode(",", $_POST['alerts_to']);
    // $top_mssg .= "<br>" . $alerts_to . "<br>";


    if ($pa_id == "new") {
        $sql = "INSERT INTO form_prior_auth (pid, activity, date, prior_auth_number, auth_number_required, comments, description, auth_for, auth_from, auth_to, units, auth_length, dollar, auth_contact, auth_phone, code1, code2, code3, code4, code5, code6, code7, archived, override, alerts_to, alert_units, alert_days, unit_adjustment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $data = array($posted_pid, $activity, $date, $prior_auth_number, $auth_number_required, $comments, $desc, $auth_for, $auth_from, $auth_to, $units, $auth_length, $dollar, $auth_contact, $auth_phone, $code1, $code2, $code3, $code4, $code5, $code6, $code7, $archived, $override, $alerts_to, $alert_units, $alert_days, $unit_adjustment);

        $stmt = sqlStatement($sql, $data);

        echo "success";


    }


}


if (isset($_POST['editing'])) {

    $pa_id = $_POST['id'];
    $date = date("Y-m-d H:i:s");

    $prior_auth_number = $_POST['prior_auth_number'];
    $units = $_POST['units'];
    $auth_contact = $_POST['auth_contact'];
    $auth_phone = $_POST['auth_phone'];
    $code1 = $_POST['code1'];
    $code2 = $_POST['code2'];
    $code3 = $_POST['code3'];
    $code4 = $_POST['code4'];
    $code5 = $_POST['code5'];
    $code6 = $_POST['code6'];
    $code7 = $_POST['code7'];

    $alert_days = $_POST['alert_days'];
    $alert_units = $_POST['alert_units'];

    // DEFAULTS
    $activity = 1;
    $auth_length = 0;
    $dollar = 0;
    $auth_for = 333;
    $posted_pid = $_POST['pid'];

    $override = 1; // $_POST['override'] == "1"? 1:0;
    $archived = 0; // $_POST['archived'] == "1"? 1:0;
    $auth_number_required = $_POST['auth_number_required'] == "1"? 1:0;

    $auth_from = $_POST['auth_from'];
    $auth_to = $_POST['auth_to'];
    // $auth_for = $_POST['auth_for']; // days

    $desc = $_POST['description'];
    $comments = $_POST['comments'];

    $unit_adjustment = $_POST['unit_adjustment'];


    $alerts_to = implode(",", $_POST['alerts_to']);
    // $top_mssg .= "<br>" . $alerts_to . "<br>";






    // This code handles new entries.
    // todo:  we need to fix the form to take in procedure and modifiers
    if ($_POST['id'] == "new") {
        $sql = "INSERT INTO form_prior_auth (pid, activity, date, prior_auth_number, auth_number_required, comments, description, auth_for, auth_from, auth_to, units, auth_length, dollar, auth_contact, auth_phone, code1, code2, code3, code4, code5, code6, code7, archived, override, alerts_to, alert_units, alert_days, unit_adjustment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $data = array($posted_pid, $activity, $date, $prior_auth_number, $auth_number_required, $comments, $desc, $auth_for, $auth_from, $auth_to, $units, $auth_length, $dollar, $auth_contact, $auth_phone, $code1, $code2, $code3, $code4, $code5, $code6, $code7, $archived, $override, $alerts_to, $alert_units, $alert_days, $unit_adjustment);

        $stmt = sqlStatement($sql, $data);

        $top_mssg .= "New Prior Auth Created: $prior_auth_number";


    } else {

        $sql = "UPDATE form_prior_auth SET auth_from=?, auth_to=?, auth_for=?, auth_phone=?, auth_contact=?, description=?, comments=?, code1=?, code2=?, code3=?, code4=?, code5=?, code6=?, code7=?, prior_auth_number=?, units=?, override='$override', archived='$archived', alerts_to='$alerts_to', auth_number_required='$auth_number_required', alert_units=?, alert_days=?, unit_adjustment=? WHERE id=?";

        $data = array($auth_from, $auth_to, $auth_for, $auth_phone, $auth_contact, $desc, $comments, $code1, $code2, $code3, $code4, $code5, $code6, $code7, $prior_auth_number, $units, $alert_units, $alert_days, $unit_adjustment, $pa_id);

        $stmt = sqlStatement($sql, $data);

        $top_mssg .= "Prior Auth Updated: $prior_auth_number";


    }

}



if($_POST['func'] == "display_prior_auth"){

    $response['data'] = array();
    $pid = $_POST['pid'];
    $valid = true;
    $pan = '';

    //$valid: if false, show archived. if true, only show valid
    //$pan: look for a specific auth number

    $auths = ibh_get_patient_prior_auths($pid, $valid, $pan);
    foreach($auths as $auth){
        //initialze row
        $row = array();
        //populate the information by adding it to the $row array
        $row['id'] = $auth['id'];
        //date created
        $row['date_created'] = $auth['date'];

        //code type
        $row['description'] = $auth['description'];
        $row['auth_required'] = ( $auth['auth_number_required'] == '1') ? 'Y' : 'N';
        //authNumber: if required and is empty, print an error. Else print authNumber or an X
        if($auth['auth_number_required'] == '1' && empty($auth['prior_auth_number'])){

            $row['prior_auth_number'] = "ERROR";

        }else if(!empty($auth['prior_auth_number'])) {

            $row['prior_auth_number'] = $auth['prior_auth_number'];

        }else{

            $row['prior_auth_number'] = "X";
        }

        //Print the auth date range
        $row['auth_range'] = $auth['auth_from'] . " thru " . $auth['auth_to'];
        $row['units'] = $auth['units'];
        $row['days_remaining'] = $auth['days_remaining'];
        $row['units_remaining'] = $auth['units_remaining'];
        $row['comments'] = $auth['comments'];
        $alerts = explode("," , $auth['alerts_to']);
        $row['alertsTo'] = array();
        foreach($alerts as $alert){

            $asql = ibh_get_user_by_id($alert);
            $name = $asql['fname'] . " " . $asql['lname'];
            if(!empty($name) || $name != "")
                array_push($row['alertsTo'], $name);
        }



        $row['codes'] = array();
        $from = $auth['auth_from'] . " 00:00:00";
        $to = $auth['auth_to'] . " 23:59:59";

        for($i = 1; $i <=7; $i++){

            if(!empty($auth["code".$i])){

                array_push($row['codes'],$auth["code".$i] );

            }
            $billed_units = 0;

        $row['applicable_bills'] = array();


            foreach($row['codes'] as $bcode){

                $billing_code = explode(':', $bcode);
                $code = $billing_code[0];
                $modifier = $billing_code[1];

                if(in_array($code, "H0031, H0032")) {

                    $bsql = "SELECT date, encounter, code, code_text, id, units, modifier FROM billing WHERE pid='$pid' AND code='$code' AND modifier='$modifier' AND (date >='{$from}' AND date <= '{$to}')";

                }else{

                    $bsql = "SELECT date, encounter, code, code_text, id, units, modifier FROM billing WHERE pid='$pid' AND code='$code' AND (date >='{$from}' AND date <= '{$to}')";

                }


                $billsq = sqlStatement($bsql);
                while($bill = sqlFetchArray($billsq)) {

                    array_push($row['applicable_bills'], $bill);

                }

            }
        }
        array_push($response['data'], $row);

    }

    echo json_encode($response);

}

//grabs data to populate prior_auth_add_view.php edit form
if($_POST['action'] === 'grab_data' && isset($_POST['id'])){

    $sql = "Select * from form_prior_auth where id = '{$_POST['id']}' ";

    $prior_auth = sqlFetchArray(sqlStatement($sql));

    echo json_encode($prior_auth);


}

if($_POST['action'] === 'edit' && isset($_POST['id'])){

    $sql = "Update form_prior_auth set";
    $bindArray = array();
    $alerts_to = array();
    foreach($_POST['fdata'] as $field){

        if($field['name'] == 'alerts_to[]') {
            array_push($alerts_to, $field['value']);
            continue;
        }



        $sql .= " `{$field['name']}` = ? , ";
        array_push($bindArray, $field['value']);
    }
    //Turn the alerts_to array into a string for saving
    $alerts = implode(",", $alerts_to);

    $sql .= " alerts_to = ? ";
    array_push($bindArray, $alerts);

    $sql .= " Where id = {$_POST['id']}";


    $statement = sqlStatement($sql, $bindArray);
    return $statement;


}