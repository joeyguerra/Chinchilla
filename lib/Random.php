<?php
	class_exists('Factory') || require('lib/DataStorage/Factory.php');
	class_exists('ByAttribute') || require('lib/DataStorage/ByAttribute.php');
	class Random{
		private static $number;
		
		public static function initialize(){
			$number = self::getNumber();
			return $number;
		}
		
		// I got these from Wordpress when I was thinking about creating a Wordpress interface 
		// for this library.
		public static function getPassword($length = 12, $useSpecialCharacters = true){
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			if ( $useSpecialCharacters )
				$chars .= '!@#$%^&*()';

			$password = '';
			for ( $i = 0; $i < $length; $i++ )
				$password .= substr($chars, self::getNumber(0, strlen($chars) - 1), 1);
			return $password;
		}
		
		public static function getNumber($min = 0, $max = 0){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$value = '';
			$seed = null;

			// Reset $rnd_value after 14 uses
			// 32(md5) + 40(sha1) + 40(sha1) / 8 = 14 random numbers from $rnd_value
			//error_log(self::$number . ' ' . strlen(self::$number));
			if ( strlen(self::$number) < 8 ) {
				self::$number = md5( uniqid(microtime() . mt_rand(), true ) . $seed );
				self::$number .= sha1(self::$number);
				self::$number .= sha1(self::$number . $seed);
				$seed = md5($seed . self::$number);
			}

			// Take the first 8 digits for our value
			$value = substr(self::$number, 0, 8);

			// Strip the first eight, leaving the remainder for the next call to wp_rand().
			self::$number = substr(self::$number, 8);

			$value = abs(hexdec($value));

			// Reduce the value to be within the min - max range
			// 4294967295 = 0xffffffff = max random number
			if ( $max != 0 )
				$value = $min + (($max - $min + 1) * ($value / (4294967295 + 1)));

			return abs(intval($value));
		}
	}
?>