<?php
class Logger{
	public function request_was_made($sender, $info){
		error_log(sprintf("%s::%s", $info->method, $info->resource_name));
	}
}

class App{
	public static $root_path = null;
	public static $domain;
	public static function url_for($file, $data = null){
        $url = (array_key_exists("REDIRECT_HTTPS", $_SERVER) && $_SERVER["REDIRECT_HTTPS"] === "on" ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . self::virtual_path() . $file;
		if($data !== null){
			$url .= "?";
			$params = array();
			foreach($data as $key=>$value){
				$params[] = "$key=$value";
			}
			$url .= implode("&", $params);
		}
		return $url;
	}
	public static function url_for_theme($file){
		return self::url_for("themes/" . Settings::$theme . "/$file");
	}
	public static function dirname(){
		return dirname(__FILE__);
	}
	public static function virtual_path(){
		$path = str_replace('index.php', '', $_SERVER["SCRIPT_NAME"]);
		$path = NotificationCenter::post("getting_virtual_path", null, $path);
		return $path;
	}
	public static function add_user_message($message){
		if(!array_key_exists("user_message", $_COOKIE)){
			App::set_user_message($message);			
		}else{
			$_COOKIE["user_message"] .= $message;
		}
	}
	public static function set_user_message($message){
		setcookie("user_message", $message, time() + 1, "/", App::$domain, false, true);
		$_COOKIE["user_message"] = $message;
	}
	public static function get_user_message(){
		if(!array_key_exists("user_message", $_COOKIE)) return null;
		return $_COOKIE["user_message"];
	}
	public static function get_theme_path($file_path = null){
		return self::get_root_path('themes/' . Settings::$theme . '/' . $file_path);
	}
	public static function get_root_path($file_path = null){
        if(self::$root_path == null){
            self::$root_path = str_replace("index.php","", $_SERVER["SCRIPT_FILENAME"]);
        }
        return self::$root_path . $file_path;
    }
}
App::$domain = $_SERVER["SERVER_NAME"];
class FrontController{
	public static function execute($request){
		NotificationCenter::post("begin_request", null, $request);
		$resource = Resource::get_instance($request->resource_name);
		if($resource === null){
			$resource = NotificationCenter::post("resource_not_found", null, $request);
		}
		if($resource === null){
			$resource = new Resource();
			$resource->set_not_found();
		}
		$resource->output = $resource->execute($request);		
		if($resource->status->code === 404){
			$resource->output = NotificationCenter::post("file_not_found", $resource, $request);
		}
		$resource->status->send();
		$resource->header->send();
		NotificationCenter::post("end_request", null, $request);
		return String::strip_whitespace($resource->output);
	}
	public static function not_found(){
		// Server protocol necessary for sending HTTP status codes like 404.
		header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true,  404);
		header("Status: 404 Not Found", true, 404);
	}

}

class NotificationCenter{
	private static $observers = array();
	
	public static function add($observer, $notification){
		self::$observers[$notification][] = (object)array("observer"=>$observer, "notification"=>$notification);
	}
	
	public static function post($name, $sender, $info){
		$output = $info;
		if(array_key_exists($name, self::$observers)){
			foreach(self::$observers[$name] as $n){
				if(is_string($n->observer)){
					$output = call_user_func_array(array($n->observer, $n->notification), array($sender, $info));
				}else{
					$output = $n->observer->{$n->notification}($sender, $info);
				}
			}
		}
		return $output;
	}
}

class DefaultPopulationStrategy{
	public function __construct($param, $request){
		$this->param = $param;
		$this->request = $request;
	}
	private $param;
	private $request;
	public function populate(){
		$key = $this->param->getName();
		if($key === "files"){
			$value = $this->request->files[$key];
			return $value;
		}
		if(array_key_exists($key, $this->request->params)){
			$value = $this->request->params[$key];
			if($value === "true") $value = true;
			if($value === "false") $value = false;
			return $value;
		}
		if(count($this->request->path) > 0){
			$value = urldecode($this->request->path[0]);
			return $value;
		}
		return null;
	}
}

class ObjectPopulationStrategy{
	public function __construct($param, $request, $class){
		$this->param = $param;
		$this->request = $request;
		$this->class = $class;
	}
	private $param;
	private $request;
	private $class;
	public function populate(){
		$key = $this->param->getName();
		$arg = null;
		$properties = $this->class->getProperties();
		$arg = null;
		if(array_key_exists($key, $this->request->params) && is_array($this->request->params[$key])){
			foreach($properties as $property){
				$name = $property->getName();
				if($property->isPublic() && array_key_exists($name, $this->request->params[$key])){
					if($arg === null) $arg = $this->class->newInstance();
					$value = $this->request->params[$key][$name];
					if($value === "true") $value = true;
					if($value === "false") $value = false;
					$property->setValue($arg, $value);
				}
			}
		}else{
			foreach($properties as $property){
				$name = $property->getName();
				if(array_key_exists($name, $this->request->params)){
					$value = $this->request->params[$name];
					if($value === "true") $value = true;
					if($value === "false") $value = false;
					if($arg === null) $arg = $this->class->newInstance();
					$property->setValue($arg, $value);
				}
			}
		}
		return $arg;
	}
}

class PopulationStrategy{
	public function __construct($param, $request){
		$this->strategy = new DefaultPopulationStrategy($param, $request);
		$class = $param->getClass();
		if($class !== null){
			$this->strategy = new ObjectPopulationStrategy($param, $request, $class);
		}
	}
	private $strategy;
	public function populate(){
		return $this->strategy->populate();
	}
}
class MessageStrategy{
	public function __construct($message, $subject, Contact $contact, Member $from){
		$this->strategy = new DefaultMessageStrategy($message, $subject, $contact, $from);
		if(($contact->url === null || strlen($contact->url) === 0) && $contact->email !== null){
			$this->strategy = new EmailMessageStrategy($message, $subject, $contact, $from);
		}
	}
	private $strategy;
	public $error;
	public function send(){
		$did_send = $this->strategy->send();
		$this->error = $this->strategy->error;
		return $did_send;
	}
}
abstract class SendMessageStrategy{
	public function __construct($message, $subject, Contact $contact, Member $from){
		$this->message = $message;
		$this->contact = $contact;
		$this->subject = $subject;
		$this->from = $from;
	}
	protected $message;
	protected $contact;
	protected $subject;
	protected $from;
	public $error;
}
class EmailMessageStrategy extends SendMessageStrategy{
	public function __construct($message, $subject, Contact $contact, Member $from){
		parent::__construct($message, $subject, $contact, $from);
		class_exists("PHPMailer") || require("lib/phpmailer/class.phpmailer.php");
		$this->mailer = new PHPMailer();
	}
	public function send(){
		$this->mailer->AddReplyTo($this->from->email, $this->from->name);
		$this->mailer->SetFrom($this->from->email, $this->from->name);
		$this->mailer->AddAddress($this->contact->email, $this->contact->name);
		$this->mailer->Subject = $this->subject;
		$this->mailer->MsgHTML($this->message);
		$did_send = $this->mailer->Send();
		$this->error = strlen($this->mailer->ErrorInfo) > 0 ? $this->mailer->ErrorInfo : null;
		return $did_send;
	}
}
class DefaultMessageStrategy extends SendMessageStrategy{
	public function __construct($message, $subject, Contact $contact, Member $from){
		parent::__construct($message, $subject, $contact, $from);
	}
	public function send(){
		$this->contact->url = trim($this->contact->url);
		if(strlen($this->contact->url) === 0) throw new Exception("The contact's website address is empty.");
		$this->message = urlencode($this->message);
		return Request::send_asynch(new HttpRequest(array("url"=>"http://" . $this->contact->url . "/inbox", "method"=>"post", "data"=>"message=$this->message&subject=$this->subject&sender=" . AuthController::$current_user->name . "@" . App::$domain, null)));
	}
}
class Resource{
	public function __construct(){
		$this->title = 'A RESTful Chinchilla website';
		$class_name = get_class($this);
		$this->resource_name = str_replace("resource", "", strtolower($class_name));
		self::$reflector = new ReflectionClass($class_name);		
		$this->css = $this->get_resource_css($this->resource_name . ".css");
		$this->js = $this->get_resource_js($this->resource_name . ".js");
	}
	public function __destruct(){}
	private static $reflector;
	public $css;
	public $js;
	public $header;
	public $output;
	public $request;
	public $title;
	public $resource_name;
	public $status;
	public $keywords;
	public $description;
	protected function get_resource_css($file_name){
		$output = null;
		if(file_exists(App::get_theme_path('css/' . $file_name))){
			$output = App::url_for_theme('css/' . $file_name);
			$output = $this->to_link_tag('stylesheet', 'text/css', $output, 'screen,projection');
		}elseif(file_exists(App::get_root_path('css/' . $file_name))){
			$output = App::url_for('css/'. $file_name);
			$output = $this->to_link_tag('stylesheet', 'text/css', $output, 'screen,projection');
		}
		return $output;
	}
	protected function get_resource_js($file_name){
		$output = null;
		if(file_exists(App::get_theme_path('js/' . $file_name))){
			$output = App::url_for_theme('js/' . $file_name);
			$output = $this->to_script_tag('text/javascript', $output);
		}elseif(file_exists(App::get_root_path('/js/' . $file_name))){
			$output = App::url_for('js/' . $file_name);
			$output = $this->to_script_tag('text/javascript', $output);
		}
		return $output;
	}
	public function to_link_tag($rel, $type, $url, $media){
		return sprintf('<link rel="%s" type="%s" href="%s" media="%s" />', $rel, $type, $url, $media);
	}
	public function to_script_tag($type, $url){
		return sprintf('<script type="%s" src="%s"></script>', $type, $url);
	}
	
	public function set_redirect_to($resource_name){
		$this->status = new HttpStatus(303);
		$this->header = new HttpHeader(array("location"=>App::url_for($resource_name), "file_type"=>$this->request->file_type));
	}
	public function set_not_found($message = null){
		$this->status = new HttpStatus(404);
	}
	public function set_unauthed($message = null){
		$this->status = new HttpStatus(401);
	}
	public function execute($request){
		$this->request = $request;
		NotificationCenter::post("request_was_made", $this, $request);
		$result = "";
		if($this->status == null){
			$result = $this->send_message($request);
		}else{
			$result = $this->status->message;
		}
		NotificationCenter::post("request_has_finished", $this, $result);
		if($this->header === null){
			$this->header = new HttpHeader(array("file_type"=>$request->file_type));		
		}
		if($this->status === null){
			$this->status = new HttpStatus(200);
		}
		return $result;
	}
	private function send_message($request){
		$method = self::$reflector->getMethod($request->method);
		if($method === null || !$method->isPublic()) return null;
		$parameters = $method->getParameters();
		$args = array();
		$ubounds = count($parameters);
		for($i = 0; $i < $ubounds; $i++){
			$param = $parameters[$i];
			$strategy = new PopulationStrategy($param, $request);
			$arg = $strategy->populate();
			if($arg === null && $param->isOptional() && $param->isDefaultValueAvailable()){
				$args[] = $param->getDefaultValue();
				continue;
			}
			$args[] = $arg;
		}
		return $method->invokeArgs($this, $args);
	}
	public function get_output(){
		return $this->output;
	}
	public static function get_instance($name){
		$template = '%sResource';
		$class_name = sprintf($template, ucfirst($name));
		$file_name = sprintf(App::dirname() . '/resources/%s.php', $class_name);
		if(class_exists($class_name)) return new $class_name();
		if(!file_exists($file_name)){
			// Look at the root level for resources as a fallback.
			$file_name = str_replace("app/", "", $file_name);
			if(!file_exists($file_name)) return null;
		}
		require($file_name);
		return new $class_name();
	}
	
}

class HttpStatus{
	public function __construct($code, $realm = 'chinchilla'){
		$this->code = $code;
		$this->realm = $realm;
		switch($code){
			case(200):
				$this->message = 'OK';
				break;
			case(201):
				$this->message = 'Created';
				break;
			case(202):
				$this->message = 'Accepted';
				break;
			case(203):
				$this->message = 'Non-Authoritative Information';
				break;
			case(204):
				$this->message = 'No Content';
				break;
			case(205):
				$this->message = 'Reset Content';
				break;
			case(206):
				$this->message = 'Partial Content';
				break;
			case(301):
				$this->message = 'Moved Permanently';
				break;
			case(302):
				$this->message = 'Found';
				break;
			case(303):
				$this->message = 'See Other';
				break;
			case(304):
				$this->message = 'Not Modified';
				break;
			case(305):
				$this->message = 'Use Proxy';
				break;
			case(307):
				$this->message = 'Temporary Redirect';
				break;
			case(400):
				$this->message = 'Bad Request';
				break;
			case(401):
				$this->message = 'Unauthorized';
				break;
			case(402):
				$this->message = 'Payment Required';
				break;
			case(403):
				$this->message = 'Forbidden';
				break;
			case(404):
				$this->message = 'Not Found';
				break;
			case(405):
				$this->message = 'Method Not Allowed';
				break;
			case(406):
				$this->message = 'Not Acceptable';
				break;
			case(407):
				$this->message = 'Proxy Authentication Required';
				break;
			case(408):
				$this->message = 'Request Timeout';
				break;
			case(409):
				$this->message = 'Conflict';
				break;
			case(410):
				$this->message = 'Gone';
				break;
			case(411):
				$this->message = 'Length Required';
				break;
			case(412):
				$this->message = 'Precondition Failed';
				break;
			case(413):
				$this->message = 'Request Entity Too Large';
				break;
			case(414):
				$this->message = 'Request Entity Too Long';
				break;
			case(415):
				$this->message = 'Unsupported Media Type';
				break;
			case(416):
				$this->message = 'Requested Range Not Satisfiable';
				break;
			case(417):
				$this->message = 'Expectation Failed';
				break;
			case(500):
				$this->message = 'Internal Server Error';
				break;
			case(501):
				$this->message = 'Not Implemented';
				break;
			case(502):
				$this->message = 'Bad Gateway';
				break;
			case(503):
				$this->message = 'Service Unavailable';
				break;
			case(504):
				$this->message = 'Gateway Timeout';
				break;
			case(505):
				$this->message = 'HTTP Version Not Supported';
				break;
		}
	}
	public $code;
	public $message;
	public function send(){
		header($_SERVER["SERVER_PROTOCOL"].' ' . $this->code . ' ' . $this->message, true,  $this->code);
		if($this->code === 401){
			header('WWW-Authenticate: Digest realm="'.$this->realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5($this->realm).'"');
		}
		if($this->code === 404){
			header('Status: 404 Not Found');
		}
	}
}

class HttpHeader{
	public function __construct($attributes){
		foreach($attributes as $key=>$value){
			$this->{$key} = $value;
		}
	}
	public function __destruct(){}
	public $file_type;
	public $cache_control;
	public $expires;
	public $location;
	public $content_location;
	public $content_length;
	public function get_content_type(){
		$content_type = null;
		switch($this->file_type){
			case('html'):
				$content_type = 'text/html;charset=UTF-8';
				break;
			case('json'):
				$content_type = 'application/json;charset=UTF-8';
				break;
			case('xml'):
				$content_type = 'text/xml;charset=UTF-8';
				break;
		}
		return $content_type;
	}
	public function send(){
		if($this->get_content_type() !== null){
			$this->send_header('Content-Type', $this->get_content_type());
		}
		if($this->cache_control !== null){
			$this->send_header('Cache-Control', $this->cache_control);
		}
		if($this->expires !== null){
			$this->send_header('Expires', $this->expires);
		}
		if($this->location !== null){
			$this->send_header('Location', $this->location);
		}
		if($this->content_location !== null){
			$this->send_header('Content-Location', $this->content_location);
		}
		if($this->content_length !== null){
			$this->send_header('Content-Length', $this->content_length);
		}
	}
	private function send_header($key, $value){
		header(sprintf("%s: %s", $key, $value));
	}
}
class HttpRequest{
	public function __construct($attributes = null){
		foreach($attributes as $key=>$value){
			$this->$key = $value;
		}
	}
	public $data;
	public $optional_headers;
	public $method;
	public $url;
}
class Request{
	public function __construct($request_array, $files_array, $server_array){
		$this->files = $files_array;
		$name = array_key_exists('r', $request_array) ? $request_array['r'] : 'index';
		if(strlen($name) === 0) $name = 'index';
		$name = preg_replace('/\/$/', '', $name);
		$pieces = explode('.', $name);
		$this->resource_name = $pieces[0];
		if(strpos($this->resource_name, '/') > 0){
			$path = explode('/', $this->resource_name);
			$this->resource_name = array_shift($path);
			$this->path = $path;
		}
		$this->file_type = count($pieces) === 1 ? 'html' : $pieces[1];
		$this->method = array_key_exists('_method', $request_array) ? $request_array['_method'] : $server_array['REQUEST_METHOD'];
		$this->method = strtolower($this->method);
		$this->params = $request_array;
	}
	public $files;
	public $path;
	public $file_type;
	public $resource_name;
	public $method;
	public $params;
	public static function handle_error($number, $message, $file, $line){
		printf("%d:%s, %s, %d<br />", $number, $message, $file, $line);
		error_log(sprintf("%d:%s, %s, %d<br />", $number, $message, $file, $line));
	}
	
	public static function send_asynch(HttpRequest $request) {
		set_error_handler(array("Request", "handle_error"));
		$params = array("http" => array(
			"method" => strtoupper($request->method)
			, "content"=> $request->data
			, "header"=>""
		));
		if ($request->optional_headers !== null) {
			$params["http"]["header"] = $request->optional_headers;
		}
		if($request->method === "post"){
			$params["http"]["header"] .= "Content-Type:application/x-www-form-urlencoded\r\n";
		}
		$ctx = stream_context_create($params);
		$fp = @fopen($request->url, "rb", false, $ctx);
		if (!$fp) return false;
		$response = true;
		fclose($fp);
		restore_error_handler();
		return $response;
	}
	public static function send(HttpRequest $request){
		set_error_handler(array("Request", "handle_error"));
		$params = array("http" => array(
			"method" => strtoupper($request->method)
			, "content"=> $request->data
			, "header"=>""
		));
		if ($request->optional_headers !== null) {
			$params["http"]["header"] = $request->optional_headers;
		}
		if($request->method === "post"){
			$params["http"]["header"] .= "Content-Type:application/x-www-form-urlencoded\r\n";
		}
		$ctx = stream_context_create($params);
		$fp = @fopen($request->url, "rb", false, $ctx);
		if (!$fp) return false;
		$response = stream_get_contents($fp);
		fclose($fp);
		restore_error_handler();
		return $response;
	}
}

class View{
	private static function render_from_file($file_name, $resource){
		extract(get_object_vars($resource));
		ob_start();
		if(file_exists($file_name)){
			require($file_name);
		}else{
			switch($resource->request->file_type){
				case("json"):
					echo json_encode($resource);
					break;
				case("phtml"):
					$file_name = str_replace('_phtml', '_html', $file_name);
					require $file_name;
					break;
				default:
					throw new Exception("$file_name doesn't exist.");
					break;
			}
		}
		return ob_get_clean();
	}
	public static function render_absolute($path, $resource, $file_type = null){
		$view_name = sprintf("%s_%s.php", $path, $file_type !== null ? $file_type : $resource->request->file_type);
		return self::render_from_file($view_name, $resource);
	}
	private static function get_phtml_file_name($file_name, $theme_view_path, $root_view_path){
		if(file_exists($theme_view_path)){
			return $theme_view_path;
		}else if(file_exists($root_view_path)){
			return $root_view_path;
		}else if(file_exists($file_name)){
			return $file_name;
		}
		
		$path = str_replace("_phtml", "_html", $theme_view_path);
		if(file_exists($path)){
			return $path;
		}
		$path = str_replace("_phtml", "_html", $root_view_path);
		if(file_exists($path)){
			return $path;
		}
		$path = str_replace("_phtml", "_html", $file_name);
		if(file_exists($path)){
			return $path;
		}
		return $file_name;
	}
	public static function render($view, $resource, $file_type = null){
		$file_type = $file_type !== null ? $file_type : $resource->request->file_type;
		$view_name = sprintf("%s_%s.php", $view, $file_type);
		$file_name = sprintf(App::dirname() . "/views/%s", $view_name);
		$theme_view_path = sprintf("%s/themes/%s/views/%s", str_replace("/app", "", App::dirname()), Settings::$theme, $view_name);
		$root_view_path = str_replace("app/", "", $file_name);
		if($file_type == "phtml"){
			$file_name = self::get_phtml_file_name($file_name, $theme_view_path, $root_view_path);
		}else if(file_exists($theme_view_path)){
			$file_name = $theme_view_path;
		}else if(file_exists($root_view_path)){
			$file_name = $root_view_path;
		}
		return self::render_from_file($file_name, $resource);
	}
	public static function render_layout($layout, $resource){
		if(!in_array($resource->request->file_type, array("html", "xml", "atom", "rss"))) return $resource->output;
		return self::render(sprintf('layouts/%s', $layout), $resource);
	}
}

class String{
	static $plural = array(
        '/(quiz)$/i'               => "$1zes",
        '/^(ox)$/i'                => "$1en",
        '/([m|l])ouse$/i'          => "$1ice",
        '/(matr|vert|ind)ix|ex$/i' => "$1ices",
        '/(x|ch|ss|sh)$/i'         => "$1es",
        '/([^aeiouy]|qu)y$/i'      => "$1ies",
        '/(hive)$/i'               => "$1s",
        '/(?:([^f])fe|([lr])f)$/i' => "$1$2ves",
        '/(shea|lea|loa|thie)f$/i' => "$1ves",
        '/sis$/i'                  => "ses",
        '/([ti])um$/i'             => "$1a",
        '/(tomat|potat|ech|her|vet)o$/i'=> "$1oes",
        '/(bu)s$/i'                => "$1ses",
        '/(alias)$/i'              => "$1es",
        '/(octop)us$/i'            => "$1i",
        '/(ax|test)is$/i'          => "$1es",
        '/(us)$/i'                 => "$1es",
        '/s$/i'                    => "s",
        '/$/'                      => "s"
    );

    static $singular = array(
        '/(quiz)zes$/i'             => "$1",
        '/(matr)ices$/i'            => "$1ix",
        '/(vert|ind)ices$/i'        => "$1ex",
        '/^(ox)en$/i'               => "$1",
        '/(alias)es$/i'             => "$1",
        '/(octop|vir)i$/i'          => "$1us",
        '/(cris|ax|test)es$/i'      => "$1is",
        '/(shoe)s$/i'               => "$1",
        '/(o)es$/i'                 => "$1",
        '/(bus)es$/i'               => "$1",
        '/([m|l])ice$/i'            => "$1ouse",
        '/(x|ch|ss|sh)es$/i'        => "$1",
        '/(m)ovies$/i'              => "$1ovie",
        '/(s)eries$/i'              => "$1eries",
        '/([^aeiouy]|qu)ies$/i'     => "$1y",
        '/([lr])ves$/i'             => "$1f",
        '/(tive)s$/i'               => "$1",
        '/(hive)s$/i'               => "$1",
        '/(li|wi|kni)ves$/i'        => "$1fe",
        '/(shea|loa|lea|thie)ves$/i'=> "$1f",
        '/(^analy)ses$/i'           => "$1sis",
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i'  => "$1$2sis",
        '/([ti])a$/i'               => "$1um",
        '/(n)ews$/i'                => "$1ews",
        '/(h|bl)ouses$/i'           => "$1ouse",
        '/(corpse)s$/i'             => "$1",
        '/(us)es$/i'                => "$1",
        '/s$/i'                     => ""
    );

    static $irregular = array(
        'move'   => 'moves',
        'foot'   => 'feet',
        'goose'  => 'geese',
        'sex'    => 'sexes',
        'child'  => 'children',
        'man'    => 'men',
        'tooth'  => 'teeth',
        'person' => 'people'
    );

    static $uncountable = array(
        'sheep',
        'fish',
        'deer',
        'series',
        'species',
        'money',
        'rice',
        'information',
        'equipment',
		"media"
    );
	public static function singularize( $string )
	{
	    // save some time in the case that singular and plural are the same
	    if ( in_array( strtolower( $string ), self::$uncountable ) )
	        return $string;

	    // check for irregular plural forms
	    foreach ( self::$irregular as $result => $pattern )
	    {
	        $pattern = '/' . $pattern . '$/i';

	        if ( preg_match( $pattern, $string ) )
	            return preg_replace( $pattern, $result, $string);
	    }

	    // check for matches using regular expressions
	    foreach ( self::$singular as $pattern => $result )
	    {		
	        if ( preg_match( $pattern, $string ) ){
				return preg_replace( $pattern, $result, $string );
			}
	    }

	    return $string;
	}
	public static function pluralize( $string )
    {
        // save some time in the case that singular and plural are the same
        if ( in_array( strtolower( $string ), self::$uncountable ) )
            return $string;

        // check for irregular singular forms
        foreach ( self::$irregular as $pattern => $result )
        {
            $pattern = '/' . $pattern . '$/i';

            if ( preg_match( $pattern, $string ) )
                return self::replace( $pattern, $result, $string);
        }

        // check for matches using regular expressions
        foreach ( self::$plural as $pattern => $result )
        {
            if ( preg_match( $pattern, $string ) )
                return preg_replace( $pattern, $result, $string );
        }

        return $string;
    }
    public static function encrypt($value){
		return sha1($value);
	}
	public static function strip_whitespace($text){
		$lines = preg_split('/\n/', $text);
		$upper_bounds = count($lines);
		$temp = '';
		for($i=0; $i < $upper_bounds; $i++){
			$temp = trim($lines[$i]);
			if($temp !== null && strlen($temp) > 0){
				$lines[$i] = $temp;
			}
		}
		$text = join(chr(10), $lines);
		return $text;
	}
	
	public static function explode_and_trim($csvString){
		$list = explode(',', $csvString);
		foreach($list as $key=>$value){
			$list[$key] = trim($value);
		}
		return $list;
	}
	public static function string_for_url($string){
		$string = strtolower($string);
		$string = preg_replace("`\[.*\]`U","",$string);
		$string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$string);
		$string = htmlentities($string, ENT_COMPAT, 'utf-8');
		$string = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $string );
		$string = preg_replace( array("`[^a-z0-9]`i","`[-]+`") , "-", $string);
		return strtolower(trim($string, '-'));
	}
	
	public static function to_array($csvString){
		$list = self::explode_and_trim($csvString);
		$new_list = array();
		foreach($list as $value){
			list($key, $val) = explode('=', $value);
			$new_list[$key] = $val;
		}
		return $new_list;
	}
}
class PluginController{
	
	public function begin_request($publisher, $request){
		$plugins = glob("plugins/*/index.php");
		$ubounds = count($plugins);
		for($i = 0; $i < $ubounds; $i++) require($plugins[$i]);
	}
}
function error_did_happen($number, $message, $file, $line){
	printf("CHINERROR -  %d:%s, %s, %d<br />", $number, $message, $file, $line);
}

function exception_did_happen($e){
	echo $e->getMessage();
}
