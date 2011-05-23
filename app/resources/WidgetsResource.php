<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("Setting") || require("models/Setting.php");
class WidgetsResource extends AppResource{
	public function __construct(){
		parent::__construct();
	}
	public function get(){
		if($this->request->path[0] !== null){
			$name = $this->request->path[0];
			if(count($this->request->path) === 1){
				$resource = $this->get_widget_resource($name, "index");
				return $resource->execute($this->request);
			}
			if($this->request->path[1] === "widgets"){
				$this->set_not_found();
				return;
			}
			$resource_name = ucwords($this->request->path[1]) . "Resource";
			if(!class_exists($resource_name)) require($resource_name . ".php");
			$resource = new $resource_name();
			return $resource->execute($this->request);
		}
		$view = "widgets/index";
		$html = View::render_layout("default", $this);
		return $html;
	}
	private function get_widget_resource($name, $resource_name){
		$class_name = sprintf("%s_%sResource", ucwords($name), ucwords($resource_name));
		require("widgets/$name/resources/$class_name.php");
		$resource = new $class_name();
		return $resource;
	}
}