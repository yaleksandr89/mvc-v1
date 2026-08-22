<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/app.php';

if (env('APP_ENV') !== 'test') {
    throw new RuntimeException('Unsafe PHPUnit environment: APP_ENV must equal test.');
}

if (env('DB_NAME') !== 'mvc_v1_test') {
    throw new RuntimeException('Unsafe PHPUnit database: DB_NAME must equal mvc_v1_test.');
}
