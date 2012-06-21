<?php

class Mysql_db_driver extends DB_driver
{
	private $result;

	function connect()
	{
		global $db;
		mysql_connect($db['server'], $db['user'], $db['pass']) or 
		die('Connect Error ' . mysql_error() . ')');
		mysql_select_db($db['name']);
	}

	function query($sql)
	{
		$this->result = mysql_query($sql);
	}

	function result($fetch_object = false)
	{
		$func = $fetch_object? 'mysql_fetch_object' : 'mysql_fetch_assoc';
		while ($row = $func($this->result))
			$data[] = $row;
		return isset($data)? $data : array();
	}

	function error()
	{
		return mysql_error();
	}

	function insert_id()
	{
		return mysql_insert_id();
	}
}