<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("Post") || require("models/Post.php");
class_exists("Setting") || require("models/Setting.php");
class IndexResource extends AppResource{
	public function __construct(){
		parent::__construct();
	}
	public function get(){
		$view = "index/index";
		$post = Post::find_public_page("index", self::$member->id);
		if($post !== null){
			$this->output = $post->body;
			$this->title = $post->title;
		}else{
			$setting = Setting::find("home_page_title", self::$member->id);
			if($setting !== null){
				$this->title = $setting->value;
			}else{
				$this->title = "Another 6d site.";
			}
		}
		if($this->output === null) $this->output = View::render($view, $this);
		$html = View::render_layout("default", $this);
		return $html;
	}
}