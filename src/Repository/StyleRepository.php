<?php

namespace App\Repository;

use App\Models\Style;
use Nacho\ORM\AbstractRepository;
use Nacho\ORM\RepositoryInterface;

class StyleRepository extends AbstractRepository implements RepositoryInterface
{
    public static function getDataName(): string
    {
        return 'style';
    }

    protected static function getModel(): string
    {
        return Style::class;
    }
}