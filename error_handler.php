<?php

function exceptions_error_handler($severity, $message, $filename, $lineno) {
  if (error_reporting() == 0) {
    return;
  }
  if (error_reporting() & $severity) {
	header("HTTP/1.1 500 Internal Server Error");
	$args = search_and_destroy_ee(func_get_args());
    throw new ErrorException($message.' (BACKTRACE: '.print_r($args, 1).')', 0, $severity, $filename, $lineno);
  }
}

function search_and_destroy_ee(&$array) {
	foreach ($array as $key => $val) {
		if (strtolower($key) == 'ee') {
			unset($array[$key]);
		}
		if (is_array($val)) {
			$array[$key] = search_and_destroy_ee($val);
		}
	}
	return $array;
}

function exception_handler($exception) {
	header("HTTP/1.1 500 Internal Server Error");
	echo $exception->getMessage();
	exit;
}

set_exception_handler('exception_handler');

set_error_handler('exceptions_error_handler');