<?php
class_exists('AppResource') || require('AppResource.php');
class ExampleResource extends AppResource{
	public function __construct(){
		parent::__construct();
	}
	public function __destruct(){
		parent::__destruct();
	}
	
	public function get_example(){
		$this->title = "An example using Chinchilla, A RESTful framework in PHP";
		$this->output = $this->renderView('example/index', null);
		return $this->renderView('layouts/default', null);
	}
		
}

?>