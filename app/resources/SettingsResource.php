<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("Setting") || require("models/Setting.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class SettingsResource extends AppResource{
	public function __construct(){
		parent::__construct();
		if(!AuthController::is_authed()){
			$this->set_unauthed();
		}
	}
	public $settings;
	public $legend;
	
	public function get(){
		$this->settings = Setting::find_all(0, 5, AuthController::$current_user->id);
		$view = "setting/index";
		$this->output = View::render($view, $this);
		return View::render_layout('default', $this);
	}
	public function post(Setting $setting){
		$this->setting = $setting;
		$message = Setting::can_save($setting, AuthController::$current_user->id);
		if(count($message) === 0){
			$setting->owner_id = AuthController::$current_user->id;
			$this->setting = Setting::save($setting);			
			$this->set_redirect_to(AuthController::$current_user->signin . '/settings');
		}else{
			App::set_user_message(implode(", ", $message));
			$this->set_redirect_to(AuthController::$current_user->signin . '/setting');
		}
		$this->output = View::render('setting/show', $this);
		return View::render_layout('default', $this);
		
	}
}