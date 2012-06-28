<?php

class Query_set implements Iterator, ArrayAccess, Countable
{
	private $model;
	public $engine;
	private $single_result = false;
	private $result;

	function get_result($refresh = false, $single = false)
	{
		if (!is_null($this->result) && !$refresh)
			return $this->result;
		$r = $this->make_objects($this->engine->result());
		return $this->result = $single? reset($r) : $r;
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
		$this->init();
	}

	function __clone()
	{
		$this->init();
	}

	function init()
	{
		$class = $this->model;
		$this->result = NULL;
		$this->engine = Engine::get($class::$engine);
		$this->engine->from($class::$db_table);
	}

	function one()
	{
		$this->limit(1)->get_result(false, true);
		return $this->result? $this->result : false;
	}

	function find($value)
	{
		$this->engine->where(array('id'=>$value));
		$this->get_result(false, true);
		return $this->result? $this->result : false;
	}

	function first()
	{
		$this->limit(1)->get_result(false, true);
		return $this->result? $this->result : false;
	}

	function last()
	{
		$this->limit(1)->order('id desc')->get_result(false, true);
		return $this->result? $this->result : false;
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

	function first_or_create()
	{
		if ($obj = $this->first())
			return $obj;
		$new = clone $this;
		return $new->find($this->engine->insert()->execute()->last_id());
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

	function insert($values = array())
	{
		$this->engine->insert($values);
		return $this;
	}

	function update($values)
	{
		$this->engine->update($values);
		return $this;
	}

	function delete()
	{
		$this->engine->delete();
		return $this;
	}

	function bulk_clear()
	{
		$this->engine->truncate();
		return $this;
	}
}

include dirname(__FILE__) . '/sql_classes.php';