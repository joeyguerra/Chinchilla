<?php

class PluginController{
	public function __construct(){}
	public function __destruct(){}
	public static function get_files($folder_name, $name){
		$root = App::get_root_path($folder_name);
		$folders = self::get_folders($root);
		$plugin_paths = array();
		foreach($folders as $folder){
			$dir = dir($folder);			
			while(($entry = $dir->read()) !== false){
				if(strpos($entry, '.') !== 0){
					$file_name = $dir->path . '/' . $entry;
					if(!is_dir($file_name) && stripos($entry, $name . '_') !== false){
						$plugin_paths[] = $file_name;
					}
				}
			}
		}
		return $plugin_paths;
	}
	public static function get_folders($path){
		$folders = array();
		$folder = dir($path);
		if($folder !== false){
			while(($entry = $folder->read()) !== false){			
				if(strpos($entry, '.') !== 0){
					$file_name = $folder->path .'/'. $entry;					
					if(is_dir($file_name)){
						$folders[] = $file_name;
					}
				}
			}
		}
		return $folders;
	}
	public static function get_plugins($folder_name, $name){
		$files = self::get_files($folder_name, $name);
		$plugins = array();
		foreach($files as $file){
			$parts = explode('/', $file);
			$class_name = array_pop($parts);
			$class_name = str_replace('.php', '', $class_name);
			class_exists($class_name) || require($file);
			$plugins[] = new $class_name();
		}
		return $plugins;
	}
}