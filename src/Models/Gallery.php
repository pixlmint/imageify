<?php

namespace App\Models;

use Nacho\Models\PicoPage;
use PixlMint\Media\Models\Media;

class Gallery
{
    /** @var array|Gallery[] $childGalleries */
    private array $childGalleries = [];
    private array $childMedias = [];
    private ?PicoPage $page;

    public function __construct(?PicoPage $page = null)
    {
        $this->page = $page;
    }

    public function mergeMedias(array $additionalMediaList): void
    {
        $this->childMedias = array_merge($this->childMedias, $additionalMediaList);
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
     * @return array|string[]
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

    public function getRandomMedia(): ?Media
    {
        if (count($this->childMedias) === 0) {
            return null;
        }

        return $this->childMedias[array_rand($this->childMedias)];
    }

    public function getPage(): ?PicoPage
    {
        return $this->page;
    }

    public function getTitle(): ?string
    {
        if (!$this->page) {
            return null;
        }
        return $this->page->meta->title;
    }
}