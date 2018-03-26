<?php
// for including into patient_account.php and era_overview.php
?>
<div id="posting_block">
	<div class="pb-cancel-button">cancel</div>
<table class="list-table">
	<tr><th>session</th><th>code type</th><th>code</th><th>mod</th><th>payer type</th><th>pay amt</th><th>adj amt</th><th>memo</th></tr>
	<tr>
		<td id='session_id'></td>
		<td><input id="code_type" type="text" value="CPT4"></td>
		<td><input id="code" type="text"></td>
		<td><input id="modifier" type="text"></td>
		<td><select id="payer_type"><option value='1'>Ins 1</option><option value='2'>Ins 2</option><option value='0'>patient</option></select></td>
		<td><input id="pay_amount" type="text"></td>
		<td><input id="adj_amount" type="text"></td>
		<td><input id="memo" type="text"></td>
	</tr>
	<tr><td colspan='8' class='sub'><input class="submit-post" type="submit" value="submit payment/adjustment"></td></tr>
	
</table>
</div>