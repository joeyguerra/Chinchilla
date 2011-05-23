<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("Post") || require("models/Post.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class BlogResource extends AppResource{
	public function __construct(){
		parent::__construct();
	}
	public $posts;
	public $post;
	public $page;
	public $next_page;
	public $previous_page;
	public $total_pages;
	public $post_count;
	public $limit;
	public function get($id = null, $page = 1){
		$view = "blog/index";
		$this->page = (int)$page;
		$this->page--;
		$this->limit = 3;
		if($id !== null){
			$this->post = Post::find_by_id($id);
			$view = "post/show";
			$this->title = $this->post->title;
		}else{
			$this->posts = Post::find_public_posts_with_limit(self::$member->id, $this->page * 3, $this->limit);
			if(count($this->posts) === 0){
				$this->set_not_found();
				return;
			}
			$this->next_page = $this->page+2;
			$this->post_count = Post::find_public_count(self::$member->id);
			$this->total_pages = ceil($this->post_count->total / 3);
			$this->previous_page = $this->next_page-2;
			if($this->next_page > $this->total_pages){
				$this->next_page = $this->total_pages;
			}
			if($this->previous_page < 0){
				$this->previous_page = 0;
			}
			$this->title = !self::$member->is_owner ? self::$member->name . "'s Blog" : "Blog";
		}
		
		$this->output = View::render($view, $this);
		return View::render_layout("default", $this);
	}	
}
