<?php

$file = __DIR__ . '/../../../../../app/autoload.php';

if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite. "php composer.phar install --dev"');
}

require_once $file;
