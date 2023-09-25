<?php

use App\Controllers\HomeController;
use App\Helpers\StyleController;

return [
    [
        'route' => '/api/view',
        'controller' => HomeController::class,
        'function' => 'viewMedia',
    ],
    [
        'route' => '/api/load-style',
        'controller' => StyleController::class,
        'function' => 'loadStyle',
    ],
];