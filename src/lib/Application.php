<?php

declare(strict_types=1);

namespace Softline;

use Softline\Core\Logger\Log;
use Softline\Core\Logger\Logger;
use Softline\Core\Message\MessageBroker;

class Application
{
	public function run(): void
	{
		$logger = new Logger();
		$logger->add('Программа запущена.', Log::INFO);
		MessageBroker::getInstance()->run();

		$path = ROOT_DIR . '/src/process/detectDowns.php';
		if (!is_file($path))
		{
			return;
		}

		$urls = explode(',', getenv('URLS'));
		foreach ($urls as $url)
		{
			$logger->add(
				"Процесс для мониторинга cайта $url по пути $path начинает запускаться.",
				Log::INFO,
			);

			exec("php $path \"$url\" > /dev/null 2>&1 &");
		}

		while (True) {
			/*

			Необходимо для бесконечной работы контейнера, чтоб дочерние процессы не умирали.
			Под дочерними процессами подразумеваются запуски php-скриптов c помощью eval

			 */
		}
	}
}