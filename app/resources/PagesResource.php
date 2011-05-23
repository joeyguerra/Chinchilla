<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class_exists("Post") || require("models/Post.php");
class PagesResource extends AppResource{
	public function __construct(){
		parent::__construct();
		if(!AuthController::is_authed()){
			$this->set_unauthed("Access denied");
		}
	}
	public $post;
	public function get($name){
		$name = preg_replace("/[^a-zA-Z0-9-]?/", "", $name);
		$this->post = Post::find_page_by_name($name, AuthController::$current_user->id);
		$view = "page/index";
		$this->output = View::render($view, $this);
		return View::render_layout("default", $this);
	}
	public function post(Post $post){
		$post->name = preg_replace("/[^a-zA-Z0-9-]?/", "", $post->name);
		$this->post = Post::find_page_by_name($name, AuthController::$current_user->id);
		if($this->post !== null){
			AppResource::set_user_message("That page already exists. Enter a different name.");
		}else{
			$this->post = new Post(array("id"=>0
				, "title"=>$post->title
				, "body"=>$post->body
				, "status"=>$post->status
				, "name"=>$post->name
				, "type"=>"page"
				, "owner_id"=>AuthController::$current_user->id
			));
			save_object::execute($this->post);
		}
				
		$this->set_redirect_to(AuthController::$current_user->signin . "/posts");
		$view = "page/edit";
		$this->output = View::render($view, $this);
		return View::render_layout("default");
	}	
}

?>