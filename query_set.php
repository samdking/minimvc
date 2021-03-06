<?php

class Query_set implements Iterator, ArrayAccess, Countable
{
	private $model;
	private $engine;
	private $single_result = false;
	private $result;

	function get_result()
	{
		if (is_null($this->result))
			$this->result = $this->make_objects($this->engine->result());
		return $this->result;
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

    function count()
	{
		$this->get_result(); 
		return count($this->result);
	}

	/* End required functions */

	function __construct($model)
	{
		$this->model = get_class($model);
		$this->engine = $model->engine;
	}

	function one()
	{

		$this->limit(1)->get_result();
		$this->result = reset($this->result);
		return $this->result? $this->result : NULL;
	}

	function find($value)
	{
		$this->engine->where(array('id'=>$value));
		$this->get_result();
		$this->result = reset($this->result);
		return $this->result? $this->result : NULL;
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

	function all()
	{
		return $this->get_result();
	}

	function values($fields)
	{
		$fields = parse_args(func_get_args());

		if (is_null($this->result)) // haven't got result yet? Great, optimise
			$result = $this->engine->select($fields)->result(true);
		else // otherwise, use existing
			$result = $this->result;

		$array = array();
		foreach($result as $i=>$row)
			if (is_array($fields))
				foreach($fields as $field)
					$array[$i][$field] = $row->$field;
			else
				$array[$i] = $row->$fields;

		if (is_null($this->result))
			$this->result = $array;

		return $array;
	}

	function update($values)
	{
		$ids = $this->values('id');
		if (!empty($ids))
			$this->engine->update($values, array('id'=>new OneOf($ids)))->execute();
	}
}