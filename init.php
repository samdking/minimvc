<?php

include 'system/sql_classes.php';
include 'system/query_set.php';
include 'system/model.php';
include 'system/engine.php';
include 'system/functions.php';

if (!file_exists('system/config.php'))
	error('No config file exists');

include 'system/config.php';