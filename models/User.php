<?php
class_exists('Object') || require('lib/Object.php');
class_exists('ModelFindCommand') || require('models/ModelFindCommand.php');
class User extends Object{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public $member_name;
	public $password;
	public $id;
	public $posts;
	public static function save($user){
		if($user === null) return null;
		$file_name = App::get_root_path('data' . DIRECTORY_SEPARATOR . 'db.json');
		$json = file_get_contents($file_name);
		$users = json_decode($json);
		$ubounds = count($users);
		if($user->id > 0){
			for($i=0; $i < $ubounds; $i++){
				if($users[$i]->id === $user->id){
					$users[$i] = $user;
					break;
				}
			}			
		}else{
			$user->id = count($users);
			$users[] = $user;
		}
		file_put_contents($file_name, json_encode($users));
		return $user;
	}
	public static function find_by_member_name($member_name){
		if($member_name === null) return new User();
		$file_name = App::get_root_path('data' . DIRECTORY_SEPARATOR . 'db.json');
		$json = file_get_contents($file_name);
		$users = json_decode($json);
		foreach($users as $user){
			if($user->member_name === $member_name) return $user;
		}
		return new User();
	}
	public static function find_by_id($id){
		$id = (int)$id;
		if($id === 0) return new User();
		$file_name = App::get_root_path('data' . DIRECTORY_SEPARATOR . 'db.json');
		$json = file_get_contents($file_name);
		$users = json_decode($json);
		foreach($users as $user){
			if($user->id == $id) return $user;
		}
		return new User();
	}
}