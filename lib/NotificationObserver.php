<?php
	class NotificationObserver{
		private $notificationName;
		public function notificationName(){return $this->notificationName;}

		private $observer;
		public function observer(){return $this->observer;}

		private $publisher;
		public function publisher(){return $this->publisher;}

		public function __construct($observer, $notificationName, $publisher){
			$this->notificationName = $notificationName;
			$this->observer = $observer;
			$this->publisher = $publisher;
		}
	}

?>