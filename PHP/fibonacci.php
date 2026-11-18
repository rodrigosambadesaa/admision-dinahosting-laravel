#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Adaptador CLI a Laravel para mantener la carpeta PHP exigida por el PDF.
 * Toda la lógica vive en el comando Artisan y los servicios App\* de Laravel.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$arguments = $_SERVER['argv'] ?? [];
array_splice($arguments, 1, 0, ['fibonacci']);
$_SERVER['argv'] = $arguments;
$_SERVER['argc'] = count($arguments);

require dirname(__DIR__) . '/artisan';
