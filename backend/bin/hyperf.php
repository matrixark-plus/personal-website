#!/usr/bin/env php
<?php

ini_set('display_errors', 'stderr');
ini_set('log_errors', 1);
ini_set('error_log', 'php://stderr');

use Hyperf\Contract\ApplicationInterface;
use Hyperf\Di\ClassLoader;
use Psr\Container\ContainerInterface;

/*
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

ini_set('display_errors', 'stderr');

$classLoader = require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    (new \Dotenv\Dotenv(__DIR__ . '/../'))->load();
}

\Hyperf\Coroutine\run(function () use ($classLoader) {
    /** @var ContainerInterface $container */
    $container = require __DIR__ . '/../config/container.php';

    /** @var ApplicationInterface $application */
    $application = $container->get(ApplicationInterface::class);
    $application->run();
});
