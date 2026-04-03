<?php


namespace Softline\Core\Message;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class MessageBroker
{
	private static ?MessageBroker $instance = null;
	private AMQPChannel $channel;
	private AMQPStreamConnection $connection;


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

	private function __construct()
	{
		$this->connect();
	}

	private function connect(): void
	{
		$connection = new AMQPStreamConnection(
			host: 'rabbitmq',
			port: 5672,
			user: 'softline',
			password: 'test',
		);

		$this->channel = $connection->channel();
		$this->connection = $connection;
	}

	public function close(): void
	{
		$this->getChannel()->close();
		$this->getConnection()->close();
	}

	public function declareQueue(string $queue): void
	{
		$this
			->getChannel()
			->queue_declare(
				queue: $queue,
				passive: false,
				durable: true,
				exclusive: false,
				auto_delete: false,
			)
		;
	}

	public function getChannel(): AMQPChannel
	{
		return $this->channel;
	}

	public function getConnection(): AMQPStreamConnection
	{
		return $this->connection;
	}
}