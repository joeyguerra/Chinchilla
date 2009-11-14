<?php
	class_exists('NotificationObserver') || require('NotificationObserver.php');
	class NotificationCenter{		
		private function __construct(){}
		private static $instance;
		private $observers;
		public static function getInstance(){
			if (!isset(self::$instance)){
				$c = __CLASS__;
				self::$instance = new $c();
				self::$instance->observers = array();
			}
			return self::$instance;
		}
		
		public function bark(){
			echo 'Woof!';
		}
		public function addObserver($observer, $notificationName, $publisher){
			if(!$this->exists($observer, $notificationName)){
				$obs = new NotificationObserver($observer, $notificationName, $publisher);
				array_push($this->observers, $obs);
			}
		}
		
		public function removeObserver($observer, $notificationName, $publisher){
			$temp = array();
			foreach($this->observers as $value){
				if($value->observer !== $observer && $value->notificationName !== $notificationName
					&& $value->publisher !== $publisher){
						$temp[] = $value;
				}
			}
			$this->$observers = $temp;
		}
		private function exists($observer, $notificationName){
			if(is_array($this->observers)){
				foreach($this->observers as $value){
					if($value->observer() === $observer && $value->notificationName() === $notificationName){
						return true;
					}
				}
			}
			return false;
		}
		
		public function postNotificationName($name, $userInfo, $publisher){
			if(count($this->observers) > 0){
				foreach($this->observers as $observer){
					if($observer->publisher() !== null){
						if($observer->notificationName() === $name && $observer->publisher() === $publisher){
							$observer->observer()->{$observer->notificationName()}($userInfo, $publisher);
						}
					}else{
						if($observer->notificationName() === $name){
							$observer->observer()->{$observer->notificationName()}($userInfo, $publisher);
						}
					}
				}
			}
		}

		public function __clone(){
			trigger_error('Clone is not allowed.', E_USER_ERROR);
		}
	}
?>