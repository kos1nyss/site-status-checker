<?php

require __DIR__ . '/../boot.php';

use PhpAmqpLib\Message\AMQPMessage;
use Softline\Core\Logger\Log;
use Softline\Core\Logger\Logger;
use Softline\Core\Mail\Mail;
use Softline\Core\Message\MessageConsumer;
use Softline\Core\Message\RoutingKey;

$logger = new Logger();

$callback = function (AMQPMessage $msg) use ($logger) {
	$logger->add(
		'Сработал обработчик сообщения по ключу sendMail' . PHP_EOL
		. 'Данные для обработки: ' . $msg->getBody(),
		Log::INFO,
	);

	$data = json_decode($msg->getBody(), true, 512, JSON_THROW_ON_ERROR);

	$mail = new Mail();
	$mail->send(
		$data['email'],
		$data['title'],
		$data['text'],
	);

	$msg->ack();
};

$logger = new Logger();
$logger->add(
	'Обработчик успешно запущен.',
	Log::INFO,
);


$messageConsumer = new MessageConsumer();
$messageConsumer->consume($callback, RoutingKey::SEND_MAIL);