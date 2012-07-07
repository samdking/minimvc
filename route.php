<?php

class Route
{
	
	static $internal;
	static $method;
	static $pregs = array(
		':text' => '[a-zA-Z]+',
		':num' => '\d',
		':any' => '.*'
	);


	private static function to_preg($path)
	{
		return '/^' . str_replace('/', '\/', str_replace(array_keys(self::$pregs), self::$pregs, $path)) . '$/';
	}


	static function get($path, $destination)
	{
		$key = self::to_preg($path);
		self::$internal['GET'][$key] = $destination;
	}


	static function post($path, $destination)
	{
		$key = self::to_preg($path);
		self::$internal['POST'][$key] = $destination;
	}


	private static function find($uri)
	{
		foreach(self::$internal[self::$method] as $key => $val)
			if (preg_match($key, $uri, $args)) {
				$route = $val;
				break;
			}
		
		if (!isset($route))
			throw new Exception('Route "' . $uri . '" not found');

		array_shift($args);

		return array($route, $args);
	}


	static function resolve()
	{
		define('URL_PATH', str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME'])) . '/');
		
		self::$method = $_SERVER['REQUEST_METHOD'];
		$uri = str_replace(URL_PATH , '', str_replace('index.php', '', $_SERVER['REQUEST_URI']));
		if ($uri == '')
			$uri = '/';

		try {
			list($route, $args) = self::find($uri);
		} catch (Exception $e) {
			echo '404';
			return false;
		}

		if (!is_a($route, 'Closure')) {
			$parts = explode(':', $route);
			$route = array(Controller::get($parts[0]), 'action_' . $parts[1]);
		}

		call_user_func_array($route, $args);
	}


	static function redirect($route)
	{
		$route = $route == '/'? '' : $route;
		header('location: ' . URL_PATH . $route);
		exit;
	}

}