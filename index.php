<?php
session_start();
date_default_timezone_set('US/Central');
set_include_path(get_include_path() . PATH_SEPARATOR . str_replace(sprintf('%sindex.php', DIRECTORY_SEPARATOR), '', $_SERVER['SCRIPT_FILENAME']));
set_include_path(get_include_path() . PATH_SEPARATOR . str_replace(sprintf('%sindex.php', DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR . 'app', $_SERVER['SCRIPT_FILENAME']));
//set_include_path(get_include_path() . PATH_SEPARATOR . '../6d/app');
if(file_exists('AppConfiguration.php')){
	class_exists('AppConfiguration') || require('AppConfiguration.php');	
}
class_exists('FrontController') || require('lib/FrontController.php');
class_exists('NotificationCenter') || require('lib/NotificationCenter.php');
class_exists('Log') || require('lib/Log.php');
class_exists('Application') || require('Application.php');
$application = new Application();
$front_controller = new FrontController($_REQUEST);
FrontController::$delegate = $application;
$logger = new Log('logs/', 0, false, null);
set_error_handler(array($front_controller, 'errorDidHappen'), E_ALL);
set_exception_handler(array($front_controller, 'exceptionDidHappen'));
$output = $front_controller->execute();
echo $output;
?>