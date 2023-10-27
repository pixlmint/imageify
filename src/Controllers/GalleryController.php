<?php

namespace App\Controllers;

use Nacho\Controllers\AbstractController;
use Nacho\Helpers\PageManager;
use PixlMint\Media\Helpers\MediaHelper;
use PixlMint\Media\Models\MediaGalleryDirectory;

class GalleryController extends AbstractController
{
    public function uploadMedia(): void
    {
        $gallery = $_REQUEST['gallery'];
        $mediaDirectory = MediaGalleryDirectory::fromPath($gallery);
        $files = $_FILES;

        $mediaHelper = new MediaHelper($this->nacho);

        $mediaHelper->storeAll($mediaDirectory, $files);

        $this->redirect('/api/view?media=' . $gallery);
    }

    public function createGallery(): string
    {
        $request = $this->nacho->getRequest();
        if (key_exists('parentGallery', $_REQUEST)) {
            $parentGallery = $_REQUEST['parentGallery'];
        } else {
            $parentGallery = '/';
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
            if (!key_exists('name', $request->getBody())) {
                return $this->render('pages/create-gallery.twig', [
                    'parentGallery' => $parentGallery,
                    'errorMessage' => 'Please enter a name for the gallery.',
                ]);
            }
            $galleryName = $request->getBody()['name'];
            $manager = new PageManager();
            $manager->create($parentGallery, $galleryName, true);
            $this->redirect('/api/view?media=' . $parentGallery . $galleryName);
        }

        return $this->render('pages/create-gallery.twig', [
            'parentGallery' => $parentGallery,
        ]);
    }
}