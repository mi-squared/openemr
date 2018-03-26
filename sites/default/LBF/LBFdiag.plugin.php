<?php


function load_descr(){
echo "
	   
	   var txt = 'Put stuff here';
	   document.getElementById('form_desc_diag1').value = txt;
	";
}


function LBFdiag_javascript_onload(){

echo load_descr();
	
}