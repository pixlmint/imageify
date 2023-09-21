<?php

namespace App\Controllers\API;

use Exception;
use Nacho\Controllers\AbstractController;
use Nacho\Helpers\Request;

class ImageController extends AbstractController
{
    /**
     * @throws Exception
     */
    public function uploadImage(Request $request)
    {
        if (strtolower($request->requestMethod) !== 'post') {
            return $this->json(['message' => 'This function only accepts post requests'], 405);
        }
        
        return $this->json((array) $request);
    }
}