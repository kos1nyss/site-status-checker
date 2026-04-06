<?php

namespace Softline;

use PhpAmqpLib\Message\AMQPMessage;
use Softline\Core\Logger\Log;
use Softline\Core\Message\MessagePublisher;
use Softline\Core\Message\RoutingKey;

class SiteStatusChecker
{
	public function run(): void
	{
		while (True)
		{
			$this->process();
			sleep(120);
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