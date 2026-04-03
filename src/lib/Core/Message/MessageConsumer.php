<?php

namespace Softline\Core\Message;

use PhpAmqpLib\Channel\AMQPChannel;

class MessageConsumer
{
	private MessageBroker $messageBroker;
	private AMQPChannel $channel;

	public function __construct()
	{
		$this->messageBroker = MessageBroker::getInstance();
		$this->channel = $this->messageBroker->getChannel();
	}

	public function consume(\Closure $callback, RoutingKey $routingKey): void
	{
		$this
			->channel
			->basic_consume(
				queue: $routingKey->value,
				callback: $callback,
			)
		;
		while ($this->channel->is_open())
		{
			$this->channel->wait();
		}

		$this->messageBroker->close();
	}
}
