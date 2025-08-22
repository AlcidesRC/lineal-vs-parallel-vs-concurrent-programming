<?php

declare(strict_types=1);

namespace App\Concurrent\Infrastructure\CLI;

require_once dirname(__DIR__, 4) . '/vendor/autoload.php';

use App\Concurrent\Infrastructure\Job\ProcessResult;

(new ProcessResult())->__invoke(
    '/var/www/html/tests/Fixtures/source.jpg',
    '/var/www/html/tests/Fixtures/source_concurrent_5x5.webp',
    5,
    5,
);

(new ProcessResult())->__invoke(
    '/var/www/html/tests/Fixtures/source.jpg',
    '/var/www/html/tests/Fixtures/source_concurrent_10x10.webp',
    10,
    10,
);

(new ProcessResult())->__invoke(
    '/var/www/html/tests/Fixtures/source.jpg',
    '/var/www/html/tests/Fixtures/source_concurrent_20x20.webp',
    20,
    20,
);
