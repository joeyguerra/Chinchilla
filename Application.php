<?php
class_exists('AppResource') || require('resources/AppResource.php');
class Application{
	public function __construct(){}
	public function __destruct(){}
	public function exceptionHasOccured($sender, $args){
		$e = $args['exception'];
		$file_type = $args['file_type'];
		$resource = new AppResource(array('file_type'=>$file_type));
		if($e->getCode() == 401){
			FrontController::send401Headers('Please login', 'chinchilla');
		}elseif($e->getCode() == 404){
			FrontController::send404Headers('Resource not found');
		}else{
			Resource::setUserMessage('Exception has occured: ' . $e->getMessage());
			return $resource->renderView('layouts/default');
		}
	}
	public function unauthorizedRequestHasOccurred($sender, $args){
		FrontController::send401Headers('Please login', 'chinchilla');
	}
	public function willExecute($path_info){
		return $path_info;
	}
	public function errorDidHappen($message){
		console::log($message);
	}
	
	public function resourceOrMethodNotFoundDidOccur($sender, $args){}
}

class console{
	public static $messages = array();
	public static function log($obj){
		self::$messages[] = $obj;
	}
	public function __destruct(){
		self::$messages = array();
	}
	
}