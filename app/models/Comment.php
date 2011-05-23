<?php
class_exists("ModelFactory") || require("ModelFactory.php");
class Comment extends ChinObject{
	public function __construct($values = array()){
		$this->id = 0;
		$this->owner_id = 0;
		$this->post_id = 0;
		$this->date = date();
		$this->date_gmt = gmmktime();
		$this->karma = 0;
		$this->approved = 0;
		$this->parent = 0;
		$this->member_id = 0;
		parent::__construct($values);
	}
	public $id;
	public $owner_id;
	public $post_id;
	public $date;
	public $date_gmt;
	public $karma;
	public $approved;
	public $parent;
	public $member_id;
}