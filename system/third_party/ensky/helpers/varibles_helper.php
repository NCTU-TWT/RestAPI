<?php
function IssetOrEmpty ($array,$check) {
	foreach ($check as $row) {
		if ( !isset($array[$row]) or empty($array[$row]) ) {
			return $row;
		}
	}
	return False;
}

function p_e ($var) {
	echo '<pre>';
	print_r($var);
	echo '</pre>';
	exit;
}

function d_e ($var) {
	echo '<pre>';
	var_dump($var);
	echo '</pre>';
	exit;
}

function is_num ($var) {
	return is_int($var) ? True : ctype_digit($var);
}

function is_num_float ($var) {
	return is_int($var) ? True : ( ctype_digit($var) ? True : preg_match('/\d+\.\d+/',$var));
}