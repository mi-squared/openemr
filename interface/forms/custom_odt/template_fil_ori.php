<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */  

/**
 * This sample script replaces some content in a template
 */
require_once '../../../library/docxpresso/CreateDocument.inc';
if(file_exists('psych_progress_note_5.pdf')){
    
    unlink('psych_progress_note_5.pdf');
}
$date = date("Y-m-d");
$time = date("H:m:s");
$therapist = "Levi Pines";
$facility = "Idaho Behavioral Health";

$doc = new Docxpresso\createDocument(array('template' => '../../../sites/default/documents/doctemplates/psych_progress_note_5.odt'));
$format = '.pdf';//.pdf, .doc, .docx, .odt, .rtf
//replace single variable
$doc->replace(array('PatientName' => array('value' => 'Frank Little John')));
$doc->replace(array('Today' => array('value' => $date)));
$doc->replace(array('therapist' => array('value' => $therapist)));
$doc->replace(array('StartDOStime' => array('value' => $time)));
$doc->replace(array('FacilityName' => array('value' => $facility)));
//replace natural text
//$doc->replace(array('replace me, please' => array('value' => 'another text')), array('format' => array('','')));
//populate the list
//$doc->replace(array('item' => array('value' => array('first', 'second', 'third'))), array('element' => 'list'));
//populate the table
/**
$vars =array('product' => array('value' => array('Smart phone', 'MP3 player', 'Camera')),
             'price' => array('value' => array('430.00', '49.99', '198,49')),
);
 * 
 */
//$doc->replace($vars, array('element' => 'table'));	
//replace single variable by different values
//$doc->replace(array('test' => array('value' => array('one', 'two', 'three'))));
//and now a variable in the header
//$doc->replace(array('example_header' => array('value' => 'header text')), array('target' => 'header'));
//include in the render method the path where you want your document to be saved
$doc->render('psych_progress_note_5' . $format); 
//echo a link to the generated document
echo 'You may download the generated document from the link below:<br/>';
echo '<a href="' . 'psych_progress_note_5' . $format . '">Download document</a>';


