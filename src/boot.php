<?php

spl_autoload_register(function ($class)
{
	$prefix = 'Kondrashov\\DownDetector';
	$base_dir = __DIR__ . '/lib/';
	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		return;
	}

	$relative_class = substr($class, $len);
	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

	if (file_exists($file)) {
		require $file;
	}
});

const ROOT_DIR = __DIR__ . '/..';

require __DIR__ . '/../vendor/autoload.php';