<?php

class Controller
{
	static function get($controller)
	{
		$class_name = self::load($controller);
		$factory = new $class_name;
		$factory->is_factory = true;
		return $factory;
	}

	static function load($controller)
	{
		$class_name = $controller . '_Controller';
		if (!class_exists($class_name))
			include APP_PATH . 'controllers/'. $controller . '_controller.php';
		$class_name::init();
		return $class_name;
	}
	
	function init()
	{

	}

}