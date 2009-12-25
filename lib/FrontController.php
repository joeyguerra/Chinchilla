<?php
class_exists('String') || require('lib/String.php');
class_exists('Object') || require('lib/Object.php');
if(file_exists('AppConfiguration.php')){
	class_exists('AppConfiguration') || require('AppConfiguration.php');	
}
class FrontController extends Object{
	public function __construct(){
		if(class_exists('AppConfiguration')){
			$this->config = new AppConfiguration();
		}else{
			$this->config = null;
		}
		$this->initSitePath();
	}
	public function __destruct(){}
	private $config;
	public static $site_path;

	public static $error_html;
	
	public static function sendRssHeaders(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 04 Oct 2004 10:00:00 GMT');
		header('Content-type: application/rss+xml;charset=UTF-8');
	}
	public static function sendXmlHeaders($type){
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 04 Oct 2004 10:00:00 GMT');
		if($type == null){
			header('Content-type: text/xml;charset=UTF-8');
		}else{
			header('Content-type: application/' . $type . '+xml;charset=UTF-8');
		}
	}
	public static function sendJsonpHeaders(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 04 Oct 2004 10:00:00 GMT');
		header('Content-type: application/javascript;charset=UTF-8');
	}
	public static function sendJavascriptHeaders(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 04 Oct 2004 10:00:00 GMT');
		header('Content-type: text/javascript;charset=UTF-8');
	}
	public static function sendJsonHeaders(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 04 Oct 2004 10:00:00 GMT');
		header('Content-type: application/json;charset=UTF-8');
	}
	public static function themePath(){
		$config = null;
		if(class_exists('AppConfiguration')){
			$config = new AppConfiguration();
		}
		if($config != null){
			return 'themes/' . $config->theme;
		}else{
			return 'themes/default';
		}
	}
	public static function isSecure(){
		return array_key_exists('HTTPS', $_SERVER);
	}
	public static function urlFor($resource = null, $params = null, $make_secure = false){
		$config = (class_exists('AppConfiguration') ? new AppConfiguration(null) : new Object());
        $use_clean_urls = file_exists('.htaccess') || $resource == null;
        $query_string = null;

        if($resource == 'themes'){
			$resource = self::themePath();
            $use_clean_urls = true;
		}
        
        if($resource != null){
            $resource .= '/';
        }        
		$resource_id = 0;
        if($params != null){
			$query_string = array();
			foreach($params as $key=>$val){
				if($key == 'id'){
					$resource_id = $val;
				}else{
					$query_string[] = sprintf('%s=%s', $key, $val);					
				}
			}
		}
        
		$url = '';
        if(!$use_clean_urls){
            $resource = 'index.php?r=' . $resource;
        }
		if($resource_id > 0){
			$resource .= $resource_id;
		}
        if($query_string != null){
			$resource .= ($use_clean_urls ? '?' : '&');
            $resource .= implode('&', $query_string);
        }
		if($make_secure && $config != null && $config->ssl_path != null){
			$url = sprintf('https://%s/%s', $config->ssl_path, $resource);
		}else{
			$site_path = self::$site_path;
			if($make_secure){
				$site_path = str_replace('http:', 'https:', $site_path);
			}else{
				$site_path = str_replace('https:', 'http:', $site_path);
			}
			
			$url = $site_path . $resource;
		}
		return $url;
	}
	private function initSitePath(){
        $is_secure = self::isSecure();
		if($this->config != null && $this->config->site_path != null){
			self::$site_path = sprintf('%s://%s/', ($is_secure ? 'https' : 'http'), $this->config->site_path);
		}else{
			$segments = explode('/', $_SERVER['SCRIPT_NAME']);
			$virtual_path = null;
			array_shift($segments);
			array_pop($segments);
			if(count($segments) > 0){
				$virtual_path = implode('/', $segments);
			}
			self::$site_path = sprintf('%s://%s%s/', ($is_secure ? 'https' : 'http'), $_SERVER['SERVER_NAME'], ($virtual_path != null ? '/'.$virtual_path : null));
		}
	}
	
	// Assumes that the first part of the url is the resource name.
	public function parseForResourceName($default_value, $parts){
		$r = $default_value;
		// This logic just sets the resource from the url, assuming that the resource name is the first item
		// in the array.
		if(count($parts) > 0){
			$r = array_shift($parts);
		}
		if(stripos($r, '.') !== false){
			$extension = expode('.', $r);
			$r = $extension[0];
		}
		return $r;
	}
	public function parseForFileType($parts){
		$file_type = 'html';
		if($parts != null && count($parts) > 0){
			$last_item = $parts[count($parts) - 1];
			if(stripos($last_item, '.') !== false){
				$extension = explode('.', $last_item);
				$file_type = $extension[count($extension) - 1];
			}
		}
		if(!in_array($file_type, array('html', 'json', 'xml', 'js', 'atom', 'rss'))){
			$file_type = 'html';
		}
		return $file_type;
	}
	public function parseForMessageAndResourceId($resource, $parts){
		$message = strtolower($_SERVER['REQUEST_METHOD']);
		$resource_id = 0;
		// During development, I was getting weird behavior on a login page posting to a secure server from
		// an insecure request. So I added this message because the request method turned out to be options. 
		// I have no idea why it was options.
		if($message == 'options'){
			error_log('method is options. You might want to check that you are loading the page via ssl');
		}
		// This is for browsers that don't support other methods like delete, put, trace, options.
		$_method = (array_key_exists('_method', $_POST) ? strtolower($_POST['_method']) : null);
		if($_method != null){
			$message = $_method;
		}
		$class_name = get_class($resource);
		if($parts != null && count($parts) > 0){
			$message .= '_' . $parts[0];
		}else{
			$message .= '_' . 'index';
		}
		if(count($parts) > 0){
			$resource_id = $parts[count($parts) - 1];
		}

		if($parts != null && count($parts) > 1){
			$reflector = new ReflectionClass($class_name);
			$ubounds = count($parts);
			$temp_message = $message;
			// This is an example where tests unveiled a possible problem. I had set $index = 0, but when I created and ran tests for this method, it exposed a bug where the first item in parts was the resource and shouldn't be part of the message.
			for($index=1;$index<$ubounds; $index++){
				$temp_message .= '_' . $parts[$index];
				if($reflector->hasMethod($temp_message)){
					$message = $temp_message;
					if($ubounds > $index){
						$resource_id = $parts[$ubounds-1];
					}
					break;
				}
			}
		}
		return array('message'=>$message, 'resource_id'=>$resource_id);
	}

	public function execute(){		
 		session_start();
		
		$resource_path = 'resources/';
		$r = (array_key_exists('r', $_GET) ? $_GET['r'] : null);
		if($r == null){
			$r = 'index';
		}
		$parts = explode('/', $r);
		// $parts contains empty items. I want to remove those items.
		$parts = array_filter($parts, array($this, 'isEmpty'));
		$r = $this->parseForResourceName($r, $parts);
		$file_type = $this->parseForFileType($parts);
		$resource_name = String::camelize($r);
		$class_name = sprintf('%sResource', $resource_name);
		$file = $resource_path . $class_name . '.php';
		// Pass all versions of the controller name to the controller. See if it's pluralized first.
		if(!file_exists($file)){
			$singular_version = sprintf('%sResource', String::singularize($resource_name));
			$file = $resource_path . $singular_version . '.php';
			if(file_exists($file)){
				$class_name = $singular_version;
			}
		}
		if(file_exists($file)){
			class_exists($class_name) || require($file);
			try{
				$obj = new $class_name();		
				$obj->file_type = $file_type;
				$result = $this->parseForMessageAndResourceId($obj, $parts);
				$method = $result['message'];
				$resource_id = $result['resource_id'];
				if(!ob_start('ob_gzhandler')===false){
					ob_start();
				}

				try{
					$output = Resource::sendMessage($obj, $method, $resource_id);
				}catch(Exception $e){
					self::notify('exceptionHasOccured', $this, $e);
				}
				if($obj->redirect_parameters != null){
					self::redirectTo($obj->redirect_parameters['resource_name'], $obj->redirect_parameters['query_parameters'], $obj->redirect_parameters['make_secure']);
				}else{
					Resource::sendMessage($obj, 'didFinishLoading');			
				}
			}catch(Exception $e){
				if($e->getCode() == 401){
					self::notify('unauthorizedRequestHasOccurred', $this, array('file_type'=>$file_type, 'query_string'=>$_SERVER['QUERY_STRING']));
				}
			}
			ob_end_flush();
			$output = $this->trim($output);
			switch($file_type){
				case('js'):
					self::sendJavascriptHeaders();
					break;
				case('jsonp'):
					self::sendJsonpHeaders();
					break;
				case('json'):
					self::sendJsonHeaders();
					break;
				case('xml'):
					self::sendXmlHeaders(null);
					break;
				case('rss'):
					self::sendXmlHeaders('rss');
					break;
				case('atom'):
					self::sendXmlHeaders('atom');
					break;
				default:
					break;
			}
			return $output;
		}else{
			// Send a 404 notification so that something else can handle it.
			self::notify('resourceOrMethodNotFoundDidOccur', $this, array('file_type'=>$file_type, 'query_string'=>$_SERVER['QUERY_STRING']));
			//throw new Exception('404: Not found - '. $_SERVER['QUERY_STRING'], 404);
		}
	}
	private function trim($text){
		$lines = preg_split('/\n/', $text);
		$upper_bounds = count($lines);
		$temp = '';
		for($i=0; $i < $upper_bounds; $i++){
			$temp = trim($lines[$i]);
			if(!empty($temp)){
				$lines[$i] = $temp;
			}
		}
		$text = join(chr(10), $lines);
		return $text;
	}
	public function isEmpty($value){
		return ($value != null || strlen(trim($value)) > 0);
	}
	
	public static function setNeedsToRedirectToPrevious($callback = null){
		$referer = (array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : null);
		$appendValue = null;
		if($callback != null){
			$appendValue = (is_array($callback) ? $callback[0]->$callback[1]($referer) : $callback($referer));				
		}
		self::setNeedsToRedirectRaw($referer . $appendValue, false);
	}
	public static function setNeedsToRedirectRaw($url){
		header(sprintf('Location: %s', $url));
	}

	public static function redirectTo($url, $params = null, $securely = false){
		self::setNeedsToRedirectRaw(self::urlFor($url, $params, $securely));
	}
	
	public static function requestedUrl(){
		if(array_key_exists('requested_url', $_SESSION)){
			return $_SESSION['requested_url'];
		}else{
			return null;
		}
	}
	public static function setRequestedUrl($value){
		$_SESSION['requested_url'] = $value;
	}
	
	public function errorDidHappen($code, $message, $file, $line, $context){
		$contents = file_get_contents($file, FILE_TEXT);
		$lines = preg_split('/\\n/', $contents);
		self::$error_html = '<code class="error">';
		self::$error_html .= sprintf('<h3>Error code %s: %s</h3>', $code, $message);		
		self::$error_html .= '<ul>';
		foreach(debug_backtrace() as $key=>$value){
			self::$error_html .= sprintf('<li>%d: %s', $key, $value['class']);
			self::$error_html .= sprintf('::%s in %s at line # %d', $value['function'], $value['file'], $value['line']);
			self::$error_html .= '</li>';
		}
		self::$error_html .= '</ul>';
		self::$error_html .= sprintf("<pre>%s</pre>", htmlentities($lines[$line-1]));
		self::$error_html .= '</code>';
		self::notify('errorDidHappen', $this, self::$error_html);
		self::$error_html = null;
		// Make sure this line is commented out in prod because if an error occurs in the database
		// code, it'll display your user name and password.
		//debug_print_backtrace();
	}
	public function exceptionDidHappen($e){
		echo $e;
	}
}
?>