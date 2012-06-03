<?php

class Query_set implements Iterator, ArrayAccess
{
	private $model;
	private $engine;
	private $single_result = false;
	private $result;

	function get_result()
	{
		if (!$this->result)
			$this->result = $this->make_objects($this->engine->result());
	}

	function offsetExists($key)
	{
		$this->get_result();
		return isset($this->result[$key]);
	}

	function offsetGet($key)
	{
		$this->get_result();
		if (isset($this->result[$key]))
			return $this->result[$key];
	}

	function offsetSet($key, $value)
	{
		$this->get_result();
		$this->result[$key] = $value;
	}

	function offsetUnset($key)
	{
		$this->get_result();
		unset($this->result[$key]);
	}
	
	function rewind() 
	{
		$this->get_result();
		reset($this->result);
    }

    function current() 
	{
		$this->get_result();
        return current($this->result);
    }

    function key() 
	{
		$this->get_result();
        return key($this->result);
    }

    function next() 
	{
		$this->get_result();
         return next($this->result);
    }

    function valid() 
	{
		$this->get_result();
        return false !== current($this->result);
    }

	function __construct($model)
	{
		$this->model = $model;
		$this->engine = $model::engine();
	}

	function find($value)
	{
		$this->engine->select(array('id'=>$value));
	}

	function first($conditions = array())
	{
		$this->filter($conditions)->limit(1)->get_result();
		$this->result = reset($this->result);
		return $this->result? $this->result : NULL;
	}

	function last($conditions = array())
	{
		$this->filter($conditions)->limit(1)->order('id desc')->get_result();
		$this->result = reset($this->result);
		return $this->result? $this->result : NULL;
	}

	function make_objects($arr)
	{
		if (empty($arr))
			return array();
		foreach($arr as $data) {
			$obj = new $this->model;
			$obj->populate($data);
			$objects[] = $obj;
		}
		return $objects;
	}

	function order($value)
	{
		$this->engine->order($value);
		return $this;
	}

	function first_or_create($params)
	{
		$obj = $this->filter($params)->first();
		if (!$obj) {
			$obj = Model::get($this->model)->create($params);
		}
		return $obj;
	}

	function limit($params)
	{
		$this->engine->limit($params);
		return $this;
	}

	function filter($where)
	{
		if (!empty($where))
			$this->engine->where($where);
		return $this;
	}

	function values($fields)
	{
		$this->engine->select($fields);
		$result = $this->engine->result();
		$array = array();
		foreach($result as $i=>$row)
			if (is_array($fields))
				foreach($fields as $field)
					$array[$i][$field] = $row[$field];
			else
				$array[$i] = $row[$fields];
		$this->result = $array;
		return $array;
	}
}