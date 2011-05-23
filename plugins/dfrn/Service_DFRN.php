<?php

class Service_DFRN{
	public function __construct(){}
	public function __destruct(){}
	
	public function canHandle(ServiceProviderCommand $command){
		return strpos($command->getTargetUrl(), 'dfrn_') !== false;
	}
	public function execute(ServiceProviderCommand $command){
		switch(get_class($command)){
			case('IntroductionCommand'):
				$command = new DFRNIntroductionCommand($command);
				break;
			default:
				throw new Exception('Unable to handle that command.');
				break;
		}
		return $command->execute();
	}
}


class DFRNIntroductionCommand{
	public function __construct(IntroductionCommand $command){
		$this->command = $command;
	}
	public function __destruct(){}
	private $command;
	
	public function execute(){
		$dfrn_url = App::url_for('profile');
		$data = sprintf("dfrn_url=%s", (str_replace('http://', '', $dfrn_url)));
		$response = NotificationResource::sendNotification($this->command->target, null, $data, 'post');
		$matches = null;
		if(preg_match('/\<div id="sysmsg".*"[^\<]\>(.?)\<\\div\>/', $response->output, $matches)){
			$response = $matches[0];
		}
		return $response;
	}
}