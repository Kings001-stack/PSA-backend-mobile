<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Boot Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::create('/inventory', 'GET')
);

echo $response->getContent();

$kernel->terminate($request, $response);
