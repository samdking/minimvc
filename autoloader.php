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
}