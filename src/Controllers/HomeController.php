<?php

namespace App\Controllers;

use App\Helpers\GalleryHelper;
use Nacho\Controllers\AbstractController;
use Nacho\Models\HttpRedirectResponse;
use Nacho\Models\HttpResponse;
use Nacho\Models\Request;

class HomeController extends AbstractController
{
    public function index(): HttpRedirectResponse
    {
        return $this->redirect('/api/view?media=/');
    }

    public function viewMedia(): HttpResponse
    {
        $request = Request::getInstance();
        $body = $request->getBody();
        if (!key_exists('media', $request->getBody())) {
            return $this->json(['error' => 'No media specified'], 400);
        }
        $gallery = $body['media'];

        if (str_ends_with($gallery, '/')) {
            $gallery = substr($gallery, 0, -1);
            $this->redirect("/api/view?media={$gallery}", true);
        }

        $gHelper = new GalleryHelper($gallery, $this->nacho);
        $mediaGallery = $gHelper->loadGallery();

        $galleryPathParts = explode('/', $gallery);

        return $this->render('pages/list-images.twig', [
            'media' => $mediaGallery,
            'gallery' => $galleryPathParts,
            'galleryId' => $gallery,
        ]);
    }
}