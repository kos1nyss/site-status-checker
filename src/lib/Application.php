<?php

declare(strict_types=1);

namespace Softline;

use PhpAmqpLib\Message\AMQPMessage;
use Softline\Core\Logger\Log;
use Softline\Core\Logger\Logger;
use Softline\Core\Message\MessageBroker;
use Softline\Core\Message\MessagePublisher;
use Softline\Core\Message\RoutingKey;

class Application
{
	public function run(): void
	{
		$logger = new Logger();
		$logger->add('Программа запущена.', Log::GENERAL);

		$this->runMessageBroker();

		while (True)
		{
			$this->process();
			sleep(120);
		}
	}

	private function runMessageBroker(): void
	{
		$logger = new Logger();

		$messageBroker = MessageBroker::getInstance();
		foreach (RoutingKey::cases() as $routingKey)
		{
			$messageBroker->declareQueue($routingKey->value);
			$logger->add('Очередь ' . $routingKey->value . ' задекларирована.', Log::GENERAL);
		}

		$callbacksDir = __DIR__ . '/../callback';
		$callbacks = scandir($callbacksDir);

		foreach ($callbacks as $callback)
		{
			$path = $callbacksDir . '/' . $callback;
			if (!is_file($path))
			{
				continue;
			}

			exec("php $path > /dev/null 2>&1 &");
			$logger->add('Обработчик по пути ' . $path . ' запущен.', Log::GENERAL);
		}
	}

	private function process(): void
	{
		$logger = new Core\Logger\Logger();
		$messagePublisher = new MessagePublisher();

		$mailTo = getenv('MAIL_TO');
		$mails = explode(',', $mailTo);

		$status = $this->getStatus();
		$logger->add('Произведена проверка сайта. Обнаружено, что сайт вернул код: ' . $status, Log::GENERAL);

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