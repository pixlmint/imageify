<?php

use App\Controllers\GalleryController;
use App\Controllers\HomeController;
use App\Controllers\StyleController;

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
    [
        'route' => '/api/gallery/create',
        'controller' => GalleryController::class,
        'function' => 'createGallery',
    ],
    [
        'route' => '/api/gallery/upload',
        'controller' => GalleryController::class,
        'function' => 'uploadMedia',
    ],
];