<?php

namespace App\Helpers;

use App\Models\Gallery;
use Exception;
use Nacho\Helpers\PageManager;
use Nacho\Nacho;
use PixlMint\Media\Helpers\MediaHelper;
use PixlMint\Media\Helpers\MimeHelper;
use PixlMint\Media\Models\MediaGalleryDirectory;
use PixlMint\Media\Models\Mime;

class GalleryHelper
{
    private string $gallery;
    private Nacho $nacho;
    private MediaHelper $mediaHelper;

    public function __construct(string $gallery, Nacho $nacho)
    {
        if (!str_starts_with($gallery, '/')) {
            $gallery = '/' . $gallery;
        }
        $this->gallery = $gallery;
        $this->nacho = $nacho;
        $this->mediaHelper = new MediaHelper();
    }

    public function loadGallery(): Gallery
    {
        PageManager::$INCLUDE_PAGE_TREE = true;
        $page = $this->nacho->getPageManager()->getPage($this->gallery);
        $directory = MediaGalleryDirectory::fromPath($page->id);
        $media = $this->mediaHelper->loadMedia($directory);
        $gallery = new Gallery($page);
        $helper = $this->mediaHelper;
        foreach ($media as $m) {
            $gallery->mergeMedias(array_map(function ($media) use ($helper) {
                try {
                    $mediaType = $this->mediaHelper->getMediaHelper($media->getMime());
                    $scaled = $mediaType->getDefaultScaled();
                    return $media->getMediaPath($scaled);
                } catch (Exception $e) {
                    return $media->getMediaPath();
                }
            }, $m->getMedias()));
        }
        if ($page->children) {
            foreach ($page->children as $child) {
                $gallery->addChildGallery(new Gallery($child));
            }
        }
        return $gallery;
    }
}