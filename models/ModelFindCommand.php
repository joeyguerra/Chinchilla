<?php
abstract class ModelFindCommand extends Object{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	protected $class_name;
	protected $args;
	abstract function execute();
}
