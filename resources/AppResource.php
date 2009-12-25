<?php
	class_exists('Resource') || require('lib/Resource.php');
	class_exists('FrontController') || require('lib/FrontController.php');
	class_exists('NotificationCenter') || require('lib/NotificationCenter.php');
	class AppResource extends Resource{
		public function __construct(){
			parent::__construct();
			
			$this->resource_name = strtolower(str_replace('Resource', '', get_class($this)));
			$this->resource_css = 'css/'.$this->resource_name . '.css';
			$this->resource_js = 'js/'. $this->resource_name . '.js';
			$root = str_replace('resources', '', dirname(__FILE__));
			if(file_exists($root . FrontController::themePath() . $this->resource_js)){
				$this->resource_js = FrontController::urlFor('themes') . $this->resource_js;
				$this->resource_js = sprintf('<script type="text/javascript" src="%s"></script>', $this->resource_js);
			}elseif(file_exists($root . $this->resource_js)){
				NotificationCenter::getInstance()->postNotificationName('LogEventHasOccurred', 'file exists', $this);
				$this->resource_js = FrontController::urlFor(null) . $this->resource_js;
				$this->resource_js = sprintf('<script type="text/javascript" src="%s"></script>', $this->resource_js);
			}else{
				$this->resource_js = null;
			}
			NotificationCenter::getInstance()->postNotificationName('LogEventHasOccurred', 'resource js = ' . $this->resource_js, $this);

			if(file_exists(FrontController::themePath() . '/' . $this->resource_css)){
				$this->resource_css = FrontController::urlFor('themes') . $this->resource_css;
				$this->resource_css = sprintf('<link rel="stylesheet" type="text/css" href="%s" media="all" />', $this->resource_css);
			}else{
				$this->resource_css = null;
			}
			
			if(class_exists('AppConfiguration')){
				$this->config = new AppConfiguration();
			}
			
		}
		
		public function __destruct(){
			parent::__destruct();
		}
		public $resource_name;
		public $resource_css;
		public $resource_js;
		protected $config;
		protected static $error_html;
		
		public function didFinishLoading(){
			parent::didFinishLoading();
		}
		public function exceptionHasOccured($sender, Exception $e){
			if($e->getCode() == 401){
				FrontController::redirectTo(null);
			}elseif($e->getCode() == 404){
				$this->output = $this->renderView('error/404');
				echo $this->renderView('layouts/default');
			}else{
				$this->output = $this->renderView('error/friendly_message', array('e'=>$e));
				echo $this->renderView('layouts/default');
			}
			
		}
		public function unauthorizedRequestHasOccurred($sender, $args){
		    header('WWW-Authenticate: Basic realm="My Realm"');
		    header('HTTP/1.0 401 Unauthorized');
		    echo 'Unauthorized access';
		    exit;
		}
		public function errorDidHappen($sender, $error_html){
			self::$error_html = $error_html;
		}
		public function hasRenderedOutput($output){
			if(self::$error_html != null){
				$output .= self::$error_html;
			}
			return $output;
		}

		public function getTitleFromOutput($output){
			$matches = array();
			if(preg_match ( '/\<h1\>.*\<\/h1\>/' , $output, &$matches)){
				return String::stripHtmlTags($matches[0]);
			}else{
				return 'New Document';
			}
		}
		public function resourceOrMethodNotFoundDidOccur($sender, $args){
			$this->file_type = $args['file_type'];
			// the url really looks like index.php?r=resource.filetype.
			parse_str($args['query_string']);
			$page_name = preg_replace('/\//', '', $r);
			$parts = explode('.', $r);
			if(count($parts) > 1){
				$this->file_type = $parts[1];
			}
			$page_name = preg_replace('/\/$/', '', $parts[0]);
			$view = $page_name . '_' . $this->file_type . '.php';
			if(file_exists(FrontController::themePath() . '/views/index/' . $view)){
				$this->resource_name = $page_name;
				$this->output = $this->renderView('index/' . $page_name);
				$this->title = $this->getTitleFromOutput($this->output);
			}elseif(file_exists('views/index/' . $view)){
				$this->resource_name = $page_name;
				$this->output = $this->renderView('index/' . $page_name);
				$this->title = $this->getTitleFromOutput($this->output);
			}else{
				$this->file_type = 'html';
				$this->output = $this->renderView('error/404');
			}
			switch($this->file_type){
				case('js'):
					FrontController::sendJavascriptHeaders();
					break;
				case('jsonp'):
					FrontController::sendJsonpHeaders();
					break;
				case('json'):
					FrontController::sendJsonHeaders();
					break;
				case('xml'):
					FrontController::sendXmlHeaders(null);
					break;
				case('rss'):
					FrontController::sendXmlHeaders('rss');
					break;
				case('atom'):
					FrontController::sendXmlHeaders('atom');
					break;
				default:
					break;
			}
			echo $this->renderView('layouts/default');			
		}
	}
?>