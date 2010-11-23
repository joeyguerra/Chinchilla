<?php
date_default_timezone_set('US/Central');
ini_set('auto_detect_line_endings',true);
set_include_path(get_include_path() . PATH_SEPARATOR . str_replace(sprintf('%sindex.php', DIRECTORY_SEPARATOR), '', $_SERVER['SCRIPT_FILENAME']));
set_include_path(get_include_path() . PATH_SEPARATOR . str_replace(sprintf('%sindex.php', DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR . 'app', $_SERVER['SCRIPT_FILENAME']));

class_exists('App') || require('lib/App.php');
Chin_session::start();
$method = strtolower((array_key_exists('_method', $_REQUEST) ? $_REQUEST['_method'] : $_SERVER['REQUEST_METHOD']));
$resource = App::dispatch($method, $_GET['r'], array_merge($_SERVER, $_REQUEST));
if($resource->status !== null){
	$resource->status->send();		
}
if(count($resource->headers) > 0){
	foreach($resource->headers as $header){
		$header->send();
	}
}
echo $resource->output;
?>