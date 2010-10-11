<?php
	class_exists('Object') || require('Object.php');
	class RequestResponse{
		public function __construct($output, $headers){
			$this->headers = $headers;
			$this->output = $output;
		}
		public $output;
		public $headers;
	}
	class Request extends Object{
		public function __construct(){}
		public function __destruct(){}
		public static function doMultiRequests($urls, $path, $datum, $method = 'get', $optionalHeaders = null){
			$handles = array();
			$output = array();
			$i = 0;			
			$ubounds = count($urls);			
			$curl_options = array(
				CURLOPT_AUTOREFERER=>true
				, CURLOPT_FOLLOWLOCATION=>true
				, CURLOPT_ENCODING=>''
				, CURLOPT_USERAGENT=>'App Notifier'
				, CURLOPT_CONNECTTIMEOUT=>5
				, CURLOPT_TIMEOUT=>5
				, CURLOPT_MAXREDIRS=>2
				, CURLOPT_RETURNTRANSFER=>true
				, CURLOPT_VERBOSE=>false
			);
			if($optionalHeaders != null && is_array($optionalHeaders)){
				$curl_options = array_merge($curl_options, $optionalHeaders);
			}
			if($method == 'post'){
				$curl_options[CURLOPT_POST] = true;
				$curl_options[CURLOPT_HTTPGET] = false;
			}elseif($method === 'get'){
				$curl_options[CURLOPT_HTTPGET] = true;
				$curl_options[CURLOPT_PUT] = false;
			}else{
				$curl_options[CURLOPT_HTTPGET] = false;
				$curl_options[CURLOPT_PUT] = true;
			}			
			for($i = 0; $i < $ubounds; $i++){
				if($path !== null){
					$urls[$i] .= '/' . $path;				
				}
				if($method === 'post'){
					$curl_options[CURLOPT_POSTFIELDS] = $datum[$i];
				}elseif($method === 'get'){
					if($datum !== null && $datum[$i] !== null){
						$urls[$i] .= '?' . $datum[$i];
					}
				}else{
					$curl_options[CURLOPT_INFILE] = $datum[$i];
					$curl_options[CURLOPT_INFILESIZE] = strlen($datum[$i]);
				}				
				$curl_options[CURLOPT_URL] = $urls[$i];
				$ch = self::getCurlHandle($curl_options);
				if($ch !== false){
					$handles[] = $ch;
				}
			}
			
			$mh = curl_multi_init();
			if($mh !== false){
				foreach($handles as $ch){
					curl_multi_add_handle($mh, $ch);
				}
				$running = null;
				do{
					$mrc = curl_multi_exec($mh, $running);
				}while($mrc == CURLM_CALL_MULTI_PERFORM);
				while($running && $mrc == CURLM_OK){
					if(curl_multi_select($mh) != -1){
						do{
							$mrc = curl_multi_exec($mh, $running);
						}while($mrc == CURLM_CALL_MULTI_PERFORM);
					}
				}
				if($mrc != CURLM_OK){
					echo "Curl multi read error $mrc \n";
				}
				
				foreach($handles as $ch){
					$output[] = curl_multi_getcontent($ch);
					curl_multi_remove_handle($mh, $ch);
				}
				curl_multi_close($mh);
				var_dump(curl_multi_info_read($mh));
				
				return $output;
			}else{
				throw new Exception("Failed to get a curl handle");
			}	
		}
		private static function getCurlHandle($curl_options){
	        $ch = curl_init(); 
			curl_setopt_array($ch, $curl_options);
			return $ch;
		}
		
		public static function doRequest($url, $path, $data, $method = 'get', $optionalHeaders = null, $follow_redirect = true){
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
				//, CURLOPT_REFERER=>FrontController::$site_path
			);
			
			if($optionalHeaders != null && is_array($optionalHeaders)){
				$curl_options = array_merge($curl_options, $optionalHeaders);
			}
			if($method == 'post'){
				$curl_options[CURLOPT_POST] = true;
				$curl_options[CURLOPT_POSTFIELDS] = $data;
				$curl_options[CURLOPT_HTTPGET] = false;
			}elseif($method === 'get'){
				if($data !== null){
					$url .= '?' . $data;
				}
			}else{
				$curl_options[CURLOPT_INFILE] = $data;
				$curl_options[CURLOPT_INFILESIZE] = strlen($data);
				$curl_options[CURLOPT_HTTPGET] = false;
				$curl_options[CURLOPT_PUT] = true;
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
			if($follow_redirect && array_key_exists('Location', $header)){
				error_log('redirecting to ' . $header['Location']);
				self::doRequest($header['Location'], $path, $data, $method, $optionalHeaders);
			}
			//error_log(String::stripCarriageReturnsAndTabs($output));
	        // close curl resource to free up system resources 
	        curl_close($ch);
			return new RequestResponse($output, $headers);
		}
		
	}
?>