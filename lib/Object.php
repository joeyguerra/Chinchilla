<?php
	class_exists('Hashtable') || require('Hashtable.php');
	class Object{
		public function __construct($attributes = null){
			if(self::$observers == null){
				self::$observers = array();
			}
			
			if($attributes != null){
				foreach($attributes as $key=>$val){
					$this->$key = $val;
				}
			}
			// 2009-08-27, jguerra: I have to use an Array object instead of a simple array because of the DataStorage
			// library. It tries to populate the _attributes variable and fails. But making _attributes
			// an object data type fixes the issue.
			$this->_attributes = new Hashtable();
		}
		public function __destruct(){}
		public $errors;
		private static $observers;
		public static function addObserver($observer, $notification, $publisher){
			self::$observers[] = new Observer($observer, $notification, $publisher);
		}
		public static function removeObserver($observer){
			$tmp = array();
			foreach(self::$observers as $o){
				if($o->obj !== $observer){
					$tmp[] = $o;
				}
			}
			self::$observers = $tmp;
		}
		public $_attributes;
		public static function notify($notification, $sender, $info){
			$publisher = $sender;			
			if(is_object($sender)){
				$publisher = get_class($sender);
			}
			if(self::$observers != null){
				foreach(self::$observers as $observer){
					if(method_exists($observer->obj, $notification) && $observer->publisher === $publisher){						
						$observer->obj->{$notification}($sender, $info);
					}
				}
				
			}
		}

		public function __get($key){
			if($this->_attributes == null){
				$this->_attributes = new Hashtable();
			}
			$val = null;
			if($this->_attributes->offsetExists($key)){
				$val = $this->_attributes->offsetGet($key);
			}
			$getter = 'get' . ucwords($key);
			if(method_exists($this, $getter)){
				$val = $this->{$getter}();
			}
			if(count(self::$observers) > 0){
				$publisher = get_class($this);
				foreach(self::$observers as $observer){
					if(method_exists($observer->obj, 'willReturnValueForKey') && $observer->publisher === $publisher){
						$val = $observer->obj->willReturnValueForKey($key, $this, $val);
					}
				}
			}
			return $val;

		}
		private function setPropertyValue($prop, $key, $val){
			
		}
		public function __set($key, $val){
			$reflector = new ReflectionClass(get_class($this));
			$properties = $reflector->getProperties();
			$obj = null;
			$name = null;
			if(count(self::$observers) > 0){
				$publisher = get_class($this);
				foreach(self::$observers as $observer){
					if(method_exists($observer->obj, 'observeForKeyPath') && $observer->publisher === $publisher){
						$observer->obj->observeForKeyPath($key, $this, $val);
					}
				}
			}
			if($this->_attributes == null){
				$this->_attributes = new Hashtable();
			}
			
			foreach($properties as $prop){
				if($prop->isPublic()){
					$name = $prop->getName();
					$obj = $this->{$name};
					if(is_object($obj) && method_exists($obj, 'set'.ucwords($key))){
						$this->{$name}->$key = $val;
					}
				}
			}
			
			$setter = 'set' . ucwords($key);
			if(method_exists($this, $setter)){
				$this->{$setter}($val);
			}else{
				$this->_attributes->offsetSet($key, $val);
			}
		}
	}
	
	class Observer{
		public function __construct($obj, $notification, $publisher){
			$this->obj = $obj;
			$this->notification = $notification;
			if(is_object($publisher)){
				$publisher = get_class($publisher);
			}
			$this->publisher = $publisher;
		}
		
		public $obj;
		public $notification;
		public $publisher;
	}
?>