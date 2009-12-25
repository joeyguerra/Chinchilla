<?php
	class_exists('TestTemplate') || require('lib/TestTemplate.php');
	class_exists('Log') || require('lib/Log.php');
    class LogTest extends TestTemplate {
        public function __construct() {
            parent::__construct();
			$this->title = "Log test";
        }
		public function __destruct(){}
				
		public function setUp(){}
		public function tearDown(){}
		
		public function testWriting(){
			$segments = explode('/', __FILE__);
			array_pop($segments);
			array_pop($segments);
			
			$log = new Log(implode('/', $segments) . '/logs/', 5, false);
			$log->write('test');
			$this->assert(file_exists(date("Ymd") . ".txt"), 'Testing writing to a file from the Log class');
		}
    }
?>
