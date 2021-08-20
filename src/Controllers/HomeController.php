<?php

namespace App\Controllers;

use Nacho\Controllers\AbstractController;

class HomeController extends AbstractController
{
    public function index()
    {
        return $this->render('base.twig');
    }
}