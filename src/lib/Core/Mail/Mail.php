<?php

namespace Softline\Core\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use Softline\Core\Logger\Log;
use Softline\Core\Logger\Logger;

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
		$logger = new Logger();
		$logger->add('Попытка отправить письмо на почту: ' . $email, Log::INFO);

		$this->mailer->addAddress($email);
		$this->mailer->Subject = $title;
		$this->mailer->Body = $text;

		try {
			$isSent = $this->mailer->send();
		}
		catch (\Exception $exception)
		{
			$message =
				'Ошибка при отправке сообщения на почту: ' . $email . '.' . PHP_EOL
				. 'Подробности ошибки:' . PHP_EOL
				. $exception->getMessage() . PHP_EOL
				. $exception->getCode() . PHP_EOL
				. $exception->getTraceAsString() . PHP_EOL
			;

			$logger->add($message, Log::ERROR);

			return false;
		}

		return $isSent;
	}
}
