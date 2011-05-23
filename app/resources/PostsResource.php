<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("Post") || require("models/Post.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class PostsResource extends AppResource{
	public function __construct(){
		parent::__construct();
	}
	public $post;
	public $legend;
	public $posts;
	public $total_pages;
	public $previous_page;
	public $next_page;
	
	public function get($page = 0){
		$page = (int)$page;
		if(!AuthController::is_authed() || AuthController::$current_user->id !== AppResource::$member->id){
			$this->set_unauthed();
			return;
		}
		$total = Post::find_total(AuthController::$current_user->id);
		$this->total_pages = (int)(ceil($total / 5));
		$this->next_page = $page+1;
		$this->previous_page = $page-1;
		if($this->previous_page < 0) $this->previous_page = 0;
		$start = $page * 5;
		$this->post = new Post(array("owner_id"=>(int)AuthController::$current_user->id));
		$this->posts = Post::find_owned_by(AuthController::$current_user->id, $start, 5);
		if($this->posts === null) $this->posts = array();
		$view = "post/index";
		$this->output = View::render($view, $this);
		return View::render_layout('default', $this);
	}
	public function post(Post $post){
		if(!AuthController::is_authed()){
			$this->set_unauthed();
			return;
		}
		//TODO: Need to create a table that just stores post titles and member name, perhaps a hash, so we can
		// check for duplicate post titles.
		$this->post = new Post(array("id"=>0
			, "title"=>$post->title
			, "body"=>$post->body
			, "status"=>$post->status
			, "type"=>$post->type
			, "owner_id"=>AuthController::$current_user->id
		));
		$errors = Post::can_save($this->post);
		if(count($errors) === 0){
			Post::save($this->post);
		}else{
			App::set_user_message(implode(", ", $errors));
		}
		$this->set_redirect_to(AuthController::$current_user->signin . '/posts');
		$this->output = View::render('blog/show', $this);
		return View::render_layout('default', $this);
	}
}