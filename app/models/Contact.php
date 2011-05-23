<?php
class_exists("ModelFactory") || require("ModelFactory.php");
class Contact extends ChinObject{
	public function __construct($values = array()){
		$this->id = 0;
		$this->owner_id = 0;
		parent::__construct($values);
	}
	public $id;
	public $owner_id;
	public $name;
	public $url;
	public $photo_url;
	public $email;
	public $json;
	
	public static function find_tagged($tag, $owner_id){
		$contacts = Repo::find("select contacts.ROWID as id, contacts.* from contacts inner join tags on tags.object_id = contacts.ROWID and tags.object_type='contact' where tags.name=:tag and contacts.owner_id=:owner_id", (object)array("tag"=>$tag, "owner_id"=>(int)$owner_id))->to_list(new Contact());
		return $contacts;
	}
	public static function find_owned_by($owner_id){
		$contacts = Repo::find("select ROWID as id, * from contacts where owner_id=:owner_id", (object)array("owner_id"=>(int)$owner_id))->to_list(new Contact());
		return $contacts;
	}
	public static function find_by_id($id, $owner_id){
		$contact = Repo::find("select ROWID as id, * from contacts where ROWID=:id and owner_id=:owner_id", (object)array("owner_id"=>(int)$owner_id, "id"=>(int)$id))->first(new Contact());
		return $contact;
	}
	public static function find_by_ids($ids, $owner_id){
		$contacts = Repo::find("select ROWID as id, * from contacts where owner_id=:owner_id and id in (:ids)", (object)array("owner_id"=>(int)$owner_id, "ids"=>implode(",", $ids)))->to_list(new Contact());
		return $contacts;
	}

}