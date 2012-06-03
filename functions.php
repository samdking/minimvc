<?php

function error($msg)
{
	exit('<span class="error">' . $msg . '</span>');
}

function format_mins($mins)
{
	$hours = floor($mins/60);
	$mins = $mins%60;
	return $hours . ':' . ($mins < 10? '0' . $mins : $mins);
}