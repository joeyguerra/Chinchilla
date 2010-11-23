<?php
class_exists('Object') || require('lib/Object.php');
abstract class Command extends Object{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){}
	public abstract function execute();
}