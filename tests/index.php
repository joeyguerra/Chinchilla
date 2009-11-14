<?php
	date_default_timezone_set('America/Chicago');
	$_appPath = str_replace('/tests/index.php', '', __FILE__);
	set_include_path(get_include_path() . PATH_SEPARATOR . $_appPath . PATH_SEPARATOR);
	require('AppConfiguration.php');
	if(!isset($_SESSION))
		$_SESSION = array();
	
	$root = str_replace('index.php', '', __FILE__);
	$unit = $root . 'unit/';
	$folder = dir($unit);
	while (false !== ($entry = $folder->read())){
		$path = $unit . $entry;
		if($entry != '.' && $entry != '..' && file_exists($path)){
			require($path);
			$pieces = explode('/', $path);
			$className = str_replace('.php', '', $pieces[count($pieces)-1]);
			$test = new $className();
			$test->execute();
			echo $test->message();
		}
	}
	$folder->close();	
?>