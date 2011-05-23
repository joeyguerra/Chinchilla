<?php
class_exists("ModelFactory") || require("ModelFactory.php");
class_exists("Inbox") || require("Inbox.php");
class Message extends ChinObject{
	public function __construct($values = array()){
		$this->id = 0;
		$this->owner_id = 0;
		$this->date = time();
		parent::__construct($values);
	}
	public $id;
	public $owner_id;
	public $email;
	public $body;
	public $date;
	public $sent;
	public $delivered;
	public static function find_owned_by($owner_id){
		$messages = Repo::find("select ROWID as id, * from inbox where owner_id=:owner_id", (object)array("owner_id"=>(int)$owner_id)->to_list(new Message());
		return $messages;
	}
}