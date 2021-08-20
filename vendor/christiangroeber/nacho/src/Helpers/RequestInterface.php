<?php

namespace Nacho\Helpers;

interface RequestInterface
{
    public function getBody();

    public function getRoute();
}
