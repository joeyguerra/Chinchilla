<?php
class_exists("ModelFactory") || require("ModelFactory.php");
class_exists("Member_meta") || require("Member_meta.php");
class Member extends ChinObject{
	public function __construct($values = array()){
		$this->id = 0;
		$this->is_owner =  false;
		$this->expiry = time();
		$this->in_directory = false;
		parent::__construct($values);
	}
	public $signin;
	public $in_directory;
	public $name;
	public $id;
	public $password;
	public $is_owner;
	public $hash;
	public $expiry;
	public $display_name;
	public $email;
	public $photo_url;
	private $member_meta;
	public function __get($key){
		return $this->{$key};
	}
	public function __set($key, $value){
		$this->{$key} = $value;
	}
	public function member_meta(){
		return $this->member_meta;
	}
	public function set_member_meta($value){
		if($value !== null){
			foreach($value as $key=>$val){
				$this[$key] = $val;
			}
		}
		$this->member_meta = $value;
	}
	
	public static function find_by_name($name){
		$member = Repo::find("select ROWID as id, * from members where name=:name", (object)array("name"=>$name))->first(new Member());
		return $member;
	}
	public static function find_owner(){
		$member = Repo::find("select ROWID as id, * from members where is_owner", null)->first(new Member());
		return $member;
	}
	public static function find_by_id($id){
		$member = Repo::find("select ROWID as id, * from members where ROWID=:id", (object)array("id"=>(int)$id))->first(new Member());
		if($member !== null){
			$meta = Member_meta::find_by_id($member->id);
			$member->set_member_meta($meta);
		}
		return $member;
	}
	public static function find_existing_by_signin($signin, $id){
		$member = Repo::find("select ROWID as id, * from members where signin=:signin and ROWID != :id", (object)array("signin"=>$signin, "id"=>(int)$id))->first(new Member());
		return $member;
	}
	public static function find_by_signin($signin){
		$member = Repo::find("select ROWID as id, * from members where signin=:signin", (object)array("signin"=>$signin))->first(new Member());
		return $member;
	}
	
	public static function find_all($page, $limit){
		$members = Repo::find("select ROWID as id, * from members order by name limit :page, :limit", (object)array("page"=>(int)$page, "limit"=>(int)$limit))->to_list(new Member());
		return $members;
	}
	public static function find_in_directory($page, $limit){
		$members = Repo::find("select ROWID as id, * from members where in_directory=1 order by name limit :page, :limit", (object)array("page"=>(int)$page, "limit"=>(int)$limit))->to_list(new Member());
		return $members;
	}
	public static function find_by_signin_and_password($signin, $password){
		$member = Repo::find("select ROWID as id, * from members where signin=:signin and password=:password", (object)array("signin"=>$signin, "password"=>$password))->first(new Member());
		return $member;
	}
	public static function find_signed_in($hash){
		$member = Repo::find("select ROWID as id, * from members where hash=:hash and expiry>=:expiry", (object)array("hash"=>$hash, "expiry"=>time()))->first(new Member());
		if($member === null) return null;
		$meta = Member_meta::find_by_id($member->id);
		$member->set_member_meta($meta);
		return $member;
	}
	public static function can_save(Member $member){
		$message = array();
		$existing = self::find_existing_by_signin($member->signin, $member->id);
		if($existing !== null){
			$message["existing"] = "That signin name is not available. Please try another.";
		}
		return $message;
	}
	public static function save(Member $member, $meta = array()){
		$member = Repo::save($member);
		if(count($meta) > 0){
			foreach($meta as $m){
				$m->owner_id = $member->id;
				Member_meta::save($m);
			}
		}
		return $member;
	}
}