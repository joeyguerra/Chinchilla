<?php
class index_resource extends resource{
	function __construct($request, $url){
		parent::__construct($request, $url);
	}
	function GET(){
		$this->output = view::render("index/index", $this);
		return layout::render("default", $this);
	}
}
