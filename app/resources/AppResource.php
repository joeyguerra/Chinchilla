<?php
class AppResource extends Resource{	
	public function __construct(){
		parent::__construct();
	}
	public static function resource_not_found($publisher, $info){
		return null;
	}
	public static function begin_request($publisher, $info){
	}
	public function __destruct(){
		parent::__destruct();
	}
	public $errors;
}