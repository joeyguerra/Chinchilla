<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class ColumnsResource extends AppResource{
	public function __construct(){
		parent::__construct();
		$this->contacts = array();
		$this->groups = array();
		if(!AuthController::is_authed()){
			$this->set_unauthed("Please signin.");
		}
	}	
}