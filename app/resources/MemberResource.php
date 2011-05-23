<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("Member") || require("models/Member.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class MemberResource extends AppResource{
	public function __construct(){
		parent::__construct();
	}
	public $person;
	public $legend;
	public function get(Member $member = null){
		$member = $member === null ? new Member() : $member;
		$this->person = Member::find_by_id((int)$member->id);
		$view = "member/show";
		$this->legend = "Edit this member";
		if($this->person === null) $this->person = new Member(array("id"=>0));
		$this->title = $this->person->name;
		if(AuthController::is_authed() && (bool)AuthController::$current_user->is_owner){
			$view = "member/edit";
			$this->legend = $this->person->id === 0 ? "Add a new member" : "Edit this member";			
		}else{
			$this->set_not_found();
		}		
		$this->output = View::render($view, $this);
		return View::render_layout("default", $this);
	}
	public function delete(Member $member){
		if(!AuthController::is_authed() || !(bool)AuthController::$current_user->is_owner){
			$this->set_unauthed();
			return;
		}
		if($member->id == AuthController::$current_user->id && AuthController::$current_user->is_owner){
			return "You don't want to delet the owner";
		}
		$this->person = Member::find_by_id((int)$member->id);
		if($this->person !== null){
			delete_object::execute($this->person);
		}
		$this->set_redirect_to("members");
		$this->output = View::render("member/index", $this);
		return View::render_layout("default", $this);
	}
	public function put(Member $member){
		if(!AuthController::is_authed() || !(bool)AuthController::$current_user->is_owner){
			$this->set_unauthed();
			return;
		}
		$this->errors = array();
		$member->signin = trim($member->signin);
		$member->email = trim($member->email);
		if(strlen($member->signin) === 0) $this->errors["signin"] = "Signin name is required. Please enter one.";
		if(strlen($member->email) === 0) $this->errors["email"] = "Email is required. Please enter one.";
		$this->errors = Member::can_save($member);
		$this->person = Member::find_by_id($member->id);
		if($this->person !== null){
			$this->person->name = $member->name;
			$this->person->display_name = $member->display_name;
			$this->person->signin = (array_key_exists("signin", $this->errors) ? $this->person->signin : $member->signin);
			$this->person->email = filter_var($member->email, FILTER_SANITIZE_EMAIL);
			$this->person->password = (strlen($member->password) > 0 ? String::encrypt($member->password) : $this->person->password);
			$this->person->in_directory = $member->in_directory === null ? false : $member->in_directory;
			$this->person->is_owner = $this->person->id == 1 ? true : false;
			$this->person = Member::save($this->person);
			App::set_user_message("{$this->person->name}'s info was saved.");
		}
		if(count($this->errors) > 0) App::set_user_message(View::render("error/list", $this));
		$this->set_redirect_to("members");
		$this->output = View::render("member/show", $this);
		return View::render_layout("default", $this);
	}	
}