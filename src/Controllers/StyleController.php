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

    public function __construct(Nacho $nacho)
    {
        parent::__construct($nacho);
        self::$STYLE_PATH = $_SERVER['DOCUMENT_ROOT'] . self::$STYLE_PATH;
    }

    public function loadStyle(): HttpResponse
    {
        $repo = RepositoryManager::getInstance()->getRepository(StyleRepository::class);
        /** @var Style $style */
        $style = $repo->getById(1);

        if (!$style) {
            $style = $this->compileStyle();
//            $repo->set($style);
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