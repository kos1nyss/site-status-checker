<?php

namespace Softline\Core\Mail;

use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
	private PHPMailer $mailer;

	public function __construct()
	{
		$this->mailer = $this->getMailer();
	}

	private function getMailer(): PHPMailer
	{
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

		return $mailer;
	}

	public function send(
		string $email,
		string $title,
		string $text,
	): bool
	{
		$this->mailer->addAddress($email);
		$this->mailer->Subject = $title;
		$this->mailer->Body = $text;

		try {
			$isSent = $this->mailer->send();
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
}
