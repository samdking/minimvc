<?php

abstract class DB_driver
{
	protected static $inst;

	abstract function connect();

	static function get($type)
	{
		$class_name = $type . '_db_driver';
		if (!class_exists($class_name))
			include dirname(__FILE__) . '/drivers/' . $type . '.php';
		return $class_name::inst();
	}

	static function inst()
	{
		if (is_null(static::$inst)) {
			static::$inst = new static;
			static::$inst->connect();
		}
		return static::$inst;
	}
}