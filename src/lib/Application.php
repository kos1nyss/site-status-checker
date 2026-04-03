<?php

declare(strict_types=1);

namespace Softline;

use PhpAmqpLib\Message\AMQPMessage;
use Softline\Core\Message\MessageBroker;
use Softline\Core\Message\MessagePublisher;
use Softline\Core\Message\RoutingKey;

class Application
{
	public function run(): void
	{
		$this->runMessageBroker();

		while (True)
		{
			$this->process();
		}
	}

	private function runMessageBroker(): void
	{
		$messageBroker = MessageBroker::getInstance();
		foreach (RoutingKey::cases() as $routingKey)
		{
			$messageBroker->declareQueue($routingKey->value);
		}

		$callbacksDir = __DIR__ . '/../callback';
		$callbacks = scandir($callbacksDir);

		foreach ($callbacks as $callback)
		{
			$path = $callbacksDir . '/' . $callback;
			exec("php $path > /dev/null 2>&1 &");
		}
	}

	private function process(): void
	{
		$messagePublisher = new MessagePublisher();

		$mailTo = getenv('MAIL_TO');
		$mails = explode(',', $mailTo);

		$status = $this->getStatus();
		echo $status . PHP_EOL;

		if (400 <= $status && $status <= 600)
		{
			$title = 'Ошибка. Портал lk.npf-transneft.ru недоступен';
			$text = 'Код ошибки: ' . $status;

			foreach ($mails as $mail)
			{
				$data = [
					'email' => $mail,
					'title' => $title,
					'text' => $text,
				];

				$message = new AMQPMessage(json_encode($data, JSON_THROW_ON_ERROR));
				$messagePublisher->publish($message, RoutingKey::SEND_MAIL->value);
			}

			sleep(300);
		}
	}

	private function getStatus(): ?int
	{
		$httpClient = new Core\Http\HttpClient();

		return
			$httpClient
				->get('https://lk.npf-transneft.ru/user/auth/')
				?->getStatusCode()
			;
	}
}