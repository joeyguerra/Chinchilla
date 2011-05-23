<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class_exists("Member") || require("models/Member.php");
class ProfileResource extends AppResource{
	public function __construct(){
		parent::__construct();
	}
	public $owner;
	public $page;
	public function get(){
		$this->page = Post::find_page_by_name("profile", AppResource::$member->id);
		$this->owner = AppResource::$member;
		$this->title = $this->page !== null ? $this->page->title : self::$member->name . "'s profile";
		$this->output = View::render("profile/index", $this);
		return View::render_layout("default", $this);
	}
	public function post($state = null){
		if($state === "edit"){
			if(!AuthController::is_authed()){
				$this->set_unauthed();
				return;
			}
			$view = "profile/edit";
		}else{
			$view = "profile/index";
		}
		$this->owner = AuthController::$current_user;
		$this->title = self::$member->name . "'s profile";
		$this->output = View::render($view, $this);
		return View::render_layout("default", $this);
	}
	public function put(Member $owner){
		if(!AuthController::is_authed()){
			$this->set_unauthed();
			return;
		}
		$this->errors = array();
		$owner->email = trim($owner->email);
		$this->errors = Member::can_save($owner);
		$this->owner = Member::find_by_id($owner->id);
		if($this->owner !== null){
			$this->owner->name = $owner->name;
			$this->owner->display_name = $owner->display_name;
			$this->owner->signin = (array_key_exists("signin", $this->errors) ? $this->owner->signin : $owner->signin);
			$this->owner->email = filter_var($owner->email, FILTER_SANITIZE_EMAIL);
			$this->owner->password = (strlen($owner->password) > 0 ? String::encrypt($owner->password) : $this->owner->password);
			$this->owner->in_directory = $owner->in_directory === null ? false : $owner->in_directory;
			$this->owner->is_owner = $this->owner->id == 1 ? true : false;
			$this->owner->photo_url = $owner->photo_url;
			$this->owner = Member::save($this->owner, null);
			App::set_user_message("{$this->owner->name}'s info was saved.");
		}
		if(count($this->errors) > 0) App::set_user_message(View::render("error/list", $this));
		$this->owner = AuthController::$current_user;
		$view = "profile/index";
		$this->title = self::$member->name . "'s profile";
		$this->output = View::render($view, $this);
		return View::render_layout("default", $this);
	}
}