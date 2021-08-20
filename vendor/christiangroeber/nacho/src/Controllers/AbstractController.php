<?php

namespace Nacho\Controllers;

use Nacho\Nacho;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

abstract class AbstractController
{
    protected Nacho $nacho;
    private ?Environment $twig = null;

    public function __construct(Nacho $nacho)
    {
        $this->nacho = $nacho;
    }

    protected function getTwig()
    {
        if (!$this->twig) {
            $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/src/Views');
            $this->twig = new Environment($loader);
            $this->twig->addFunction(new TwigFunction('base64_encode', 'base64_encode'));
            $this->twig->addFunction(new TwigFunction('base64_decode', 'base64_decode'));
            $this->twig->addFunction(new TwigFunction('is_array', 'is_array'));
            $this->twig->addFunction(new TwigFunction('is_granted', [$this, 'is_granted']));
        }

        return $this->twig;
    }

    protected function redirect(string $route)
    {
        header('HTTP/1.1 302');
        header("Location: ${route}");
        die();
    }

    protected function json(array $json = [], int $code = 200)
    {
        header("HTTP/1.1 ${code}");
        
        return json_encode($json);
    }

    protected function render(string $file, array $args = [])
    {
        $args['user'] = $_SESSION['user'];
        $args['nacho'] = $this->nacho;
    
        return $this->getTwig()->render($file, $args);
    }

    public function is_granted($role)
    {
        return $this->isGranted($role);
    }

    protected function isGranted($role)
    {
        return $this->nacho->isGranted($role);
    }
}
