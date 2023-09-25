<?php

namespace App\Models;

use Nacho\ORM\AbstractModel;
use Nacho\ORM\ModelInterface;
use Nacho\ORM\TemporaryModel;

class Style extends AbstractModel implements ModelInterface
{
    private string $style;

    public static function init(TemporaryModel $data, int $id): ModelInterface
    {
        return new Style($data->get('style'));
    }

    public function __construct(string $style)
    {
        $this->id = 1;
        $this->style = $style;
    }

    public function getStyle(): string
    {
        return $this->style;
    }

    public function setStyle(string $style): void
    {
        $this->style = $style;
    }

    public function toArray(): array
    {
        return [
            'style' => $this->style,
        ];
    }
}