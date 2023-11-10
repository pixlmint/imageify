<?php

namespace App\Controllers;

use App\Models\Style;
use App\Repository\StyleRepository;
use Nacho\Controllers\AbstractController;
use Nacho\Models\HttpResponse;
use Nacho\Nacho;
use Nacho\ORM\RepositoryManager;
use ScssPhp\ScssPhp\Compiler;

class StyleController extends AbstractController
{
    private static string $STYLE_PATH = '/assets/css/main.scss';
    private float $compileTime;
    private StyleRepository $styleRepository;

    public function __construct(StyleRepository $styleRepository)
    {
        parent::__construct();
        self::$STYLE_PATH = $_SERVER['DOCUMENT_ROOT'] . self::$STYLE_PATH;
        $this->styleRepository = $styleRepository;
    }

    public function loadStyle(): HttpResponse
    {
        /** @var Style $style */
        $style = $this->styleRepository->getById(1);

        if (!$style) {
            $style = $this->compileStyle();
            if (Nacho::$container->get('debug')) {
                $this->styleRepository->set($style);
            }
        }

        $content = $style->getStyle();
        $response = new HttpResponse($content);

        if (isset($this->compileTime)) {
            $response->setHeader('X-Compile-Time', $this->compileTime);
        }
        $response->setHeader('Content-Type', 'text/css');

        return $response;
    }

    private function compileStyle(): Style
    {
        $compileStart = microtime(true);
        $compiler = new Compiler();
        $styleContent = file_get_contents(self::$STYLE_PATH);
        $css = $compiler->compileString($styleContent);
        $style = new Style($css->getCss());
        $compileEnd = microtime(true);
        $this->compileTime = $compileEnd - $compileStart;

        return $style;
    }
}