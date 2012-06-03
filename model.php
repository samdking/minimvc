<?php

class Model
{
	protected $properties;
	protected static $engine;

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
		return new $class_name;
	}

	static function engine()
	{
		$engine = Engine::get('mysql');
		$engine->from(static::$db_table);
		return $engine;
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
			$this->create($this->properties);
	}

	function update($props)
	{
		static::engine()->update($props, array('id' => $this->id))->execute();
	}

	function create($props)
	{
		$props['id'] = static::engine()->insert($props)->execute()->last_id();
		$obj = new $this;
		$obj->populate($props);
		return $obj;
	}

}