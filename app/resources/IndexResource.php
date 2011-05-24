<?php
class_exists("AppResource") || require("AppResource.php");
class IndexResource extends AppResource{
	public function __construct(){
		parent::__construct();
	}
	public function get(){
		$view = "index/index";
		$this->output = View::render($view, $this);
		return View::render_layout("default", $this);
	}
}