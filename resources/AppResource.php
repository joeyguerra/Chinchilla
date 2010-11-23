<?php
	class_exists('Resource') || require('lib/Resource.php');
	class_exists('NotificationCenter') || require('lib/NotificationCenter.php');
	class_exists('HttpStatus') || require('lib/HttpStatus.php');
	class_exists('App') || require('lib/App.php');
	class AppResource extends Resource{
		public function __construct($attributes = null){
			parent::__construct($attributes);
			$resource_name = $this->name;				
			$this->resource_css = $resource_name . '.css';
			$this->resource_js = $resource_name . '.js';			
			if(file_exists(App::get_theme_path('/js/' . $this->resource_js))){
				$this->resource_js = App::url_for('themes') . 'js/' . $this->resource_js;
				$this->resource_js = $this->to_script_tag('text/javascript', $this->resource_js);
			}elseif(file_exists(App::get_root_path('/js/' . $this->resource_js))){
				$this->resource_js = App::url_for('js') . $this->resource_js;
				$this->resource_js = $this->to_script_tag('text/javascript', $this->resource_js);
			}else{
				$this->resource_js = null;
			}
			if(file_exists(App::get_theme_path() . '/css/' . $this->resource_css)){
				$this->resource_css = App::url_for('themes') . 'css/' . $this->resource_css;
				$this->resource_css = $this->to_link_tag('stylesheet', 'text/css', $this->resource_css, 'screen,projection');
			}elseif(file_exists(App::get_root_path('css/' . $this->resource_css))){
				$this->resource_css = App::url_for('css') . $this->resource_css;
				$this->resource_css = $this->to_link_tag('stylesheet', 'text/css', $this->resource_css, 'screen,projection');
			}else{
				$this->resource_css = null;
			}

			$theme_path = App::get_theme_path('/ThemeController.php');
			if(file_exists($theme_path)){
				class_exists('ThemeController') || require($theme_path);
				$this->theme = new ThemeController($this);
			}			
		}
		
		public function __destruct(){
			parent::__destruct();
		}
		public $member;
		public $show_notes;
		public $notes;
		public $theme;
		public $resource_css;
		public $resource_js;
		protected $settings;
		protected $config;
		public $q;
		public $current_user;
		
		public function user_is_unauthorized($resource_name){
			$this->status = new HttpStatus(401);
		}
		public function to_link_tag($rel, $type, $url, $media){
			return sprintf('<link rel="%s" type="%s" href="%s" media="%s" />', $rel, $type, $url, $media);
		}
		public function to_script_tag($type, $url){
			return sprintf('<script type="%s" src="%s"></script>', $type, $url);
		}

		public function did_finish_loading(){
			parent::did_finish_loading();
		}
		public function output_has_rendered($layout, $output){
			if(class_exists('AppConfiguration')){
				$output = $this->filter_header($output);
				$output = $this->filter_footer($output);
			}
			return $output;
		}		
		private function filter_header($output){
			$filters = PluginController::get_plugins('filters', 'HeaderFilter');
			foreach($filters as $filter){
				$output = $filter->execute($output);
			}
			return $output;
		}
		private function filter_footer($output){
			$filters = $this->get_plugins('filters', 'FooterFilter');
			foreach($filters as $filter){
				$output = $filter->execute($output);
			}
			if(count(console::$messages) > 0){
				$output = str_replace('</body>', '<pre id="__console">' . implode('', console::$messages) . '</pre></body>', $output);
			}
			return $output;
		}
		
		protected function get_plugins($folder_name, $name){
			$files = $this->get_files($folder_name, $name);
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
		private function get_files($folder_name, $name){
			$root = FrontController::getRootPath('/' . $folder_name);
			$folders = $this->getFolders($root);
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
		private function getFolders($path){
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
	}
?>