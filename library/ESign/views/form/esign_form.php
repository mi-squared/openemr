<?php 
/**
 * Signature form view script for form module
 * 
 * Copyright (C) 2013 OEMR 501c3 www.oemr.org
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
 * @author  Ken Chapple <ken@mi-squared.com>
 * @author  Medical Information Integration, LLC
 * @link    http://www.open-emr.org
 **/

require_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . "/_ibh/ibh_functions.php");
 
$string = $_SERVER['HTTP_REFERER'];
$msgId = substr(strrchr($string, "="), 1);

if(!is_numeric($msgId)){
	$msgId = 0;
}
?>

<!-- IBH_DEV -->
<link rel="stylesheet" href="/openemr/_ibh/css/encounter.css" type="text/css">
       
<div id='esign-form-container'>
	<form id='esign-signature-form' method='post' action='<?php echo attr( $this->form->action ); ?>'>
		<div class="esign-signature-form-element">
                     
              <div class="esign-checkbox" id="checkout_row">
              	<input id="checkout" name="checkout" type="checkbox" value="1" checked/>
			  	<label>Checkout patient</label>
			  	<div class="esign-options" id="checkout_options">
				  	<label>Modifiers:</label>
					 <select name="mod" id="checkout_modifiers">
			         <option value="1"> None Needed </option>
					 <option value="76"> 76 </option>
					 <option value="25"> 25 </option>
					 <option value="59"> 59 </option>
					 <option value="GT"> GT </option>
					 <option value="U1"> U1 </option>
					 </select>
			  	</div>
			  </div>
			  
			  <div class="esign-checkbox" id="interactive_row">
			  <input id="interactive_complexity" name="interactive_complexity" type="checkbox" value='1'/>
              <label>Interactive Complexity</label>
			  </div>



			  <div class="esign-checkbox" id="interpreter_row">
				  <input id="interpreter" name="interpreter_used" type="checkbox" value='1'/>
	              <label>Was Interpreter Used?</label>
	              
	              <div class="esign-options" id="interpreter_options" style="display:none">
		          	<label>Units: </label>
					 <select name="interpreter_minutes" id="interpreter_minutes">
			         <option value="1"> 1 </option>
					 <option value="2"> 2 </option>
					 <option value="3"> 3 </option>
					 <option value="4"> 4 </option>
					 <option value="5"> 5 </option>
			         <option value="6"> 6 </option>
					 <option value="7"> 7 </option>
					 <option value="8"> 8 </option>
					 <option value="9"> 9 </option>
					 <option value="10"> 10 </option>
			         <option value="11"> 11 </option>
					 <option value="12"> 12 </option>
					 <option value="13"> 13 </option>
					 <option value="14"> 14 </option>
					 <option value="15"> 15 </option>
			         <option value="16"> 16 </option>
					 <option value="17"> 17 </option>
					 <option value="18"> 18 </option>
					 <option value="19"> 19 </option>
					 <option value="20"> 20 </option>		 
			         <option value="21"> 21 </option>
					 <option value="22"> 22 </option>
					 <option value="23"> 23 </option>
					 <option value="24"> 24 </option>
					 <option value="25"> 25 </option>		 		 
					 </select>
					 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					 <label>Interpreter Name:</label>
				     <input type="text" name="interpreter_name" id="interpreter_name" size = "18" value="" autocomplete="off">
				     
	              </div>
              </div>
              
              <!-- 
               <div class="esign-checkbox">
			  <input id="injection" name="injection" type="checkbox" onclick="doinjection()" />
              <label>Add Injection Code?</label>
			  </div>
			  -->
			   <div class="esign-checkbox" id='transportation_row'>
			  <input id="_transportation" name="transportation" type="checkbox" value="1" />
              <label>Psychotherapy Home Visit?</label>
			  </div> 
              
              
              <?php 
	          $acl_names=acl_get_group_titles($_SESSION['authUser']);
			  $is_physician = in_array("Physicians", $acl_names) ? true: false;
           
	          if ( $this->form->showLock && (ibh_user_is_supervisor() || $is_physician) ) { ?>
		
		      <div class="esign-checkbox">
		      	<input type="checkbox" id="lock" name="lock" />
		      	<label for='lock'><?php echo xlt('Lock?');?></label>
			  	<input type="hidden" id="msgid" name="msgid" value="<?php echo $msgId ; ?>" />
			  </div>
		<?php } else { ?>
				<input type="hidden" id="lock" name="lock" value=""/>
			
		<?php } ?>
		  

			  					  
		</div>		
		<div class="esign-signature-form-element">
		      <textarea name='amendment' id='amendment' placeholder='<?php echo xlt("Enter an amendment..."); ?>'></textarea> 
		</div>
        <div class="password-banner">
		      <span id='esign-signature-form-prompt'><?php echo xlt("Your password is your signature" ); ?></span> 
		</div>

		<div class="esign-signature-form-element">
		      <label for='password'>Password: </label> 
		      <input type='password' id='password' name='password' size='10' />
		</div>		
		<div class="esign-signature-form-element">
		      <input class="go-button delete" type='button' value='<?php echo xla('Back'); ?>' id='esign-back-button' /> 
	          <input class="go-button" type='submit' value='<?php echo xla('Sign'); ?>' id='esign-sign-button-form'/>
	    </div>
	    
	    <input type='hidden' name='esign_form_submit' value='1' />
	    <input type='hidden' id='formId' name='formId' value='<?php echo attr( $this->form->formId ); ?>' /> 
		<input type='hidden' id='table' name='table' value='<?php echo attr( $this->form->table ); ?>' /> 
		<input type='hidden' id='formDir' name='formDir' value='<?php echo attr( $this->form->formDir ); ?>' />
		<input type='hidden' id='encounterId' name='encounterId' value='<?php echo attr( $this->form->encounterId ); ?>' />
		<input type='hidden' id='userId' name='userId' value='<?php echo attr( $this->form->userId ); ?>' />
	    <input type='hidden' id='pid' name='pid' value='<?php echo $GLOBALS['pid']; ?>' />
	    
	</form> 
	
</div>

<script type="text/javascript">
	
	// window.encounter_id gets set when this is being rendered
	// in /library/Esign/js/jquery.esign.js
	// We'll use it later to get the billing status of the encounter.
	var encounter_id = window.encounter_id;
	console.log("encounter id", encounter_id);
	
	
	$("#esign-sign-button-form").click(function() {
				
		var interp = $("#interpreter").attr('checked');
		var conf = true;
		
		if (interp) {
			
			conf = confirm("Confirming: " + $("#interpreter_name").val() + " interpreted, " + $("#interpreter_minutes").val() + " units?");
		}
		
		return conf;
	});
	
	$.ajax({
		url:"/openemr/_ibh/ajax/get_appointment_status.php",
		data:{encounter:encounter_id},
		success: function(res) {
			console.log("encounter id in  ajax:", encounter_id, res)
			if (res.pc_apptstatus == ">" ) {
				$("#checkout_row").addClass("esign-bill-recorded").html("Checked out!&nbsp;&nbsp;&nbsp;&nbsp;<span id='get_mod'></span>");
			}
			
			
		}
	});
	
	
	$.ajax({
		url:"/openemr/_ibh/ajax/get_encounter_billing.php",
		data:{encounter:encounter_id},
		success: function(res) {
			console.log("billing stuff" , res);
			var bill = {}
			for (var i=0; i<res.billing.length; i++) {
				bill = res.billing[i];
				if (bill.code_text == "Interpreter") {
					$("#interpreter_row").addClass("esign-bill-recorded").html("Interpreter: " + bill.notecodes + "<br>$" + bill.fee);
				} else if (bill.code_text == "Interactive Complexity") { 
					$("#interactive_row").addClass("esign-bill-recorded").html("Interactive Complexity billed");
					
				} else if (bill.code_type == "CPT4" && bill.modifier) {
					$("#get_mod").text("modifier: " + bill.modifier);
				} else if (bill.code == "T2002") { 
					$("#transportation_row").addClass("esign-bill-recorded").html("Transportation billed");
					
				}
				
			}
		}
	});
	
	
   
    $("#checkout").click(function() {
	    
	    
		var is_checked = $(this).is(":checked");
        console.log("checkout...");

        if (is_checked) {
	       	$("#checkout_options").show();
        } else {
	        $("#checkout_options").hide();
	        $("#checkout_modifiers").val("1");
        }
        
    });
    
    
    $("#interpreter").click(function() {
		var is_checked = $(this).is(":checked");
        
        if (is_checked) {
	       	$("#interpreter_options").show();
	       	$("#interpreter_name").val("");
        } else {
	        $("#interpreter_options").hide();
	        $("#iused").val("1");
        }
        
    });

    
    
</script>

