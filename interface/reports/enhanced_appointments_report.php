<?php
// library/ajax/sms_notification_log_report_ajax.php
// Copyright (C) 2017 -Daniel Pflieger
// daniel@growlingflea.com daniel@mi-squared.com
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This is a reporting tool that shows all sent notifications and their status.

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$webserver_root/library/globals.inc.php");
require_once("{$GLOBALS['srcdir']}/sql.inc");
require_once("$srcdir/patient.inc");;
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";
require_once "$srcdir/appointments.inc.php";
require_once "$srcdir/clinical_rules.php";


$form_from_date = $_POST['form_from_date'] ?  $_POST['form_from_date'] : date('Y-m-d');
$form_to_date = $_POST['form_to_date'] ?  $_POST['form_to_date'] : date('Y-m-d');
$form_apptcat = $_POST['appcat'];

?>
<html>
<head>
    <?php html_header_show();?>
    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
    <title><?php xl('Enhanced Appointment Report: ','e'); ?></title>
    <style type="text/css">
        @import "../../library/js/datatables/media/css/demo_page.css";
        @import "../../library/js/datatables/media/css/demo_table.css";
        .dt-button{color: black !important;}

        .mytopdiv { float: left; margin-right: 1em; }

    </style>

    <script type="text/javascript" src="../../library/js/datatables/media/js/jquery.js"></script>
    <script type="text/javascript" src="../../library/js/datatables/media/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.10.1.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/tooltip.js"></script>
    <script type='text/javascript' src='<?php echo $GLOBALS['webroot'] ?>/library/dialog.js'></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/fancybox-1.3.4/jquery.fancybox-1.3.4.pack.js"></script>
    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/fancybox-1.3.4/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />

    <link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/public/assets/DataTables-1.10.16/datatables.css">
    <script type="text/javascript" charset="utf8" src="<?php echo $GLOBALS['webroot'] ?>/public/assets/DataTables-1.10.16/datatables.js"></script>
    <script type="text/javascript" src="../../library/js/datatables/extras/ColReorder/media/js/ColReorderWithResize.js"></script>
    <link rel="stylesheet" href="../../library/css/jquery.datetimepicker.css">


    <script>

        $(document).ready(function() {

            function format ( d , className ) {
                var encounter_details = d.encounter_details;
                var response = "" +

                        '<table align="center"  class = " sub_report  compact formtable className ' + className + ' " style="width:87%; padding: 5px 205px 5px 205px; background-color:rgba(233, 239, 231, 255);" >' ;

                            response += '<tr class="'+className+'"></tr>';
                            encounter_details.forEach(function(detail){
                             response +=   '<tr class="'+className+'">' +

                                                '<td width="10%">CPT4 ' + detail.code +     '</td>' +

                                                 '<td width="25%">' + detail.code_text +     '</td>' +

                                                 '<td width="10%">' + detail.pc_startTime + '-' + detail.pc_endTime + '</td>' +

                                                 '<td width="45%">' + detail.justify_text +     '</td>' +
                                            '</tr>' ;

                                });


            response +='</table>';






                return response;

            }

            var oTable;

            oTable=$('#show_enhanced_appointment_report').DataTable({
                dom: 'Bfrtip',
                autoWidth: true,
                scrollX:false,
                "order": [[ 3, "asc" ], [ 4, "asc" ]],

                buttons: [
                    'copy', 'excel', 'pdf', 'csv'
                ],
                ajax:{ type: "POST",
                    url: "../../library/ajax/enhanced_appointments_report_ajax.php",
                    data: {
                        func:"show_appointments",
                        to_date:   "<?php echo $form_to_date; ?>",
                        from_date:" <?php echo $form_from_date; ?> ",
                        category: "<?php echo $_POST['form_apptcat']; ?>",
                        facility: "<?php echo $_POST['form_facility']; ?>",
                        provider: "<?php echo $_POST['form_provider']; ?>"

                    },

                },

                columns:[
                    { 'data': 'pc_eid'},
                    { 'data': 'encounter'},
                    { 'data': 'provider'},
                    { 'data': 'client_name'},
                    { 'data': 'pid'},
                    { 'data': 'appt_date'},
                    { 'data[encounter_details]': 'unit_time'},
                    { 'data': 'appt_title'},
                    { 'data': 'status'}



                ],

                "columnDefs": [

                    {className: "compact", "targets": [ 0,1,2,3 ] },






                    {
                        //These are the hipaa permission columns
                        "targets": [0],

                        "createdCell": function (td, cellData, rowData, row, col) {



                        }
                    }

                ],
                "rowCallback": function( row, data ) {
                    //We are passing the row which is the closes tr.
                    //we need to make a child row from t0.
                    var t0 = oTable.row(row);
                    var className = row.className;
                    var enc = data.encounter;
                    if(enc > 0 ) {
                        var ch = t0.child(format(data, className)).show()
                    }
                },
                "iDisplayLength": 100,
                "select":true,
                "searching":true,
                "retrieve" : true


            });

            $('#column0_search_show_appt_table').on( 'keyup', function () {
                oTable
                    .columns( 0 )
                    .search( this.value )
                    .draw();
            } );

            $('#column1_search_show_appt_table').on( 'keyup', function () {
                oTable
                    .columns( 1 )
                    .search( this.value )
                    .draw();
            } );

            $('#column2_search_show_appt_table').on( 'keyup', function () {
                oTable
                    .columns( 2 )
                    .search( this.value )
                    .draw();
            } );

            $('#column3_search_show_appt_table').on( 'keyup', function () {
                oTable
                    .columns( 3 )
                    .search( this.value )
                    .draw();
            } );

            $('#column4_search_show_appt_table').on( 'keyup', function () {
                oTable
                    .columns( 4 )
                    .search( this.value )
                    .draw();
            } );

            $('#column5_search_show_appt_table').on( 'keyup', function () {
                oTable
                    .columns( 5 )
                    .search( this.value )
                    .draw();
            } );

            $('#column6_search_show_appt_table').on( 'keyup', function () {
                oTable
                    .columns( 6 )
                    .search( this.value )
                    .draw();
            } );

            $('#column7_search_show_appt_table').on( 'keyup', function () {
                oTable
                    .columns( 7 )
                    .search( this.value )
                    .draw();
            } );

            $('#column8_search_show_appt_table').on( 'keyup', function () {
                oTable
                    .columns( 8 )
                    .search( this.value )
                    .draw();
            } );






            $('.sub_report').css('color', 'blue');


            $("button.dt-button").css('color', 'black !important');



        });





    </script>
</head>
<body class="body_top formtable">
<span class='title'><?php xl('Report','e'); ?> - <?php xl('Enhanced Appointment Report','e');  ?></span>


<form name='theform' id='theform' method='post' action='./enhanced_appointments_report.php'
      onsubmit='return top.restoreSession()'>
    <div id="report_parameters">

        <table>
            <tr>
                <td width='410px'>
                    <div style='float:left'>
                        <table>

                            <tr>

                                <td class='label'><?php echo xlt('From'); ?>:</td>
                                <td><input type='text' name='form_from_date' id="form_from_date"
                                           size='10' value='<?php echo attr($form_from_date) ?>'
                                           onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
                                           title='yyyy-mm-dd'> </td>
                                <td class='label'><?php echo xlt('To'); ?>:</td>
                                <td><input type='text' name='form_to_date' id="form_to_date"
                                           size='10' value='<?php echo attr($form_to_date) ?>'
                                           onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
                                           title='yyyy-mm-dd'> </td>
                            </tr>
                            <tr>

                                <td class='label'><?php echo xlt('Category') #category drop down creation ?>:</td>
                                <td>
                                    <select id="form_apptcat" name="form_apptcat">
                                        <?php
                                        $categories=fetchAppointmentCategories();
                                        echo "<option value='ALL'>".xlt("All")."</option>";
                                        while($cat=sqlFetchArray($categories))
                                        {
                                            echo "<option value='".attr($cat['id'])."'";
                                            if($cat['id']==$_POST['form_apptcat'])
                                            {
                                                echo " selected='true' ";
                                            }
                                            echo    ">".text(xl_appt_category($cat['category']))."</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td class='label'><?php echo xlt('Facility'); ?>:</td>
                                <td><?php dropdown_facility($facility , 'form_facility'); ?>
                                </td>
                                <td class='label'><?php echo xlt('Provider'); ?>:</td>
                                <td><?php

                                    // Build a drop-down list of providers.
                                    //

                                    $query = "SELECT id, lname, fname FROM users WHERE ".
                                        "authorized = 1 $provider_facility_filter ORDER BY lname, fname"; //(CHEMED) facility filter

                                    $ures = sqlStatement($query);

                                    echo "   <select name='form_provider'>\n";
                                    echo "    <option value=''>-- " . xlt('All') . " --\n";

                                    while ($urow = sqlFetchArray($ures)) {
                                        $provid = $urow['id'];
                                        echo "    <option value='" . attr($provid) . "'";
                                        if ($provid == $_POST['form_provider']) echo " selected";
                                        echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                                    }

                                    echo "   </select>\n";
                                    ?>
                                </td>
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
    </div> <!-- end of parameters -->

</form>




<table width="98%" cellpadding="0" cellspacing="0"  table-layout="fixed"
       class="display formtable session_table compact " id="show_enhanced_appointment_report">
    <thead align="left" class="dataTable-header">

    <tr >

        <th ><input  id = 'column0_search_show_appt_table' style = "width:7em;" ></th>
        <th ><input  id = 'column1_search_show_appt_table' style = "width:7em;" ></th>
        <th ><input  id = 'column2_search_show_appt_table' style = "width:14em;" ></th>
        <th ><input  id = 'column3_search_show_appt_table' style = "width:14em;" ></th>
        <th ><input  id = 'column4_search_show_appt_table' style = "width:4em;" ></th>
        <th ><input  id = 'column5_search_show_appt_table' style = "width:6em;" ></th>
        <th ><input  id = 'column6_search_show_appt_table' style = "width:12em;" ></th>
        <th ><input  id = 'column7_search_show_appt_table' style = "width:20em;" ></th>
        <th ><input  id = 'column8_search_show_appt_table' style = "width:12em;" ></th>


    </tr>

    <tr>
        <th> <?php xl('Appt. ID','e'); ?> </th>
        <th> <?php xl('Encounter','e'); ?> </th>
        <th> <?php xl('Provider','e'); ?> </th>
        <th> <?php xl('Client','e'); ?> </th>
        <th> <?php xl('PID','e'); ?> </th>
        <th> <?php xl('Appt Date','e'); ?> </th>
        <th> <?php xl('Minutes / Units','e'); ?> </th>

        <th> <?php xl('Appt Title','e'); ?> </th>
        <th> <?php xl('Status','e'); ?> </th>



    </tr>

    </thead>
    <tbody id="users_list" >
    </tbody>

</table>


</body>


<link rel="stylesheet" href="../../public/assets/jquery-datetimepicker-2-5-4/jquery.datetimepicker.css">
<script type="text/javascript" src="../../public/assets/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>

<script>
    $(function() {
        $("#form_from_date").datetimepicker({
            timepicker: false,
            format: "Y-m-d"

        });
        $("#form_to_date").datetimepicker({
            timepicker: false,
            format: "Y-m-d"
        });

    });
</script>


</html>