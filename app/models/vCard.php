<?php
// RFC 2525
class_exists("ModelFactory") || require("ModelFactory.php");
class vCard extends ChinObject{
	public function __construct($values = array()){
		$this->id = 0;
		$this->owner_id = 0;
		parent::__construct($values);
	}
	public $vcard;
	public $id;
	public $owner_id;
	private $properties;
	public function get_property($key){
		$key = strtolower($key);
		$ubound = count($this->properties);
		$i = 0;
		$kv = explode(":", $key);
		$type = null;
		if(count($kv) > 1) $type = $kv[1];
		$key = $kv[0];
		for($i; $i < $ubound; $i++){
			$property = $this->properties[$i];
			if($property->name === $key && ($type !== null ? $property->get_type($type) === $type : true)){
				return $this->properties[$i];
			}
		}
		return null;
	}
	public function serialize(){
	}
	public function deserialize(){
		$this->parse($this->vcard);		
	}
	private function parse($text){
		NotificationCenter::post("begin_vcard_parsing", $this, $text);
		$text = $this->unfold($text);
		$lines = preg_split("/" . PHP_EOL . "/", $text);
		$fields = array();	
		$properties = array();	
		$matches = array();
		foreach($lines as $key=>$line){
			$kv = explode(":", $line);			
			if(count($kv) === 1){
				$fields[count($fields) - 1][1] .= "\n" . $kv[0];
			}else{
				$fields[] = $kv;
			}
		}
		foreach($fields as $field){
			$parts = explode(";", $field[0]);
			$name = null;
			$types = array();
			$parms = array();
			while($key = array_shift($parts)){
				if($name === null){
					$name = $key;
					continue;
				}
				if(strpos($key, "type") !== false){
					$kv = explode("=", $key);
					$types[] = strtolower($kv[1]);
				}else{
					$parms[] = $key;
				}
			}
			$properties[] = new vCardProperty(array("name"=>strtolower($name), "value"=>$field[1], "types"=>$types, "parms"=>$parms));
		}
		$this->properties = $properties;
		NotificationCenter::post("end_vcard_parsing", $this, $text);
	}
	
	// 5.8.1
	public function unfold($text){
		return preg_replace("/[\r\n]+\s+/", "", $text);
	}
	
	// 5.8.2 abnf content-type definition
	public function fold($text){
		return chunk_split($text, 75, "\r\n ");
	}
}
class vCardProperty extends ChinObject{
	public function __construct($values = array()){
		$this->types = array();
		$this->parms = array();
		parent::__construct($values);
	}
	public $name;
	public $types;
	public $parms;
	public $value;
	public function get_type($value){
		$i = array_search($value, $this->types);
		if($i) return $this->types[$i];
		return null;
	}
}