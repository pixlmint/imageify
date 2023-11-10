<?php

namespace App\Controllers;

use Nacho\Contracts\PageManagerInterface;
use Nacho\Contracts\RequestInterface;
use Nacho\Contracts\Response;
use Nacho\Controllers\AbstractController;
use Nacho\Helpers\PageManager;
use Nacho\Models\HttpRedirectResponse;
use PixlMint\Media\Helpers\MediaHelper;
use PixlMint\Media\Models\MediaGalleryDirectory;

class GalleryController extends AbstractController
{
    private MediaHelper $mediaHelper;
    private PageManagerInterface $pageManager;

    public function __construct(MediaHelper $mediaHelper, PageManagerInterface $pageManager)
    {
        parent::__construct();
        $this->mediaHelper = $mediaHelper;
        $this->pageManager = $pageManager;
    }

    public function uploadMedia(): HttpRedirectResponse
    {
        $gallery = $_REQUEST['gallery'];
        $mediaDirectory = MediaGalleryDirectory::fromPath($gallery);
        $files = $_FILES;

        $this->mediaHelper->storeAll($mediaDirectory, $files);

        return $this->redirect('/api/view?media=' . $gallery);
    }

    public function createGallery(RequestInterface $request): Response
    {
        if (key_exists('parentGallery', $_REQUEST) && $_REQUEST['parentGallery']) {
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
            $this->pageManager->create($parentGallery, $galleryName, true);

            if (!str_ends_with($parentGallery, '/')) {
                $galleryName = '/' . $galleryName;
            }

            return $this->redirect('/api/view?media=' . $parentGallery . $galleryName);
        }

        return $this->json([], 405);
    }
}