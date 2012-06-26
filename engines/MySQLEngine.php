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
		$this->driver->connect();
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
		$table = $this->sql['table'];
		$this->sql = array();
		if ($table)
			$this->sql['table'] = $table;
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

class SQLCommand
{
	protected $value;
	protected $operator = '=';

	function __construct($value = false)
	{
		$this->value = $value;
	}

	function __tostring()
	{
		return $this->operator . ' ' . $this->value($this->value);	
	}

	function value($value = false)
	{
		$value = $value? $value : $this->value;
		$value = $this->driver->escape($value);
		$val = !is_numeric($value) || empty($value)? "'" . $value . "'" : $value;
		return $val;
	}
}
