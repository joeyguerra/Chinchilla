<?php

class Resource_DFRN{
	public function __construct(){}
	public function __destruct(){}
	public function canHandle($class_name, $http_method){
		return in_array($class_name, array('Dfrn_requestResource', 'Dfrn_pollResource', 'Dfrn_confirmResource', 'Dfrn_notifyResource'));
	}
	public function execute($class_name, $http_method = 'get', $path_info = null){
		$resource = class_exists($class_name) ? new $class_name() : null;
		if($resource !== null){
			return Resource::sendMessage($resource, $http_method, null);
		}
		return null;
	}
}

class Dfrn_requestResource extends AppResource{
	public function __construct(){}
	public function __destruct(){}
	public function get($dfrn_url){
		return pack("H*",$dfrn_url);
	}
	
	public function post(){
	
	}
}

class Dfrn_pollResource{
	public function __construct(){}
	public function __destruct(){}
	public function get($path_info){
		return $path_info;
	}
	
	public function post(){
	
	}
}

class Dfrn_confirmResource{
	public function __construct(){}
	public function __destruct(){}
	
	public function get($path_info){
		return $path_info;
	}
	
	public function post(){
	
	}
}

class Dfrn_notifyResource{
	public function __construct(){}
	public function __destruct(){}

	public function get($path_info){
		return $path_info;
	}
	
	public function post(){
	
	}
}