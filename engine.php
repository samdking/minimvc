<?php

abstract class Engine
{
	static function get($type)
	{
		$class_name = $type . 'Engine';
		if (!class_exists($class_name))
			include dirname(__FILE__) . '/engines/' . $class_name . '.php';
		$obj = new $class_name;
		$obj->init();
		return $obj;
	}

	abstract function init();
}