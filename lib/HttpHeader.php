<?php
class_exists('Object') || require('lib/Object.php');
class HttpHeader extends Object{
	public function __construct($attributes){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public $file_type;
	public $cache_control;
	public $expires;
	public $location;
	public function get_content_type(){
		$content_type = null;
		switch($this->file_type){
			case('html'):
				$content_type = 'text/html;charset=UTF-8';
				break;
			case('json'):
				$content_type = 'application/json;charset=UTF-8';
				break;
			case('xml'):
				$content_type = 'text/xml;charset=UTF-8';
				break;
		}
		return $content_type;
	}
	public function send(){
		if($this->get_content_type() !== null){
			$this->send_header('Content-Type', $this->get_content_type());
		}
		if($this->cache_control !== null){
			$this->send_header('Cache-Control', $this->cache_control);
		}
		if($this->expires !== null){
			$this->send_header('Expires', $this->expires);
		}
		if($this->location !== null){
			$this->send_header('Location', $this->location);
		}
	}
	private function send_header($key, $value){
		header(sprintf("%s: %s", $key, $value));
	}
}