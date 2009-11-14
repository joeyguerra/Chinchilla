<?php
date_default_timezone_set('US/Central');
class_exists('FrontController') || require('lib/FrontController.php');
class_exists('NotificationCenter') || require('lib/NotificationCenter.php');
class_exists('Log') || require('lib/Log.php');
$front_controller = new FrontController();
$_start_time = microtime(true);
$logger = new Log('logs/', 1, true, FrontController::$site_path);
NotificationCenter::getInstance()->addObserver($logger, 'LogEventHasOccurred', null);
NotificationCenter::getInstance()->postNotificationName('LogEventHasOccurred', 'Request has begun ' . $_start_time, $front_controller);

if(file_exists('AppConfiguration.php')){
	class_exists('AppResource') || require('resources/AppResource.php');
	$front_controller->addObserver(new AppResource());
}
set_error_handler(array($front_controller, 'errorDidHappen'));
set_exception_handler(array($front_controller, 'exceptionDidHappen'));
echo $front_controller->execute();
$_end_time = microtime(true);

NotificationCenter::getInstance()->postNotificationName('LogEventHasOccurred', 'Request has ended ' . $_end_time, $front_controller);
$_diff = $_end_time-$_start_time;
NotificationCenter::getInstance()->postNotificationName('LogEventHasOccurred', 'Total Time: ' . $_diff, $front_controller);

?>