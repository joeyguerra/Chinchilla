<?php
class_exists("ModelFactory") || require("ModelFactory.php");
class CommentMeta extends ChinObject{
	public function __construct($values = array()){
		$this->id = 0;
		$this->comment_id = 0;
		parent::__construct($values);
	}
	public $id;
	public $comment_id;
	public $key;
	public $value;
}