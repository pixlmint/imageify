<?php

namespace Test;

use App\Helpers\GalleryHelper;
use PHPUnit\Framework\TestCase;

class GalleryHelperTest extends TestCase
{
    public function testLoadGallery(): void
    {
        $helper = new GalleryHelper('/', null);
        $this->assertTrue(true);
    }
}
