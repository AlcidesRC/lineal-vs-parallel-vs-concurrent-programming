<?php

declare(strict_types=1);

namespace App\Concurrent\Infrastructure\CLI;

require_once dirname(__DIR__, 4) . '/vendor/autoload.php';

use App\Concurrent\Infrastructure\Job\Worker;

(new Worker())->__invoke();
