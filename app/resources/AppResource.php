<?php
class_exists("Member") || require("models/Member.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class AppResource extends Resource{	
	public function __construct(){
		parent::__construct();
	}
	public static function owns_content(){
		return AuthController::is_authed() && AppResource::$member->id === AuthController::$current_user->id;
	}
	public static function url_for_member($url, $data = null){
		return App::url_for(self::$member !== null  && !self::$member->is_owner ? self::$member->signin . "/" . $url : $url, $data);
	}
	public static function url_for_user($url, $data = null){
		return App::url_for(AuthController::$current_user !== null  && !AuthController::$current_user->is_owner ? AuthController::$current_user->signin . "/" . $url : $url, $data);
	}
	public static function resource_not_found($publisher, $info){
		$page_name = $info->resource_name;
		$resource = null;
		$member = Member::find_by_signin($page_name);
		if($member !== null){
			self::$member = $member;
			if($info->path === null) $info->path = array("index");
			$resource = Resource::get_instance($info->path[0]);
			//if($info->path !== null) array_shift($info->path);
			if($resource !== null) return $resource;
			$page_name = $info->path[0];
		}
		$post = Post::find_page_by_name($page_name, self::$member->id);
		if($post !== null){
			class_exists("PageResource") || require("resources/PageResource.php");
			$resource = new PageResource();
			return $resource;
		}

		return $resource;
	}
	public static function begin_request($publisher, $info){
		if(AuthController::get_chin_auth() !== null){
			AuthController::set_current_user();
		}
		self::$member = Member::find_by_name($info->resource_name);
		if(self::$member !== null){
			if($info->path !== null){
				$info->resource_name = array_shift($info->path);
			}else{
				$info->resource_name = "index";
			}
		}else{			
			self::$member = Member::find_owner();
		}
	}
	public function __destruct(){
		parent::__destruct();
	}

	public static $member;
	public $page;
	public $errors;
}