<?php

/*
	YEAR(date) = 2012		'date' => new Year(2012)
	NOW() 					'date' => new Now
	name LIKE '%on%' 		'name' = new Contains('on')
*/

abstract class SQLCommand
{
	protected $input;
	protected $pre;
	protected $suf;

	function __construct($input = false)
	{
		$args = func_get_args();
		$this->input = $this->input($args);
	}

	function __tostring()
	{
		return $this->operator . ' ' . $this->output();
	}

	function value()
	{
		return is_array($this->input)? $this->input : ($this->pre . $this->input . $this->suf);
	}

	function input($args)
	{
		return count($args) > 1? $args : (is_array($args[0])? $args[0] : $args[0]);
	}

	function output()
	{
		return '?';
	}
}

abstract class Like extends SQLCommand
{
	protected $operator = 'LIKE';
}

class Equals extends SQLCommand
{
	protected $operator = '=';	
}

class Now extends SQLCommand
{
	protected $output = 'NOW()';
}

class LessThan extends SQLCommand
{
	protected $operator = '<';
}

class LessThanOrEqualTo extends SQLCommand
{
	protected $operator = '<=';
}

class GreaterThan extends SQLCommand
{
	protected $operator = '>';
}

class GreaterThanOrEqualTo extends SQLCommand
{
	protected $operator = '>=';
}

class BeginsWith extends Like
{
	protected $pre = '%';
}

class EndsWith extends Like
{
	protected $suf = '%';
}

class Contains extends Like
{
	protected $pre = '%';
	protected $suf = '%';
}

class OneOf extends SQLCommand
{
	protected $operator = 'IN';

	function output()
	{
		return '(' . implode(', ', array_fill(0, count($this->input), '?')) . ')';
	}
}

class Is extends SQLCommand
{
	protected $operator = 'IS';
}