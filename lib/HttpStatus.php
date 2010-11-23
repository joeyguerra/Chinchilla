<?php
class HttpStatus{
	public function __construct($code, $realm = 'chinchilla'){
		$this->code = $code;
		$this->realm = $realm;
		switch($code){
			case(200):
				$this->message = 'OK';
				break;
			case(201):
				$this->message = 'Created';
				break;
			case(202):
				$this->message = 'Accepted';
				break;
			case(203):
				$this->message = 'Non-Authoritative Information';
				break;
			case(204):
				$this->message = 'No Content';
				break;
			case(205):
				$this->message = 'Reset Content';
				break;
			case(206):
				$this->message = 'Partial Content';
				break;
			case(301):
				$this->message = 'Moved Permanently';
				break;
			case(302):
				$this->message = 'Found';
				break;
			case(303):
				$this->message = 'See Other';
				break;
			case(304):
				$this->message = 'Not Modified';
				break;
			case(305):
				$this->message = 'Use Proxy';
				break;
			case(307):
				$this->message = 'Temporary Redirect';
				break;
			case(400):
				$this->message = 'Bad Request';
				break;
			case(401):
				$this->message = 'Unauthorized';
				break;
			case(402):
				$this->message = 'Payment Required';
				break;
			case(403):
				$this->message = 'Forbidden';
				break;
			case(404):
				$this->message = 'Not Found';
				break;
			case(405):
				$this->message = 'Method Not Allowed';
				break;
			case(406):
				$this->message = 'Not Acceptable';
				break;
			case(407):
				$this->message = 'Proxy Authentication Required';
				break;
			case(408):
				$this->message = 'Request Timeout';
				break;
			case(409):
				$this->message = 'Conflict';
				break;
			case(410):
				$this->message = 'Gone';
				break;
			case(411):
				$this->message = 'Length Required';
				break;
			case(412):
				$this->message = 'Precondition Failed';
				break;
			case(413):
				$this->message = 'Request Entity Too Large';
				break;
			case(414):
				$this->message = 'Request Entity Too Long';
				break;
			case(415):
				$this->message = 'Unsupported Media Type';
				break;
			case(416):
				$this->message = 'Requested Range Not Satisfiable';
				break;
			case(417):
				$this->message = 'Expectation Failed';
				break;
			case(500):
				$this->message = 'Internal Server Error';
				break;
			case(501):
				$this->message = 'Not Implemented';
				break;
			case(502):
				$this->message = 'Bad Gateway';
				break;
			case(503):
				$this->message = 'Service Unavailable';
				break;
			case(504):
				$this->message = 'Gateway Timeout';
				break;
			case(505):
				$this->message = 'HTTP Version Not Supported';
				break;
		}
	}
	public $code;
	public $message;
	public function send(){
		header($_SERVER["SERVER_PROTOCOL"].' ' . $this->code . ' ' . $this->message, true,  $this->code);
		if($this->code === 401){
			header('WWW-Authenticate: Digest realm="'.$this->realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5($this->realm).'"');
		}
		if($this->code === 404){
			header('Status: 404 Not Found');
		}		
	}
}