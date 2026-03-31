<?php

declare(strict_types=1);

use Softline\Application;

spl_autoload_register(function ($class)
{
	$prefix = 'Softline\\';
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

echo shell_exec('ls -la');
require __DIR__ . '/../vendor/autoload.php';

$app = new Application();
// $app->run();

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

sleep(10);
$connection = new AMQPStreamConnection(
	'rabbitmq',
	5672,
	'softline',
	'test',
);
$channel = $connection->channel();

var_dump($connection);
var_dump($channel);