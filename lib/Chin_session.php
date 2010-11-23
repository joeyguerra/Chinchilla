<?php
class Chin_session{
	public static function get_is_signed_in(){
		return array_key_exists('is_signed_in', $_SESSION) ? $_SESSION['is_signed_in'] : false;
	}
	public static function set_is_signed_in($val, $member_id){
		$_SESSION['is_signed_in'] = $val;
		if($val === false){
			$_SESSION['member_id'] = 0;
		}else{
			$_SESSION['member_id'] = $member_id;
		}
	}
	public static function start(){
		session_start();
	}
	public static function get_member_id(){
		return array_key_exists('member_id', $_SESSION) ? $_SESSION['member_id'] : 0;
	}
}