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
		
		private static $observers;
		public static function addObserver($observer){
			self::$observers[] = $observer;
		}
		public static function removeObserver($observer){
			$tmp = array();
			foreach(self::$observers as $o){
				if($o !== $observer){
					$tmp[] = $o;
				}
			}
			self::$observers = $tmp;
		}
		public $_attributes;
		public static function notify($notification, $sender, $info){
			if(self::$observers != null){
				foreach(self::$observers as $observer){
					if(method_exists($observer, $notification)){
						$observer->{$notification}($sender, $info);
					}
				}
				
			}
		}
		
		public function __get($key){
			if($this->_attributes == null){
				$this->_attributes = new Hashtable();
			}
			if($this->_attributes->offsetExists($key)){
				return $this->_attributes->offsetGet($key);
			}
			$getter = 'get' . String::camelize($key);
			if(method_exists($this, $getter)){
				return $this->{$getter}();
			}
		}

		public function __set($key, $val){
			if(count(self::$observers) > 0){
				foreach(self::$observers as $observer){
					if(method_exists($observer, 'observeForKeyPath')){
						$observer->observeForKeyPath($key, $this, $val);
					}
				}
			}
			if($this->_attributes == null){
				$this->_attributes = new Hashtable();
			}
			$setter = 'set' . String::camelize($key);
			if(method_exists($this, $setter)){
				$this->{$setter}($val);
			}else{
				$this->_attributes->offsetSet($key, $val);
			}
		}
	}
?>