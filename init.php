<?php

session_start();
date_default_timezone_set('Europe/London');

include 'system/autoloader.php';

spl_autoload_register(array('Autoloader', 'get'));

if (!file_exists('system/config.php'))
	error('No config file exists');

include 'system/functions.php';
include 'system/config.php';

Autoloader::define(array(
	'Query_set' => 'query_set.php',
	'Model' => 'model.php',
	'Engine' => 'engine.php',
));