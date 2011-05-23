<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class SignoutResource extends AppResource{	
	public function __construct(){
		parent::__construct();
	}
	public function get(){
		AuthController::set_chin_auth(false, time()-3600);
		$this->set_redirect_to(null);
	}
}