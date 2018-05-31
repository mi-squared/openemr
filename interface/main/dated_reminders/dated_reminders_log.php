<?php 
/**
 * Used for displaying log of dated reminders.
 *
 * Copyright (C) 2012 tajemo.co.za <http://www.tajemo.co.za/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Craig Bezuidenhout <http://www.tajemo.co.za/>
 * @link    http://www.open-emr.org
 */

  $fake_register_globals=false;
  $sanitize_all_escapes=true;

  require_once("../../globals.php");
  require_once("$srcdir/htmlspecialchars.inc.php");
  require_once("$srcdir/acl.inc");
  require_once("$srcdir/dated_reminder_functions.php");
  
  // IBH_DEV_CHG
require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");

	$authUser_id = ibh_get_session_user_id();

  $isAdmin =acl_check('admin', 'users');
?>
<?php
  /*
    -------------------  HANDLE POST ---------------------
  */
  if($_GET){
    if(!$isAdmin){
      if(empty($_GET['sentBy']) and empty($_GET['sentTo']))
        $_GET['sentTo'] = array(intval($_SESSION['authId']));
    }
    echo '<table border="1" width="100%" cellpadding="5px" class="messages-table" id="logTable">
            <thead>
              <tr>
                <!-- <th>'.xlt('ID').'</th> -->
                <th>'.xlt('Sent Date').'<a class="sorter" href="#" data-sort="dr_message_sent_date"><img src="/openemr/_ibh/img/sort_asc.png"></a> <a class="sorter" href="#"  data-sort="dr_message_sent_date DESC"><img src="/openemr/_ibh/img/sort_desc.png"></a></th>
                
                <th>'.xlt('From').'/'.xlt('To').'</th>
                
                <th>'.xlt('Patient').'<a class="sorter" href="#" data-sort="pid"><img src="/openemr/_ibh/img/sort_asc.png"></a> <a class="sorter" href="#" data-sort="pid DESC"><img src="/openemr/_ibh/img/sort_desc.png"></a></th>
                
                <th>'.xlt('Message').'</th>
                
                <th>'.xlt('Due Date').'<a class="sorter" href="#" data-sort="dr_message_due_date"><img src="/openemr/_ibh/img/sort_asc.png"></a> <a href="#" class="sorter" data-sort="dr_message_due_date DESC"><img src="/openemr/_ibh/img/sort_desc.png"></a></th>
                
                <th>'.xlt('Processed Date').'<a class="sorter" href="#" data-sort="processed_date"><img src="/openemr/_ibh/img/sort_asc.png"></a> <a href="#" class="sorter" data-sort="processed_date DESC"><img src="/openemr/_ibh/img/sort_desc.png"></a></th>
                
                <th>'.xlt('Processed By').'</th>
                <th>edit</th>
              </tr>
            </thead>
            <tbody>';
    $remindersArray = array();
    $TempRemindersArray = logRemindersArray();
    foreach($TempRemindersArray as $RA){
	  $remindersArray[$RA['messageID']]['PatientID'] = $RA['PatientID'];
	  $remindersArray[$RA['messageID']]['senderID'] = $RA['senderID'];
      $remindersArray[$RA['messageID']]['messageID'] = $RA['messageID'];
      $remindersArray[$RA['messageID']]['ToName'] = ($remindersArray[$RA['messageID']]['ToName'] ? $remindersArray[$RA['messageID']]['ToName'].', '.$RA['ToName'] : $RA['ToName']);
      $remindersArray[$RA['messageID']]['PatientName'] = $RA['PatientName'];
      $remindersArray[$RA['messageID']]['message'] = $RA['message'];
      $remindersArray[$RA['messageID']]['dDate'] = $RA['dDate'];
      $remindersArray[$RA['messageID']]['sDate'] = $RA['sDate'];
      $remindersArray[$RA['messageID']]['pDate'] = $RA['pDate'];
      $remindersArray[$RA['messageID']]['processedByName'] = $RA['processedByName'];
      $remindersArray[$RA['messageID']]['fromName'] = $RA['fromName'];
    }


    $bar = "foo";
    $foo = "bar";

    foreach($remindersArray as $RA){
      echo '<tr class="heading" id="mssg_' . $RA['messageID'] . '">
              <!-- <td>' . text($RA['messageID']) . '</a></td> -->
              <td>',text($RA['sDate']),'</td>
              <td class="mssg-from-to"><div class="mssg-from">',text($RA['fromName']),'
              <div class="mssg-to">',text($RA['ToName']),'</div></td>
              <td>',text($RA['PatientName'] . " (" . $RA['PatientID'] . ")"),'</td>     
              <td class="message"><div class="content">'.text($RA['message']).'</div></td>    
              <td class="date_td"><div class="content">'.text($RA['dDate']).'</div></td>    
              <td>',text($RA['pDate']),'</td>      
              <td>',text($RA['processedByName']),'</td>';

			  //  ibh_user_is_supervisor() ||
			  if($isAdmin || $authUser_id==$RA['senderID']){
				  echo '<td><button data-pid="' . $RA['PatientID'] . '" data-drid="' . $RA['messageID'] . '" class="edit-button">edit</button><button class="save-button">save</button><br><button data-drid="' . $RA['messageID'] . '" class="delete-button">delete</button><button data-drid="' . $RA['messageID'] . '" class="cancel-button">cancel</button>
				  </td>';
			  } else {
				  echo '<td></td>';
			  }

            echo '</tr>';
    }
    echo '</tbody></table>';
    
    die;
  }
?> 
<html>
  <head>                                    
    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css"> 
    <script type="text/javascript" src="/openemr/_ibh/js/jquery_latest.min.js"></script>
    <script type="text/javascript" src="/openemr/library/js/jquery-calendar.js"></script>
    <!-- <script type="text/javascript" src="/openemr/library/js/jquery.grouprows.js"></script>
    <script type="text/javascript" src="/openemr/library/js/grouprows.js"></script> -->
    <script language="JavaScript">   
      $(document).ready(function (){  


	    var temp_cal = {};


        $("#submitForm").click(function(){

	      console.log($("#logForm").serialize());

          // top.restoreSession(); --> can't use this as it negates this ajax refresh
          $.get("dated_reminders_log.php?"+$("#logForm").serialize(), 
               function(data) {
                  $("#resultsDiv").html(data);
                  <?php
                    if(!$isAdmin){
                      echo '$("select option").removeAttr("selected");';
                    }
                  ?>  
                	return false;
               }
             )   
          return false;
        });



        function getContent($row) {
	        var mssg = $row.find(".message .content").text();
	        return mssg;
        }

        function getDate($row) {
	        var mssg = $row.find(".date_td .content").text();
	        return mssg;
        }


        function edit_dated_reminder(obj) {

	        $.ajax({
					url:"/openemr/_ibh/ajax/edit_dated_reminder.php",
					data:obj,
					success: function(data) {
						console.log("dr response: ", data);
						$("#submitForm").trigger("click");
					}
				});
        }




         $("#resultsDiv").on("click", ".cancel-button", function() {
	        var $row = $(this).closest("tr");
	        cancelEditing($row);
	    });


        $("#resultsDiv").on("click", ".delete-button", function() {

	        var $bt = $(this);
	        var $row = $bt.closest(".heading");
	        console.log("$row", $row);

	        $row.addClass("deleting");

	        setTimeout(function() {

		        var conf = confirm("Are you sure you want to delete this message?");
		        if (conf) {

			        var drid = $bt.data("drid");
			        var stuff = {dr_id:drid, delete_it:"true"};

			        var edited = edit_dated_reminder(stuff);

		        } else {
			        $row.removeClass("deleting");
		        }


	        }, 100);

	    });

	    function cancelEditing($row) {
		    $row.find(".message .content").show();
		    $row.find(".message .content_edit").remove();
		    $row.find(".edit-button").show();
		     $row.find(".cancel-button").hide();
		    $row.find(".save-button").hide();
		    $row.find(".date_td .content").show();
		    $row.find(".date_edit").hide();

		    $row.removeClass("editing");

	    }


	    function clearEditing() {

		    $("tr.editing").each(function(el) {
			    cancelEditing($(this));

		    });
	    }

	    $("#resultsDiv").on("click", ".sorter", function() {

		    var sort = $(this).data("sort");
		    $("#form_sort").val(sort);
		    $("#submitForm").trigger("click");

		});



	    $("#resultsDiv").on("click", ".edit-button", function() {

		    // close all others that are open
		    clearEditing();

		    var $bt = $(this);

		    var $row = $bt.closest(".heading");
		    var row_id = $row.attr("id");

		    $row.addClass("editing");

		    var drid = $bt.data("drid");
		    var pid = $bt.data("pid");

		    $row.find(".cancel-button").show();

		    var $mssg_td = $row.find(".message");
		    var $mssg_container = $row.find(".message .content");
		    var mssg_content = getContent($row);
		    var user_id = <?=$authUser_id?>;


		    $txt_area = $("<textarea class='content_edit'>" + mssg_content + "</textarea>").appendTo($mssg_td);
		    $mssg_container.hide();

		    var $date_td = $row.find(".date_td");
		    var $date_container = $row.find(".date_td .content");
		    var date_content = getDate($row);
		    $date_field = $('<input type="text" id="_' + row_id + '" class="date_edit" value="' + date_content + '" title="yyyy-mm-dd">').appendTo($date_td);
		    $date_container.hide();

		    var $save_button = $row.find(".save-button");


		    Calendar.setup({inputField:"_" + row_id, ifFormat:"%Y-%m-%d", button:"", showsTime:'false'});



		    $save_button.show().on("click", function() {

			    var new_content = $txt_area.val();
			    var due_date = $date_field.val();

			    $bt.show();

			    $mssg_container.text(new_content).show();
			    $date_container.text(due_date).show();
			    $txt_area.remove();
			    $date_field.remove();

			    $row.removeClass("editing");

			    $save_button.off("click").hide();

			    var stuff = {dr_id:drid, due_date:due_date, pid:pid, user_id:user_id, dr_message_text:new_content};

			    var edited = edit_dated_reminder(stuff);



		    });


		    $bt.hide();




		    return false;

	    });




      });
    </script> 


    <link rel="stylesheet" type="text/css" href="<?= $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?>/_ibh/css/messages.css">



  </head>
  <body class="body_top"> 
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

             
<?php 
  $allUsers = array();
  $uSQL = sqlStatement('SELECT id, fname,	mname, lname  FROM  `users` WHERE  `active` = 1 AND `facility_id` > 0 AND id != ? ORDER BY lname, fname',array(intval($_SESSION['authId'])));
  for($i=0; $uRow=sqlFetchArray($uSQL); $i++){ $allUsers[] = $uRow; }
?>     
    <form method="get" id="logForm" onsubmit="return top.restoreSession()">         
	  <input type="hidden" name="orderBy" id="form_sort" value="">
      <h3><?php echo xlt('Dated Message Log') ?></h3>
      <div class="filters">

      Start Date: <input id="sd" type="text" name="sd" value="" onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php echo xla('yyyy-mm-dd'); ?>' />   &nbsp;&nbsp;&nbsp;

      End Date : <input id="ed" type="text" name="ed" value="" onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php echo xla('yyyy-mm-dd'); ?>' />   <br /><br />

      <table style="width:100%">
        <tr>
          <td style="width:50%">
            <?php echo xlt('Sent By, Leave Blank For All') ?> : <br />                                    
            <select style="height:200px;width:100%;" id="sentBy" name="sentBy[]" multiple="multiple">
              <option value="<?php echo attr(intval($_SESSION['authId'])); ?>"><?php echo xlt('Myself') ?></option>
              <?php 
                // if($isAdmin)
                  foreach($allUsers as $user) {
                    echo '<option value="',attr($user['id']),'">',text($user['lname'] . ", " . $user['fname'].' '.$user['mname']),'</option>';
                    }
              ?>
            </select>   
          </td>
          <td style="width:50%">
            <?php echo xlt('Sent To, Leave Blank For All') ?> : <br />      
            <select style="height:200px;width:100%" id="sentTo" name="sentTo[]" multiple="multiple">
              <option value="<?php echo attr(intval($_SESSION['authId'])); ?>"><?php echo xlt('Myself') ?></option>
              <?php 
                // if($isAdmin)
                  foreach($allUsers as $user) {
                    echo '<option value="',attr($user['id']),'">',text($user['lname'] . ", " . $user['fname'].' '.$user['mname']),'</option>';
                    }
              ?>
            </select>  
          </td>
        </tr>
      </table>
      <div class="checkboxes">
      <input type="checkbox" name="processed" id="processed"><label for="processed"><?php echo xlt('Processed') ?></label>      
<!-----------------------------------------------------------------------------------------------------------------------------------------------------> 
      <input type="checkbox" name="pending" id="pending"><label for="pending"><?php echo xlt('Pending') ?></label>          


      <button class="go-button group" value="Refresh" id="submitForm"><?php echo xlt('Refresh') ?></button>
      </div>

       </div>


    </form>
    
    <div id="resultsDiv"></div> 
 
  </body> 
<!-- stuff for the popup calendar -->
<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>
<script language="Javascript"> 
  Calendar.setup({inputField:"sd", ifFormat:"%Y-%m-%d", button:"img_begin_date", showsTime:'false'});  
  Calendar.setup({inputField:"ed", ifFormat:"%Y-%m-%d", button:"img_begin_date", showsTime:'false'}); 
</script>
</html> 
