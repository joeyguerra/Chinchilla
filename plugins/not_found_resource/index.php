<?php
class not_found_resource extends resource{
	function __construct($request, $url){
		parent::__construct($request, $url);		
	}
	function resource_not_found($publisher, $info){
		$this->url = $publisher->url;
		$publisher->resource_name = "not_found_resource";
		return $this;
	}
	function GET(){
		$this->status = new http_status(array("code"=>404, "message"=>$this->url->resource_name . " not found."));
		$this->output = view::render("index", $this);
		return layout::render("default", $this);
	}
}
filter_center::subscribe("resource_not_found", null, new not_found_resource($_REQUEST, null));
