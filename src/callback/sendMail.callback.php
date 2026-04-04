<?php

require __DIR__ . '/../boot.php';

use PhpAmqpLib\Message\AMQPMessage;
use Softline\Core\Logger\Log;
use Softline\Core\Logger\Logger;
use Softline\Core\Message\MessageConsumer;
use Softline\Core\Message\RoutingKey;

$logger = new Logger();

$callback = function (AMQPMessage $msg) use ($logger) {
	$logger->add(
		'Сработал обработчик сообщения по ключу ' . Log::GENERAL->value . PHP_EOL
		. 'Данные для обработки: ' . $msg->getBody(),
		Log::GENERAL,
	);

	$data = json_decode($msg->getBody(), true, 512, JSON_THROW_ON_ERROR);

	$mail = new \Softline\Core\Mail\Mail();
	$mail->send(
		$data['email'],
		$data['title'],
		$data['text'],
	);

	$msg->ack();
};

$messageConsumer = new MessageConsumer();
$messageConsumer->consume($callback, RoutingKey::SEND_MAIL);