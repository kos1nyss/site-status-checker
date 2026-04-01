<?php

declare(strict_types=1);

use Softline\Application;

require 'autoload.php';

echo 'started' . PHP_EOL;

$app = new Application();
$app->run();