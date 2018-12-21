<?php
/**
 * Created by PhpStorm.
 * User: growlingflea
 * Date: 12/19/18
 * Time: 6:12 PM
 */

//Get the credentials
$fake_register_globals=false;
$sanitize_all_escapes=true;

$ignoreAuth=true;
require_once("../interface/globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$webserver_root/library/globals.inc.php");
require_once("{$GLOBALS['srcdir']}/sql.inc");
//
//
//***********************************************************************
//***********************************************************************


echo("\n Successfully connected to database....... Waiting...... \n ");

//create an array of the codes we need.
$authcodes = array('H0031', 'H0032', 'G9007');

foreach($authcodes as $authcode){

    //select the codes that are missing a code2
    $sql = "select * from form_prior_auth where date >= '2018-07-01' and ((code1 = '{$authcode}' and code2 != '{$authcode}:HO') " .
            " or (code2 = '{$authcode}' and code1 != '{$authcode}:HO')) order by date desc ";

    $query = sqlStatement($sql) ;

    while ($result = sqlFetchArray($query)) {

        $update = "update form_prior_auth set code2 = '$authcode:H0', comments = concat(comments, 'altered by script') " .
            "where id = '{$result['id']}' ";

        sqlStatement($update) ;
        //create the HN modifier
        $insert = "" .
            "insert into form_prior_auth " .
            "(pid, activity, date, auth_number_required, auth_for, auth_from, auth_to, units, code1, alerts_to, alert_units, alert_days ) " .
            "VALUES ('{$result['pid']}', '{$result['activity']}', '{$result['date']}', '{$result['auth_number_required']}', '{$result['auth_for']}', " .
            "'{$result['auth_from']}', '{$result['auth_to']}', '{$result['units']}', '{$authcode}:HN', '{$result['alerts_to']}', " .
            "'{$result['alert_units']}', '{$result['alert_days']}' )";

        sqlInsert($insert) or print( "\n QUERY '$update' DID NOT WORK.  PLEASE VERIFY THE TABLE AND COLUMN EXISTS \n");

    }






}
//for each code that is equal to H0031 add H0031:HO to code 2

//for every patient that has a H0031, we need to create a new prior auth that will be H0031:HN