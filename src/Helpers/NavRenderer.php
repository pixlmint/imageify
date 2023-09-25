<?php

namespace App\Helpers;

use Nacho\Helpers\PageSecurityHelper;
use Nacho\Models\PicoPage;
use Nacho\Nacho;

class NavRenderer
{
    private Nacho $nacho;

    public function __construct(Nacho $nacho)
    {
        $this->nacho = $nacho;
    }

    /**
     * Return if the given path is a subpath of the given parent path(s)
     */
    public static function isSubPath(string $path, string $parentPath)
    {
        return str_starts_with($path, $parentPath) && $path !== $parentPath;
    }

    /**
     * Return an array based on a nested pages array.
     */
    public function loadNav(?array $pages = null): array
    {
        if (!$pages) {
            $tmp = $this->nacho->getPageManager()->getPages();
            $page = $this->nacho->getPageManager()->getPage('/');
            $pages = ['/' => $this->findChildPages('/', $page, $tmp)];
        }
        $ret = [];
        foreach ($pages as $pageID => $page) {
            if (!empty($page->hidden)) continue;

            $childrenOutput = [];
            if (isset($page->children)) {
                $childrenOutput = $this->loadNav($page->children);
            }

            $url = $page->url ?? false;
            $title = $page->meta->title;
            $isPublic = PageSecurityHelper::isPagePublic($page);

            $ret[] = [
                'id' => $page->id,
                'title' => $title,
                'url' => $url,
                'children' => $childrenOutput,
                'isFolder' => str_ends_with($page->file, 'index.md'),
                'isPublic' => $isPublic,
            ];
        }
        return $ret;
    }

    private static function isDirectChild(string $path, string $parentPath): bool
    {
        if (!self::isSubPath($path, $parentPath)) {
            return false;
        }

        if ($parentPath === '/') {
            if (count(explode('/', $path)) === 2) {
                return true;
            }
            return false;
        }

        return count(explode('/', $path)) - 1 === count(explode('/', $parentPath));
    }

    public function findChildPages(string $id, PicoPage &$parentPage, array $pages): PicoPage
    {
        foreach ($pages as $childId => $page) {
            if (isset($page->meta->min_role)) {
                if (!$this->nacho->getUserHandler()->isGranted($page->meta->min_role)) {
                    continue;
                }
            }
            if (self::isDirectChild($childId, $id)) {
                $page = $this->findChildPages($childId, $page, $pages);
                $parentPage->children[$childId] = $page;
            }
        }

        return $parentPage;
    }
}