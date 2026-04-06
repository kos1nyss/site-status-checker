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
		(new Logger())->add('Программа запущена.', Log::GENERAL);
		MessageBroker::getInstance()->run();
		(new SiteStatusChecker())->run();
	}
}