<?php

namespace App\Helpers;

use App\Models\Gallery;
use Exception;
use Nacho\Contracts\PageManagerInterface;
use Nacho\Helpers\PageManager;
use Nacho\Nacho;
use PixlMint\Media\Helpers\MediaHelper;
use PixlMint\Media\Models\MediaGalleryDirectory;

class GalleryHelper
{
    private string $gallery;
    private MediaHelper $mediaHelper;
    private PageManagerInterface $pageManager;

    public function __construct(string $gallery)
    {
        if (!str_starts_with($gallery, '/')) {
            $gallery = '/' . $gallery;
        }
        $this->gallery = $gallery;
        $this->mediaHelper = Nacho::$container->get(MediaHelper::class);
        $this->pageManager = Nacho::$container->get(PageManagerInterface::class);
    }

    public function loadGallery(): Gallery
    {
        PageManager::$INCLUDE_PAGE_TREE = true;
        $page = $this->pageManager->getPage($this->gallery);
        $directory = MediaGalleryDirectory::fromPath($page->id);
        $media = $this->mediaHelper->loadMedia($directory);
        $gallery = new Gallery($page);
        $helper = $this->mediaHelper;
        foreach ($media as $m) {
            $gallery->mergeMedias(array_map(function ($media) use ($helper) {
                try {
                    $mediaType = $this->mediaHelper->getMediaHelper($media->getMime()->printMime());
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