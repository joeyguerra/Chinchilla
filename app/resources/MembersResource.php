<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("Member") || require("models/Member.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class_exists("IndexResource") || require("IndexResource.php");
class MembersResource extends AppResource{
	public function __construct(){
		parent::__construct();
	}
	public $members;
	public $legend;
	
	public function get(){
		if(count($this->request->path) > 0 && $this->request->path[0] !== null){
			$member_name = $this->request->path[0];
			if(count($this->request->path) === 1){
				$resource = new IndexResource();
				return $resource->execute($this->request);
			}
			if($this->request->path[1] === "members"){
				$this->set_not_found();
				return;
			}
			$resource_name = ucwords($this->request->path[1]) . "Resource";
			if(!class_exists($resource_name)) require("resources/$resource_name.php");
			$resource = new $resource_name();
			$this->output = $resource->execute($this->request);
			$this->status = $resource->status;
			return $this->output;
		}
		if(AuthController::is_authed() && AuthController::$current_user->is_owner){
			$this->members = Member::find_all(0, 5);
		}else{
			$this->members = Member::find_in_directory(0, 5);
		}

		$view = "member/index";
		$this->output = View::render($view, $this);
		return View::render_layout('default', $this);
	}
	public function post(Member $member){
		if(!AuthController::is_authed() && (bool)AuthController::$current_user->is_owner){
			$this->set_unauthed();
			return;
		}
		$this->member = $member;
		$message = Member::can_save($member, AuthController::$current_user->id);
		if(count($message) === 0){
			$member->owner_id = AuthController::$current_user->id;
			$member->password = String::encrypt($member->password);
			$this->member = Member::save($member);			
			$this->set_redirect_to(AuthController::$current_user->signin . '/members');
		}else{
			App::set_user_message(implode(", ", $message));
			$this->set_redirect_to(AuthController::$current_user->signin . '/member');
		}
		$this->output = View::render('member/show', $this);
		return View::render_layout('default', $this);
		
	}
}