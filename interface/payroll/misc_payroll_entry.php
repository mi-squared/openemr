<?php


require_once("../globals.php");
require_once("{$GLOBALS['srcdir']}/user.inc");
require_once("{$GLOBALS['srcdir']}/options.inc.php");

if(!empty($_POST['value'])){
	echo "POSTED";

}

$frow = 10;
$nlist = generate_form_field($frow);

?>
<html>
<title></title>
<head>
<?php html_header_show(); ?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="../../library/js/jquery-ui.min.css" type="text/css" /> 

<style>
.pform
{
	padding: 30px 80px;
}

label
{
	display: inline-block;
	float: left;
	clear: left;
	width: 90px;
	margin-top: 3px;
	padding: 3px;
	
	
}

input 
{
	display: inline-block;
	float: left;
	padding: 5px 8px;
	
}

</style>
</head>
<body class="body_top">
	<div class="pform">
	<h2>Enter PTO, Holiday or Other</h2>


		<form class="payroll" method="post" action="misc_payroll_entry.php">
            <?php echo $nlist; ?>
		    <label> Employee:</label><input type="text" value="" name="value" size="15" class='auto'><br>
			<label> Entry:</label><input type="text" value="" name="value" size="5"><br>
			<br>
			<br>
			<input type="radio" name="value" value="PTO"> PTO<br>
			<input type="radio" name="value" value="Holiday"> Holiday<br>
			<input type="radio" name="value" value="Holiday"> Milage<br>
			<input type="radio" name="value" value="Other"> Other<br>
			<br>

			<input type="submit" value="Submit">
		</form>

	</div>
<script type="text/javascript" src="../../library/js/jquery-1.10.1.js"></script>
<script type="text/javascript" src="../../library/js/jquery-ui.min.js"></script>  
<script type="text/javascript">
$(function() {
    
    //autocomplete
    $(".auto").autocomplete({
        source: "search.php",
        minLength: 1
    });                

});
</script>
</body>
</html>