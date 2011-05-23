<?php
class_exists("AppResource") || require("AppResource.php");
class ExampleResource extends AppResource{
	public function __construct(){
		parent::__construct();
	}
	public $message;
	public function get(){
		$this->title = "An example using Chinchilla, A RESTful framework in PHP";
		$this->message = View::render('example/message', $this, 'html');
		$this->output = View::render('example/index', $this);
		return View::render_layout('default', $this);
	}
		
}