<?php

use App\Controllers\HomeController;

return [
    'routes' => require_once('routes.php'),
    'base' => [
        'frontendController' => HomeController::class,
        'debugEnabled' => true,
    ]
];