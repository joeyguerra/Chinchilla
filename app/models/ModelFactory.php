<?php
class_exists("Repo") || require("Repo.php");
class ChinObject{
	public function __construct($values = array()){
		foreach($values as $key=>$value){
			$this->$key = $value;
		}
	}
	private static $observers;
	public static function observe($observer, $notification, $publisher){
		self::$observers[] = (object)array("observer"=>$observer, "notification"=>$notification, "publisher"=>$publisher);
	}
	public static function remove($observer){
		$tmp = array();
		foreach(self::$observers as $o){
			if($o->obj !== $observer){
				$tmp[] = $o;
			}
		}
		self::$observers = $tmp;
	}
	public static function notify($notification, $sender, $info){
		$publisher = $sender;			
		if(is_object($sender)){
			$publisher = get_class($sender);
		}
		if(self::$observers != null){
			foreach(self::$observers as $observer){
				if(method_exists($observer->observer, $notification) && $observer->publisher === $sender){		
					$observer->observer->{$notification}($sender, $info);
				}
			}
		}
	}
	
}
class ModelFactory{
	public static $path = "data";
	public static function get_path($file){
		return Settings::$app_path . "/" . self::$path . "/" . $file;
	}
	public static function populate_single($array, $type){
		if($array === false) return null;
		$class = new ReflectionClass($type);
		$properties = get_object_vars($array);
		$obj = $class->newInstance();
		foreach($properties as $key=>$value){
			$obj->{$key} = $value;
		}
		return $obj;
	}
	public static function populate($list, $type){
		$ubounds = count($list);
		$class = new ReflectionClass($type);
		for($i = 0; $i < $ubounds; $i++){
			$list[$i] = self::populate_single($list[$i], $type);
		}
		return $list;
	}
}
