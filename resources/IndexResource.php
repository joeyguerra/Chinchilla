<?php
class_exists('AppResource') || require('AppResource.php');
class IndexResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public $id;
	public $test;
	public function get($id = null, $test = null){		
		$this->id = $id;
		$this->test = $test;
		$this->title = "A RESTful framework in PHP";
		$this->output = $this->render('index/index', null);
		return $this->render_layout('default', null);			
	}	
}

?>