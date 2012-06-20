<?php

class Model
{
	protected static $default_engine = 'MySQL';
	protected static $db_table;
	protected $properties;
	protected $is_factory;
	public $engine;

	function __construct()
	{
		$this->engine = Engine::get(static::$default_engine);
		$this->engine->from(static::$db_table);
	}

	function __call($method, $args)
	{
		$queryset = $this->query_set();
		if (method_exists($queryset, $method))
			return call_user_func_array(array($queryset, $method), $args);
		return $this;
	}

	function __get($prop)
	{
		if (isset($this->properties[$prop]))
			return $this->properties[$prop];
	}

	function __set($prop, $value)
	{
		$this->properties[$prop] = $value;
	}

	static function get($model)
	{
		$class_name = strpos($model, '_model') === false? $model . '_model' : $model;
		if (!class_exists($class_name))
			include dirname(__FILE__) . '/../app/models/' . $class_name . '.php';
		$factory = new $class_name;
		$factory->is_factory = true;
		return $factory;
	}

	function action($method)
	{
		$params = func_get_args();
		array_shift($params);
		if (method_exists($this, $method))
			call_user_func_array(array($this, $method), $params);
	}

	function query_set()
	{
		return new Query_set($this);
	}

	function populate($props)
	{
		$this->properties = $props;
	}

	function save($execute = true, $force_insert = false)
	{
		$this->action('before_write');
		if ($this->id && !$force_insert)
			$this->update($this->properties);
		else
			$e = $this->engine->insert($this->properties);
		if ($execute && isset($e))
			$this->id = $e->execute()->last_id();
		return $this;
	}

	function update($props)
	{
		$this->engine->update($props, array('id' => $this->id))->execute();
	}

	function bulk_clear()
	{
		$this->engine->truncate()->execute();
		return $this;
	}

	function bulk_create($props)
	{
		foreach($props as $row)
			$this->create($row, false);
		$this->engine->execute();
	}

	function delete()
	{
		$this->engine->delete(array('id' => $this->id))->execute();
		return $this;
	}

	function create($props, $execute = true)
	{
		$obj = new $this;
		$obj->populate($props);
		return $obj->save($execute, true);
	}

}
