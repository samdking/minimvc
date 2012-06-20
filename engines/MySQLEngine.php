<?php

class MySQLEngine extends Engine
{

	private $sql = array();
	private $query_type;
	private $handle;
	private $result;

	function init()
	{
		global $db;
		$this->handle = new mysqli($db['server'], $db['user'], $db['pass'], $db['name']);
		if ($this->handle->connect_error)
	 		exit('Connect Error (' . $this->handle->connect_errno . ') ' . $this->handle->connect_error);
		$this->handle->set_charset("utf8");
	}

	private function construct_sql()
	{
		switch ($this->query_type) {
			case 'insert':
				$sql = 'INSERT INTO `' . $this->sql['table'] . '` (' . implode(', ', $this->sql['fields']) . ')';
				$sql.= ' VALUES ' . implode(', ', $this->sql['values']);
			break;
			case 'update':
				$sql = 'UPDATE `' . $this->sql['table'] . '` SET ' . $this->sql['set'];
			break;
			case 'select':
			default:
				$sql = 'SELECT ' . (isset($this->sql['fields'])? $this->sql['fields'] : '*');
				$sql.= ' FROM `' . $this->sql['table'] . '`';
			break;
			case 'truncate':
				$sql = 'TRUNCATE `' . $this->sql['table'] . '`';
			break;
		}
		
		if (isset($this->sql['where']))
			$sql .= ' WHERE ' . $this->sql['where'];

		if (isset($this->sql['order']))
			$sql .= ' ORDER BY ' . $this->sql['order'];

		if (isset($this->sql['limit']))
			$sql .= ' LIMIT ' . $this->sql['limit'];

		return $sql;
	}

	function result($fetch_object = false)
	{
		$this->execute();
		$func = $fetch_object? 'fetch_object' : 'fetch_assoc';
		if (is_object($this->result))
			while($row = $this->result->$func())
				$data[] = $row;
		return isset($data)? $data : array();
	}

	function execute()
	{
		$sql = $this->construct_sql();
		if (DEBUG)
			echo('<p>' . $sql . '</p>');
		$this->result = $this->handle->query($sql);
		if ($error = $this->handle->error)
			throw new Exception($error . ' in ' . $sql);
		$table = $this->sql['table'];
		$this->sql = array();
		if ($table)
			$this->sql['table'] = $table;
		return $this;
	}

	function last_id()
	{
		return $this->handle->insert_id;
	}

	function from($value)
	{
		$this->sql['table'] = $value;
	}

	function limit($val)
	{
		$this->sql['limit'] = isset($this->sql['limit'])? $this->sql['limit'] . ', ' . $val : $val;
	}

	function order($val)
	{
		$this->sql['order'] = $val;
	}

	function offset($val)
	{
		$this->sql['limit'] = isset($this->sql['limit'])? $val . ', ' . $this->sql['limit'] : $val;
	}

	function insert($vals)
	{
		if (!isset($this->sql['fields']))
			$this->sql['fields'] = array_keys($vals);
		$this->sql['values'][] = '(' . $this->params(array_values($vals)) . ')';
		$this->query_type = 'insert';
		return $this;
	}

	function update($vals, $conditions = false)
	{
		$this->sql['set'] = $this->params($vals);
		if ($conditions)
			$this->sql['where'] = $this->params($conditions, ' AND ');
		$this->query_type = 'update';
		return $this;
	}

	function delete($conditions)
	{
		$this->sql['where'] = $this->params($conditions);
		$this->query_type = 'delete';
	}

	function select($values)
	{
		$values = parse_args(func_get_args());
		foreach((array)$values as $v)
			$fields[] = '`' . $v . '`';
		$this->sql['fields'] = implode(', ', $fields);
		return $this;
	}

	function where($conditions)
	{
		$this->sql['where'] = $this->params($conditions, ' AND ');
		return $this;
	}

	function truncate()
	{
		$this->query_type = 'truncate';
		return $this;
	}

	private function params($params, $join = ', ')
	{
		foreach($params as $key=>$val) {
			if (!is_object($val))
				$val = new SQLCommand($val);
			$conditions[] = is_int($key)? $val->value() : "`$key` " . $val;
		}
		return implode($join, $conditions);
	}
}
