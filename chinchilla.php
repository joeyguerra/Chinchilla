<?php
class output_compressor{
	function end_request($publisher, $output){
		return string::strip_whitespace($output);
	}
}
class front_controller{
	function __construct(){}
	function __destruct(){}
	public $resource;
	function execute($request){
		notification_center::publish("begin_request", $this, $request);
		$finder = new resource_finder();
		$this->resource = $finder->find($request);
		$http_method = $request->server["REQUEST_METHOD"];
		$warning = null;
		if(array_key_exists("_method", $request->request)) $http_method = strtoupper($request->request["_method"]);
		try{
			if(!method_exists($this->resource, $http_method)) $this->resource = filter_center::publish("resource_not_found", $this->resource, $this->resource->resource_name);
			$request->output = $this->send($this->resource, $http_method, $request);			
		}catch(Exception $e){
			$this->resource->status = new http_status(array("code"=>500, "message"=>"Internal error occurred"));
			$warning = $e->getMessage() . "-" . str_replace(PHP_EOL, " ", $e->getTraceAsString());				
		}
		$request->output = filter_center::publish("end_request", $request, $request->output);
		if(view::get_user_message() !== null) $warning .= view::get_user_message();
		if($warning !== null) $this->resource->headers[] = new http_header(array("Warning"=>$warning));
		resource::send_status($this->resource->status);
		if($this->resource->headers !== null){
			foreach($this->resource->headers as $header){
				foreach($header->values as $key=>$value)
				resource::send_header($key, $value);
			}
		}
		resource::send_header("Content-Type", resource::content_type($this->resource->url->file_type));
		return $request->output;
	}
	function send($resource, $method, $request){
		$class = new ReflectionObject($resource);
		if(!$class->hasMethod($method)) throw new not_found_exception($resource, $method, "Method not found");
		$method_info = new ReflectionMethod($resource, $method);
		$parameters = $method_info->getParameters();
		$args = url_argument_parser::parse(url_parser::get_r($request));
		$hash = array();
		if(count($parameters) > 0 && count($args) > 0){
			foreach($parameters as $key=>$parameter){
				if($key > count($args) - 1) break;
				$hash[$parameter->getName()] = $args[$key];
			}
		}
		$args = request_argument_mapper::map($resource, $parameters, $request, $hash);
		notification_center::publish("before_calling_http_method", $resource, $args);
		$result = $method_info->invokeArgs($resource, $args);
		return $result;
	}
}
class not_found_exception extends Exception{
	function __construct($resource, $method, $message){
		$this->message = $message;
		$this->resource = $resource;
		$this->method = $method;
	}
	public $resource;
	public $method;
}
class auth_controller{
	function before_calling_http_method($publisher, $info){
		$secured = array("members", "posts");
		if(in_array($publisher->resource_name, $secured)){
			if(self::get_chin_auth() === null){
				view::set_user_message("Unauthed");
				error_log("unauthed request to $publisher->resource_name");
				resource::redirect("signin");
			}
		}
	}
	private static function create_key($name, $expiry){
		return hash("sha256", $name . $_SERVER["REMOTE_ADDR"] . $expiry, false);
	}
	private static function get_chin_auth(){
		return array_key_exists("chin_auth", $_COOKIE) ? $_COOKIE["chin_auth"] : null;
	}
	private static function set_chin_auth($value, $expire = 0, $path = null, $domain = null, $secure = false, $httponly = true){
		setcookie("chin_auth", $value, $expire, $path, $domain, $secure, $httponly);
		$_COOKIE["chin_auth"] = $value;
	}
	static function signin($signin, $password){
		$member = self::current_user();
		if($member === null) $member = storage::find_members_one(array("where"=>"signin=:signin and password=:password","args"=>array("signin"=>$signin, "password"=>string::password($password))));
		if($member === null) return null;
		self::set_authed($member);
		return $member;
	}
	static function is_authed(){
		return self::get_chin_auth() !== null;
	}
	static function signout(){
		self::set_chin_auth(null, time()-3600);
	}
	static function current_user(){
		$member = storage::find_members_one((object)array("where"=>"hash=:hash", "args"=>array("hash"=>self::get_chin_auth())));
		return $member;
	}
	private static function set_authed($member){
		$expiry = 0;
		$auth_key = self::create_key($member->signin, $expiry);
		self::set_chin_auth($auth_key, $expiry);
		return $auth_key;
	}
}

class magic_quotes_remover{
	function setting_parameter_from_request($publisher, $info){
		if(is_object($info) || is_array($info)) return $info;
		if(function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) return stripslashes($info);
        return $info;
	}
}
class object_populator_from_request{
	function setting_parameter_from_request($param, $info){
		$reflector = $param->getClass();
		if($reflector === null) return $info;
		$obj = $reflector->newInstance(null);
		foreach($info as $key=>$value){
			if(is_object($value)) continue;
			if(is_array($value)) continue;
			if(!$reflector->hasProperty($key)) continue;
			$property = $reflector->getProperty($key);
			if(!$property->isPublic()) continue;
			$obj->{$key} = $value;
		}
		return $obj;
	}
}
class request_argument_mapper{
	static function map($resource, $parameters, $request, $hash){
		foreach($parameters as $param){
			$value = null;
			$name = $param->getName();
			if(array_key_exists($name, $request->request)){
				$value = filter_center::publish("setting_parameter_from_request", $param, $request->request[$name]);
			}
			if($value === null && $param->isDefaultValueAvailable()) $value = $param->getDefaultValue();
			if($value === null) continue;
			$hash[$name] = $value;
		}
		return $hash;
	}
}

class urL_argument_parser{
	static function parse($path){
		$time = url_date_parser::parse($path);
		$args = array();
		$parts = explode("/", preg_replace("/\/$/", "", $path));
		if($time !== null){
			$args[] = new DateTime(date("Y/m/j", $time));
			array_shift($parts);
			array_shift($parts);
			array_shift($parts);
		}
		if(count($parts) > 1){
			// shift off the resource name.
			array_shift($parts);
			foreach($parts as $key=>$part){
				array_push($args, $part);
			}
		}
		return $args;
	}
}
class url_date_parser{
	static function parse($path){
		$time = null;
		$matches = array();
		if(preg_match_all("/\d{4,4}\/\d{1,2}\/\d{1,2}/", $path, $matches) !== false){
			if(count($matches[0]) > 0){
				$time = strtotime($matches[0][0]);
			}
		}
		return $time;
	}
}

class url_parser{
	function parse($request){
		$name = self::get_r($request);
		$date = url_date_parser::parse($name);
		if($date !== null){
			$name = "index";
		}
		$name = filter_center::publish("parsing_url", $this, $name);
		if(strlen($name) === 0) $name = "index";
		$file_type = "html";
		if(strpos($name, ".") !== false){
			$parts = explode(".", $name);
			$file_type = $parts[count($parts)-1];
			$name = str_replace(".$file_type", "", $name);
		}
		if(strpos($name, "/") !== false){
			$parts = explode("/", $name);
			$name = $parts[0];
		}
		return (object)array("resource_name"=>$name, "request"=>$request, "file_type"=>$file_type);
	}
	static function get_r($request){
		return array_key_exists("r", $request->request) && strlen($request->request["r"]) > 0 ? $request->request["r"] : "index";
	}
}
class resource{
	function __construct($request, $url){
		$this->request = $request;
		$this->url = $url;
		$this->resource_name = str_replace("_resource", "", get_class($this));
		$this->css = $this->get_link_markup($this->resource_name);
		$this->js = $this->get_script_markup($this->resource_name);
		$this->status = new http_status(array("code"=>200, "message"=>"Ok"));
		$this->title = settings::site_title();
		$this->description = "Chinchilla, a RESTful library";
		$this->keywords = "restful, rest, php, sqlite, framework, library";
	}
	public $resource_name;
	public $css;
	public $js;
	public $title;
	public $output;
	public $request;
	public $url;
	public $status;
	public $headers;
	public $description;
	public $keywords;
	static function domain(){
		return $_SERVER["SERVER_NAME"] === "localhost" ? null : $_SERVER["SERVER_NAME"];
	}
	static function get_virtual_path($file = null){
		$path = str_replace('index.php', '', $_SERVER["SCRIPT_NAME"]);
		$path = filter_center::publish("getting_virtual_path", null, $path);
		return $path . ($file !== null ? "$file" : null);
	}
	static function get_absolute_path($file = null){
		return dirname(__FILE__) . ($file === null ? null : "/$file");
	}
	static function url_for($path, $data = null){
		$url = (array_key_exists("REDIRECT_HTTPS", $_SERVER) && $_SERVER["REDIRECT_HTTPS"] === "on" ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . self::get_virtual_path() . $path;
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
	function get_link_markup($name){
		$path = "css/$name.css";
		$path = filter_center::publish("should_set_css_path", $this, $path);
		if(!file_exists($path)) return null;
		$path = self::url_for($path);
		return <<<eos
<link rel="stylesheet" href="$path" type="text/css">

eos;
	}
	function get_script_markup($name){
		$path = "js/$name.js";
		$path = filter_center::publish("should_set_js_path", $this, $path);
		if(!file_exists($path)) return null;
		$path = self::url_for($path);
		return <<<eos
<script type="text/javascript" src="$path"></script>

eos;
	}
	static function redirect($url){
		if(strpos($url, "http") === false) $url = self::url_for($url);
		$status = (object)array("code"=>303, "location"=>$url, "message"=>"See $url");
		self::send_status($status);
		die;
	}
	static function send_status($status){
		header($_SERVER["SERVER_PROTOCOL"].' ' . $status->code . ' ' . $status->message, true,  $status->code);
		if($status->location !== null) header("Location: {$status->location}");
		if($status->code === 401){
			header('WWW-Authenticate: Digest realm="'.$status->realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5($status->realm).'"');
		}
		if($status->code === 404){
			header('Status: 404 Not Found');
		}
	}
	static function send_headers($header){
		if($header->values === null) return;
		foreach($header->values as $key=>$value){
			self::send_header($key, $value);			
		}
	}
	static function send_header($key, $value){
		header(sprintf("%s: %s", $key, $value));
	}
	static function content_type($file_type){
		if($file_type === "html") return "text/html;charset=UTF-8";
		if($file_type === "phtml") return "text/html;charset=UTF-8";
		if($file_type === "xml") return "text/xml;charset=UTF-8";
		if($file_type === "json") return "application/json;charset=UTF-8";
		if($file_type === "js") return "application/javascript;charset=UTF-8";
		if($file_type === "manifest") return "text/cache-manifest;charset=UTF-8";
		return "text/plain;charset=UTF-8";
	}
	
}
class http_header{
	function __construct($args = null){
		$this->values = $args;
	}
	public $values;
}
class http_status{
	function __construct($args = null){
		if($args !== null){
			$this->code = array_key_exists("code", $args) ? $args["code"] : null;
			$this->message = array_key_exists("message", $args) ? $args["message"] : null;
			$this->location = array_key_exists("location", $args) ? $args["location"] : null;
		}
	}
	public $realm;
	public $code;
	public $message;
	public $location;
	
}
class resource_finder{
	function __construct(){}
	function __destruct(){}
	public $parser;
	public $url;
	public $request;
	public $resource_name;
	function find($request){
		$this->request = $request;
		$this->parser = new url_parser();
		$this->url = $this->parser->parse($this->request);
		$this->resource_name = $this->url->resource_name . "_resource";
		$file_path = filter_center::publish("before_including_resource_file", $this, "resources/{$this->resource_name}.php");
		if(!file_exists($file_path)){
			$resource = filter_center::publish("resource_not_found", $this, $this->resource_name);
		}else{
			require $file_path;			
			$resource = new $this->resource_name($request, $this->url);
		}
		$file_path = filter_center::publish("after_creating_resource", $this, $resource);
		return $resource;
	}
}
class filter_center{
	private static $observers = array();
	static function publish($name, $publisher, $info){
		if(!array_key_exists($name, self::$observers)) return $info;
		foreach(self::$observers[$name] as $observer){
			$info = $observer->{$name}($publisher, $info);
		}
		return $info;
	}
	static function subscribe($name, $publisher, $subscriber){
		self::$observers[$name][] = $subscriber;
	}
}
class notification_center{
	private static $observers = array();
	static function publish($name, $publisher, $info){
		if(!array_key_exists($name, self::$observers)) return;
		foreach(self::$observers[$name] as $observer){
			$observer->{$name}($publisher, $info);
		}
	}
	static function subscribe($name, $publisher, $subscriber){
		self::$observers[$name][] = $subscriber;
	}
}

class request{
	function __construct($server, $request){
		$this->server = $server;
		$this->request = $request;
		$this->post = $_POST;
		$this->get = $_GET;
		$this->files = $_FILES;
		if(in_array(strtolower($server["REQUEST_METHOD"]), array("put", "delete"))){
			$this->put = $this->map();
			if($this->put !== null){
				foreach($this->put as $k=>$v){
					$this->request[$k] = $v;
				}
			}
		}
	}
	public $files;
	public $put;
	public $post;
	public $get;
	public $server;
	public $request;
	public $output;
	private function map(){
		$body="";
		if(strpos("application/json", $this->server["CONTENT_TYPE"]) === false) return null;
		$stream = fopen("php://input", "r");
		while ($block = fread($stream, 1024)) {
			$body = $body.$block;
		}
		fclose($stream);
		$obj = json_decode($body);
		$properties = get_object_vars($obj);
		foreach($properties as $k=>$v){
			$hash[$k] = $v;
		}
		return $hash;
	}
}
class theme_controller{
	function before_rendering_view($publisher, $info){
		$view = $info;
		$theme = array((object)array("value"=>"default"));
		try{
			$theme = storage::find_settings((object)array("where"=>"key=:key", "args"=>array("key", "theme")));			
		}catch(Exception $e){}
		if(count($theme) > 0) $view = $theme[0]->value . "/" . $info;
		else $view = "themes/default/$info";
		if(file_exists(resource::get_absolute_path($view))) return $view;
		return $info;
	}
	static function url_for($file, $data = null){
		$theme = "default";
		if(array_key_exists("theme", $_COOKIE)) $theme = $_COOKIE["theme"];
		try{
			$setting = storage::find_settings((object)array("where"=>"key=:key", "args"=>array("key", "theme")));
		}catch(Exception $e){}
		if(count($setting) > 0) $theme = $setting[0]->value;
		return resource::url_for("themes/$theme/$file", $data);
	}
}
class plugin_controller{
	static $plugins;
	function begin_request($publisher, $info){
		self::$plugins = array();
		$path = resource::get_absolute_path("plugins/*");
		$folders = glob($path);
		$ubounds = count($folders);
		$file = null;
		for($i = 0; $i < $ubounds; $i++){
			$file = $folders[$i] . "/index.php";
			if(file_exists($file)){
				self::$plugins[] = str_replace("plugins/", "", $folders[$i]);
				require($file);
			}
		}
	}
	function before_rendering_view($publisher, $view){
		$plugin_name = get_class($publisher);
		$result = array_filter(self::$plugins, function($item) use($plugin_name){
			return strpos($item, $plugin_name) !== false;
		});
		if(count($result) === 0) return $view;
		$view = "plugins/$plugin_name/$view";
		return $view;
	}
}

class view{
	static function set_user_message($value){
		$message = $value;
		if(is_array($value)){
			$message = "<ul>";
			foreach($value as $key=>$value){
				if(is_object($value)){
					$message .= "<li>" . json_encode($value) . "</li>";
				}else{
					$message .= "<li>$value</li>";					
				}
			}
			$message .= "</ul>";
		}
		$existing_message = self::get_user_message();
		if($existing_message !== null){
			$message .= $existing_message;
		}
		setcookie("user_message", $message, time() + 1, "/", resource::domain(), false, true);
		$_COOKIE["user_message"] = $message;
	}
	static function get_user_message(){
		if(!array_key_exists("user_message", $_COOKIE)) return null;
		return $_COOKIE["user_message"];
	}
	static function render($view, $resource, $args = null){
		$view = "views/$view." . $resource->url->file_type;
		ob_start();
		extract(get_object_vars($resource));
		if($args !== null) extract($args);
		$output = $resource->output;
		$view = filter_center::publish("before_rendering_view", $resource, $view);
		if($resource->url->file_type === "phtml"){
			if(!file_exists($view)){
				$view = str_replace(".phtml", ".html", $view);
			}
		}
		require($view);
		$output = ob_get_clean();
		$output = filter_center::publish("after_rendering_view", $resource, $output);
		return $output;
	}
}
class setting{
	function __construct($args = null){
		if($args !== null){
			foreach($args as $key=>$value){
				$this->$key = $value;
			}
		}
	}
	public $key;
	public $value;
	public $owner_id;
}
class settings{
	static function site_title(){
		$default = array(new setting(array("key"=>"site_title", "value"=>"Chinchilla, a RESTful framework")));
		try{
			$settings = storage::find_settings((object)array("where"=>"key=:key", "args"=>array("key"=>"site_title")));			
		}catch(Exception $e){}
		if(count($settings) === 0) $settings = $default;
		return $settings[0]->value;
	}
}
class layout{
	static function render($layout, $resource, $args = null){
		$view = "layouts/$layout." . $resource->url->file_type;
		ob_start();
		$view = filter_center::publish("before_rendering_layout", null, $view);
		extract(get_object_vars($resource));
		if($args !== null) extract($args);
		$output = $resource->output;
		if(file_exists($view)){
			require($view);
		}else{
			echo $output;
		}
		$output = ob_get_clean();		
		$output = filter_center::publish("after_rendering_layout", null, $output);
		return $output;
	}
}
class string{		
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
        'equipment'
    );
    static function pluralize( $string )
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
                return self::replace( $pattern, $result, $string );
        }

        return $string;
    }

    static function singularize( $string )
    {
        // save some time in the case that singular and plural are the same
        if ( in_array( strtolower( $string ), self::$uncountable ) )
            return $string;

        // check for irregular plural forms
        foreach ( self::$irregular as $result => $pattern )
        {
            $pattern = '/' . $pattern . '$/i';

            if ( preg_match( $pattern, $string ) )
                return self::replace( $pattern, $result, $string);
        }

        // check for matches using regular expressions
        foreach ( self::$singular as $pattern => $result )
        {		
            if ( preg_match( $pattern, $string ) ){
				return self::replace( $pattern, $result, $string );
			}
        }

        return $string;
    }

    static function pluralize_if($count, $string)
    {
        if ($count == 1)
            return "1 $string";
        else
            return $count . " " . self::pluralize($string);
    }
	static function get_conjunctions(){
		return array('an', 'and', 'but', 'or', 'nor', 'so', 'yet', 'when', 'for', 'after', 'although', 'as', 'because', 'before', 'how', 'if', 'once', 'since', 'than', 'though', 'till', 'until', 'where', 'whether', 'while', 'either', 'not', 'only', 'also', 'the', 'thats', 'that', "that's", 'that is', 'that was'); 
	}
	static function get_prepositions(){
		return array('about', 'above', 'across', 'after', 'against', 'along', 'among', 'around', 'at', 'before', 'behind', 'below', 'beneath', 'beside', 'between', 'beyond', 'but', 'by', 'despite', 'down', 'during', 'except', 'for', 'from', 'in', 'inside', 'into', 'like', 'near', 'of', 'off', 'on', 'onto', 'out', 'outside', 'over', 'past', 'since', 'through', 'throughout', 'till', 'to', 'toward', 'under', 'underneath', 'until', 'up', 'upon', 'with', 'within','without', 'was', 'a', 'to');
	}
	static function get_pronouns(){
		return array('him', 'he', 'his', 'it', 'her', 'she', 'hers', 'we', 'our', 'ours', 'theirs', 'their', 'us');
	}
	static function get_adjectives(){
		return array('tough');
	}
	static function get_adverbs(){
		return array('how', 'when', 'where', 'how much');
	}
	static function get_verbs(){
		return array('are', 'am', 'is', 'was', 'using', 'use', 'uses', 'want');
	}
	static function get_nouns(){
		return array('key');
	}
	static function get_keywords_from($content){
		$pattern = implode(' | ', self::get_conjunctions());
		$pattern .= implode(' | ', self::get_prepositions());
		$pattern .= implode(' | ', self::get_pronouns());
		$pattern .= implode(' | ', self::get_adjectives());
		$pattern .= implode(' | ', self::get_adverbs());
		$pattern .= implode(' | ', self::get_verbs());
		$pattern .= implode(' | ', self::get_nouns());
		$content = self::replace('/'. $pattern . '/i', '', $content);			
		$keywords = self::get_important_words_from($content);
		$popular_words = array();
		$current_word = null;
		$ubounds = count($keywords);
		$list = implode(' ', $keywords);
		$matches = array();
		foreach($keywords as $current_word){
			if(preg_match_all('/' . $current_word . '/i', $list, $matches) > 5 && array_search($current_word, $popular_words) === false){
				$popular_words[] = $current_word;
			}
		}
		return $popular_words;
	}
	static function get_important_words_from($content){
		$words = explode(' ', $content);
		$keywords = array();
		$index = 0;
		$ubounds = count($words);
		for($index = 0; $index < $ubounds; $index++){
			$words[$index] = self::replace('/[^A-Z^a-z^0-9]+/', '', $words[$index]);
			if(strlen(trim($words[$index])) > 0){
				$keywords[] = $words[$index];
			}
		}
		$keywords = array_diff($keywords, self::getConjunctions(), self::getPrepositions(), self::getAdverbs(), self::getVerbs(), self::getPronouns(), self::getNouns(), self::getAdjectives());
		return $keywords;
	}

	static function explode_and_trim($csvString){
		$list = explode(',', $csvString);
		foreach($list as $key=>$value){
			$list[$key] = trim($value);
		}
		return $list;
	}
	static function to_array($csvString){
		$list = self::explodeAndTrim($csvString);
		$new_list = array();
		foreach($list as $value){
			list($key, $val) = explode('=', $value);
			$new_list[$key] = $val;
		}
		return $new_list;
	}
	static function decamelize($string){
		if(strlen(trim($string)) > 0){
			return strtolower(ltrim(preg_replace('/([A-Z])+/', '_$1', $string), '_'));
		}else{
			return $string;
		}
	}
	static function camelize($string){
        return str_replace(' ','',ucwords(self::replace('/[^A-Z^a-z^0-9]+/',' ',$string)));
    }
	static function replace($pattern, $with, $string){
		return preg_replace($pattern, $with, $string);
	}
	static function strip_returns_and_tabs($string){
		$string = preg_replace('/[\\r?|\\n?]+/', '', $string);
		$string = preg_replace('/[\\t?]+/', '', $string);
		return $string;
	}
	static function find($pattern, $value){
		$matches = array();
		$did_match = preg_match($pattern, $value, $matches);
		return $matches;
	}
	static function for_url($string){
		$string = strtolower($string);
		$string = preg_replace("`\[.*\]`U","",$string);
		$string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$string);
		$string = htmlentities($string, ENT_COMPAT, 'utf-8');
		$string = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $string );
		$string = preg_replace( array("`[^a-z0-9]`i","`[-]+`") , "-", $string);
		return strtolower(trim($string, '-'));
	}
	static function password($value){
		return sha1($value);
	}
	static function strip_whitespace($text){
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
	
	static function to_lower($value){
		return strtolower($value);
	}
	static function strip_html_tags($html, $allowed_tags = null){
		return strip_tags($html, $allowed_tags);
	}
	static function truncate($text, $length, $suffix = '...'){
		$string = $text;
		if(strlen($string) > $length){
			$string = substr($string, 0, $length - 1) . $suffix;
		}
		return $string;
	}
	static function to_string($val){
		if(is_array($val)){
			return implode(',', $val);
		}
		return $val;
	}
	static function sanitize($val){
		return filter_var($val, FILTER_SANITIZE_STRING);
	}
	static function null_or_empty($val){
		if($val === null){
			return true;
		}else if(strlen($val) === 0){
			return true;
		}else{
			return false;
		}
	}
}