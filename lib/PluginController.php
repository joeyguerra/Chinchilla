<?php

class PluginController{
	public function __construct(){}
	public function __destruct(){}
	public static function getFiles($folder_name, $name){
		$root = FrontController::getRootPath('/' . $folder_name);
		$folders = self::getFolders($root);
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
	public static function getFolders($path){
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
	public static function getPlugins($folder_name, $name){
		$files = self::getFiles($folder_name, $name);
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