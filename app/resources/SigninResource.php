<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("Member") || require("models/Member.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class SigninResource extends AppResource{
	public function __construct(){
		parent::__construct();
	}
	public function get(){
		if(AuthController::$current_user !== null){
			$this->set_redirect_to(null);
			return;
		}
		$this->title = "Chinchllalite Sign in Page";
		$this->output = View::render("signin/index", $this);
		return View::render_layout("default", $this);
	}
	public function post(Member $member){
		$member = AuthController::signin($member);
		if($member !== null){
			$this->set_redirect_to($member->is_owner ? null : $member->signin);
			return null;
		}
		App::set_user_message("Invalid credentials");
		$this->output = View::render("signin/index", $this);
		return View::render_layout("default", $this);		
	}	
}
