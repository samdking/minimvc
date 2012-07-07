<?php

class Autoloader
{
	static $mappings = array();

	static function get($class_name)
	{
		if (isset(self::$mappings[$class_name]))
			include self::$mappings[$class_name];
	}

	static function define($mappings)
	{
		self::$mappings = $mappings;
	}

	static function system($files)
	{
		self::load($files, SYS_PATH);
	}

	static function app($files)
	{
		self::load($files, APP_PATH);
	}

	static function load($files, $location)
	{
		foreach($files as $file) {
			if (file_exists($path = $location . $file))
				include $path;
			else
				self::error('The file ' . $path . ' cannot be found');
		}
	}

	static function error($msg)
	{
		exit('<span class="error">' . $msg . '</span>');
	}
}