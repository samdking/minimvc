<?php

class DB_driver
{
	static function get($type)
	{
		$class_name = $type . '_db_driver';
		if (!class_exists($class_name))
			include dirname(__FILE__) . '/drivers/' . $type . '.php';

		return new $class_name;
	}
}