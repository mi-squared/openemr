<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once('../../globals.php');
global $pid, $encounter;

$dir = '../../../sites/default/documents/doctemplates';
$files = array_diff(scandir($dir), array('..', '.'));
sort($files);

$today = date("Y-m-d"). " 00:00:00";

$sql = "SELECT encounter FROM form_encounter WHERE pid = $pid AND date = '$today'";
$qd = sqlQuery($sql);
?>

<html >
    <title>
        
    </title>
    <head>

        <script type="text/javascript" src="../../../library/overlib_mini.js"></script>
        <script type="text/javascript" src="../../../library/dialog.js"></script>
        <script type="text/javascript" language="javascript">
 
            function question(temp_list){
                dlgopen('../custom_odt/popup.php', '_blank', 200, 250);
                return false; 
            }
            
            
       </script>
       
    </head>
    <body>



<h1>Template List</h1>
<?php
if(empty($qd) && empty($encounter)){
    echo "<head><meta http-equiv='refresh' content='5' ></head>";
    echo "Please select an encounter, to proceed";
    exit;
}
//echo var_dump($files);  //troubleshooting
if(!empty($qd['encounter'])){
echo "<font color='blue'>Current encounter being used is " . $qd['encounter'] . "</font>";
}else{
  echo "<font color='blue'>Current encounter being used is " . $encounter . "</font>";  
}
?>

<form method="post" action='template_fill.php' name="temp_list">
<table>
<?php

foreach($files as $file){
    if($file != "." || $file != ".."){
    echo "<input type=radio name='template' onclick='question(this);' value='".$file."' >" . $file . "<br>";
    }
}

echo "</p>";

?>
</table>
    <input type='submit' value='Fetch'>
</form>

    </body>
    </html>