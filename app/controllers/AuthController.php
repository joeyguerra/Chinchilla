<?php
class_exists("Member") || require("models/Member.php");
class AuthController{
	public static $current_user;
	public static function set_current_user(){
		if(self::$current_user !== null) return self::$current_user;
		$hash = self::get_chin_auth();
		self::$current_user = Member::find_signed_in($hash);
		return self::$current_user;
	}
	public static function is_authed(){
		return self::get_chin_auth() !== null && self::$current_user !== null;
	}
	public static function signin(Member $member){
		self::$current_user = Member::find_by_signin_and_password($member->signin, String::encrypt($member->password));
		if(self::$current_user == null) return null;
		return self::set_authed(self::$current_user);
	}
	private static function set_authed($member){
		$expiry = time() + 60*60*24*30;
		$hash = AuthController::get_chin_auth_hash($member->name, $expiry);
		self::set_chin_auth($hash, $expiry);
		$member->hash = $hash;
		$member->expiry = $expiry;
		return Member::save($member);
	}
	public static function get_chin_auth_hash($name, $expiry){
		return hash("sha256", $name . $_SERVER["REMOTE_ADDR"] . $expiry, false);
	}
	public static function get_chin_auth(){
		return array_key_exists("chin_auth", $_COOKIE) ? $_COOKIE["chin_auth"] : null;
	}
	public static function set_chin_auth($value, $expire = 0, $path = null, $domain = null, $secure = false, $httponly = true){
		setcookie("chin_auth", $value, $expire, $path, $domain, $secure, $httponly);
		$_COOKIE["chin_auth"] = $value;
	}
}