<?php
class_exists("Post") || require("models/Post.php");
class NotfoundResource extends Resource{
	public $page;
	public function file_not_found($publisher, $info){
		$this->request = $info;
		$this->resource_name = $this->request->resource_name;
		$this->file_type = $this->request->file_type;
		//$this->page = Post::find_public_page($this->request->resource_name, AppResource::$member->id);
		
		if($this->page !== null){
			$this->output = View::render("page/index", $this);
			return View::render_layout("default", $this);
		}
		
		$this->output = View::render_absolute("plugins/404handler/views/notfound", $this);			
		return View::render_layout("default", $this);
	}
}
NotificationCenter::add(new NotfoundResource(), "file_not_found");
