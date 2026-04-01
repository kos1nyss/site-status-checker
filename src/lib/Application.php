<?php

declare(strict_types=1);

namespace Softline;

use PhpAmqpLib\Message\AMQPMessage;
use PHPMailer\PHPMailer\PHPMailer;
use Softline\Message\MessageBroker;

class Application
{
	private static ?PHPMailer $mailer = null;

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
		$messageBroker->declareQueue('hello');

		$workersDir = __DIR__ . '/../workers';
		$workers = scandir($workersDir);

		foreach ($workers as $worker)
		{
			$path = $workersDir . '/' . $worker;
			exec("php $path > /dev/null 2>&1 &");
		}
	}

	private function process(): void
	{
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
				$this->sendMail(
					$mail,
					$title,
					$text,
				);
			}

			sleep(300);
		}
	}

	private function getStatus(): ?int
	{
		$httpClient = new Http\HttpClient();

		return
			$httpClient
				->get('https://lk.npf-transneft.ru/user/auth/')
				?->getStatusCode()
			;
	}

	public function sendMail(
		string $email,
		string $title,
		string $text,
	): bool
	{
		$mailer = $this->getMailer();

		$mailer->addAddress($email);
		$mailer->Subject = $title;
		$mailer->Body = $text;
		try {
			$isSent = $mailer->send();
		}
		catch (\Exception $exception)
		{
			echo 'Ошибка при отправке сообщения на почту: ' . PHP_EOL;
			echo $exception->getMessage() . PHP_EOL;
			echo $exception->getCode() . PHP_EOL;
			echo $exception->getTraceAsString() . PHP_EOL;

			return false;
		}

		return $isSent;
	}

	private function getMailer(): PHPMailer
	{
		if (static::$mailer)
		{
			return static::$mailer;
		}

		$mailer = new PHPMailer(true);

		$smtpHostName = getenv('SMTP_HOST_NAME');
		$mailPort = getenv('SMTP_PORT');

		$mailUsername = getenv('MAIL_FROM');
		$mailPassword = getenv('MAIL_PASSWORD');
		$mailFrom = getenv('MAIL_FROM');

		$mailer->SMTPDebug = 0;
		$mailer->isSMTP();
		$mailer->CharSet = "utf-8";
		$mailer->Timeout = 15;
		$mailer->Host = $smtpHostName;
		$mailer->SMTPAuth = true;
		$mailer->Username = $mailUsername;
		$mailer->Password = $mailPassword;
		$mailer->SMTPKeepAlive = true;
		$mailer->Port = $mailPort;
		$mailer->SMTPSecure = true;
		$mailer->SMTPAutoTLS = true;
		$mailer->SMTPOptions = [
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true,
			],
		];

		$mailer->setFrom($mailFrom, 'Found');

		static::$mailer = $mailer;

		return $mailer;
	}
}