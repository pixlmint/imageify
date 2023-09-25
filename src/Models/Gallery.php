<?php

namespace App\Models;

use PixlMint\Media\Models\Media;

class Gallery
{
    private array $childGalleries = [];
    private array $childMedias = [];

    public function __construct() {

    }

    public function addChildGallery(Gallery $gallery): void
    {
        $this->childGalleries[] = $gallery;
    }

    public function addChildMedia(Media $media): void
    {
        $this->childMedias[] = $media;
    }

    /**
     * @return array|Gallery[]
     */
    public function getChildGalleries(): array
    {
        return $this->childGalleries;
    }

    /**
     * @return array|Media[]
     */
    public function getChildMedias(): array
    {
        return $this->childMedias;
    }
}