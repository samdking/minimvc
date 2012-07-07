<?php

class View
{
	static $vars = array();

	static function show($file)
	{
		extract(self::$vars);
		include APP_PATH . 'views/' . $file . '.php';
	}

	static function bind($key, $val = false)
	{
		self::$vars = !is_array($key)? array($key => $val) : $key;
	}
}