<?php
class_exists('AppResource') || require('AppResource.php');
class ExampleResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public $message;
	public function get(){
		$this->title = "An example using Chinchilla, A RESTful framework in PHP";
		$this->message = $this->render('example/message', null, 'html');
		$this->output = $this->render('example/index');
		return $this->render_layout('default');
	}
		
}

?>