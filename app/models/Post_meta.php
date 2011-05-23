<?php
class_exists("ModelFactory") || require("ModelFactory.php");
class Post_meta extends ChinObject{
	public function __construct($values = array()){
		$this->id = 0;
		$this->post_id = 0;
		parent::__construct($values);
	}
	public $id;
	public $post_id;
	public $key;
	public $value;
	public function add_to_post($post){
		if($this->post_id === $post->id){
			$post->set_post_meta($this);
		}
		return $post;
	}
	public static function find_by_id($post_id, $key = null){
		$post_meta = Repo::find("select ROWID as id, * from post_metas where post_id=:post_id" . ($key === null ? null : " and key=:key"), (object)array("post_id"=>(int)$post_id, "key"=>$key))->first(new Post_meta());
		return $post_meta;
	}
	public static function find_by_ids($ids){
		$list = Repo::find("select ROWID as id, * from post_metas where post_id in (:ids)", (object)array("ids"=>implode(",", $ids)))->to_list(new Post_meta());
		return $list;
	}
	public static function save(Post_meta $meta){
		return Repo::save($meta);
	}
}