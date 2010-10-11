<?php
	class_exists('Resource') || require('lib/Resource.php');
	class_exists('FrontController') || require('lib/FrontController.php');
	class_exists('NotificationCenter') || require('lib/NotificationCenter.php');
	class_exists('PluginController') || require('lib/PluginController.php');
	class AppResource extends Resource{
		public function __construct($attributes = null){
			parent::__construct($attributes);
			$resource_name = strtolower(str_replace('Resource', '', get_class($this)));				
			$this->resource_css = $resource_name . '.css';
			$this->resource_js = $resource_name . '.js';
			$root = FrontController::getRootPath(null);
			if(file_exists($root . '/' . FrontController::getThemePath() . '/js/' . $this->resource_js)){				
				$this->resource_js = FrontController::urlFor('themes') . 'js/' . $this->resource_js;
				$this->resource_js = $this->to_script_tag('text/javascript', $this->resource_js);
			}elseif(file_exists($root . '/js/' . $this->resource_js)){
				$this->resource_js = FrontController::urlFor('js') . $this->resource_js;
				$this->resource_js = $this->to_script_tag('text/javascript', $this->resource_js);
			}else{
				$this->resource_js = null;
			}
			if(file_exists(FrontController::getThemePath() . '/css/' . $this->resource_css)){
				$this->resource_css = FrontController::urlFor('themes') . 'css/' . $this->resource_css;
				$this->resource_css = $this->to_link_tag('stylesheet', 'text/css', $this->resource_css, 'screen,projection');
			}elseif(file_exists($root . 'css/' . $this->resource_css)){
				$this->resource_css = FrontController::urlFor('css') . $this->resource_css;
				$this->resource_css = $this->to_link_tag('stylesheet', 'text/css', $this->resource_css, 'screen,projection');
			}else{
				$this->resource_css = null;
			}

			$theme_path = FrontController::getRootPath('/' . FrontController::getThemePath() . '/ThemeController.php');
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
		
		public function willReturnValueForKey($key, $obj, $val){
			return $val;
		}
		public function to_link_tag($rel, $type, $url, $media){
			return sprintf('<link rel="%s" type="%s" href="%s" media="%s" />', $rel, $type, $url, $media);
		}
		public function to_script_tag($type, $url){
			return sprintf('<script type="%s" src="%s"></script>', $type, $url);
		}

		public function didFinishLoading(){
			parent::didFinishLoading();
		}
		public function hasRenderedOutput($layout, $output){
			if(class_exists('AppConfiguration')){
				$output = $this->filterHeader($output);
				$output = $this->filterFooter($output);
			}
			return $output;
		}		
		protected function filterText($text){
			$post_filters = $this->getPlugins('filters', 'PostFilter');
			foreach($post_filters as $filter){
				$text = $filter->execute($text);
			}
			return $text;
		}
		private function filterHeader($output){
			$filters = PluginController::getPlugins('filters', 'HeaderFilter');
			foreach($filters as $filter){
				$output = $filter->execute($output);
			}
			return $output;
		}
		private function filterFooter($output){
			$filters = $this->getPlugins('filters', 'FooterFilter');
			foreach($filters as $filter){
				$output = $filter->execute($output);
			}
			if(count(console::$messages) > 0){
				$output = str_replace('</body>', '<pre id="__console">' . implode('', console::$messages) . '</pre></body>', $output);
			}
			return $output;
		}
		
		protected function getPlugins($folder_name, $name){
			$files = $this->getFiles($folder_name, $name);
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
		private function getFiles($folder_name, $name){
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
		public function getTitleFromOutput($output){
			$matches = array();
			preg_match( '/\<h1\>.*\<\/h1\>/' , $output, $matches);
			if(count($matches) > 0){
				return String::stripHtmlTags($matches[0]);
			}else{
				return null;
			}
		}
		
		public function getPreference($name){
			if($this->settings != null){
				foreach($this->settings as $setting){
					if($name == $setting->name){
						return $setting;
					}
				}				
			}
			return null;
		}
		public static function randomIndexWithWeights($weights) {
		    $r = mt_rand(1,1000);
		    $offset = 0;
		    foreach ($weights as $k => $w) {
		        $offset += $w*1000;
		        if ($r <= $offset) {
		            return $k;
		        }
		    }
		}
	}
?>