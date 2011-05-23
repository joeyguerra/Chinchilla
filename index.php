<?php
date_default_timezone_set("US/Central");
ini_set("auto_detect_line_endings",true);
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . "/app");
require("app.php");
set_error_handler('error_did_happen', E_ALL);
set_exception_handler('exception_did_happen');
require("settings.php");
require("resources/AppResource.php");
require("controllers/InstallController.php");
NotificationCenter::add("AppResource", "begin_request");
NotificationCenter::add("AppResource", "resource_not_found");
NotificationCenter::add(new PluginController(), "begin_request");
NotificationCenter::add(new InstallController(), "query_failed");
echo FrontController::execute(new Request($_REQUEST, $_FILES, $_SERVER));