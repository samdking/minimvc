<?php

abstract class Engine
{
	static function get($type)
	{
		$class_name = $type . '_Engine';
		if (!class_exists($class_name))
			include dirname(__FILE__) . '/engines/' . $type . '/'. $type . '_engine.php';
		$obj = new $class_name;
		$obj->init();
		return $obj;
	}

	abstract function init();
}