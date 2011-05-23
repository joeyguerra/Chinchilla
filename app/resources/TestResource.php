<?php
class_exists('AppResource') || require('AppResource.php');
class TestResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public $date;
	public $title;
    public $id;
	public function get($date = null, $title = "Testing URL"){
		$this->date = $date;
		$this->title = $title;
		$this->output = $this->render('test/date', null);
		return $this->render_layout('default', null);			
	}
	public function post($id, $title){
		$this->title = $title;
		$this->id = $id;
		$this->output = $this->render('test/post', null);
		return $this->render_layout('default', null);
	}
	public function put($title){
		$this->title = $title;
		$this->output = $this->render('test/date', null);
		return $this->render_layout('default', null);
	}
}

?>