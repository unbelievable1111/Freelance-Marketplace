<?php

// ---- CHECK POST SIZE BEFORE LARAVEL ----
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_SERVER['CONTENT_LENGTH'])
) {
    function parseSize($size)
    {
        $unit = strtolower(substr($size, -1));
        $value = (int) $size;

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }

    $max = parseSize(ini_get('post_max_size'));

    if ($_SERVER['CONTENT_LENGTH'] > $max) {
        http_response_code(413);
        header('Content-Type: application/json');

        echo json_encode([
            'message' => 'Payload too large!',
            'max_size' => ini_get('post_max_size')
        ]);

        exit;
    }
}
// ---- END CHECK ----


use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
