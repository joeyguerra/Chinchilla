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
		protected $title;
		
		public function execute(){
			$this->setUp();
			$this->runTests();
			$this->tearDown();			
		}
		public function message(){
			$message = '<h1>' . $this->title . '</h1>';
			$message .= sprintf('<dl class="passed"><dt>Passed: %d</dt>', count($this->passed));
			foreach($this->passed as $key=>$value){
				$message .= sprintf('<dd>%d=>%s</dd>', $key, $value);
			}
			$message .= '</dl><dl class="failed">';
			
			$message .= sprintf('<dt>Failed: %d</dt>', count($this->failed));
			foreach($this->failed as $key=>$value){
				$message .= sprintf('<dd>%d=>%s</dd>', $key, $value);
			}
			return $message . '</dl>';
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