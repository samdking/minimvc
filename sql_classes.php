<?php

/*
	YEAR(date) = 2012		'date' => new Year(2012)
	NOW() 					'date' => new Now
	name LIKE '%on%' 		'name' = new Contains('on')
*/

abstract class Like extends SQLCommand
{
	protected $operator = 'LIKE';
}

class Now extends SQLCommand
{
	function value($value = false)
	{
		return 'NOW()';
	}	
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
	function value($value)
	{
		return "'%" . $value . "'";
	}
}

class EndsWith extends Like
{
	function value($value)
	{
		return "'" . $value . "%'";
	}
}

class Contains extends Like
{
	function value($value)
	{
		return "'%" . $value . "%'";
	}
}

class OneOf extends SQLCommand
{
	protected $operator = 'IN';

	function value($value)
	{
		foreach($value as $val)
			$array[] = parent::value($val);
		return '(' .  implode(', ', $array) . ')';
	}
}

class Is extends SQLCommand
{
	protected $operator = 'IS';

	function value($value)
	{
		return $value;
	}
}