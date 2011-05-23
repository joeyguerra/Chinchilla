<?php

class HomePage{
	
	public function __construct(){
		$this->limit = 4;
	}
	public function __destruct(){}
	private $limit;
	public function get_limit(){
		return $this->limit;
	}

}