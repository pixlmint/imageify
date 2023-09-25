<?php

namespace App\Controllers;

use App\Helpers\GalleryHelper;
use Nacho\Controllers\AbstractController;
use Nacho\Models\Request;
use PixlMint\Media\Helpers\MediaHelper;
use PixlMint\Media\Models\MediaGalleryDirectory;

class HomeController extends AbstractController
{
    public function index(): string
    {
        $request = Request::getInstance();
        $body = $request->getBody();
        if (key_exists('gallery', $body)) {
            $gallery = $body['gallery'];
        } else {
            $gallery = '/';
        }

        $gHelper = new GalleryHelper($gallery, $this->nacho);
        $gHelper->loadGallery();
        $helper = new MediaHelper($this->nacho);
        $directory = MediaGalleryDirectory::fromPath($gallery);
        $media = $helper->loadMedia($directory);
        return $this->render('pages/list-images.twig', ['media' => $media]);
    }

    public function viewMedia(): string
    {
        $request = Request::getInstance();
        if (!key_exists('media', $request->getBody())) {
            return $this->json(['error' => 'No media specified'], 400);
        }
        $media = $request->getBody()['media'];

        return $this->render('pages/list-images.twig', ['media' => $media]);
    }
}