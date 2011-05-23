<?php
class_exists("ModelFactory") || require("ModelFactory.php");
class Member_meta extends ChinObject{
	public function __construct($values = array()){
		$this->id = 0;
		$this->owner_id = 0;
		parent::__construct($values);
	}
	public $id;
	public $owner_id;
	public $key;
	public $value;
	
	public static function find_by_id($id, $key = null){
		$member_meta = Repo::find("select ROWID as id, * from member_metas where owner_id=:owner_id" . ($key === null ? null : " and key=:key"), (object)array("owner_id"=>(int)$id, "key"=>$key))->first(new Member_meta());
		return $member_meta;
	}
	public static function find_by_ids($ids){
		$list = Repo::find("select ROWID as id, * from member_metas where member_id in (:ids)", (object)array("ids"=>implode(",", $ids)))->to_list(new Member_meta());
		return $list;
	}
	public static function save(Member_meta $meta){
		return Repo::save($meta);
	}
	
}