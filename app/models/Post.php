<?php
class_exists("ModelFactory") || require("ModelFactory.php");
class_exists("Post_meta") || require("Post_meta.php");
class Post extends ChinObject{
	public function __construct($values = array()){
		$this->owner_id = 0;
		$this->id = 0;
		$this->post_date = time();
		$this->post_date_gmt = gmmktime();
		$this->modified = time();
		$this->modified_gmt = gmmktime();
		$this->parent = 0;
		$this->comment_count = 0;
		parent::__construct($values);
	}
	public $id;
	public $title;
	public $body;
	public $owner_id;
	public $post_date;
	public $post_date_gmt;
	public $excerpt;
	public $status;
	public $comment_status;
	public $ping_status;
	public $password;
	public $name;
	public $to_ping;
	public $pinged;
	public $modified;
	public $modified_gmt;
	public $content_filtered;
	public $parent;
	public $url;
	public $type;
	public $mime_type;
	public $comment_count;
	
	private $post_meta;
	public function post_meta(){
		return $this->post_meta;
	}
	public function set_post_meta($value){
		$this->post_meta = $value;
	}
	public static function get_excerpt($post){
		if($post->excerpt !== null) return $post->excerpt;
		$lines = explode(PHP_EOL, $post->body);
		if(count($lines) > 0) return $lines[0];
		return $post->body;
	}
	public function extract_id($post){
		return $post->id;
	}
	public function owner(){
		return Member::find_by_id($this->owner_id);
	}
	
	public static function find_by_id($id){
		$post = Repo::find("select ROWID as id, * from posts where ROWID=:id", (object)array("id"=>(int)$id))->first(new Post());		
		if($post === null) return null;
		$meta = Post_meta::find_by_id($post->id);
		$post->set_post_meta($meta);
		return $post;
	}
	public static function find_owned_by($owner_id, $page, $limit){
		$posts = Repo::find("select ROWID as id, * from posts where owner_id=:owner_id order by post_date desc limit :page, :limit", (object)array("owner_id"=>AuthController::$current_user->id, "page"=>$page, "limit"=>$limit))->to_list(new Post());
		return $posts;
	}
	public static function find_type_owned_by($owner_id, $type, $page, $limit){
		$posts = Repo::find("select ROWID as id, * from posts where owner_id=:owner_id and type=:type order by post_date desc limit :page, :limit", (object)array("owner_id"=>$owner_id, "type"=>$type, "page"=>$page, "limit"=>$limit))->to_list(new Post());
		if($posts !== null){
			$ids = array_map(array("Post", "extract_id"), $posts);
			$meta = Post_meta::find_by_ids($ids);
			if($meta !== null){
				foreach($meta as $m){
					array_map(array($m, "add_to_post"), $posts);
				}
			}
		}
		return $posts === null ? array() : $posts;
	}
	
	public static function find_by_id_and_owned_by($id, $owner_id){
		$post = Repo::find("select ROWID as id, * from posts where ROWID=:id and owner_id=:owner_id", (object)array("id"=>(int)$id, "owner_id"=>(int)$owner_id))->first(new Post);
		return $post;
	}
	public static function find_public_posts_with_limit($owner_id, $page, $limit){
		$post = Repo::find("select ROWID as id, * from posts where owner_id=:owner_id and type='post' order by post_date desc limit :page, :limit", (object)array("owner_id"=>(int)$owner_id, "page"=>(int)$page, "limit"=>(int)$limit))->to_list(new Post());
		return $post;
	}
	
	public static function find_public_count($owner_id){
		$count = Repo::find("select count(1) as total from posts where status='public' and owner_id=:owner_id", (object)array("owner_id"=>(int)$owner_id))->first(new Post());
		return $count;
	}
	public static function find_total($owner_id){
		$count = Repo::find("select count(1) as total from posts where owner_id=:owner_id", (object)array("owner_id"=>(int)$owner_id))->first(new Post());
		return (int)$count->total;
	}
	public static function find_public_page($name, $owner_id){
		$page = Repo::find("select ROWID as id, * from posts where type='page' and status='public' and name=:name and owner_id=:owner_id and post_date<=:post_date", (object)array("name"=>$name, "owner_id"=>(int)$owner_id, "post_date"=>time()))->first(new Post());
		return $page;
	}
	public static function find_public_pages($owner_id){
		$page = Repo::find("select ROWID as id, * from posts where type='page' and status='public' and owner_id=:owner_id and post_date<=:post_date and name!='index'", (object)array("owner_id"=>(int)$owner_id, "post_date"=>time()))->to_list(new Post());
		return $page;
	}
	public static function find_page_by_name($name, $owner_id){
		$page = Repo::find("select ROWID as id, * from posts where name=:name and type='page' and owner_id=:owner_id", (object)array("name"=>$name, "owner_id"=>(int)$owner_id))->first(new Post());
		return $page;
	}
	public static function find_by_name($name, $owner_id){
		$post = Repo::find("select ROWID as id, * from posts where name=:name", (object)array("name"=>$name, "owner_id"=>(int)$owner_id))->first(new Post());		
		return $post;
	}
	public static function find_by_title($title, $owner_id){
		$name = String::string_for_url($title);
		return self::find_by_name($name, $owner_id);
	}
	
	public static function find_public_attachments_owned_by($owner_id, $page, $limit){
		$attachments = Repo::find("select ROWID as id, * from posts where status='public' and owner_id=:owner_id and type='attachment' order by post_date desc limit :page, :limit", (object)array("owner_id"=>(int)$owner_id, "page"=>(int)$page, "limit"=>(int)$limit))->to_list(new Post());
		return $attachments;
	}
	public static function can_save($post){
		$message = array();
		$duplicate_title = null;
		if($post->title === null || strlen(trim($post->title)) === 0){
			$message["title"] = "The title is required.";
		}
		if(count($message) === 0){
			$duplicate_title = self::find_by_title($post->title, $owner_id);
		}
		if($duplicate_title !== null) $message["duplicate_title"] = "The title is the same as another ad you've posted. Please change the title or update the one that already exists.";
		return $message;
	}
	public static function save($post){
		return Repo::save($post);
	}
}