<?php

require __DIR__ . '/../autoload.php';

use PhpAmqpLib\Message\AMQPMessage;
use Softline\Core\Message\MessageConsumer;
use Softline\Core\Message\RoutingKey;

$callback = function (AMQPMessage $msg)
{
	$data = json_decode($msg->getBody(), true);

	$mail = new \Softline\Core\Mail\Mail();
	$mail->send(
		$data['email'],
		$data['title'],
		$data['text'],
	);

	file_put_contents('text.log', $msg->getBody());
};

$messageConsumer = new MessageConsumer();
$messageConsumer->consume($callback, RoutingKey::SEND_MAIL);