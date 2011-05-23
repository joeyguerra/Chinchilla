<?php
class_exists('Random') || require('lib/Random.php');
class_exists('Request') || require('lib/Request.php');

// models
class OpenidRequest extends Object{
	public function __construct($attributes = null){
		parent::__construct($attributes);
		if($this->ns === null){
			$this->ns = 'http://specs.openid.net/auth/2.0';
		}
	}
	public function __destruct(){
		parent::__destruct();
	}
	private $id;
	public function getId(){
		return $this->id;
	}
	public function setId($val){
		$this->id = $val;
	}

	private $op_endpoint;
	public function getOp_endpoint(){
		return $this->op_endpoint;
	}
	public function setOp_endpoint($val){
		$this->op_endpoint = $val;
	}

	private $assoc_type;
	public function getAssoc_type(){
		return $this->assoc_type;
	}
	public function setAssoc_type($val){
		$this->assoc_type = $val;
	}
	
	private $mac_key;
	public function getMac_key(){
		return $this->mac_key;
	}
	public function setMac_key($val){
		$this->mac_key = $val;
	}
	
	private $enc_mac_key;
	public function getEnc_mac_key(){
		return $this->enc_mac_key;
	}
	public function setEnc_mac_key($val){
		$this->enc_mac_key = $val;
	}
	
	private $expires_in;
	public function getExpires_in(){
		return $this->expires_in;
	}
	public function setExpires_in($val){
		$this->expires_in = $val;
	}
	
	private $owner_id;
	public function getOwner_id(){
		return $this->owner_id;
	}
	public function setOwner_id($val){
		$this->owner_id = $val;
	}

	private $mode;
	public function getMode(){
		return $this->mode;
	}
	public function setMode($val){
		$this->mode = $val;
	}

	private $identity;
	public function getIdentity(){
		return $this->identity;
	}
	public function setIdentity($val){
		$this->identity = $val;
	}

	private $return_to;
	public function getReturn_to(){
		return $this->return_to;
	}
	public function setReturn_to($val){
		$this->return_to = $val;
	}
		
	private $response_nonce;
	public function getResponse_nonce(){
		return $this->response_nonce;
	}
	public function setResponse_nonce($val){
		$this->response_nonce = $val;
	}
	
	private $assoc_handle;
	public function getAssoc_handle(){
		return $this->assoc_handle;
	}
	public function setAssoc_handle($val){
		$this->assoc_handle = $val;
	}
	
	private $invalidate_handle;
	public function getInvalidate_handle(){
		return $this->invalidate_handle;
	}
	public function setInvalidate_handle($val){
		$this->invalidate_handle = $val;
	}
	
	private $claimed_id;
	public function getClaimed_id(){
		return $this->claimed_id;
	}
	public function setClaimed_id($val){
		$this->claimed_id = $val;
	}
	
	private $ns;
	public function getNs(){
		return $this->ns;
	}
	public function setNs($val){
		$this->ns = $val;
	}
	
	private $session_type;
	public function getSession_type(){
		return $this->session_type;
	}
	public function setSession_type($val){
		$this->session_type = $val;
	}
	private $is_valid;
	public function getIs_valid(){
		return $this->is_valid;
	}
	public function setIs_valid($val){
		$this->is_valid = $val;
	}
	
	private $dh_modulus;
	public function getDh_modulus(){
		return $this->dh_modulus;
	}
	public function setDh_modulus($val){
		$this->dh_modulus = $val;
	}
	
	private $dh_gen;
	public function getDh_gen(){
		return $this->dh_gen;
	}
	public function setDh_gen($val){
		$this->dh_gen = $val;
	}

	private $dh_consumer_public;
	public function getDh_consumer_public(){
		return $this->dh_consumer_public;
	}
	public function setDh_consumer_public($val){
		$this->dh_consumer_public = $val;
	}	
	
	private $dh_server_public;
	public function getDh_server_public(){
		return $this->dh_server_public;
	}
	public function setDh_server_public($val){
		$this->dh_server_public = $val;
	}	
	
	private $private_key;
	public function getPrivate_key(){
		return $this->private_key;
	}
	public function setPrivate_key($val){
		$this->private_key = $val;
	}	
	
	private $realm;
	public function getRealm(){
		return $this->realm;
	}
	public function setRealm($val){
		$this->realm = $val;
	}	
	
	public static function findByAssocHandle($assoc_handle, $current_time_in_seconds){
		$config = new AppConfiguration();
		$db = Factory::get($config->db_type, $config);
		$clause = new ByClause(sprintf("assoc_handle='%s' and expires_in > %s", $assoc_handle, $current_time_in_seconds), null, 1, null);
		$obj = $db->find($clause, new OpenidRequest(null));
		return $obj;
	}
	public static function save($openid_request){
		$config = new AppConfiguration();
		$db = Factory::get($config->db_type, $config);
		try{
			$new_openid_request = $db->save(null, $openid_request);			
		}catch(DSException $e){
			$openid_request->install($config);
			$new_openid_request = $db->save(null, $openid_request);
		}
		$openid_request->id = $new_openid_request->id;
		self::notify('didSaveOpenidRequest', $openid_request, $openid_request);
		return $openid_request;
	}
	public function getTableName($config = null){
		if($config == null){
			$config = new AppConfiguration();
		}
		return $config->prefix . 'openid_requests';
	}
	
	public function install($config){
		$message = '';
		$db = Factory::get($config->db_type, $config);
		try{
			$table = new Table($this->getTableName($config), $db);
			$table->addColumn('id', 'biginteger', array('is_nullable'=>false, 'auto_increment'=>true));
			$table->addColumn('op_endpoint', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>255));
			$table->addColumn('assoc_handle', 'string', array('is_nullable'=>false, 'size'=>255));
			$table->addColumn('invalidate_handle', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>255));
			$table->addColumn('expires_in', 'biginteger', array('is_nullable'=>false, 'default'=>0));
			$table->addColumn('owner_id', 'biginteger', array('is_nullable'=>false, 'default'=>1));
			$table->addColumn('assoc_type', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>80));
			$table->addColumn('session_type', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>80));
			$table->addColumn('mode', 'string', array('is_nullable'=>false, 'size'=>80));
			$table->addColumn('identity', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>255));
			$table->addColumn('claimed_id', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>255));
			$table->addColumn('return_to', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>255));
			$table->addColumn('response_nonce', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>80));
			$table->addColumn('ns', 'string', array('is_nullable'=>false, 'size'=>80));
			$table->addColumn('is_valid', 'boolean', array('is_nullable'=>true, 'default'=>0));
			$table->addColumn('mac_key', 'string', array('is_nullable'=>true, 'size'=>255));
			$table->addColumn('enc_mac_key', 'string', array('is_nullable'=>true, 'size'=>255));
			$table->addColumn('dh_modulus', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>255));
			$table->addColumn('dh_gen', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>255));
			$table->addColumn('dh_consumer_public', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>255));
			$table->addColumn('dh_server_public', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>255));
			$table->addColumn('private_key', 'string', array('is_nullable'=>false, 'size'=>255));
			$table->addColumn('realm', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>255));
			
			$table->addKey('primary', 'id');
			$table->addKey('key', array('assoc_handle_key'=>'assoc_handle'));
			$table->addKey('key', array('owner_id_key'=>'owner_id'));
			$table->addKey('key', array('op_endpoint_key'=>'op_endpoint'));
			$table->addOption('ENGINE=MyISAM DEFAULT CHARSET=utf8');
			$errors = $table->save();
			if(count($errors) > 0){
				foreach($errors as $error){
					$message .= $error;
				}
				throw new Exception($message);
			}
		}catch(Exception $e){
			$db->deleteTable($this->getTableName($config));
			throw $e;
		}
	}
}

class Resource_openid{
	public function __construct(){}
	public function __destruct(){}
	public function canHandle($class_name, $http_method){
		return in_array($class_name, array('OpenidResource'));
	}
	public function execute($class_name, $http_method = 'get', $url_parts){
		$resource = class_exists($class_name) ? new $class_name($url_parts) : null;
		if($resource !== null){
			return Resource::sendMessage($resource, $http_method, null);
		}
		return null;
	}
}

// resource.
class OpenidResource extends AppResource{
	public function __construct($url_parts){
		parent::__construct($url_parts);
		$config = new AppConfiguration();
		$db = Factory::get($config->db_type, $config);
		$openid_request = new OpenidRequest();
		if(!$db->tableExists($openid_request->getTableName())){
			$openid_request->install($config);
		}
		self::$expires_in = 60*60*1;
	}
	public function __destruct(){}
	
	public static $expires_in;
	public function get($openid_mode = null){
		error_log('get ' . $openid_mode);
		return $this->dispatch($openid_mode, 'get');
	}
	
	public function post($openid_mode = null){
		error_log('post ' . $openid_mode);
		return $this->dispatch($openid_mode, 'post');
	}
	private function dispatch($openid_mode, $http_method){
		error_log(json_encode($_REQUEST));
		$command = new OpenidCommand($this->url_parts, $this);
		$mode = $openid_mode;
		if(class_exists('Openid_' . $mode)){
			$mode = 'Openid_' . $mode;
			$command = new $mode($this->url_parts, $this);
		}
		return $command->$http_method($openid_mode);
	}
	public static function btowc($str){
		if (ord($str[0]) > 127) {
			return "\x00" . $str;
		}
		return dechex($str);
	}	
}

class OpenidCommand{
	public function __construct($url_parts, $resource){
		$this->expires_in = time() + (60*60*1);
		$this->ns = 'http://specs.openid.net/auth/2.0';
		$this->url_parts = $url_parts;
		$this->resource = $resource;
	}
	protected $resource;
	public $url_parts;
	public $expires_in;
	public $ns;
	public $error_message;
	public $request;
	public function get($openid_mode){
		$assoc_handle = self::request('openid_assoc_handle');
		$assoc_handle = $assoc_handle !== null ? $assoc_handle : uniqid();
		$request = new OpenidRequest(array('ns'=>$this->ns, 'mode'=>'checkid_setup', 'identity'=>self::request('openid_identity'), 'claimed_id'=>self::request('openid_claimed_id'), 'assoc_handle'=>$assoc_handle, 'return_to'=>self::request('openid_return_to'), 'realm'=>self::request('openid_realm'), 'expires_in'=>$this->expires_in, 'owner_id'=>AppResource::$member->id));
		$request = $this->findExistingAssociation($assoc_handle, $request);
		$request = $this->applyDefaultValueRules($request);		
		$does_realm_match = $this->doRealmCheck($request);
		if($does_realm_match){
			OpenidRequest::save($request);
			if(AuthController::is_authed() && $request->return_to !== null){
				$data = $this->makePositiveAssertion($request);
				$url = $this->buildUrl($request->return_to, $data);
				Resource::redirect_to::setNeedsToRedirectRaw($url);
			}
			$this->resource->output = $this->resource->render('plugins/openid/views/check_authentication/login', array('request'=>$request));
			
			return $this->resource->render_layout('default');		
		}
	}
	public function post($openid_mode){
		$email = self::request('email');
		$password = self::request('password');
		$user = null;
		if(!AuthController::is_authed()){
			if(empty($email) || empty($password)){
				$isAuthed = false;
			}else{
				$user = AuthController::do_verification($email, $password);
				if($user !== null){
					AuthController::setAuthKey($email);
				}
			}
		}
		
		$assoc_handle = self::request('openid_assoc_handle');
		$request = $this->findExistingAssociation($assoc_handle, $request);
		if(AuthController::is_authed()){
			$data = $this->makePositiveAssertion($request);
			$url = $this->buildUrl($request->return_to, $data);
			Resource::redirect_to::setNeedsToRedirectRaw($url);
		}else{
			$data = $this->makeNegativeAssertion($request);
			$url = $this->buildUrl($request->return_to, $data);
			Resource::redirect_to::setNeedsToRedirectRaw($url);
		}
	}
	
	protected static function request($key){
		return array_key_exists($key, $_REQUEST) ? $_REQUEST[$key] : null;
	}
		
	protected function getKeyValueFormEncodedErrorResponse($request, $error_message){
		$response = self::keyValueFormEncode('ns', $this->ns);
		$response .= self::keyValueFormEncode('error', $error_message);
		$response .= self::keyValueFormEncode('contact', '6d Support');
		$response .= self::keyValueFormEncode('reference', App::url_for('openid'));
		$response .= self::keyValueFormEncode('mode', 'error');
		return $response;
	}
	protected function sendErrorResponse($request, $error_message){
		$response = $this->getKeyValueFormEncodedErrorResponse($request, $error_message);
		throw new Exception($response, 400);
	}
	protected function sendIndirectErrorResponse($request, $error_message){
		$params = array('openid.ns'=>$this->ns, 'openid.mode'=>'error', 'openid.contact'=>'6d support', 'openid.reference'=>App::url_for('openid'), 'error'=>$error_message);
		error_log('sending indirect response ' . $error_message);
		Resource::redirect_to::setNeedsToRedirectRaw($request->return_to, $params);
	}
	protected function sendDirectResponseForAssociation($request){
		$response = '';
		$response .= self::keyValueFormEncode('assoc_type', $request->assoc_type);
		$response .= self::keyValueFormEncode('ns', $request->ns);
		$response .= self::keyValueFormEncode('mode', 'id_res');
		$response .= self::keyValueFormEncode('session_type', $request->session_type);
		$response .= self::keyValueFormEncode('assoc_handle', $request->assoc_handle);
		$response .= self::keyValueFormEncode('expires_in', $request->expires_in);
		if($request->mac_key !== null){
			$response .= self::keyValueFormEncode('mac_key', $request->mac_key);
		}
		if($request->enc_mac_key !== null){
			$response .= self::keyValueFormEncode('mac_key', $request->enc_mac_key);
		}
		return $response;
	}
	protected function sendDirectResponse($request){
		$reflector = new ReflectionObject($request);
		$name = null;
		$response = null;
		foreach($_REQUEST as $key=>$value){
			if(strpos($key, 'openid_') !== false){
				$name = str_replace('openid_', '', $key);
				$method = $reflector->getMethod('get' . ucwords($name));
				if($method !== null && $method->isPublic()){
					$response .= self::keyValueFormEncode($name, $method->invoke($request));
				}
			}
		}
		error_log('sending direct response = ' . $response);
		return $response;
	}
	public static function keyValueFormEncode($key, $value){
		return $key . ':' . $value . PHP_EOL;
	}
	protected static function generateSecret(){
		$g = Random::getNumber(1, 256);
		$p = Random::getNumber(257, 524);
		$k = Random::getNumber(1, $p-1);
		$secret = ($g^$k) % $p;
		return $secret;
	}
	protected function buildUrl($base, $params){
		$search = array();
		if($params !== null){
			foreach($params as $key=>$value){
				$search[] = sprintf("%s=%s", $key, $value);
			}
		}
		$base .= ((strpos($base, '?') !== false) ? '&' : '?') . implode('&', $search);
		return $base;
	}
	
	protected function makeImmediateNegativeAssertion($request){
		$data = array('openid.ns'=>urlencode($request->ns), 'openid.mode'=>'setup_needed');
		Resource::redirect_to::setNeedsToRedirectRaw($this->buildUrl($request->return_to, $data));
	}
	protected function makeNegativeAssertion($request){
		$data = array('openid.ns'=>urlencode($request->ns), 'openid.mode'=>'cancel');
		return $data;
	}
	
	protected function makePositiveAssertion($request){
		$data = array('openid.ns'=>urlencode($request->ns), 'openid.mode'=>'id_res', 'openid.op_endpoint'=>urlencode(App::url_for('openid.txt/')));
		$data['openid.signed'] = 'op_endpoint,return_to,response_nonce,assoc_handle';		
		if($request->claimed_id !== null && $request->identity !== null){
			$data['openid.claimed_id'] = urlencode($request->claimed_id);
			$data['openid.identity'] = urlencode($request->identity);
			$data['openid.signed'] .= ',claimed_id,identity';
		}
		$data['openid.return_to'] = urlencode($request->return_to);
		$data['openid.response_nonce'] = gmdate("Y-m-d\TH:i:s\Z").uniqid();
		if($request->invalidate_handle !== null){
			$data['openid.invalidate_handle'] = $request->invalidate_handle;
		}
		$data['openid.assoc_handle'] = $request->assoc_handle;
		$data['openid.sig'] = base64_encode($this->generateMessageSignature($data['openid.signed'], $request));
		return $data;
	}
	protected function generateMessageSignature($signed, $request){
		$data = null;
		$signed = String::explodeAndTrim($signed);
		foreach($signed as $key){
			$data .= self::keyValueFormEncode($key, $request->$key);
		}
		$sig = $this->sign($data, $request->secret, $request->assoc_type);
		return $sig;
	}
	protected function sign($data, $secret, $assoc_type){
		$openid_sig = null;
		if($assoc_type == 'HMAC-SHA256'){
			$openid_sig = hash_hmac('sha256', $data, $secret);
		}else if($assoc_type == 'HMAC-SHA1'){
			$openid_sig = hash_hmac('sha1', $data, $secret);
		}
		return $openid_sig;
	}
	
	protected function findExistingAssociation($assoc_handle, $request){
		if($assoc_handle !== null){
			$association = OpenidRequest::findByAssocHandle($assoc_handle, time());
			if($association !== null){
				$request = $association;
			}
		}
		return $request;
	}
	
	protected function applyDefaultValueRules($request){
		if($request->identity === null){
			$request->identity = $request->claimed_id;
		}
		if($request->identity === 'http://specs.openid.net/auth/2.0/identifier_select'){
			$request->identity = App::url_for(null);
		}
		
		if($request->realm === null){
			$request->realm = $request->return_to;
		}
		if($request->invalidate_handle === null){
			$request->dh_gen = 2;
			//$request->dh_modulus = $this->generateRandomNumber($request->dh_gen, hexdec('DCF93A0B883972EC0E19989AC5A2CE310E1D37717E8D9571BB7623731866E61EF75A2E27898B057F9891C2E27A639C3F29B60814581CD3B2CA3986D2683705577D45C2E7E52DC81C7A171876E5CEA74B1448BFDFAF18828EFD2519F14E45E3826634AF1949E5B535CC829A483B8A76223E5D490A257F05BDFF16F2FB22C583AB'));
			$request->dh_modulus = $this->generateRandomNumber($request->dh_gen, PHP_INT_MAX);
			$request->private_key = self::generatePrivateKey($request);	
			$request->dh_consumer_public = self::generatePublicKey($request);
		}
		return $request;
	}
	
	protected function doRealmCheck($request){
		if($request->realm !== null){
			$realm = str_replace('*.', 'www.', $request->realm);
			$discovery_response = Request::doRequest($realm, null, null, 'get', null, false);
			$matches = String::find('/\<meta http\-equiv="X\-XRDS\-Location" content="(.+)"\s?\/\>/', $discovery_response->output);
			if(count($matches) > 1){
				$discovery_response = Request::doRequest($matches[1], null, null, 'get', null, false);
			}
			if(strpos($discovery_response->output, '<URI>' . $realm) === false){
				Resource::set_user_message("The realm doesn't match the site you came from.");
				$request->return_to = App::url_for(null);
				$this->sendIndirectErrorResponse($request, "The realm doesn't match the discovered endpoint.");
				return false;
			}

			if(!$this->compareRealmWithReturnTo($request)){
				Resource::set_user_message("The realm doesn't match the site you came from.");
				$request->return_to = App::url_for(null);
				$this->sendIndirectErrorResponse($request, "The return_to url doesn't match the realm.");
				return false;
			}
		}
		return true;
	}
	protected function compareRealmWithReturnTo($request){
		if($request->realm !== null){
			$realm_host = parse_url($request->realm, PHP_URL_HOST);
			$return_to_host = parse_url($request->return_to, PHP_URL_HOST);
			if(strpos($realm_host, '*.') !== false){
				$realm_host = str_replace('*.', '', $realm_host);
			}
			return strpos($return_to_host, $realm_host) !== false;
		}
		return true;
	}
	
	protected static function generateEncryptedSharedKey($request){
		$shared_key = self::generateSharedKey($request);		
		if($request->session_type == 'DH-SHA256'){
			$shared_key = hash_hmac('sha256', $shared_key, $request->private_key);
		}else if($request->session_type == 'DH-SHA1'){
			$shared_key = hash_hmac('sha1', $shared_key, $request->private_key);
		}else{
			throw new Exception("no encription is not supported right now");
		}
		return base64_encode($shared_key);
	}
	protected static function generateRandomNumber($min, $max){
		$num = Random::getNumber($min, $max);
		return $num;
	}
	protected static function generateSharedKey($request){
		$secret_size = null;
		switch($request->assoc_type){
			case('HMAC-SHA1'):
				$secret_size = 20;
				break;
			case('HMAC-SHA256'):
				$secret_size = 32;
				break;
			default:
				break;
		}
		$random_number = Random::getRandomBytes($secret_size);
		return base64_encode($random_number);
	}
	protected static function generatePrivateKey($request){
		return self::generateRandomNumber(2, PHP_INT_MAX) + 1;
	}
	protected static function generatePublicKey($request){
		$public_key = bcpowmod($request->dh_gen, $request->private_key, $request->dh_modulus);
		return $public_key;
	}
	
}
class Openid_checkid_setup extends OpenidCommand{
	public function __construct($url_parts, $resource){
		parent::__construct($url_parts, $resource);
	}
}

class Openid_checkid_immediate extends OpenidCommand{
	public function __construct($url_parts, $resource){
		parent::__construct($url_parts, $resource);
	}
	public function get($openid_mode){
		$assoc_handle = self::request('openid_assoc_handle');
		$assoc_handle = $assoc_handle !== null ? $assoc_handle : uniqid();
		$request = new OpenidRequest(array('ns'=>$this->ns, 'mode'=>'checkid_immediate', 'identity'=>self::request('openid_identity'), 'claimed_id'=>self::request('openid_claimed_id'), 'assoc_handle'=>$assoc_handle, 'return_to'=>self::request('openid_return_to'), 'realm'=>self::request('openid_realm'), 'expires_in'=>$this->expires_in, 'owner_id'=>AppResource::$member->id));
		$request = $this->findExistingAssociation($assoc_handle, $request);
		$request = $this->applyDefaultValueRules($request);	
		$does_realm_match = $this->doRealmCheck($request);
		if($does_realm_match && AuthController::is_authed()){
			OpenidRequest::save($request);
			$data = $this->makePositiveAssertion($request);
			$url = $this->buildUrl($request->return_to, $data);
			Resource::redirect_to::setNeedsToRedirectRaw($url);
		}else{
			return $this->makeImmediateNegativeAssertion($request);
		}
	}
}

class Openid_check_authentication extends OpenidCommand{
	public function __construct($url_parts, $resource){
		parent::__construct($url_parts, $resource);
	}
	public function get($openid_mode){
		$assoc_handle = self::request('openid_assoc_handle');
		$request = new OpenidRequest(array('ns'=>$this->ns, 'mode'=>'check_authentication', 'identity'=>self::request('openid_identity'), 'claimed_id'=>self::request('openid_claimed_id'), 'assoc_handle'=>$assoc_handle, 'return_to'=>self::request('openid_return_to'), 'realm'=>self::request('openid_realm'), 'expires_in'=>$this->expires_in, 'owner_id'=>AppResource::$member->id, 'is_valid'=>false));
		$request = $this->findExistingAssociation($assoc_handle, $request);
		if($assoc_handle !== null && $request->id === null){
			$request->invalidate_handle = $assoc_handle;
		}
		if($request->response_nonce === self::request('openid_response_nonce') && $request->is_valid){
			$this->resource->output = "It seems like the request has been compromised. Go back and try again. If this message is displayed again, I wouldn't trust the site you're trying to login to.";
		}
		
		// This checks if the request is an association. The spec says to use private associations to encrypt.
		if($request->enc_mac_key !== null){
			$this->resource->output = "It seems like the request has been compromised. Go back and try again. If this message is displayed again, I wouldn't trust the site you're trying to login to.";
		}else{
			$sig = base64_encode($this->generateMessageSignature($request->signed, $request));
			if($sig === self::request('openid_sig')){
				$this->resource->output = $this->resource->render('plugins/openid/views/check_authentication/index', array('request'=>$request));
				$request->is_valid = true;
				OpenidRequest::save($request);
				$this->sendDirectResponse($request);
			}else{
				$this->resource->output = "It seems like the request has been compromised. Go back and try again. If this message is displayed again, I wouldn't trust the site you're trying to login to.";
			}
		}
		return $this->resource->render_layout('default');
	}
	public function post($openid_mode){
		
	}
}
class Openid_associate extends OpenidCommand{
	public function __construct($url_parts, $resource){
		parent::__construct($url_parts, $resource);
	}
	public function post($openid_mode){
		$session_type = self::request('openid_session_type');
		if(in_array($session_type, array('DH-SHA1', 'DH-SHA256'))){
			return $this->doSha1($session_type);
		}else if($session_type == 'no-encryption'){
			return $this->doNoEncryption($session_type);
		}else{
			return doUnsupported();
		}
	}
	private function doUnsupported(){
		$request = new OpenidRequest(array('ns'=>$this->ns, 'mode'=>'id_res', 'error'=>"This session type is not supported", 'session_type'=>'DH-SHA256', 'assoc_type'=>'HMAC-SHA256', 'error_code'=>'unsupported-type'));
		return $this->sendDirectResponseForAssociation($request);
	}
	private function doNoEncryption($session_type){
		$request = new OpenidRequest(array('ns'=>$this->ns, 'mode'=>'id_res', 'assoc_handle'=>uniqid(), 'session_type'=>$session_type, 'assoc_type'=>self::request('openid_assoc_type'), 'expires_in'=>$this->expires_in, 'owner_id'=>AppResource::$member->id));
		if(array_key_exists('HTTPS', $_SERVER)){
			$request = $this->applyDefaultValueRules($request);
			$request->mac_key = self::generateSharedKey($request);
			OpenidRequest::save($request);
			$request->dh_modulus = null;
			$request->dh_gen = null;
			$request->dh_consumer_public = null;
		}else{
			$request->error_code = "An Unencrypted association is only supported over HTTPS (SSL).";
		}
		return $this->sendDirectResponseForAssociation($request);
	}
	private function doSha1($session_type){
		$request = new OpenidRequest(array('ns'=>$this->ns, 'mode'=>'id_res', 'assoc_handle'=>uniqid(), 'session_type'=>$session_type, 'assoc_type'=>self::request('openid_assoc_type'), 'expires_in'=>$this->expires_in, 'dh_modulus'=>base64_decode(self::request('openid_dh_modulus')), 'dh_gen'=>base64_decode(self::request('openid_dh_gen')), 'dh_consumer_public'=>base64_decode(self::request('openid_dh_consumer_public')), 'owner_id'=>AppResource::$member->id));
		$request = $this->applyDefaultValueRules($request);
		$request->enc_mac_key = self::generateEncryptedSharedKey($request);
		$request->dh_server_public = self::generatePublicKey($request);
		OpenidRequest::save($request);
		$request->dh_modulus = null;
		$request->dh_gen = null;
		$request->dh_consumer_public = null;
		return $this->sendDirectResponseForAssociation($request);
	}
}
