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
		$engine = Engine::get('MySQL');
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

	function save($execute = true)
	{
		$this->action('before_write');
		if ($this->id)
			$this->update($this->properties);
		else
			$e = static::engine()->insert($this->properties);
		if ($execute && isset($e))
			$this->id = $e->execute()->last_id();
		return $this;
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
		foreach($props as $row)
			$this->create($row, false);
		static::engine()->execute();
	}

	function delete()
	{
		static::engine()->delete(array('id' => $this->id))->execute();
		return $this;
	}

	function create($props, $execute = true)
	{
		$obj = new $this;
		$obj->populate($props);
		return $obj->save($execute);
	}

}