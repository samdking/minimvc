<?php

class PDO_db_driver extends DB_Driver
{
	private $handle;
	private $statement;

	function connect()
	{
		global $db;
		$this->handle = new PDO("mysql:host=".$db['server'].";dbname=".$db['name'], $db['user'], $db['pass']);  
		$this->handle->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  
	}

	function query($sql, $params)
	{
		if (!$this->statement || $this->statement->queryString != $sql)
			 $this->statement = $this->handle->prepare($sql);
		$this->statement->execute($params);
	}

	function result($fetch_object = false)
	{
		$this->statement->setFetchMode($fetch_object? PDO::FETCH_OBJ : PDO::FETCH_ASSOC);
		while($row = $this->statement->fetch())
			$data[] = $row;
		return isset($data)? $data : array();
	}

	function error()
	{
		//return $this->handle->error;
	}

	function insert_id()
	{
		return $this->handle->lastInsertId();
	}
}