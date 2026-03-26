<?php

declare(strict_types=1);

namespace Softline;

use PHPMailer\PHPMailer\PHPMailer;

class Application
{
	private static ?PHPMailer $mailer = null;

	public function run(): void
	{
		while (True)
		{
			$this->process();
			sleep(10);
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
		$url = 'https://lk.npf-transneft.ru/user/auth/';

		$ch = \curl_init($url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_exec($ch);

		if (curl_errno($ch))
		{
			sleep(60);

			return null;
		}

		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		return (int)$status;
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