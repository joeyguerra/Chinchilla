<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("Post") || require("models/Post.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class PostResource extends AppResource{
	public function __construct(){
		parent::__construct();
	}
	public $post;
	public $legend;
	public function get(Post $post = null){
		$post = $post === null ? new Post() : $post;
		$post->id = (int)$post->id;
		if($post->id > 0){
			$this->post = Post::find_by_id($post->id);
		}
		$this->title = "Chinchllalite Blog";
		$view = "post/show";
		$this->legend = "Edit this post";
		if($this->post === null) $this->post = new Post();
		if(AuthController::is_authed()){
			$view = "post/edit";
			$this->legend = "Add a new post";
		}else{
			$this->set_not_found();
		}
		$this->output = View::render($view, $this);
		return View::render_layout("default", $this);
	}
	public function delete(Post $post){
		if(!AuthController::is_authed()){
			$this->set_unauthed();
			return;
		}
		$this->post = Post::find_by_id_and_owned_by($post->id, AuthController::$current_user->id);
		if($this->post === null){
			$this->set_not_found();
			return;
		}
		delete_object::execute($this->post);
		$this->set_redirect_to(AuthController::$current_user->signin . '/posts');
		$this->output = View::render("blog/index", $this);
		return View::render_layout("default", $this);
	}
	public function put(Post $post){
		if(!AuthController::is_authed()){
			$this->set_unauthed();
			return;
		}

		$this->post = Post::find_by_id_and_owned_by($post->id, AuthController::$current_user->id);
		if($this->post === null){
			$this->set_not_found();
			return;
		}
		$this->post->title = $post->title;
		$this->post->body = $post->body;
		$this->post->status = $post->status;
		$this->post->post_date = strtotime($post->post_date);
		$this->post->type = $post->type;
		$errors = Post::can_save($this->post);
		if(count($errors) === 0){
			Post::save($this->post);
		}
		$this->set_redirect_to(AuthController::$current_user->signin . '/posts');
		$this->output = View::render('blog/show', $this);
		return View::render_layout('default', $this);
	}
	
	public static function add_p_tags($body){
		$lines = explode(PHP_EOL, $body);
		$p_body = "";
		foreach($lines as $line){
			$p_body .= "<p>$line</p>";
		}
		return $p_body;
	}
}