<?php

function error($msg)
{
	exit('<span class="error">' . $msg . '</span>');
}

function parse_args($args)
{
	if (is_array($args[0]) || count($args) == 1)
		return $args[0];
	else
		return $args;
}

function redirect($uri)
{
	header('location: ' . $uri);
	exit;
}