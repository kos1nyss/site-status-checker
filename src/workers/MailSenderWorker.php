<?php

require __DIR__ . '/../autoload.php';

echo 'worker started' . PHP_EOL;

use PhpAmqpLib\Message\AMQPMessage;
use Softline\Message\MessageBroker;

$callback = function (AMQPMessage $msg)
{
	echo 'handled' . PHP_EOL;

	file_put_contents('text.log', $msg->getBody());
};

$messageBroker = MessageBroker::getInstance();
$messageBroker->waitMessages($callback, 'hello');