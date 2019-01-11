<?php

// interface/forms/
// Copyright (C) 2016 by following authors:
//  Daniel Pflieger - daniel@growlingflea.com

//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This is a reporting tool that shows prior_auths as described in IDBH manual
//


//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
// forms/prior_auth/display.php?pid=&pa_id=

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/appointments.payroll.inc.php");

// global $pid;
// We're NOT using the global $pid variable here
// but bypassing it with a local $_pid var that
// can be set with the $_GET url argument

$top_mssg = "";

$authUser = $_SESSION['authUser'];



function ibh_getPayrollCodePulldown($code) {

    $html = "<select id='codes' name='billing_code'><option value=''>All Codes</option>";

    $cres = sqlStatement("SELECT pc_catname FROM openemr_postcalendar_categories WHERE pc_catname LIKE '%:%' ORDER BY pc_catname");

    while ($crow = sqlFetchArray($cres)) {

        $code_name = $crow['pc_catname'];

        $ex_code = trim(explode(":", $code_name)[1]);

        $html .= "<option value='" . $ex_code . "'";

        if ($ex_code == $code) {
            $html .= " selected";
        }
        $html .= ">" . text(xl_appt_category($crow['pc_catname'])) . "</option>\n";
    }

    $html .= "</select>";

    return $html;


}


$alertmsg = ''; // not used yet but maybe later
$patient = $_REQUEST['patient'];

$billing_code = $_REQUEST['billing_code'];


if ($patient && ! $_POST['form_from_date']) {
    // If a specific patient, default to 2 years ago.
    $tmp = date('Y') - 2;
    $from_date = date("$tmp-m-d");
} else {
    $from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
    $to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}

$show_available_times = false;
if ( $_POST['form_show_available'] ) {
    $show_available_times = true;
}

$chk_with_out_provider = false;
if ( $_POST['with_out_provider'] ) {
    $chk_with_out_provider = true;
}

$chk_with_out_facility = false;
if ( $_POST['with_out_facility'] ) {
    $chk_with_out_facility = true;
}

//$to_date   = fixDate($_POST['form_to_date'], '');
$provider  = $_POST['form_provider'];
$facility  = $_POST['form_facility'];  //(CHEMED) facility filter
$form_orderby = getComparisonOrder( $_REQUEST['form_orderby'] ) ?  $_REQUEST['form_orderby'] : 'doctor';





?>

<html lang="en">
<head>
        <title>Prior Auths Editing</title>


        <?php html_header_show();?>
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/datatables/media/js/jquery.js"></script>
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/public/assets/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-2.0.3.min.js"></script>
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/tooltip.js"></script>
        <script type='text/javascript' src='<?php echo $GLOBALS['webroot'] ?>/library/dialog.js'></script>
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.pack.js"></script>
        <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />

        <link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/public/assets/DataTables-1.10.18/datatables.css">
        <script type="text/javascript" charset="utf8" src="<?php echo $GLOBALS['webroot'] ?>/public/assets/DataTables-1.10.18/datatables.js"></script>
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/datatables/extras/ColReorder/media/js/ColReorderWithResize.js"></script>
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/public/assets/FixedHeader-3.1.4/js/fixedHeader.dataTables.js"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/public/assets/FixedHeader-3.1.4/css/fixedHeader.dataTables.css">
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/public/assets/jquery-datetimepicker-2-5-4/jquery.datetimepicker.min.js"></script>
        <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/public/assets/jquery-datetimepicker-2-5-4/jquery.datetimepicker.css">
        <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

    <script type="text/javascript">

        var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

        function dosort(orderby) {
            var f = document.forms[0];
            f.form_orderby.value = orderby;
            f.submit();
            return false;
        }



            function refreshme() {
            // location.reload();
            document.forms[0].submit();
        }

        $(document).ready(function() {

            display_enhanced_payroll();

            $('.dt-buttons').css('padding-left', '2em');
            $('.detail_row').css('background-color', 'rgba(86,183,60,189)');


        });

        var oTable;

        function format( d , className ) {

            var response = "";
            response += '<table  border=".125" class="formtable session_table compact odd " style="background-color:rgba(86,183,60,.1); width:90%; ">' +
                '<tr class="detail_row">' +
                '<th style="width:10%" >Date of Service</th>' +
                '<th style="width:10%">Code</th>' +
                '<th style="width:10%">Encounter</th>' +
                '<th style="width:20%">Provider</th>' +
                '<th style="width:20%">Patient</th>' +
                '<th style="width:20%">Facility</th>' +
                '<th style="width:5%">Billing Units</th>' +
                '<th style="width:5%">Payroll Units</th></tr>';

            response += '<table  class = "detail_row formtable session_table compact  odd" style=" width:90%;' +
                'background-color:rgba(169,172,213,1); ">';

             var details = d.details;

            if(details.length < 1){

                response += '<tr></tr>'

            }else {

                details.forEach(function (detail) {

                    response += '<tr class="detail_row cell-border" >' +
                        '<td style="width:10%; padding-left:2%">' + detail.pc_eventDate + '</td>' +
                        '<td style="width:10%; padding-left:2%" >' + detail.code + '</td>' +
                        '<td style="width:10%; padding-left:2%">' + detail.encounter + '</td>' +
                        '<td style="width:20%; padding-left:2%">' + detail.provider + '</td>' +
                        '<td style="width:20%; padding-left:2%">' + detail.patientName + '</td>' +
                        '<td style="width:20%; padding-left:2%">' + detail.facility + '</td>' +
                        '<td style="width:5%; padding-left:2%">' + detail.units + '</td>' +
                        '<td style="width:5%; padding-left:2%">' + detail.payroll_units + '</td>' +


                        '</tr>';

                });

                response += '<tr class="highlight">' +
                    "<td style='width:10%; padding-left:2%'></td>" +
                    "<td style='width:10%; padding-left:2%'></td>" +
                    "<td style='width:10%; padding-left:2%'></td> " +
                    "<td style='width:20%; padding-left:2%'></td>" +
                    "<td style='width:20%; padding-left:2%'></td>" +
                    "<td style='width:20%; padding-left:2%; font-size:15px;'>Totals: </td>" +
                    "<td style='width:5%; padding-left:2%; font-size:15px;'>" + d.total_billing_units + "</td>" +
                    "<td style='width:5%; padding-left:2%; font-size:15px;'>" + d.total_payroll_units + "</td></tr>";
                response += '</table>'
            }
            response += "</table>";






            return response;
        }

        function display_enhanced_payroll(){
            $('#image').show();

            oTable=$('#show_payroll_report').DataTable({
                dom: 'Bfrtip',
                autoWidth: false,
                scrollX: false,
                fixedHeader: true,
                buttons: [
                    'copy', 'excel', 'pdf', 'csv'
                ],
                ajax:{
                    type: "POST",
                    url: "<?php echo $GLOBALS['webroot'] ?>/library/ajax/payroll_report_enhanced_ajax.php",
                    data: {
                        func:"display_payroll",
                        form_facility:"<?= $_POST['form_facility'] ?>",
                        form_provider:"<?= $_POST['form_provider'] ?>",
                        billing_code:"<?= $_POST['billing_code'] ?>",
                        form_from_date:"<?= $_POST['form_from_date'] ?>",
                        form_to_date:"<?= $_POST['form_to_date'] ?>",
                        display_nonbillables:"<?= $_POST['display_nonbillables'] ?>"


                    }, complete: function(){

                        $('#image').hide();

                    }},


                order:[[0, "desc"]],
                columns:[

                    {'data' : 'pc_catname', "className": "dt-center", 'width' : '81%'},



                ],

                "columnDefs": [


                    { className: "details-control", targets: "_all" },
                ],

                "rowCallback": function( row, data ) {

                    //We are passing the row which is the closes tr.
                    //we need to make a child row from t0.
                    var t0 = oTable.row(row);
                    var className = row.className;
                    var ch = t0.child(format(data, className)).show();
                    $(row).css( "background-color", 'rgba(86,183,60,.2)' );
                    $(row).css("font-size", "14px");

                },

                "iDisplayLength": 100,
                "select":true,
                "searching":true,
                "retrieve" : true,


            });

            $('#column0_show_payroll').on( 'keyup', function () {
                oTable
                    .columns( 0 )
                    .search( this.value )
                    .draw();
            } );

            $('#show_payroll_report_filter').hide();


        }


    </script>

    <style type="text/css">





        .bottom .loc-total {

            float: right;
            clear:both;
            font-size: 12px;
            font-weight: bold;
            background-color: #fff;
            margin:4px 0;
            padding:0;
        }


        @media print {
            #report_parameters {
                visibility: hidden;
                display: none;
            }
            #report_parameters_daterange {
                visibility: visible;
                display: inline;
            }
            #report_results table {
                margin-top: 0px;
            }
        }


        @media screen {
            #report_parameters_daterange {
                visibility: hidden;
                display: none;
            }
        }
    </style>

</head>

<body class="body_top formtable">

<!-- Required for the popup date selectors -->


<span class='title'><?php xl('Report','e'); ?> - <?php xl('Enhanced Payroll Report','e'); ?></span>

<div class="position-static">

<div id="report_parameters_daterange"><?php echo date("d F Y", strtotime($from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($to_date)); ?>
</div>
<form name='theform' id='theform' method='post' action='./payroll_report_enhanced.php'
      onsubmit='return top.restoreSession()'>

    <div id="report_parameters">

        <table>
            <tr>
                <td >
                    <div style='float: left'>

                        <table class='text nav-table'>
                            <tr>
                                <td class='label'><?php xl('Facility','e'); ?>:</td>
                                <td><?php dropdown_facility(strip_escape_custom($facility), 'form_facility'); ?>
                                </td>
                                <td class='label'><?php xl('Provider','e'); ?>:</td>
                                <td><?php

                                    // Build a drop-down list of providers.
                                    //

                                    $query = "SELECT id, lname, fname FROM users WHERE ".
                                        "authorized = 1 $provider_facility_filter and active = 1 ORDER BY lname, fname"; //(CHEMED) facility filter

                                    $ures = sqlStatement($query);

                                    echo "   <select name='form_provider'>\n";
                                    echo "    <option value=''>-- " . xl('All') . " --\n";

                                    while ($urow = sqlFetchArray($ures)) {
                                        $provid = $urow['id'];
                                        echo "    <option value='$provid'";
                                        if ($provid == $_POST['form_provider']) echo " selected";
                                        echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
                                    }

                                    echo "   </select>\n";

                                    ?></td>

                            </tr>
                            <tr><td class='label'>Code:</td><td colspan=3><?=ibh_getPayrollCodePulldown($_REQUEST['billing_code'])?></td></tr>
                            <tr>
                                <td class='label'><?php xl('From','e'); ?>:</td>
                                <td><input type='text' name='form_from_date' id="form_from_date"
                                           size='10' value='<?php echo $from_date ?>'
                                           onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
                                           title='yyyy-mm-dd'> <img src='../pic/show_calendar.gif'
                                                                    align='absbottom' width='24' height='22' id='img_from_date'
                                                                    border='0' alt='[?]' style='cursor: pointer'
                                                                    title='<?php xl('Click here to choose a date','e'); ?>'></td>
                                <td class='label'><?php xl('To','e'); ?>:</td>
                                <td><input type='text' name='form_to_date' id="form_to_date"
                                           size='10' value='<?php echo $to_date ?>'
                                           onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
                                           title='yyyy-mm-dd'> <img src='../pic/show_calendar.gif'
                                                                    align='absbottom' width='24' height='22' id='img_to_date'
                                                                    border='0' alt='[?]' style='cursor: pointer'
                                                                    title='<?php xl('Click here to choose a date','e'); ?>'></td>
                            </tr>
                            <tr><td>&nbsp;</td>
                                <?php
                                $nb_checked = $_POST['display_nonbillables']=="yes" ? "checked='checked'" : "checked='foo'";
                                ?>
                                <td colspan=3><input name="display_nonbillables" type="checkbox" value="yes" <?php echo $nb_checked; ?>> Display non-billable encounters?</td>
                            </tr>

                            <tr>
                                <td>
                                    <label><input value="<?php echo htmlspecialchars(xl('Submit')) ?> " type="submit" id="submit_selector" name="sms_submit" ></label>
                                    <input hidden id = 'submit_button' value = '<?php echo $_POST['sms_submit']  ?>'
                                </td>
                            </tr>
                        </table>

                    </div>

                </td>

            </tr>
        </table>

    </div>
</form>

</div>

<!-- end of search parameters -->
<table class=" formtable compact no-wrap " id="show_payroll_report" border=".5" style="width:95% ; align=:center;  padding: 2px 5px 5px 5px;">

    <thead align="center">

    <tr align="center" >

        <th ><input  id = 'column0_show_payroll' style = "width:16em;" ></th>
    </tr>
    <tr align="center" >

        <th>Sort</th>
    </tr>

    </thead>
    <tbody id="payroll_report">

    </tbody>





</table>

</body>

<!-- stuff for the popup calendar -->
<style type="text/css">
    @import url(../../library/dynarch_calendar.css);
</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript"
        src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript">
    Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
    Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

</script>

<script type="text/javascript">

    $(".toggle-details").on("click", function() {
        var bt = $(this);
        var sec = bt.data("section");
        var ope = bt.data("open");

        console.log("section", sec);

        if (ope == "1") {
            $(sec).hide();
            bt.data("open", "0");
            bt.text("show details");
        } else {
            $(sec).show();
            bt.data("open", "1");
            bt.text("hide details");
        }


    });




</script>
</html>