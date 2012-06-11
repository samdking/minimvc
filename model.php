<?php

class Model
{
	protected static $engine;
	protected $properties;
	protected $is_factory;
	
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

	static function engine()
	{
		$engine = Engine::get('mysql');
		$engine->from(static::$db_table);
		return $engine;
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
		return new Query_set(get_class($this));
	}

	function populate($props)
	{
		$this->properties = $props;
	}

	function save()
	{
		if ($this->id)
			$this->update($this->properties);
		else
			$this->create();
	}

	function update($props)
	{
		static::engine()->update($props, array('id' => $this->id))->execute();
	}

	function bulk_clear()
	{
		static::engine()->truncate()->execute();
		return $this;
	}

	function bulk_create($props)
	{
		$engine = static::engine();
		foreach($props as $row) {
			$obj = new $this;
			$obj->populate($row);
			$obj->action('before_write');
			$engine->insert($obj->properties);
		}
		$engine->execute();
	}

	function create($props = false)
	{
		if ($this->is_factory) {
			$obj = new $this;
			$obj->populate($props);
			return $obj->create();
		}
		$this->action('before_write');
		$this->id = static::engine()->insert($this->properties)->execute()->last_id();
		return $this;
	}

}