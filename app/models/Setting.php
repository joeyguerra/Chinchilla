<?php
class_exists("ModelFactory") || require("ModelFactory.php");
class Setting extends ChinObject{
	public function __construct($values = array()){
		$this->id = 0;
		$this->owner_id = 0;
		parent::__construct($values);
	}
	public $id;
	public $owner_id;
	public $key;
	public $value;

	public static function find($key, $owner_id){
		$owner_id = (int)$owner_id;
		$setting = Repo::find("select ROWID as id, * from settings where key=:key and owner_id=:owner_id", (object)array("owner_id"=>$owner_id, "key"=>$key))->first(new Setting());
		if($setting === null) return new Setting(array("key"=>$key, "value"=>null));
		return $setting;
	}
	public static function find_all($page, $limit, $owner_id){
		$owner_id = (int)$owner_id;
		$settings = Repo::find("select ROWID as id, * from settings order by key limit :page, :limit", (object)array("page"=>(int)$page, "limit"=>(int)$limit))->to_list(new Setting());
		return $settings;
	}
	public static function can_save(Setting $setting){
		$message = array();
		return $message;
	}
	public static function save(Setting $setting){
		return Repo::save($setting);
	}
	
}