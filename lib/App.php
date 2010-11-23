<?php
class_exists('Resource') || require('lib/Resource.php');
class_exists('HttpStatus') || require('lib/HttpStatus.php');
class_exists('Chin_session') || require('lib/Chin_session.php');
class AppRequest {
    public $url;
    public $resource;
}

class App {
    public static $cache;
    private static $site_path;
    private static $virtual_path;
    public static function get_virtual_path() {
        if (self::$virtual_path == null) {
            self::$virtual_path = str_replace('/^\//', '', str_replace(sprintf('%sindex.php', '/'), '', $_SERVER['SCRIPT_NAME']));
        }
        return self::$virtual_path;
    }
    private static $root_path;
    public static function get_root_path($file = null){
        if(self::$root_path == null){
            self::$root_path = str_replace(self::get_index_script(), '', $_SERVER['SCRIPT_FILENAME']);
        }
        return self::$root_path . $file;
    }
    private static function get_site_path($is_secure = false) {
        if (self::$site_path == null) {
            self::$site_path = sprintf('%s://%s%s', ($is_secure ? 'https' : 'http'), $_SERVER['SERVER_NAME'], (self::get_virtual_path() != null ? self::get_virtual_path() : null));
        }
        return self::$site_path;
    }
    private static function get_rewrite_file_name(){
        return '.htaccess';
    }
    private static $is_rewrite_on;
    private static function get_is_rewrite_on(){
        if(self::$is_rewrite_on == null){
            self::$is_rewrite_on = file_exists(self::get_root_path(self::get_rewrite_file_name()));
        }
        return self::$is_rewrite_on;
    }
    private static function get_index_script(){
        return 'index.php';
    }
	public static function url_for_theme(){
		return self::url_for('themes/default/');
	}
	public static function get_theme_path($file_name = null){
		return self::get_root_path('themes/default/' . $file_name);
	}
    public static function url_for($path, $params = null, $make_secure = false) {
        $resource_name = $path;
        if (stripos($path, '/') !== false) {
            $path = explode('/', $path);
            $resource_name = $path[0];
            array_shift($path);
            $path = implode('/', $path);
        }else{
            $path = null;
        }
        if ($make_secure) {
            $site_path = str_replace('http:', 'https:', self::get_site_path());
        } else {
            $site_path = str_replace('https:', 'http:', self::get_site_path());
        }
        $query_string = null;
        if ($params != null) {
            $query_string = array();
            foreach ($params as $key => $val) {
                $query_string[] = $key . '=' . $val;
            }
        }
        if (!self::get_is_rewrite_on()) {
            $resource_name = self::get_index_script() . '?r=' . ($resource_name != null ? '' . $resource_name : null);
        } else {
            $resource_name = ($resource_name !== null ? $resource_name : null);
        }        
        if ($path !== null) {
            $resource_name .= '/' . $path;
        }
        if ($query_string != null) {
            $resource_name .= '?';
            $resource_name .= implode('&', $query_string);
        }
        return $site_path . '/' . $resource_name;
    }
	public static function add_request_time_to_footer($output, $start_time, $end_time){
		return str_replace('</footer>', sprintf("<small>Processed in %s</small>
</footer>", $end_time - $start_time), $output);
	}
	public static function dispatch($method, $url, $env) {
		$start_time = gettimeofday(true);
		if ($url == null) $url = 'index';
		$parts = explode('/', $url);
		$file_type = self::get_file_type($parts);
		$parts = self::remove_file_type($parts);
		$first_part = array_shift($parts);
		$resource = self::get_resource($first_part, $parts, $file_type);
		if($resource === null) return self::file_not_found();
		$resource->output = $resource->call_with($method, $parts, $env);
		$end_time = gettimeofday(true);
		$resource->output = self::add_request_time_to_footer($resource->output, $start_time, $end_time);
		return $resource;
	}
	private static function file_not_found(){
		$resource = new Resource();
		$resource->status = new HttpStatus(404);
		$resource->output = $resource->render('error/404');
		$resource->output = $resource->render_layout('default');
		return $resource;
	}
	private static function remove_file_type($parts){
		if($parts !== null && count($parts) > 0){
			$last_part = $parts[count($parts)-1];
			if(strpos($last_part, '.') !== false){
				$last_part = explode('.', $last_part);
				array_pop($last_part);
				$last_part = implode('', $last_part);
				$parts[count($parts)-1] = $last_part;
			}
		}
		return $parts;
	}
	private static $resource;
	private static function get_resource($first_part, $parts, $file_type) {
		self::$resource = null;
		$class_name = ucwords($first_part) . 'Resource';
		if(!file_exists(self::get_root_path('resources/' . $class_name . '.php'))){
			return null;
		}
		if(!class_exists($class_name)) {
			require('resources/' . $class_name . '.php');
		}
		self::$resource = new $class_name(array('file_type'=>$file_type, 'url_parts'=>$parts));
		return self::$resource;
	}
	private static function get_file_type($url_parts){
		if($url_parts === null || count($url_parts) === 0){
			return 'html';
		}
		$last_part = array_pop($url_parts);
		if(strpos($last_part, '.') === false){
			return 'html';
		}
		$file_type = explode('.', $last_part);
		$file_type = $file_type[count($file_type) - 1];
		return $file_type;
	}
	public static function error_did_happen($number, $message, $file, $line){
		printf("%d:%s, %s, %d<br />", $number, $message, $file, $line);
	}
	public static function exception_did_happen($e){
		echo $e->getMessage();
	}
}

set_error_handler(array('App', 'error_did_happen'), E_ALL);
set_exception_handler(array('App', 'exception_did_happen'));
