<?php
	class_exists('Resource') || require('lib/Resource.php');
	class_exists('FrontController') || require('lib/FrontController.php');
	class_exists('NotificationCenter') || require('lib/NotificationCenter.php');
	class AppResource extends Resource{
		public function __construct($attributes = null){
			parent::__construct($attributes);
			if(isset($_SERVER['PHP_AUTH_USER'])){
				
			}
			if($this->original_resource_name != null){
				$resource_name = $this->original_resource_name;
			}else{
				$resource_name = strtolower(str_replace('Resource', '', get_class($this)));				
			}
			$this->resource_css = 'css/'.$resource_name . '.css';
			$this->resource_js = 'js/'. $resource_name . '.js';
			$root = str_replace('resources', '', dirname(__FILE__));
			if(file_exists($root . FrontController::themePath() . '/' . $this->resource_js)){
				$this->resource_js = FrontController::urlFor('themes') . $this->resource_js;
				$this->resource_js = $this->to_script_tag('text/javascript', $this->resource_js);
			}elseif(file_exists($root . $this->resource_js)){
				$this->resource_js = FrontController::urlFor(null) . $this->resource_js;
				$this->resource_js = $this->to_script_tag('text/javascript', $this->resource_js);
			}else{
				$this->resource_js = null;
			}

			if(file_exists(FrontController::themePath() . '/' . $this->resource_css)){
				$this->resource_css = FrontController::urlFor('themes') . $this->resource_css;
				$this->resource_css = $this->to_link_tag('stylesheet', 'text/css', $this->resource_css, 'screen,projection');
			}elseif(file_exists($root . $this->resource_css)){
				$this->resource_css = FrontController::urlFor(null) . $this->resource_css;
				$this->resource_css = $this->to_link_tag('stylesheet', 'text/css', $this->resource_css, 'screen,projection');
			}else{
				$this->resource_css = null;
			}
		}
		
		public function __destruct(){
			parent::__destruct();
		}
		public $resource_css;
		public $resource_js;
		protected $settings;
		protected $config;
		protected static $error_html;
		public function willReturnValueForKey($key, $obj, $val){
			switch($key){
				case('custom_url'):
					if(!FrontController::can_rewrite_url() && strpos($val, FrontController::index_script()) === false){
						$val = FrontController::index_script() . $val;
					}
					break;
			}
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
		public function exceptionHasOccured($sender, Exception $e){
			if($e->getCode() == 401){
				FrontController::redirectTo('login');
			}elseif($e->getCode() == 404){
				$this->output = $this->renderView('error/404', array('message'=>$e->getMessage() . '(' . $sender->context['r'] . ')'));
				echo $this->renderView('layouts/default');
			}elseif(get_class($e) == 'DSException'){
				FrontController::redirectTo('install', null);
			}else{
				Resource::setUserMessage($e->getMessage());
				echo $this->renderView('layouts/default');
			}			
		}
		public function unauthorizedRequestHasOccurred(){
			FrontController::redirectTo('login');
		}
		public function errorDidHappen($sender, $error_html){
			self::$error_html = $error_html;
		}
		public function hasRenderedOutput($layout, $output){
			if(self::$error_html != null){
				$output .= self::$error_html;
			}
			//error_log('request uri ' . $layout . ' ' . $_SERVER['REQUEST_URI']);
			$output = $this->get_plugin_footers($output);
			return $output;
		}
		private function traverse($path){
			$root = $path;
			$folder = dir($root);
			$files = array();
			$recursion_limit = 10;
			$recursion_counter = 0;
			if($folder != null){
				while (false !== ($entry = $folder->read()) && $recursion_counter <= $recursion_limit){
					if(strpos($entry, '.') !== 0){
						$recursion_counter++;
						$file_name = $folder->path .'/'. $entry;
						if(is_dir($file_name)){
							$files[] = $this->traverse($file_name);						
						}else{						
							return $file_name;
						}
					}
				}
				$folder->close();
			}
			return $files;
		}
		
		private function get_plugin_folders($path){
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
		
		private function get_plugin_footers($output){
			$root = str_replace('resources/AppResource.php', 'plugins/', __FILE__);
			$folders = $this->get_plugin_folders($root);
			$footer_output = null;
			foreach($folders as $folder){
				if(file_exists($folder . '/footer.php')){
					ob_start();
					require($folder . '/footer.php');
					$footer_output .= ob_get_contents();
					ob_end_clean();
				}
			}
			if($footer_output != null){
				$output = String::replace('/<\/body>/i', $footer_output . '</body>', $output);
			}
			return $output;
		}
		public function getTitleFromOutput($output){
			$matches = array();
			preg_match ( '/\<h1\>.*\<\/h1\>/' , $output, $matches);
			return String::stripHtmlTags($matches[0]);
		}
		public function resourceOrMethodNotFoundDidOccur($sender, $args){
			$this->file_type = $args['file_type'];
			$method = array_key_exists('_method', $args['server']) ? $args['server']['_method'] : $args['server']['REQUEST_METHOD'];
			parse_str($args['query_string']);
			$page_name = preg_replace('/\/$/', '', $r);
			$view = $page_name . '_' . $this->file_type . '.php';
			$this->original_resource_name = $sender->original_resource_name;
			if(file_exists(FrontController::themePath() . '/views/index/' . $view)){
				$this->output = $this->renderView('index/' . $page_name);
			}elseif(file_exists('index/' . $view)){
				$this->output = $this->renderView('index/' . $page_name);
			}else{
				$this->output = $this->renderView('error/404', array('message'=>$page_name . $method));
			}
			if($this->title === null){
				$this->title = $this->getTitleFromOutput($this->output);				
			}
			echo $this->renderView('layouts/default');
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