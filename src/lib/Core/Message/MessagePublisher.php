<?php

namespace Softline\Core\Message;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class MessagePublisher
{
	private AMQPChannel $channel;

	public function __construct()
	{
		$this->channel = MessageBroker::getInstance()->getChannel();
	}

	public function publish(AMQPMessage $message, string $queue = ''): void
	{
		$this
			->channel
			->basic_publish(
				msg: $message,
				routing_key: $queue,
			)
		;
	}
}
