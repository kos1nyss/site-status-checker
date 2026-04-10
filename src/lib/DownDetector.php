<?php

namespace Kondrashov\DownDetector;

use PhpAmqpLib\Message\AMQPMessage;
use Kondrashov\DownDetector\Core\Logger\Log;
use Kondrashov\DownDetector\Core\Logger\Logger;
use Kondrashov\DownDetector\Core\Message\MessagePublisher;
use Kondrashov\DownDetector\Core\Message\RoutingKey;

class DownDetector
{
	private string $url;

	public function __construct(string $url)
	{
		$this->url = $url;
	}

	public function run(): void
	{
		while (True)
		{
			$status = $this->iteration();

			$this->isStatusNegative($status) ? sleep(300) : sleep(10);
		}
	}

	private function iteration(): int
	{
		$logger = new Logger();
		$logger->add(
			"Началась проверка сайта $this->url.",
			Log::INFO,
		);

		$messagePublisher = new MessagePublisher();

		$mailTo = getenv('MAIL_TO');
		$mails = explode(',', $mailTo);

		$status = $this->getStatus();
		$logger->add("Произведена проверка сайта $this->url. Обнаружено, что сайт вернул код: " . $status, Log::INFO);

		if ($this->isStatusNegative($status))
		{
			$title = "Ошибка. Портал $this->url недоступен";
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
		}

		return $status;
	}


	private function getStatus(): ?int
	{
		$httpClient = new Core\Http\HttpClient();

		return
			$httpClient
				->get($this->url)
				?->getStatusCode()
			;
	}

	private function isStatusNegative(int $status): bool
	{
		return 400 <= $status && $status <= 600;
	}
}