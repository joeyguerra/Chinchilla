<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("Setting") || require("models/Setting.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class SettingResource extends AppResource{
	public function __construct(){
		parent::__construct();
		if(!AuthController::is_authed()){
			$this->set_unauthed();
			return;
		}
	}
	public $setting;
	public $legend;
	public $errors;
	public function get(Setting $setting = null){
		$setting = $setting === null ? new Setting() : $setting;
		$this->setting = Setting::find_by_id((int)$setting->id);
		$view = "setting/show";
		$this->legend = "Edit this setting";
		if($this->setting === null) $this->setting = new Setting(array("id"=>0));
		$this->title = $this->setting->key;
		if(AuthController::is_authed() && (bool)AuthController::$current_user->is_owner){
			$view = "setting/edit";
			$this->legend = $this->setting->id === 0 ? "Add a new setting" : "Edit this setting";			
		}else{
			$this->set_not_found();
		}		
		$this->output = View::render($view, $this);
		return View::render_layout("default", $this);
	}
	public function delete(Setting $setting){
		$this->setting = Setting::find_by_id((int)$setting->id);
		if($this->setting !== null){
			delete_object::execute($this->setting);
		}
		$this->set_redirect_to("settings");
		$this->output = View::render("setting/index", $this);
		return View::render_layout("default", $this);
	}
	public function post($state = "show", $key){
		$this->legend = "Create a new setting";
		$key = preg_replace("/[^a-zA-Z0-9-]?/", "", $key);
		$this->title = "Create a new setting called $key";
		$this->state = $state == "edit" ? "edit" : "show";
		$this->setting = Setting::find($key, AuthController::$current_user->id);
		if($this->setting === null){
			$this->setting = new Setting(array("key"=>$key));
		}
		$this->output = View::render("setting/{$this->state}", $this);
		return View::render_layout("default", $this);
	}
	
	public function put(Setting $setting){
		$this->errors = array();
		$this->setting = Setting::find($setting->key, AuthController::$current_user->id);
		if($this->setting !== null){
			$this->setting->value = $setting->value;
			$this->setting->owner_id = AuthController::$current_user->id;
			Setting::can_save($this->setting);
			Setting::save($this->setting);
		}
		if(count($this->errors) > 0) App::set_user_message(View::render("error/list", $this));
		$this->set_redirect_to("settings");
		$this->output = View::render("setting/show", $this);
		return View::render_layout("default", $this);
	}	
}