<?php
class_exists('Object') || require('Object.php');
class Resource extends Object{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public $output;
	public $original_resource_name;
	public $resource_css;
	public $title;
	public $description;
	public $file_type;
	public $redirect_parameters;
	protected function redirectTo($resource_name, $query_parameters = null, $make_secure = false){
		$this->redirect_parameters = array('resource_name'=>$resource_name, 'query_parameters'=>$query_parameters, 'make_secure'=>$make_secure);
	}
	
	/* This method is for rendering a view. It's based on the file type and assumes that the file type is html.
	* It also maps the resources properties to the templates in the view like {$person->name} or you can send 
	* in an array that will be exported for the view to use the variables.
	* I've prefixed all variable names with __ to avoid collisions when extracing variables from the array.
	*/
	protected function renderView($__file, $__data = null, $__file_type = null){
		$this->file_type = ($this->file_type == null ? 'html' : $this->file_type);
		if($__file != null){
			$__r = new ReflectionClass(get_class($this));
			$__properties = array();
			foreach($__r->getProperties() as $property){
				if($property->isPublic()){
					$name = $property->getName();
					$__properties[$name] = $this->{$name};
				}
			}						
			if(count($__properties) > 0){
				extract($__properties);
			}

			if($__data != null){
				extract($__data);
			}

			$__full_path = sprintf('%s_%s.php', $__file, $this->file_type);
			if(!in_array($this->file_type, array('html', 'xml')) && $this->is_layout($__file)){
				return $this->output;
			}
			
			ob_start();
			$__theme_view = FrontController::get_virtual_path() . '/' . FrontController::themePath() . '/views/' . $__full_path;
			$__default_view = str_replace('lib/Resource.php', '', __FILE__) . 'views/' . $__full_path;
			// phtml is a special file type that I want to provide fallback logic for. If the file type
			// is phtml, then I want to check for a view with that extension but if it doesn't exist, 
			// the code should fall back and load the html view instead. This allows us to use .html views
			// for partial html requests while providing the ability to define a .phtml view specifically.
			// I don't like the way this is coded. Nested if statements are confusing. But it works. I'd like
			// to come up with a more structured way to implement this logic.
			// I've also added the __file_type parameter for situations where you want to render a view inline another
			// view so you can specify a different file type than what's assigned for the resource.
			if($this->file_type === 'phtml'){
				if(file_exists($__theme_view)){
					require($__theme_view);
				}else if(file_exists($__default_view)){
					require($__default_view);
				}else{
					$__phtml_theme_view = String::replace('/\_phtml/', '_html', $__theme_view);
					$__phtml_default_view = String::replace('/\_phtml/', '_html', $__default_view);
					if(file_exists($__phtml_theme_view)){
						require($__phtml_theme_view);
					}else if(file_exists($__phtml_default_view)){
						require($__phtml_default_view);
					}else{
						throw new Exception("404: File not found for phtml", 404);
					}
				}
			}else if($__file_type === 'phtml'){
				$__phtml_theme_view = String::replace('/\_html/', '_' . $__file_type, $__theme_view);
				$__phtml_default_view = String::replace('/\_html/', '_' . $__file_type, $__default_view);
				if(file_exists($__phtml_theme_view)){
					require($__phtml_theme_view);
				}else if(file_exists($__phtml_default_view)){
					require($__phtml_default_view);
				}else{
					if(file_exists($__theme_view)){
						require($__theme_view);
					}else if(file_exists($__default_view)){
						require($__default_view);
					}else{
						throw new Exception("404: File not found for phtml", 404);
					}
				}
			}else if(file_exists($__theme_view)){
				require($__theme_view);
			}else if(file_exists($__default_view)){
				require($__default_view);
			}else{
				throw new Exception("404: File not found", 404);
			}
						
			
			$this->output = ob_get_contents();
			ob_end_clean();
			if($this->is_layout($__file) && method_exists($this, 'hasRenderedOutput')){
				$this->output = $this->hasRenderedOutput($__file, $this->output);
			}
			if(count($__properties) > 0){
				$__data = array_merge($__data == null ? array() : $__data, $__properties);
			}

			if($__data != null){
				$this->output = $this->replace($this->output, $__data);
			}
		}	
		return $this->output;
	}
	private function is_layout($file){
		return strpos($file, 'layouts/') !== false;
	}
	
	protected function replace($output, $data){
		foreach($data as $key=>$value){
			if(is_object($value)){
				if(property_exists($value, '_attributes')){
					foreach($value->_attributes as $name=>$val){
						$output = str_replace(sprintf("{\$%s->%s}", $key, $name), $val, $output);								
					}
					$r = new ReflectionClass(get_class($value));
					foreach($r->getProperties() as $property){
						$name = $property->getName();
						if(!is_object($value->$name) && !is_array($value->$name)){
							$output = str_replace(sprintf("{\$%s->%s}", $key, $name), $value->$name, $output);								
						}
					}
					
					/*$methods = $r->getMethods();
					foreach($methods as $method){
						$method_name = $method->getName();
						if($method->isPublic() && strpos($method_name, 'get') !== false){
							$property_name = str_replace('get', '', $method_name);
							$property_name = String::decamelize($property_name);
							
						}
					}*/
					
				}				
			}elseif(!is_array($value)){
				$output = str_replace(sprintf("{\$%s}", $key), $value, $output);
			}
		}
		return $output;
	}
	
	protected function replace_output_with_object($output, $obj){
		if(!is_array($array)){
			if($array === 'true'){
				$array = true;
			}
			
			if($array === 'false'){
				$array = false;
			}
			
			return $array;
		}
		
		if($obj != null && is_object($obj)){
			foreach($array as $key=>$value){
				$r = new ReflectionClass(get_class($obj));
				$property = $r->getProperty($key);
				if($property != null && $property->isPublic()){
					if(is_object($property->getValue($obj))){
						$obj->{$key} = self::initWithArray($property->getValue($obj), $value);
						//$property->setValue($obj, self::initWithArray($property->getValue($obj), $value));
					}else{
						$obj->{$key} = self::initWithArray(null, $value);
						//$property->setValue($obj, self::initWithArray(null, $value));
					}
				}
			}
		}else{
			$obj = $array;
		}
		return $obj;
	}
	
	public static function sendMessage($obj, $message, $resource_id = 0){
		$class_name = get_class($obj);
		$reflector = new ReflectionClass($class_name);
		$args = array();
		if($reflector->hasMethod($message)){
			$method = $reflector->getMethod($message);
			$numberOfParams = $method->getNumberOfParameters();
			if($numberOfParams > 0){
				$params = $method->getParameters();
				foreach($params as $param){
					$arg = self::populateParameter($param, $resource_id);
					if($arg != null){
						$args[] = $arg;
					}elseif($param->isDefaultValueAvailable()){
						$args[] = $param->getDefaultValue();
					}
				}
			}
			$output = $method->invokeArgs($obj, $args);
			return $output;
		}else{
			throw new Exception("404: {$class_name}::{$message} not found.", 404);
		}
	}
	
	public function didFinishLoading(){
		self::setUserMessage(null);
	}
	
	public static function getUserMessage(){
		if(array_key_exists('userMessage', $_SESSION)){
			return $_SESSION['userMessage'];
		}else{
			return null;
		}
	}
	public static function setUserMessage($value){
		if($value == null){
			unset($_SESSION['userMessage']);
		}else{
			$_SESSION['userMessage'] = $value;
		}
	}
	private static function populateParameter($param, $id = 0){
		$value = null;
		$obj = null;
		$ref_class = null;
		$class_name = null;
		$name = $param->getName();
		$ref_class = $param->getClass();
		if($id > 0 && $name == 'id'){
			return $id;
		}
		if(array_key_exists($name, $_FILES)){
			$obj = $_FILES[$name];
		}elseif(array_key_exists($name, $_REQUEST)){
			$value = self::sanitize($_REQUEST[$name]);
			// 2009-08-26, jguerra: Arrays are used to populate 2 different types of parameters. The 1st is to populate
			// a parameter that's an object. Where the key is the object's property name; e.g an input field name='user[name]' 
			// maps to a parameter called $user which is an instance of a class User with a public property called $name.
			// This logic should populate $user->name = the value in $_REQUEST['user[name]'];
			// The 2nd situation is for an input field name='photo_names[]'. This code should look for a parameter named 
			// photo_names that is an array data type and populate it with the values from $_REQUEST['photo_names'].
			if(is_array($value)){
				// This block is for the situation where the parameter is an object, not an array.
				if($ref_class != null){
					$class_name = $ref_class->getName();
					$obj = new $class_name(null);
					$obj = self::initWithArray($obj, $value);
				}else{
					// and this block is for the situation where the value from the request is an indexed array.
					$obj = $value;
				}
			}else{
				$obj = self::valueWithCast($value, ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null));
			}
		}else{// This else block handles the case where you want to populate an object with a form that has the object property names
			// as their field names. For instance, I want to save a "post" and the form field names match the attributes on a Post object.
			if($ref_class != null){
				$obj = $ref_class->newInstance(null);
				$is_null = true;
				foreach($_REQUEST as $key=>$value){
					if($ref_class->hasProperty($key)){
						$prop = $ref_class->getProperty($key);
						if($prop != null){
							$obj->{$key} = self::valueWithCast(self::sanitize($value), null);
							$is_null = false;
						}
					}
				}
				// 2009-12-01, jguerra: I want to handle the situation where the id is passed in the url as a path
				// value like user/1. Right now, this code assumes that there's a property called id and it's an integer.
				// I can imagine someone wanting to use a non integer as an identifier and possibly a different name for 
				// the id property. But I'm not coding for that at this time.
				if($id > 0 && $ref_class->hasProperty('id')){
					$prop = $ref_class->getProperty('id');
					if($prop != null){
						$obj->{'id'} = self::valueWithCast(self::sanitize($id), null);
						$is_null = false;
					}
				}
				
				if($is_null){
					$obj = null;
				}
			}
		}
		return $obj;
	}
	public static function sanitize($value){
		if(function_exists('get_magic_quotes_gpc')){
			if(get_magic_quotes_gpc()){
				if(is_array($value)){
					array_walk_recursive($value, array('Resource', 'sanitize'));
				}else{
					$value = preg_replace('/[\\\\]*/', '', $value);
				}
			}
		}
		return $value;
	}
	
	private static function initWithArray($obj, $array){
		$setter = null;
		$getter = null;
		if(!is_array($array)){
			if($array === 'true'){
				$array = true;
			}
			
			if($array === 'false'){
				$array = false;
			}
			
			return $array;
		}
		
		if($obj != null && is_object($obj)){
			foreach($array as $key=>$value){
				$name = ucfirst($key);
				if(!method_exists($obj, 'set' . $name)){
					$setter = 'set' . String::camelize($key);
					$getter = 'get' . String::camelize($key);
				}else{
					$setter = 'set' . $name;
					$getter = 'get' . $name;
				}
				
				if(method_exists($obj, $setter)){
					if(is_object($obj->{$getter}())){
						$obj->{$key} = self::initWithArray($obj->{$getter}(), $value);
					}else{
						$obj->{$key} = self::initWithArray(null, $value);	
					}
				}
				
				$setter = null;
				$getter = null;
			}
		}else{
			$obj = $array;
		}
		return $obj;
	}	
	
	private static function valueWithCast($value, $attribute_value = null){
		// I have to handle boolean's specifically because checkboxes return on or off.
		// So default to false, then set to true if value = on for checkbox.
		$result = $value;
		if(is_bool($value)){
			return $value;
		}
		
		if($value == 'false'){
			return false;
		}
		
		if($value == 'true'){
			return true;
		}
		if(is_bool($attribute_value) && $value == 'on'){
			$result = true;
		}elseif($value == 'true' || $value == 'false'){
			$result = ($value == 'true');
		}else{
			if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
				$result = stripslashes($value);
		}
		return $result;
	}
	
}

?>