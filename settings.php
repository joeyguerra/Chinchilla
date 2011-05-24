<?php
class Settings{
	public static $app_path;
	public static $theme;
	public static $site_header;
	public static $max_filesize;
	public static function virtual_path($file){
		return dirname(__FILE__) . "/" . $file;
	}
	public static function path_for($file){
		return self::$app_path . "/" . $file;
	}
	public static function title($owner_id){
		return "Chinchille, a RESTful PHP Framework";
	}
}
Settings::$app_path = dirname(__FILE__) . "/app";
Settings::$theme = "default";
Settings::$site_header = "Chinchilla, a RESTful PHP Framework";
$logger = new Logger();
//NotificationCenter::add($logger, "request_was_made");
