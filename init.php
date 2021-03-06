<?php

session_start();
date_default_timezone_set('Europe/London');

include 'system/query_set.php';
include 'system/model.php';
include 'system/engine.php';
include 'system/engines/db_driver.php';
include 'system/functions.php';

if (!file_exists('system/config.php'))
	error('No config file exists');

include 'system/config.php';