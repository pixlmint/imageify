<?php

namespace App\Helpers;

use App\Models\Gallery;
use Nacho\Helpers\PageManager;
use Nacho\Nacho;

class GalleryHelper
{
    private string $gallery;
    private Nacho $nacho;

    public function __construct(string $gallery, Nacho $nacho)
    {
        if (!str_starts_with($gallery, '/')) {
            $gallery = '/' . $gallery;
        }
        $this->gallery = $gallery;
        $this->nacho = $nacho;
    }

    public function loadGallery(): Gallery
    {
        PageManager::$INCLUDE_PAGE_TREE = true;
        $page = $this->nacho->getPageManager()->getPage($this->gallery);
        var_dump($page);
    }
}