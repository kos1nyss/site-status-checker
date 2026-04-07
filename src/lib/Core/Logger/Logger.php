<?php

namespace Softline\Core\Logger;

use DateTime;

class Logger
{
	public function add(string $message, Log $log): void
	{
		$message = $this->prepareMessage($message);

		if ($log !== Log::GENERAL)
		{
			$this->writeToFile($message, Log::GENERAL);
		}

		$this->writeToFile($message, $log);
	}

	private function prepareMessage(string $message): string
	{
		$now = DateTime::createFromFormat('U.u', microtime(true));
		$prefix = $now->format('d-m-Y H:i:s.u');

		return $prefix . ': ' . $message . PHP_EOL;
	}

	private function writeToFile(string $message, Log $log): void
	{
		$file = ROOT_DIR . '/logs/' . $log->value . '.log';

		$dir = dirname($file);
		if (!is_dir($dir))
		{
			mkdir($dir, recursive: true);
			chmod($dir, 0777);
		}

		$isFileExists = file_exists($file);
		file_put_contents($file, $message, FILE_APPEND | LOCK_EX);

		if (!$isFileExists)
		{
			chmod($file, 0777);
		}
	}
}