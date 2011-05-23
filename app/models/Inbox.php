<?php
class_exists("ModelFactory") || require("ModelFactory.php");
class Inbox extends ChinObject{
	public function __construct($values = array()){
		$this->id = 0;
		$this->owner_id = 0;
		$this->received = time();
		parent::__construct($values);
	}
	public $id;
	public $owner_id;
	public $message;
	public $sender;
	public $received;
}