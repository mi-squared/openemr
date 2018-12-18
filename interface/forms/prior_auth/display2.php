<?php
// interface/forms/prior_auth/display.php
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

require_once("../../globals.php");
require_once("$srcdir/forms.inc");

// IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
require_once("$srcdir/patient.inc");

// global $pid;
// We're NOT using the global $pid variable here
// but bypassing it with a local $_pid var that
// can be set with the $_GET url argument

$top_mssg = "";

$authUser = $_SESSION['authUser'];


if (isset($_GET['pid'])) {
	$_pid = $_GET['pid'];
}

if (isset($_POST['pid'])) {
	$_pid = $_POST['pid'];
}

$patient = ibh_get_patient($_pid);



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
	<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/jquery.datetimepicker.css">


    <link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/public/assets/">
    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

	<script>

        //This is for the child row that displays the response
        function format ( d , className ) {
            console.log(d);
            var response =  "" +
                            '<table class = "sub_report" style=" width:74%; padding: 5px 5px 5px 105px;" border="3">'+'<tr><td>' +
                                '<table  padding-left="15px" class = "sub_report formtable session_table "  style=" width:80%;  table-layout:fixed " >' +

                                    '<tr >' +
                                    '<th border="0" align="left" valign="top" width="16%">Supervisor Alerts</th>' +
                                    '<td border="0" width ="80%">';
                                    //here we get the names of the supervisors
                                    var alerts = d.alertsTo;
                                    alerts.forEach(function(alert, i){

                                        response += "<span style='padding-left: 2em; '> " + alert + "</span> ";

                                        if(i > 0 && i%3==0) response +='<br>';
                                    });

                response +=         '</td>' +
                                    '</tr>';


                response += '</table>'; //end of auth_details table

                //Codes Table
                response+=  '<table class = "compact formtable session_table display sub_report"  style="float: left; width:80%; table-layout:fixed "  >' +

                                '<tr border="0">' +
                                '<th border="0" align="left" width="16%" valign="top">Codes</th>' +
                                '<td border="0" width ="80%">';

                        var codes = d.codes;

                        codes.forEach(function(code){

                            if(code.length > 0) {

                                response += "<span style='padding-left: 2em; '> " + code + "</span> ";;
                            }
                        });







                                response += '</td>' +
                                '</tr>';
                response +=  '<tr border="0">' +
                                '<th border="0" align="left" width="16%" valign="top">Comments</th>' +
                                '<td  border="0" width ="80%" left >' + d.comments;
                response += '</td>' + '</tr>';
                response += '</table>';

                //
                response += '<tr><td>' +
                            '<table id = "prior_auth_table" class = "compact formtable session_table display sub_report prior_auth_table"  style="font-size:11px float: left; width:100%; " >' +

                            '<tr>' +
                            '<th align="left" width="14%">Applicable Bills</th>' +
                            '<td >';
                            //Prior Auths go here
            var bills = d.applicable_bills;
            var count = 0;

            bills.forEach(function(bill, i){



                var date = bill.date;
                date = date.split(" ");
                var code;

                if(bill.modifier.length > 0){

                    code = bill.code + ":" + bill.modifier;

                }else{

                    code = bill.code;
                }

                    if(i%4 === 0) response += '<tr>';
                    response += "<td border='.1'></td>" //do the title TD
                    response += "<td hidden></td>" //do the title TD
                    response += "<td>";
                    response += "<span style='padding-left: 2em'>Encounter Date:</span><span> " + date[0] + "</span><br>";
                    response += "<span style='padding-left: 2em'>Encounter:</span><span> " + bill.encounter + "</span><br>";
                    response += "<span style='padding-left: 2em'>Code: </span><span> " + code + "</span><br>";
                    response += "<span style='padding-left: 2em'>Desc: </span><span> " + bill.code_text + "</span><br>";
                    response += "<span style='padding-left: 2em'>Units: </span><span> " + bill.units + "</span><br>";
                    response += "</td>";

                if(i%4 === 3) response += '</tr>';

            });


                response += '</td>' +
                            '</tr>';


                response += '</table>';

			return response;

        }


		$(document).ready(function() {

			display_prior_auth();

            $('.dt-buttons').css('padding-left', '2em');



		});


		var oTable;

		function display_prior_auth(){
			$('#image').show();
			oTable=$('#show_prior_auth_table').DataTable({
                dom: 'Bfrtip',
                autoWidth: false,
                scrollY: false,
                fixedHeader: true,
                buttons: [
                    'copy', 'excel', 'pdf', 'csv'
                ],
				ajax:{ type: "POST",
					url: "../../../library/ajax/display2_ajax.php",
					data: {
						func:"display_prior_auth",
						pid: "<?php echo $patient['id'] ?>"
					}, complete: function(){

						$('#image').hide();

					}},
                order:[[0, "desc"]],
				columns:[

                    {'data' : 'id', 'width' : '2%'},
					{ 'data': 'date_created' , 'width': '10%'           },
					{ 'data': 'description'  , 'width': '20%'           },
                    { 'data': 'auth_required'  , 'width': '5%'           },
					{ 'data': 'prior_auth_number', 'width': '5%'      	},
                    { 'data': 'auth_range' , 'width': '20%'             },


					{ 'data': 'units' , 'width': '5%'	              	},
                    { 'data': 'days_remaining' , 'width': '5%'          },
					{ 'data': 'units_remaining' , 'width': '5%'         },
					{
						"data": null,
                        'width': '5%',
                        "defaultContent": "<button style='float: right;' class='Edit'>Edit</button>",
						"targets": -2
					},
					{
						"data": null,
                        'width': '5%',
						"defaultContent": "<button style='float: right;' class='Delete'>Delete</button>",
						"targets": -1
					}
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

				},


                "iDisplayLength": 100,
                "select":true,
                "searching":false,
                "retrieve" : true
			});

			$('#column0_show_prior_auth_table').on( 'keyup', function () {
				oTable
					.columns( 0 )
					.search( this.value )
					.draw();
			} );

			$('#column1_show_prior_auth_table').on( 'keyup', function () {
				oTable
					.columns( 1 )
					.search( this.value )
					.draw();
			} );

            $("#refresh_selector").click(function(){

                location.reload();

            });

            $(".add_edit_selector").click(function(){

                dlgopen('./prior_auth_add_view.php?pid=' + <?= $_pid ?> + '&action=add', '_blank', 1775, 1375);


            });


			$("#show_codes_selector").click(function(){
                var authTable = $('.prior_auth_table')

                authTable.toggle();

			});

            $(".nav-bar ").css('padding-left', '7em');
            $('.menu-button ').css('width', '15em');



            $('#show_prior_auth_table tbody').on( 'click', 'button', function () {
                var data = oTable.row( $(this).parents('tr') ).data();
                var next = this.className;

                //handle the delete function

                if(next === "Delete"){

                   alert("delete");


                }else if (next === "Edit"){

                    dlgopen('./prior_auth_add_view.php?pid=' + <?= $_pid ?> + '&action=edit&id='+data.id, '_blank', 1775, 1375);

                }

            } );


            $('.details-control td').css('background-color', 'red');




		}

		function edit_priot_auth(){

		}

		function delete_prior_auth(){


		}

		function create_prior_auth(){


		}
	</script>

</head>
<body class="overview-pane">
<div class="ibh-wrapper">
	<img hidden id="image" src="../../../images/loading.gif" width="75" height="100" >



    <table name='theform' id='theform' method='post'  >
        <tr id = "topnav">
        <td ><button class="menu-button"  align = "top" value="refresh" type="button" id="refresh_selector" name="prior_auth_refresh" ><?php echo htmlspecialchars(xl('Refresh')) ?></button></td>
        <td ><button  class="menu-button add_edit_selector" align = "top" value="add" type="button" id="add_selector" name="prior_auth_add" ><?php echo htmlspecialchars(xl('New Authorization')) ?></button></td>
        <td ><button  class="menu-button" align = "top" value="add" type="button" id="show_codes_selector" name="prior_auth_show_codes" ><?php echo htmlspecialchars(xl('Hide/Show Applicable Bills')) ?></button></td>

        <td width="75%" align="right"><h4 id="title">Prior Auths for Patient PID: <?= $patient['pid'] ?></h4></td>

        </tr>

    </table>
</div>
	<table class=" formtable session_table compact row-border " id="show_prior_auth_table" style="align=:center; width:80%; padding: 2px 5px 5px 5px;">
        <thead align="center">

            <tr align="center" >
                <th align="center" ><?php echo xla('ID'); ?></th>
                <th align="center" ><?php echo xl('Created'); ?></th>
                <th align="center" ><?php echo xla('Description'); ?></th>
				<th align="center" ><?php echo xla('Auth') . "<br>" .  xla('Req?'); ?></th>
                <th align="center" ><?php echo xla('Prior') . "<br>" .  xla('Auth#'); ?></th>
                <th align="center" ><?php echo xla('Auth Valid Period'); ?></th>
				<th align="center" ><?php echo xla('Allotted Units'); ?></th>
				<th align="center" ><?php echo xla('Days Remaining'); ?></th>
				<th align="center" ><?php echo xla('Units Remaining'); ?></th>
                <th align="center" ></th>
                <th align="center" ></th>
            </tr>


		</thead>
		<tbody id="display_prior_auths">

		</tbody>
        <tfoot align="center">
        <tr align="center">
            <th align="center" ><?php echo xla('ID'); ?></th>
            <th align="center" ><?php echo xla('Date') . "\n" . xla('Created'); ?></th>
            <th align="center" ><?php echo xla('Description'); ?></th>
            <th align="center" ><?php echo xla('Auth Req?'); ?></th>
            <th align="center" ><?php echo xla('Prior Auth#'); ?></th>
            <th align="center" ><?php echo xla('Authorization Valid Period'); ?></th>
            <th align="center" ><?php echo xla('Allotted Units'); ?></th>
            <th align="center" ><?php echo xla('Days Remaining'); ?></th>
            <th align="center" ><?php echo xla('Units Remaining'); ?></th>
            <th align="center" ></th>
            <th align="center" ></th>
        </tr>



        </tfoot>

	</table>


</body>

</html>