<?php

session_start();
date_default_timezone_set('Europe/London');

define('APP_PATH', realpath( __DIR__ . '/../app') . '/');
define('SYS_PATH', __DIR__ . '/');

include 'system/autoloader.php';

spl_autoload_register(array('Autoloader', 'get'));

Autoloader::define(array(
	'Query_set' => 'query_set.php',
	'Model' => 'model.php',
	'Engine' => 'engine.php',
	'Route' => 'route.php',
	'Controller' => 'controller.php',
	'View' => 'view.php'
));

Autoloader::system(array(
	'functions.php',
));

Autoloader::app(array(
	'routing.php',
	'config.php'
));