<?php

session_start();
date_default_timezone_set('Europe/London');

define('APP_PATH', __DIR__ . '/../app/');

include 'system/autoloader.php';
include 'system/functions.php';

spl_autoload_register(array('Autoloader', 'get'));

if (!file_exists('system/config.php'))
	error('No config file exists');

include 'system/config.php';

Autoloader::define(array(
	'Query_set' => 'query_set.php',
	'Model' => 'model.php',
	'Engine' => 'engine.php',
	'Route' => 'route.php',
	'Controller' => 'controller.php'
));

include APP_PATH . 'routing.php';