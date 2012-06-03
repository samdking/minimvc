<?php

class Engine
{
	function __construct()
	{
		if (method_exists($this, 'init'))
			$this->init();
	}

	static function get($type)
	{
		$class_name = $type . 'Engine';
		if (!class_exists($class_name))
			include 'engines/' . $class_name . '.php';
		return new $class_name;
	}
}