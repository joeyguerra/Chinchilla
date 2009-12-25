<?php
	class_exists('TestTemplate') || require('lib/TestTemplate.php');
	class_exists('FrontController') || require('lib/FrontController.php');
	class_exists('IndexResource') || require('resources/IndexResource.php');
    class FrontControllerTest extends TestTemplate {
        public function __construct() {
            parent::__construct();
			$this->title = 'FrontController tests';

        }
		public function __destruct(){}
				
		public function setUp(){}
		public function tearDown(){}
		
		public function testUrlParsing(){
			$segments = explode('/', __FILE__);
			array_pop($segments);
			array_pop($segments);
			$controller = new FrontController();
			$resource = new IndexResource();
			$parts = explode('/', 'index/test/asdf');
			$result = $controller->parseForMessageAndResourceId($resource, $parts);
			$this->assert($result['resource_id'] == 'asdf', 'Resource id should be asdf, testing passing a non int for resource id. resource_id = ' . $result['resource_id'] );
			$this->assert($result['message'] == 'get_index_test', 'Tests multi part routing like get_index_test that maps to the associated method. message = '. $result['message']);
		}
    }
?>
