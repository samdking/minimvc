<?php

class Mysqli_db_driver extends DB_driver
{
	private $handle;
	private $result;

	function connect()
	{
		global $db;
		$this->handle = new mysqli($db['server'], $db['user'], $db['pass'], $db['name']);
		if ($this->handle->connect_error)
	 		exit('Connect Error (' . $this->handle->connect_errno . ') ' . $this->handle->connect_error);
		$this->handle->set_charset("utf8");
	}

	function query($sql)
	{
		$this->result = $this->handle->query($sql);
	}

	function fetch_assoc()
	{
		return $this->result->fetch_assoc();
	}

	function fetch_object()
	{
		return $this->result->fetch_object();
	}

	function result($fetch_object = false)
	{
		$func = $fetch_object? 'fetch_object' : 'fetch_assoc';
		if (is_object($this->result))
			while($row = $this->result->$func())
				$data[] = $row;
		return isset($data)? $data : array();
	}

	function error()
	{
		return $this->handle->error;
	}

	function insert_id()
	{
		return $this->handle->insert_id;
	}
}