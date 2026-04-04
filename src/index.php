<?php

declare(strict_types=1);

use Softline\Application;

require 'boot.php';

echo 'started' . PHP_EOL;

$app = new Application();
$app->run();