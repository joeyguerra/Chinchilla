<?php
class_exists('AppResource') || require('AppResource.php');
class IndexResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	
	public function get(){
		if(count($this->url_parts) > 0){
			return $this->test();
		}
		
		$this->title = "A RESTful framework in PHP";
		$this->output = $this->renderView('index/index', null);
		return $this->renderView('layouts/default', null);			
		
	}
	
	public function test(){
		$this->title = "Chinchilla Test Method";
		$this->output = 'Just a test method to test multipart url routing.';
		return $this->renderView('layouts/default', null);
	}
	
}

?>