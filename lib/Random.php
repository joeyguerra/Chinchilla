<?php
	class_exists('Factory') || require('lib/DataStorage/Factory.php');
	class_exists('ByAttribute') || require('lib/DataStorage/ByAttribute.php');
	class Random{
		private static $number;
		private static $duplicate_cache = array();
		public static function initialize(){
			$number = self::getNumber();
			return $number;
		}
		
		// I got these from Wordpress when I was thinking about creating a Wordpress interface 
		// for this library.
		public static function getPassword($length = 12, $useSpecialCharacters = true){
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			if($useSpecialCharacters)
				$chars .= '!@#$%^&*()';

			$password = '';
			for ( $i = 0; $i < $length; $i++ )
				$password .= substr($chars, self::getNumber(0, strlen($chars) - 1), 1);
			return $password;
		}
		public static function getNumber($min = 0, $max = 0){
			$duplicate = 0;
			$length = 0;
			
			$key = self::longToBinary($max);
			list($duplicate, $length) = array_key_Exists($key, self::$duplicate_cache) ? self::$duplicate_cache[$key] : array(0,0);
			if($duplicate === 0){
				if($key[0] == "\x00"){
					$length = self::numberOfBytes($key) - 1;
				}else{
					$length = self::numberOfBytes($key);
				}
				$max_rand = bcpow(256, $length);
				$duplicate = bcmod($max_rand, $max);
				if(count(self::$duplicate_cache) > 10){
					$duplicate_cache = array();
				}
				self::$duplicate_cache[$key] = array($duplicate, $length);
			}
			do{
				$bytes = "\x00" . self::getRandomBytes($length);
				$num = self::binaryToLong($bytes);
			}while(bccomp($num, $duplicate) < 0);
			return bcmod($num, $max);

		}
		public static function getRandomBytes($length){
			static $file = null;
			$bytes = '';
			
			if($file === null){
				$file = @fopen('/dev/urandom', "r");
			}
			if($file === false){
			// pseudorandom used
				for ($i = 0; $i < $length; $i += 4) {
					$bytes .= pack('L', mt_rand());
				}
				$bytes = substr($bytes, 0, $length);
			}else{
				$bytes = fread($file, $length);
			}
			
			return $bytes;
		}
		protected static function numberOfBytes($value){
			return strlen(bin2hex($value)) / 2;
		}
		protected static function binaryToLong($value){
			if($value == null){
				return $value;
			}
			$bytes = array_merge(unpack('C*', $value));
			$long = 0;
			if($bytes !== null && count($bytes) > 0 && ($bytes[0] > 127)){
				throw new Exception("Only supports positive numbers.");
			}
			
			foreach($bytes as $byte){
				$long = bcmul($long, pow(2,8));
				$long = bcadd($long, $byte);
			}
			return $long;
		}
		protected static function longToBinary($long){
			$is_positive = bccomp($long, 0);
			if($is_positive < 0){
				throw new Exception("Only supports positive numbers.");
			}
			if($is_positive == 0){
				return "\x00";
			}
			
			$bytes = array();
			while(bccomp($long, 0) > 0){
				array_unshift($bytes, bcmod($long, 256));
				$long = bcdiv($long, pow(2,8));
			}
			if($bytes && ($bytes[0] > 127)){
				array_unshift($bytes, 0);
			}
			$value = '';
			foreach($bytes as $byte){
				$value .= pack('C', $byte);
			}
			return $value;
		}
	}
?>