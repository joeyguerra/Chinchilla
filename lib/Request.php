<?php
	class_exists('Object') || require('Object.php');
	class Request extends Object{
		public function __construct(){}
		public function __destruct(){}
		public static function doRequest($url, $path, $data, $method = 'get', $optionalHeaders = null){
			// create curl resource 
			if($path != null){
				$url .= '/' . $path;
			}
	        $ch = curl_init(); 
			$curl_options = array(
				CURLOPT_AUTOREFERER=>true
				//, CURLOPT_HEADER=>true
				, CURLOPT_FOLLOWLOCATION=>false
				, CURLOPT_ENCODING=>''
				, CURLOPT_USERAGENT=>'App Notifier'
				, CURLOPT_CONNECTTIMEOUT=>5
				, CURLOPT_TIMEOUT=>5
				, CURLOPT_MAXREDIRS=>2
				, CURLOPT_RETURNTRANSFER=>true
				, CURLOPT_VERBOSE=>false
				, CURLOPT_REFERER=>FrontController::$site_path
			);
			
			if($optionalHeaders != null && is_array($optionalHeaders)){
				$curl_options = array_merge($curl_options, $optionalHeaders);
			}

			if($method == 'post'){
				$curl_options[CURLOPT_POST] = true;
				$curl_options[CURLOPT_POSTFIELDS] = $data;
				$curl_options[CURLOPT_HTTPGET] = false;
			}elseif($method === 'get' && $data !== null){
				$url .= '&' . $data;
			}
			
			$curl_options[CURLOPT_URL] =$url;
			
	        // set url 
			curl_setopt_array($ch, $curl_options);

	        // $output contains the output string 
	        $output = curl_exec($ch); 

			if(curl_errno($ch) > 0){
				error_log('curl error = ' . curl_error($ch));
			}
			$headers = curl_getinfo($ch);
			$header = array();
			// 301 and 302 are redirects. I'm seeing this happen when I don't use www. in the domain name for the url.
			// So i'm going to just get the location to redirect to and repost.
			if($headers['http_code'] == 301 || $headers['http_code'] == 302){
				$output = preg_replace('/\\r\\n/', '\r\n', $output);
				$lines = explode('\r\n\r\n', $output);
				$lines = explode('\r\n', $lines[0]);
				foreach($lines as $line){
					$pairs = explode(': ', $line);
					$header[$pairs[0]] = $pairs[1];
				}
			}
			if(array_key_exists('Location', $header)){
				error_log('redirecting to ' . $header['Location']);
				self::doRequest($header['Location'], $path, $data, $method, $optionalHeaders);
			}
			//error_log(String::stripCarriageReturnsAndTabs($output));
	        // close curl resource to free up system resources 
	        curl_close($ch);
			return $output;
		}
		
	}
?>