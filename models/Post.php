<?php
class_exists('User') || require('models/User.php');
class Post extends Object{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public $title;
	public $body;
	public static function save($post, $owner_id){
		$member = User::find_by_id($owner_id);
		$post->id = count($member->posts);
		$member->posts[] = $post;
		$member = User::save($member);
		return $post;
	}
	public static function delete($post, $owner_id){
		$owner_id = (int)$owner_id;
		$member = User::find_by_id($owner_id);
		$offset = null;
		foreach($member->posts as $key=>$p){
			if($p->id == $post->id){
				$offset = $key;
			}
		}
		if($offset !== null){
			array_splice($member->posts, $offset, 1);
		}
		User::save($member);
		return $post;
	}
	public static function is_public($post){
		return $post->permission[strlen($post->permission) - 1] >= 5;
	}
	public static function sort_by_most_recent($a, $b){
		if($a->date == $b->date) return 0;
		return ($a->date > $b->date) ? -1 : 1;
	}
	public static function find_by_title($title){
		$file_name = App::get_root_path('data' . DIRECTORY_SEPARATOR . 'db.json');
		$json = file_get_contents($file_name);
		$users = json_decode($json);
		foreach($users as $user){
			foreach($user->posts as $post){
				if($post->title === $title){
					return $post;
				}
			}
		}
		return new Post();		
	}
	public static function find_last($count, $owner_id){
		$owner_id = (int)$owner_id;
		$count = (int)$count;
		$counter = 0;
		$list = array();
		$member = User::find_by_id($owner_id);
		if($member === null) return $list;
		while($post = array_shift($member->posts)){
			if($count < $counter) break;
			$list[] = $post;
			$counter++;
		}
		return $list;	
	}
}
