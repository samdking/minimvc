<?php

class Model
{
	static $db_table;
	static $engine = 'sql';
	protected $properties;
	protected $is_factory = false;

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

	function save($force_insert = false)
	{
		$query = $this->query_set();
		$this->action('before_write');
		if ($this->id && !$force_insert)
			$this->update($this->properties);
		else
			$this->id = $query->insert($this->properties)->engine->execute()->last_id();
		return $this;
	}

	function update($props)
	{
		$this->query_set()->filter(array('id' => $this->id))->update($props);
	}

	function bulk_create($props)
	{
		$query = $this->query_set();
		foreach($props as $row)
			$query->insert($row);
		$query->engine->execute();
	}

	function delete()
	{
		$this->query_set()->filter(array('id' => $this->id))->delete();
		return $this;
	}

	function create($props)
	{
		$obj = new $this;
		$obj->populate($props);
		return $obj->save(true);
	}

}