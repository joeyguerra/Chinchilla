<?php
	abstract class TestTemplate{
		public function __construct(){
			$this->passed = array();
			$this->failed = array();
		}
		public function __destruct(){}
		
		private $assertions;
		private $passed;
		private $failed;
		
		public function execute(){
			$this->setUp();
			$this->runTests();
			$this->tearDown();			
		}
		public function message(){
			$message = 'Tests have executed:<br />';
			$message .= sprintf('Passed: %d<br />', count($this->passed));
			foreach($this->passed as $key=>$value){
				$message .= sprintf('%d=>%s <br /><br />', $key, $value);
			}
			$message .= sprintf('<br />Failed: %d<br />', count($this->failed));
			foreach($this->failed as $key=>$value){
				$message .= sprintf('%d=>%s <br />', $key, $value);
			}
			return $message;
		}
		private function runTests(){
			$reflector = new ReflectionClass(get_class($this));
			$methods = $reflector->getMethods();
			foreach($methods as $method){
				if($method->isPublic() && stristr($method->getName(), 'test') !== false){
					$this->{$method->getName()}();
				}
			}
		}
		
		protected function assert($condition, $message){
			if($condition){
				$this->passed[] = $message;
			}
			else{
				$this->failed[] = $message;
			}
		}
		abstract public function setUp();
		abstract public function tearDown();
	}
?>