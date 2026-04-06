<?php


namespace Softline\Core\Message;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Softline\Core\Logger\Log;
use Softline\Core\Logger\Logger;

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

		$logger = new Logger();
		$logger->add('Установлено соединение с RabbitMQ.', Log::GENERAL);
	}

	public function close(): void
	{
		$this->getChannel()->close();
		$this->getConnection()->close();
	}

	public function run(): void
	{
		$logger = new Logger();

		foreach (RoutingKey::cases() as $routingKey)
		{
			$this->declareQueue($routingKey->value);
			$logger->add('Очередь ' . $routingKey->value . ' задекларирована.', Log::GENERAL);
		}

		$callbacksDir = __DIR__ . '/../callback';
		$callbacks = scandir($callbacksDir);

		$mailTo = getenv('MAIL_TO');
		$mails = explode(',', $mailTo);

		foreach ($callbacks as $callback)
		{
			$path = $callbacksDir . '/' . $callback;
			if (!is_file($path))
			{
				continue;
			}

			foreach ($mails as $index => $mail)
			{
				exec("php $path > /dev/null 2>&1 &");
				$logger->add(
					'Обработчик номер ' . $index + 1 . ' по пути ' . $path . ' запущен.',
					Log::GENERAL,
				);
			}
		}
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