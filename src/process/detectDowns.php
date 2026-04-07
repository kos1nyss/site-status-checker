<?php

use Softline\Core\Logger\Log;
use Softline\Core\Logger\Logger;
use Softline\SiteStatusChecker;

require __DIR__ . '/../boot.php';

$url = $argv[1];

$logger = new Logger();
$logger->add(
	'Процесс проверки состояния сайта ' . $url . ' запущен.',
	Log::INFO,
);

$siteStatusChecker = new SiteStatusChecker($url);
$siteStatusChecker->run();