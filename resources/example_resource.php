<?php
class example_resource extends resource{
	function __construct($request, $url){
		parent::__construct($request, $url);
	}
	public $message;
	function GET(){
		$this->title = "An example using Chinchilla, A RESTful framework in PHP";
		$this->message = view::render('example/message', new example_resource($this->request, (object)array("resource_name"=>$this->resource_name, "request"=>$this->request, "file_type"=>"html")));
		$this->output = view::render("example/index", $this);
		return layout::render("default", $this);
	}
}
