<?php

require_once(dirname(__FILE__) . "/../../../library/classes/ORDataObject.class.php");



/**
 * class PriorAuth
 *
 */
class FormPriorAuth extends ORDataObject {

	/**
	 *
	 * @access public
	 */


	
	/**
	 *
	 * @access private
	 */

	var $id;
	var $date;
	var $pid;
	var $activity;
	var $prior_auth_number;
	var $comments;
    var $desc;
    var $auth_from;
	var $auth_to;
	var $auth_alert;
	var $units;
	var $auth_length;
	var $auth_contact;
	var $auth_phone;
	var $code1;
	var $code2;
	var $code3;
	var $code4;
	var $code5;
	var $code6;
	var $code7;
	var $used;
	var $archived;
	
	/**
	 * Constructor sets all Form attributes to their default value
	 */

	function FormPriorAuth($id= "", $_prefix = "")	{


		$pid = $GLOBALS['pid'];

		//Pull the saved prior auth if there is one - Sherwin
		//$sid = sqlQuery("SELECT form_id FROM `forms` WHERE `pid` = $pid AND `formdir` LIKE 'prior_auth' ");
		/*
		if(empty($id) && !empty($sid['form_id'])){
			$id = $sid['form_id'];
		}*/
		if (is_numeric($id)) {
			$this->id = $id;
		}
		else {
			$id = "";
		}
		$this->_table = "form_prior_auth";
		$this->date = date("Y-m-d H:i:s");
		$this->activity = 1;
		$this->pid = $GLOBALS['pid'];
		$this->prior_auth_number = "";
		if ($id != "") {
			$this->populate();
		}
	}

	function toString($html = false) {
		$string .= "\n"
			."ID: " . $this->id . "\n";

		if ($html) {
			return nl2br($string);
		}
		else {
			return $string;
		}
	}
	function set_id($id) {
		if (!empty($id) && is_numeric($id)) {
			$this->id = $id;
		}
	}
	function get_id() {
		return $this->id;
	}
	function set_pid($pid) {
		if (!empty($pid) && is_numeric($pid)) {
			$this->pid = $pid;
		}
	}
	function get_pid() {
		return $this->pid;
	}
	function set_activity($tf) {
		if (!empty($tf) && is_numeric($tf)) {
			$this->activity = $tf;
		}
	}
	function get_activity() {
		return $this->activity;
	}
	
	
	function set_comments($string) {
		$this->comments = $string;
	}
	
	function get_comments() {
		return $this->comments;
	}
	
	function set_prior_auth_number($string) {
		$this->prior_auth_number = $string;
	}
	
	function get_prior_auth_number() {
		return $this->prior_auth_number;
	}
    function get_desc() {
            return $this->desc;
        }
	function set_desc($string) {
            $this->desc = $string;
        }
    function get_auth_for() {
            return $this->auth_for;
        }
    function set_auth_for($string){
            $this->auth_for = $string;
        }
    function get_auth_from() {
            return $this->auth_from;
        }
    function set_auth_from($string){
            $this->auth_from = $string;
        }
    function get_auth_to() {
            return $this->auth_to;
        }
    function set_auth_to($string){
            $this->auth_to = $string;
        }
    function get_auth_alert() {
            return $this->auth_alert;
        }
    function set_auth_alert($string){
            $this->auth_alert = $string;
        }
	function set_units($string){
            $this->units = $string;
        }
    function get_units(){
            return $this->units;
        }
    function get_auth_length(){
            return $this->auth_length;
        }
	function set_auth_length($string){
            $this->auth_length = $string;
        }
    function get_dollar(){
            return $this->dollar;
        }
	function set_dollar($string){
            $this->dollar = $string;
        }
        function get_used(){
            return $this->used;
        }
	function set_used($string){
            $this->used = $string;
        }
        function get_auth_contact(){
            return $this->auth_contact;
        }
	function set_auth_contact($string){
            $this->auth_contact = $string;
        }
        function get_auth_phone(){
            return $this->auth_phone;
        }
	function set_auth_phone($string){
            $this->auth_phone = $string;
        }
	function get_date() {
		return $this->date;
	}
	function set_code1($string){
            $this->code1 = $string;
        }
	function get_code1(){
		return $this->code1;
	}
	function set_code2($string){
            $this->code2 = $string;
        }
	function get_code2(){
		return $this->code2;
	}
	function set_code3($string){
            $this->code3 = $string;
        }
	function get_code3(){
		return $this->code3;
	}
	function set_code4($string){
            $this->code4 = $string;
        }
	function get_code4(){
		return $this->code4;
	}
	function set_code5($string){
            $this->code5 = $string;
        }
	function get_code5(){
		return $this->code5;
	}
	function set_code6($string){
            $this->code6 = $string;
        }
	function get_code6(){
		return $this->code6;
	}
	function set_code7($string){
            $this->code7 = $string;
        }
	function get_code7(){
		return $this->code7;
	}
    function set_not_req($string){
		return $this->not_req = $string;
    }
	function get_not_req(){
		return $this->not_req;
	}
    function set_override($string){
		return $this->override = $string;
    }
	function get_override(){
		return $this->override;
	}
    function set_archived($string){
		return $this->archived = $string;
    }
	function get_archived(){
		return $this->archived;
	}
}	// end of Form

?>