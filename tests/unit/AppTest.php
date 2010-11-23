<?php
	class_exists('App') || require('lib/App.php');
	class_exists('TestTemplate') || require('lib/TestTemplate.php');
    class_exists('Request') || require('lib/Request.php');
    class AppTest extends TestTemplate {
        public function __construct() {
            parent::__construct();
			$this->title = 'App tests';
        }
		public function __destruct(){}
				
		public function setUp(){
                    $_SERVER['SCRIPT_NAME'] = str_replace('tests/', '', $_SERVER['SCRIPT_NAME']);
                    $_SERVER['SCRIPT_FILENAME'] = str_replace('tests/index.php', 'index.php', $_SERVER['SCRIPT_FILENAME']);
                }
		public function tearDown(){}
		
		public function test_url_mapping_to_resource(){
			$urls = array('empty'=>'', 'resource'=>'example', 'resource_with_slash'=>'example/', 'resource_with_id'=>'example/1', 'resource_with_id_and_slash'=>'example/1/', 'resource_with_string_parm'=>'example/contacts');
			foreach($urls as $key=>$value){
				$resource = App::dispatch('get', $value, $_SERVER);
				if($key=='empty'){
					$this->assert($resource !== 'Chinchilla: A RESTful framework in PHP', 'index resource for empty request.');
				}else{
					$this->assert(strpos($resource->output, 'This is an example on how to use Chinchilla, a RESTful framework in PHP') !== false, $key);
				}
			}
		}
		
		public function test_url_mapping_to_resource_with_single_parameter(){
			$urls = array('resource_with_id'=>'index/1', 'resource_with_id_and_slash'=>'index/1/');
			foreach($urls as $key=>$value){
				$resource = App::dispatch('get', $value, $_SERVER);
				$this->assert(strpos($resource->output, '<!--1-->') !== false, $key);
			}
		}
		public function test_url_mapping_to_resource_with_multiple_parameters(){
			$urls = array('resource_with_2_parms'=>'index/1/test', 'resource_with_2_parms_and_slash'=>'index/1/test');
			foreach($urls as $key=>$value){
				$resource = App::dispatch('GET', $value, $_SERVER);
				$this->assert(strpos($resource->output, '<!--1test-->') !== false, $key);
			}
		}
		
		public function test_url_mapping_to_resource_with_date_parameter(){
			$resource = App::dispatch('get', 'test/2010/11/01/some-title', $_SERVER);
			$this->assert(strpos($resource->output, '2010-11-01') !== false, 'Testing sending a date in the URL.');
			$this->assert(strpos($resource->output, 'some-title') !== false, 'The article title should be in the page.');

			$resource = App::dispatch('get', 'test/2010/11/asd/some-title', $_SERVER);
			$this->assert(strpos($resource->output, date('Y-m-d')) !== false, 'Testing sending an invalid date in the URL.');
		}
        public function test_url_for(){
            $url = str_replace('/tests/', '/', App::url_for('test'));
            $this->assert($url === 'http://localhost/chinchilla/test', "Testing url creation. $url");
            $url = str_replace('/tests/', '/', App::url_for('test', array('id'=>1)));
            $this->assert($url === 'http://localhost/chinchilla/test?id=1', $url);
            $url = str_replace('/tests/', '/', App::url_for('test/1', array('id'=>1)));
            $this->assert($url === 'http://localhost/chinchilla/test/1?id=1', $url);
            $url = str_replace('/tests/', '/', App::url_for('test/some-title/'));
            $this->assert($url === 'http://localhost/chinchilla/test/some-title/', $url);
        }
        public function test_post_data_mapped_to_parms(){
            $_REQUEST['id'] = 1;
            $_REQUEST['title'] = 'some-title';
            $resource = App::dispatch('post', 'test', $_SERVER);
            $this->assert(strpos($resource->output,'some-title') !== false, 'Testing posting to a resource. Checking for title sent.');
            $this->assert(strpos($resource->output,'id = 1') !== false, 'Testing posting to a resource. Checking for id = 1.');
        }

		public function test_put_to_resource(){
            $resource = App::dispatch('put', 'test/some-title', $_SERVER);
            $this->assert(strpos($resource->output,'some-title') !== false, 'Testing put call to a resource.');
		}
		public function test_rendering_phtml(){
			$resource = App::dispatch('get', 'test/2010/11/01/some-title.phtml', $_SERVER);
			$this->assert(strpos($resource->output, '<html>') === false, 'Testing rendering phtml.');
		}
		public function test_rendering_json(){
			$resource = App::dispatch('get', 'test/2010/11/01/some-title.json', $_SERVER);
			$expected = new HttpHeader(null);
			while($header = array_shift($resource->headers)){
				if($header->get_content_type() == 'application/json;charset=UTF-8'){
					$expected = $header;
				}
			}
			$this->assert($expected->get_content_type() === 'application/json;charset=UTF-8', 'Content type should be for application/json;charset=UTF-8: ' . $expected->get_content_type());
			$this->assert(strpos($resource->output, '{"date":"2010-11-01"}') !== false, 'Testing rendering json.' . $resource->output);
		}
		public function test_rendering_html(){
			$resource = App::dispatch('get', 'example.html', $_SERVER);
			$expected = new HttpHeader(null);
			while($header = array_shift($resource->headers)){
				if($header->get_content_type() == 'text/html;charset=UTF-8'){
					$expected = $header;
				}
			}
			$this->assert($expected->get_content_type() === 'text/html;charset=UTF-8', 'Content type should be for text/html: ' . $expected->get_content_type());
			$this->assert(strpos($resource->output, 'This is an example on how to use Chinchilla') !== false, 'Testing rendering html.');
		}
		public function test_rendering_xml(){
			
		}
		
    }
?>
