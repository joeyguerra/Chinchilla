<?php

class DefaultServicePlugin{
		public function __construct(){}
		public function __destruct(){}
		public function execute($command){
			$class_name = get_class($command);
			switch($class_name){
				case('IntroductionCommand'):
					$command = new DefaultIntroductionCommand($command);
					break;
				default:
					throw new Exception("Can't handle $class_name");
					break;
			}
			return $command->execute();
		}
}

class DefaultIntroductionCommand{
	public function __construct($command){
		$this->command = $command;
	}
	public function __destruct(){}
	private $command;
	public function execute(){
		$site_path = String::replace('/\/$/', '', $this->command->sender->url);
		$data = sprintf("email=%s&name=%s&url=%s&created=%s", urlencode($this->command->sender->email), urlencode($this->command->sender->name),  urlencode(str_replace('http://', '', $site_path)), urlencode(date('c')));
		error_log($data);
		$response = NotificationResource::sendNotification($this->command->target, 'followers', $data, 'post');
		return $response;
	}
}