<?php
class csrf_token{
	function should_require_csrf_token(){
		if(function_exists("should_require_csrf_token")) return should_require_csrf_token();
		return true;
	}
	function after_rendering_view($publisher, $info){
		if(!self::should_require_csrf_token()) return $info;
		if(strpos($info, "</form>") === false) return $info;
		//$salt = microtime(true).mt_rand(10000,90000);
		$token = self::get_csrf_token();
		if($token === null){
			$token = $this->get_hash();
			self::set_csrf_token($token);
		}
		$info = str_replace("</form>", "<input type=\"hidden\" name=\"_csrf_token\" value=\"$token\" />
</form>", $info);
		return $info;
	}
	function before_calling_http_method($publisher, $info){
		if(!auth_controller::is_authed()) return $info;
		if(strtolower($info) === "get") return $info;
		if(self::should_require_csrf_token()) return $info;
		$token = self::get_csrf_token();
		if($token === null){
			resource::unauthorized("Unauthorized");
			return $info;
		}
		if($token !== $_POST["_csrf_token"]){
			resource::unauthorized("Unauthorized");
			return $info;
		}
	}

	function after_calling_http_method($publisher, $info){
		if(!method_exists($publisher, "should_require_csrf_token")) return $info;
		if(!$publisher->should_require_csrf_token()) return $info;
		if(strpos($info, "</form>") === false) return $info;
		//$salt = microtime(true).mt_rand(10000,90000);
		if(auth_controller::is_authed()){
			$token = self::get_csrf_token();
			if($token === null){
				$token = $this->get_hash();
				self::set_csrf_token($token);
			}
			$info = str_replace("</form>", "<input type=\"hidden\" name=\"_csrf_token\" value=\"$token\" />
</form>", $info);
		}
		return $info;
	}
	static function get_csrf_token(){
		return array_key_exists("_csrf_token", $_COOKIE) ? $_COOKIE["_csrf_token"] : null;
	}
	static function set_csrf_token($value){
		$expire = 0;
		$path = "/";
		$domain = resource::domain();
		$secure = false;
		$httponly = false;
		setcookie("_csrf_token", $value, $expire, $path, $domain, $secure, $httponly);
		$_COOKIE["_csrf_token"] = $value;
	}
	private function get_hash($bit_length = 128){
		if(strpos("WIN", PHP_OS) !== false){
			return md5(uniqid(rand(), 1));
		}
	    $fp = @fopen('/dev/random','rb');
	    if ($fp !== FALSE) {
	        $key = substr(base64_encode(@fread($fp,($bit_length + 7) / 8)), 0, (($bit_length + 5) / 6)  - 2);
	        @fclose($fp);
	        return $key;
	    }
	    return null;
	}
}

filter_center::subscribe("after_rendering_view", null, new csrf_token());
filter_center::subscribe("before_calling_http_method", null, new csrf_token());
filter_center::subscribe("after_calling_http_method", null, new csrf_token());
