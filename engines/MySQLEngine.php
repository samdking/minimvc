<?php

class MySQLEngine extends Engine
{
	private $driver;
	private $sql = array();
	private $query_type;

	function init()
	{
		global $db;
		$this->driver = DB_driver::get($db['type']);
	}

	private function construct_sql()
	{
		switch ($this->query_type) {
			case 'insert':
				$sql = 'INSERT INTO `' . $this->sql['table'] . '` (' . implode(', ', $this->sql['fields']) . ')';
				$sql.= ' VALUES ' . implode(', ', $this->sql['values']);
				return $sql;
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
			$sql .= ' WHERE ' . $this->params($this->sql['where'], ' AND ');

		if (isset($this->sql['order']))
			$sql .= ' ORDER BY ' . $this->sql['order'];

		if (isset($this->sql['limit']))
			$sql .= ' LIMIT ' . $this->sql['offset'] . ', ' . $this->sql['limit'];

		return $sql;
	}

	function result($fetch_object = false)
	{
		$this->execute();
		return $this->driver->result($fetch_object);
	}

	function execute()
	{
		$sql = $this->construct_sql();
		if (DEBUG)
			echo('<p>' . $sql . '</p>');
		$this->driver->query($sql);
		if ($error = $this->driver->error())
			throw new Exception($error . ' in ' . $sql);
		return $this;
	}

	function last_id()
	{
		return $this->driver->insert_id();
	}

	function from($value)
	{
		$this->sql['table'] = $value;
		return $this;
	}

	function limit($val)
	{
		$this->sql['offset'] = isset($this->sql['offset'])? $this->sql['offset'] : 0;
		$this->sql['limit'] = $val;
	}

	function order($val)
	{
		$this->sql['order'] = $val;
	}

	function offset($val)
	{
		$this->sql['offset'] = $val;
	}

	function insert($vals = array())
	{
		$vals = $vals? $vals : (array)@$this->sql['where'];
		if (!isset($this->sql['fields']))
			$this->sql['fields'] = array_keys($vals);
		$this->sql['values'][] = '(' . $this->params(array_values($vals)) . ')';
		$this->query_type = 'insert';
		return $this;
	}

	function update($vals)
	{
		$this->sql['set'] = $this->params($vals);
		$this->query_type = 'update';
		$this->execute();
		return $this;
	}

	function delete()
	{
		$this->query_type = 'delete';
		$this->execute();
		return $this;
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
		$this->sql['where'] = $conditions;
		return $this;
	}

	function truncate()
	{
		$this->query_type = 'truncate';
		$this->execute();
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
