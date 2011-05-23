<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class TablesResource extends AppResource{
	public function __construct(){
		parent::__construct();
		if(!AuthController::is_authed()){
			$this->set_unauthed("Please signin.");
		}
	}
	public $table_name;
	
	public function post($sql){
		$sql = explode(";", $sql);
		$query = new Query(null);
		$db = Repo::get_provider();
		$view = "db/index";
		$result = array();
		foreach($sql as $s){
			$result[] = $query->execute($db, $s);
		}
		App::set_user_message(json_encode($result));
		$this->set_redirect_to("db");
		$this->output = View::render($view, $this);
		return View::render_layout("default", $this);
	}
}