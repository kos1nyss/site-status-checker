<?php

use Kondrashov\DownDetector\Core\Logger\Log;
use Kondrashov\DownDetector\Core\Logger\Logger;
use Kondrashov\DownDetector\DownDetector;

require __DIR__ . '/../boot.php';

$url = $argv[1];

$logger = new Logger();
$logger->add(
	'Процесс проверки состояния сайта ' . $url . ' запущен.',
	Log::INFO,
);

$siteStatusChecker = new DownDetector($url);
$siteStatusChecker->run();