<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class DbResource extends AppResource{
	public function __construct(){
		parent::__construct();
		if(!AuthController::is_authed()){
			$this->set_unauthed("Please signin.");
		}
	}
	public $tables;
	public function get(){
		$output = array();
		$db = new SQLite3(Settings::$storage_provider->path, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
		$table_query = <<<eos
SELECT name FROM sqlite_master 
WHERE type IN ('table','view') AND name NOT LIKE 'sqlite_%'
UNION ALL 
SELECT name FROM sqlite_temp_master 
WHERE type IN ('table','view') 
ORDER BY 1
eos;
		$result = $db->query($table_query);
		$this->tables = array();
		while($table = $result->fetchArray(SQLITE3_ASSOC)){
			$this->tables[] = (object)$table;
		}		
		$view = "db/index";
		$this->output = View::render($view, $this);
		return View::render_layout("default", $this);
	}
}