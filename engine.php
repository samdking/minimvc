<?php

abstract class Engine
{
	static function get($type)
	{
		$class_name = self::load($type);
		$obj = new $class_name;
		$obj->init();
		return $obj;
	}

	static function load($engine)
	{
		$class_name = $engine . '_Engine';
		if (!class_exists($class_name))
			include dirname(__FILE__) . '/engines/' . $engine . '/'. $engine . '_engine.php';
		return $class_name;
	}

	abstract function init();
}