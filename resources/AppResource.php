<?php
	class_exists('Resource') || require('lib/Resource.php');
	class_exists('FrontController') || require('lib/FrontController.php');
	class AppResource extends Resource{
		public function __construct(){
			parent::__construct();
			
			if(isset($_SERVER['PHP_AUTH_USER'])){
				
			}
			$resource_name = strtolower(str_replace('Resource', '', get_class($this)));
			$this->resource_css = 'css/'.$resource_name . '.css';
			$this->resource_js = 'js/'. $resource_name . '.js';
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
		}
		
		public function __destruct(){
			parent::__destruct();
		}
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
			}
			
			if($e->getCode() == 404){
				$this->output = $this->renderView('error/404');
				echo $this->renderView('layouts/default');
			}
			
		}
		public function unauthorizedRequestHasOccurred(){
			$this->redirectTo(null);
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
		public function resourceOrMethodNotFoundDidOccur($sender, $args){
			$this->file_type = $args['file_type'];
			parse_str($args['query_string']);
			$page_name = preg_replace('/\//', '', $r);
			$this->output = $this->renderView('error/404');
			echo $this->renderView('layouts/default');
		}
	}
?>