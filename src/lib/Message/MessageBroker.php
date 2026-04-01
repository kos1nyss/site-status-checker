<?php

namespace Softline\Message;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MessageBroker
{
	private static ?MessageBroker $instance = null;
	private AMQPChannel $channel;
	private AMQPStreamConnection $connection;

	private function __construct()
	{
		$this->connect();
	}

	private function connect(): void
	{
		$connection = new AMQPStreamConnection(
			'rabbitmq',
			5672,
			'softline',
			'test',
		);

		$this->channel = $connection->channel();
		$this->connection = $connection;
	}

	public function declareQueue(string $queue): void
	{
		$this->channel->queue_declare($queue, false, true, false, false);
	}

	public function publishMessage(AMQPMessage $message, string $queue = ''): void
	{
		$this->channel->basic_publish($message, '', $queue);
	}

	public function waitMessages(\Closure $callback, string $queue): void
	{
		$this->channel->basic_consume($queue, '', false, false, false, false, $callback);
		while ($this->channel->is_open())
		{
			$this->channel->wait();
		}

		$this->close();
	}

	public function close(): void
	{
		$this->channel->close();
		$this->connection->close();
	}

	public static function getInstance(): static
	{
		if (static::$instance !== null)
		{
			return static::$instance;
		}

		$instance = new MessageBroker();
		static::$instance = $instance;

		return $instance;
	}
}
