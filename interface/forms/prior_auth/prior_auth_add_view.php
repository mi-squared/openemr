<?php
// interface/forms/prior_auth/prior_auth_add_view.php
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

<head>
    <title>Add Authorization</title>



    <?php html_header_show();?>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/ajtooltip.js"></script>

    <style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_en.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-2.0.3.min.js"></script>

    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/interface/themes/style_metal.css" type="text/css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/_ibh/css/encounter.css" type="text/css">

</head>
<body class="overview-pane">
<div class="ibh-wrapper">
    <div class="new_prior_auth_buttons">
        <h4 align="center">Prior Auth for <?=$patient['fname'] . " " . $patient['lname']?></h4>
        <br>
        <h5  id="greet_msg" align="center"></h5>
        <?php if ($top_mssg) { ?>
            <div class='top-message'><?=$top_mssg?></div>
        <?php	} ?>

        <br>
    </div>
    <form name="prior_auth" method="post" action="<?php echo $GLOBALS['webroot'] ?>/library/ajax/display_ajax.php">
        <input id="id" type="hidden" name="id" value="<?=$pa_id?>">
        <input id="pid" type="hidden" name="pid" value="<?=$_pid?>">


        <table class = "new_prior_auth_buttons">
            <tr class="nav-bar" >
                <td class="menu-button new_prior_auth_save"><button class="btn_prior_auth_save" align = "top" value="refresh" type="button" name="prior_auth_refresh" ><?php echo htmlspecialchars(xl('Save')) ?></button></td>
                <td class="menu-button new_prior_auth_cancel"><button class="btn_prior_auth_cancel"  align = "top" value="add" type="button" name="prior_auth_add" ><?php echo htmlspecialchars(xl('Cancel')) ?></button></td>
                <td class="menu-button new_prior_auth_test"><button class="btn_prior_auth_test"  align = "top" value="add" type="button" name="prior_auth_add" ><?php echo htmlspecialchars(xl('Test')) ?></button></td>

                <td width="60%"></td>
            </tr>
        </table>




        <table class="ibh-form-table">

            <tr>
                <td>Is the Auth ID # Required?</td>
                <td>
                    <input type="radio" name="auth_number_required" value="1" id="authReq" <?=$an_checked?>>
                    <label for="authReq">Yes</label>

                    <input type="radio" name="auth_number_required" value="0" id="noAuthReq" <?=$an_no_checked?>>
                    <label for="noAuthReq">No</label>
                </td>
            </tr>

            <tr id="prior_auth_number_row" style="<?=$an_no_style?>">
                <td>Auth ID #:</td>
                <td><input  type="text" size="35" name="prior_auth_number" id="prior_auth_number" placeholder="<?=$an_placeholder?>" value="<?=$pa['prior_auth_number']?>"></td>
            </tr>
            <tr>
                <td>Code:</td><td>
                    <input class='required' id="code1" type="text" size="5" name="code1" value="<?=$pa['code1']?>">
                    <input id="code2" type="text" size="5" name="code2" value="<?=$pa['code2']?>">
                    <input id="code3" type="text" size="5" name="code3" value="<?=$pa['code3']?>">
                    <input id="code4" type="text" size="5" name="code4" value="<?=$pa['code4']?>">
                    <input id="code5" type="text" size="5" name="code5" value="<?=$pa['code5']?>">
                    <input id="code6" type="text" size="5" name="code6" value="<?=$pa['code6']?>">
                    <input id="code7" type="text" size="5" name="code7" value="<?=$pa['code7']?>">
                </td>

            </tr>

            <tr>
                <td>Description:</td>
                <td><input  class="required" type="text" size="55" name="description" id="description" value="<?=$pa['description']?>"></td>
            </tr>



            <tr class="auth-units">

                <td>Initial Units/Sessions:</td><td><input class="required" type="text" size="5" name="units" id="units" value="<?=$pa['units']?>"> <small>(used: -<?=$pa['bills'] . " billed units and " . $pa['unit_adjustment'] . " adj. = " . ((-1 * $pa['bills']) + $pa['unit_adjustment'])?>) remaining: <?=$pa['units_remaining']?></small></td>
            </tr>
            <tr class="auth-units">
                <td>Unit Adjustment:</td><td>
                    <input  id="unit_adjustment" type="text" size="5" name="unit_adjustment"  value="<?=$pa['unit_adjustment']?>"/> <small>...</small>
                </td>
            </tr>
            <tr class="auth-units">
                <td>Alert @ Units Used:</td><td>
                    <input class = required id="alert_units" type="text" size="5" name="alert_units"  value="<?=$pa['alert_units']?>"/> <small>(@ units remaining)</small>
                </td>
            </tr>


            <tr class="auth-days ">
                <td>Auth Length:</td>
                <td><label>From: </label>
                    <input class='required' type='text' size='10' name="auth_from" id="auth_from"
                           value="<?=$pa['auth_from']?>"
                           title="yyyy-mm-dd"/>
                    <img src="../../pic/show_calendar.gif" align="absbottom" width="24" height="22"
                         id="img_auth_from" border="0" alt="[?]" style="cursor:pointer;cursor:hand"
                         title="Click here to choose a date"/>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <label>To: </label>
                    <input class='required' type='text' size='10' name='auth_to' id="auth_to"
                           value="<?=$pa['auth_to']?>"
                           title="yyyy-mm-dd"/>
                    <img src="../../pic/show_calendar.gif" align="absbottom" width="24" height="22"
                         id="img_auth_to" border="0" alt="[?]" style="cursor:pointer;cursor:hand"
                         title="Click here to choose a date"/>

                </td>
            </tr>
            <tr class="auth-days">
                <td>Alert @ Days Out:</td><td>
                    <input class="required" id="alert_days" type="text" size="5" name="alert_days" value="<?=$pa['alert_days']?>" placeholder="days"/> <small>(days prior to end-date)</small>
                </td>
            </tr>




            <tr>
                <td>Auth Contact:</td>
                <td><input type="text" size="25" name="auth_contact" id="auth_contact" value="<?=$pa['auth_contact']?>"></td>
            </tr>


            <tr>
                <td>Auth Phone:</td><td> <input type="text" size="15" name="auth_phone" value="<?=$pa['auth_phone']?>">  </td>
            </tr>



            <tr>
                <td>Comments:</td><td colspan="2"><textarea id="comments" name="comments" value="" cols="75" rows="8"><?=$pa['comments']?></textarea></td>
            </tr>

            <tr>
                <td>Send Alerts To:</td><td>

                    <?php
                    $alerts_to_arr = explode(",", $pa['alerts_to']);

                    $my_sel = in_array($_SESSION['authId'], $alerts_to_arr) ? "selected": "";
                    //echo "<br>in db alerts to: " . $pa['alerts_to'];
                    //echo "<br>session authId: " . $_SESSION['authId'];
                    ?>


                    <select class="required" style="width:100%; height:200px;" id="alerts_to" name="alerts_to[]" multiple="multiple">

                        <option value="<?php echo attr(intval($_SESSION['authId'])); ?>" <?=$my_sel?>><?php echo xlt('Myself') ?></option>
                        <?php

                        $uSQL = sqlStatement('SELECT id, fname,	mname, lname  FROM  `users` WHERE  `authorized` = 1 AND `facility_id` > 0 AND id != ? and active=1 ORDER BY lname, fname', array(intval($_SESSION['authId'])));

                        for($i=2; $uRow=sqlFetchArray($uSQL); $i++){
                            $sel = in_array($uRow['id'], $alerts_to_arr) ? "selected": "";
                            echo '<option value="',attr($uRow['id']),'" ' . $sel . '>',text($uRow['lname'].', '.$uRow['fname'].' '.$uRow['mname']),'</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>&nbsp;


                </td>


            </tr>
        </table>

        <table class = "new_prior_auth_buttons">
            <tr class="nav-bar" >
                <td class="menu-button new_prior_auth_save"><button class="btn_prior_auth_save" align = "top" value="refresh" type="button" name="prior_auth_refresh" ><?php echo htmlspecialchars(xl('Save')) ?></button></td>
                <td class="menu-button new_prior_auth_cancel"><button class="btn_prior_auth_cancel"  align = "top" value="add" type="button" name="prior_auth_add" ><?php echo htmlspecialchars(xl('Cancel')) ?></button></td>
                <td class="menu-button new_prior_auth_test"><button class="btn_prior_auth_test"  align = "top" value="add" type="button" name="prior_auth_add" ><?php echo htmlspecialchars(xl('Test')) ?></button></td>
                <td width="60%"></td>
            </tr>
        </table>


    </form>

</body>
</html>
<script>
    $(document).ready(function() {

        var id = '<?= $_GET['id'] ?>';
        var action = '<?= $_GET['action'] ?>';
        /*Calendar.setup({inputField:"dob", ifFormat:"%Y-%m-%d", button:"img_dob"});*/
        Calendar.setup({inputField:"auth_from", ifFormat:"%Y-%m-%d", button:"img_auth_from"});
        Calendar.setup({inputField:"auth_to", ifFormat:"%Y-%m-%d", button:"img_auth_to"});

        //handle the formatting of the form
        $('.menu-button').css('padding-left', '2em');
        $('.new_prior_auth_buttons').css('padding-left', '8em');


        $('.required').css('border', '1px solid red');

        $(".required").change(function() {

                if($(this).val().length > 0){
                    $(this).css('border', '2px solid green');
                }else{

                    $(this).css('border', '2px solid red');
                }

        });

        $('.btn_prior_auth_save').prop("disabled", true); //disable the save button as default
        $("#greet_msg").text("Please fill in all necessary fields").css('color', 'rgba(228,123,123,244)').css('font-size', '18px');

        //handle the cancel button
        $('.btn_prior_auth_cancel').on('click', function(){
            var p = window.self;
            p.close();
        });

        //Fill in all available form field if this is an edit
        if(action == "edit"){

            //Fill the form

            $.ajax({
                type: "POST",
                url: "<?php echo $GLOBALS['webroot'] ?>/library/ajax/display.php",
                data: {

                    id: id,
                    action: 'grab_data'
                },
                success: function (result) {
                    console.log(result);
                    var data = JSON.parse(result);

                    //Fill in all the fields
                    $('#id').val(data.id);
                    $('#pid').val(data.pid);
                    $('#prior_auth_number').val(data.prior_auth_number);
                    $('#units').val(data.units);
                    $('#auth_from').val(data.auth_from);
                    $('#auth_to').val(data.auth_to);
                    if(data.auth_number_required == 1){

                        $("#authReq").prop("checked", true);

                    }else{

                        $("#noAuthReq").prop("checked", true);
                    }

                    $('#unit_adjustment').val(data.unit_adjustment).trigger("change");
                    $('#alert_units').val(data.alert_units).trigger("change");
                    $('#alert_days').val(data.alert_days).trigger("change");
                    $('#description').val(data.description).trigger("change");
                    $('#auth_contact').val(data.auth_contact).trigger("change");
                    $('#auth_phone').val(data.auth_phone).trigger("change");
                    $('#code1').val(data.code1).trigger("change");
                    $('#code2').val(data.code2).trigger("change");
                    $('#code3').val(data.code3).trigger("change");
                    $('#code4').val(data.code4).trigger("change");
                    $('#code5').val(data.code5).trigger("change");
                    $('#code6').val(data.code6).trigger("change");
                    $('#code7').val(data.code7).trigger("change");

                    $('#comments').val(data.comments).trigger("change");
                    $("#greet_msg").val("Editing record  " + data.id + '. Patient ' + data.pid).trigger("change");

                    var alerts = data.alerts_to.split(",");
                    console.log(alerts);
                    alerts.forEach(function(alert){

                        $('#alerts_to option[value=' + alert + ']').attr('selected', true).trigger("change");

                    });


                },
                error: function (result) {
                    alert('Error filling form');
                }
            });

        }


        //We want to send the file via ajax
        $(".btn_prior_auth_save").click(function(e) {
            e.preventDefault();
            var data = $('form').serializeArray();

            if(action == 'add') {
                $.ajax({
                    type: "POST",
                    url: "<?php echo $GLOBALS['webroot'] ?>/library/ajax/display.php",
                    data: {
                        id: 'new',
                        func: 'add_auth',
                        fdata: data,
                        action: '<?php echo $_GET['action']; ?>'
                    },
                    success: function (result) {
                        window.opener.location.reload(true);
                        window.close();
                    },
                    error: function (result) {
                        alert('error');
                    }
                });

            }else if(action=='edit'){



                $.ajax({
                    type: "POST",
                    url: "<?php echo $GLOBALS['webroot'] ?>/library/ajax/display.php",
                    data: {
                        id: '<?php echo $_GET['id']; ?>',
                        func: 'edit',
                        fdata: data,
                        action: '<?php echo $_GET['action']; ?>'
                    },
                    success: function (result) {

                        window.opener.location.reload(true);
                        window.close();
                    },
                    error: function (result) {
                        alert('error');
                    }
                });



            }else{

                alert('error');

            }
        });

        //we disable the save button unless all required fields are filled out.
        //listening for  keyups or changes in the fields.
        $("input[type='text'] ,#alerts_to").on("keyup change", function(){

            var reqlength = $('.required').length;
            console.log(reqlength);
            var value = $('.required').filter(function () {
                return this.value != '';
            });

            if ((value.length >=0 && (value.length !== reqlength))  ||  !$('#alerts_to').find(":selected").text() )  {

                $('.btn_prior_auth_save').prop("disabled", true);
                $("#greet_msg").text("Please fill in all necessary fields").css('color', 'rgba(228,123,123,244)').css('font-size', '18px');


                if(!$('#alerts_to').val()){
                    console.log('Please fill out all required send tos.');
                }
            } else {

                $('.btn_prior_auth_save').prop("disabled", false);
                $("#greet_msg").text("Necessary Fields Complete").css('color', 'rgba(70, 91, 21, 1)').css('font-size', '18px');

            }


        });

        //*******************************************************************
        //This is for testing purposes to fill in a form with a single click.
        //In production this button will not be available to press but the code
        //will stay here for future development.
        //*******************************************************************
        $(".btn_prior_auth_test").click(function() {
            $("input[type=radio][name=noAuthReq]:checked").val();
            $("#alerts_to").val($("#alerts_to option:first").val()).trigger("change");
            $("#units").val('40').trigger("change");;
            $("#alert_units").val('5').trigger("change");;
            $('#alert_days').val("7").trigger("change");
            $("#unit_adjustment").val('0').trigger("change");;
            $("#auth_to").val('2019-12-12').trigger("change");;
            $("#auth_from").val('2018-12-01').trigger("change");;
            $("#code1").val('H0000').trigger("change");;
            $("#description").val('testDesc').trigger("change");





        });

    });


</script>