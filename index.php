<?php
date_default_timezone_set("US/Central");
ini_set("auto_detect_line_endings",true);
set_error_handler("error_handler", E_ALL);
set_exception_handler("exception_handler");

function error_handler($code, $message, $file, $line, $context){
	$value = "$message ($code): line = $line - $file<br /><br />";
	$value .= "<textarea>" . json_encode($context) . "</textarea>";
	$value .= "<br /><br />";
	view::set_user_message($value);
}
function exception_handler($e){
	view::set_user_message($e->getMessage());
}
function storage_provider(){
	return "data.sqlite";
}

require("chinchilla.php");
require("storage.php");
class member{
	static function find_one($where, $args){
		return null;
	}
}
class member_url_parser{
	function parsing_url($publisher, $path){
		$parts = explode("/", $path);
		$name = $parts[0];
		$member = member::find_one("signin=:name", array("name"=>$name));
		if($member !== null || $name === "joey"){
			notification_center::publish("member_site_requested", $this, $member);
			array_shift($parts);
			$path = implode("/", $parts);
		}
		return $path;
	}
}
class column{
	function __construct($obj = null){
		if($obj !== null){
			$this->cid = property_exists($obj, "cid") ? $obj->cid : null;
			$this->pk = property_exists($obj, "pk") ? (int)$obj->pk === 1 : false;
			$this->name = property_exists($obj, "name") ? $obj->name : null;
			$this->type = property_exists($obj, "type") ? $obj->type : null;
			$this->dflt_value = property_exists($obj, "dflt_value") ? $obj->dflt_value : null;
			$this->notnull = property_exists($obj, "notnull") ? (int)$obj->notnull === 1 : true;
		}
	}
	public $cid;
	public $pk;
	public $name;
	public $type;
	public $dlft_value;
	public $notnull;
}

class repo{
	function __construct(){}
	private $connection_string;
	function should_save_post($publisher, $post){
		$db = new storage(array("table_name"=>"posts", "primary_key_field"=>"id", "connection_string"=>storage_provider()));
		$db->save(array($post));
	}
	function should_save_story($publisher, $story){
		$db = new storage(array("table_name"=>"stories", "primary_key_field"=>"id", "connection_string"=>storage_provider()));
		$db->save(array($story));
	}
}
notification_center::subscribe("begin_request", null, new plugin_controller());
filter_center::subscribe("before_rendering_view", null, new plugin_controller());
filter_center::subscribe("before_rendering_view", null, new theme_controller());
notification_center::subscribe("should_save_post", null, new repo());
notification_center::subscribe("should_save_story", null, new repo());
filter_center::subscribe("end_request", null, new output_compressor());
filter_center::subscribe("parsing_url", null, new member_url_parser());
filter_center::subscribe("setting_parameter_from_request", null, new magic_quotes_remover());
filter_center::subscribe("setting_parameter_from_request", null, new object_populator_from_request());
$request_controller = new front_controller();
echo $request_controller->execute(new request($_SERVER, $_REQUEST, $_FILES, $_POST, $_GET));