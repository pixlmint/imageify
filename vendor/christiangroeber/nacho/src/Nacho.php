<?php

namespace Nacho;

use InvalidArgumentException;
use Parsedown;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Nacho\Helpers\RequestInterface;
use Nacho\Security\UserHandlerInterface;

class Nacho
{
    protected $request;
    public $userHandler;
    private $pages;
    private $metaHeaders;
    private $yamlParser;
    private $config;
    private Parsedown $mdParser;

    public function __construct(RequestInterface $request, UserHandlerInterface $userHandler)
    {
        $this->request = $request;
        $this->userHandler = $userHandler;
        $this->pages = [];
        $this->metaHeaders = [];
        $this->config = [];
        $this->yamlParser = null;
        $this->mdParser = new Parsedown();
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getUserHandler()
    {
        return $this->userHandler;
    }

    public function getPages()
    {
        if (!$this->pages) {
            $this->readPages();
        }

        return $this->pages;
    }

    public function clearPages()
    {
        $this->pages = [];

        return $this;
    }

    public function isGranted(string $minRight = 'Guest', ?array $user = null)
    {
        return $this->userHandler->isGranted($minRight, $user);
    }

    public function getFiles($directory)
    {
        $directory = rtrim($directory, '/');
        $fileExtensionLength = strlen('.md');
        $result = array();

        $files = scandir($directory);
        if ($files !== false) {
            foreach ($files as $file) {
                // exclude hidden files/dirs starting with a .; this also excludes the special dirs . and ..
                // exclude files ending with a ~ (vim/nano backup) or # (emacs backup)
                if (($file[0] === '.') || in_array(substr($file, -1), array('~', '#'), true)) {
                    continue;
                }

                if (is_dir($directory . '/' . $file)) {
                    // get files recursively
                    $result = array_merge($result, $this->getFiles($directory . '/' . $file));
                } elseif (substr($file, -$fileExtensionLength) === '.md') {
                    $result[] = $directory . '/' . $file;
                }
            }
        }

        return $result;
    }

    public function getPage(string $url)
    {
        $pages = $this->getPages();
        foreach ($pages as $page) {
            if (!isset($page['id'])) {
                continue;
            }
            if ($page['id'] === $url) {
                return $page;
            }
        }

        return false;
    }

    public function renderPage(array $page)
    {
        if (!isset($page['raw_content'])) {
            return '';
        }

        $content = $this->prepareFileContent($page['raw_content']);

        return $this->mdParser->parse($content);
    }

    /**
     * @param string $rawContent
     *
     * @return mixed
     */
    public function prepareFileContent(string $rawContent)
    {
        // remove meta header
        $metaHeaderPattern = "/^(?:\xEF\xBB\xBF)?(\/(\*)|---)[[:blank:]]*(?:\r)?\n"
            . "(?:(.*?)(?:\r)?\n)?(?(2)\*\/|---)[[:blank:]]*(?:(?:\r)?\n|$)/s";
        return preg_replace($metaHeaderPattern, '', $rawContent, 1);
    }

    public function getPageUrl($page, $queryData = null, $dropIndex = true)
    {
        if (!is_array($queryData)) {
            $queryData = [];
        }
        $queryData['p'] = $page;
        if (is_array($queryData)) {
            $queryData = http_build_query($queryData, '', '&');
        } elseif (($queryData !== null) && !is_string($queryData)) {
            throw new InvalidArgumentException(
                'Argument 2 passed to ' . __METHOD__ . ' must be of the type array or string, '
                    . (is_object($queryData) ? get_class($queryData) : gettype($queryData)) . ' given'
            );
        }

        // drop "index"
        if ($dropIndex) {
            if ($page === 'index') {
                $page = '';
            } elseif (($pagePathLength = strrpos($page, '/')) !== false) {
                if (substr($page, $pagePathLength + 1) === 'index') {
                    $page = substr($page, 0, $pagePathLength);
                }
            }
        }

        if (!$queryData) {
            $queryData = '';
        }

        return $this->getBaseUrl() . 'nacho?' . $queryData;
    }

    public function getBaseUrl()
    {
        $host = 'localhost';
        if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } elseif (!empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } elseif (!empty($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'];
        }

        $port = 80;
        if (!empty($_SERVER['HTTP_X_FORWARDED_PORT'])) {
            $port = (int)$_SERVER['HTTP_X_FORWARDED_PORT'];
        } elseif (!empty($_SERVER['SERVER_PORT'])) {
            $port = (int)$_SERVER['SERVER_PORT'];
        }

        $hostPortPosition = ($host[0] === '[') ? strpos($host, ':', strrpos($host, ']') ?: 0) : strrpos($host, ':');
        if ($hostPortPosition !== false) {
            $port = (int)substr($host, $hostPortPosition + 1);
            $host = substr($host, 0, $hostPortPosition);
        }

        $protocol = 'http';
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $secureProxyHeader = strtolower(current(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])));
            $protocol = in_array($secureProxyHeader, array('https', 'on', 'ssl', '1'), true) ? 'https' : 'http';
        } elseif (!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] !== 'off')) {
            $protocol = 'https';
        } elseif ($port === 443) {
            $protocol = 'https';
        }

        $basePath = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '/';
        $basePath = !in_array($basePath, array('.', '/', '\\'), true) ? $basePath . '/' : '/';

        if ((($protocol === 'http') && ($port !== 80)) || (($protocol === 'https') && ($port !== 443))) {
            $host = $host . ':' . $port;
        }

        $this->config['base_url'] = $protocol . "://" . $host . $basePath;
        return $this->config['base_url'];
    }

    public function readPages()
    {
        $contentDir = $_SERVER['DOCUMENT_ROOT'] . '/content';

        $this->pages = array();
        $files = $this->getFiles($contentDir);
        foreach ($files as $i => $file) {
            $id = substr($file, strlen($contentDir), -3);

            // skip inaccessible pages (e.g. drop "sub.md" if "sub/index.md" exists) by default
            $conflictFile = $contentDir . $id . '/index.md';
            $skipFile = in_array($conflictFile, $files, true) ?: null;

            if ($skipFile) {
                continue;
            }

            if (endswith($id, '/index')) {
                $id = substr($id, 0, -6);
            }
            if (!$id) {
                $id = '/';
            }

            $url = $this->getPageUrl($id);
            $rawContent = $this->loadFileContent($file);

            $headers = $this->getMetaHeaders();
            try {
                $meta = $this->parseFileMeta($rawContent, $headers);
            } catch (ParseException $e) {
                $meta = $this->parseFileMeta('', $headers);
                $meta['YAML_ParseError'] = $e->getMessage();
            }

            // build page data
            // title, description, author and date are assumed to be pretty basic data
            // everything else is accessible through $page['meta']
            $page = array(
                'id' => $id,
                'url' => $url,
                'title' => &$meta['title'],
                'description' => &$meta['description'],
                'author' => &$meta['author'],
                'time' => &$meta['time'],
                'date' => &$meta['date'],
                'date_formatted' => &$meta['date_formatted'],
                'hidden' => ($meta['hidden'] || preg_match('/(?:^|\/)_/', $id)),
                'raw_content' => &$rawContent,
                'meta' => &$meta,
                'file' => $file,
            );

            unset($rawContent, $meta);

            if ($page !== null) {
                $this->pages[$id] = $page;
            }
        }
    }

    public function loadFileContent($file)
    {
        return file_get_contents($file);
    }

    public function getYamlParser()
    {
        if ($this->yamlParser === null) {
            $this->yamlParser = new Parser();
        }

        return $this->yamlParser;
    }

    public function parseFileMeta($rawContent, array $headers)
    {
        $pattern = "/^(?:\xEF\xBB\xBF)?(\/(\*)|---)[[:blank:]]*(?:\r)?\n"
            . "(?:(.*?)(?:\r)?\n)?(?(2)\*\/|---)[[:blank:]]*(?:(?:\r)?\n|$)/s";
        if (preg_match($pattern, $rawContent, $rawMetaMatches) && isset($rawMetaMatches[3])) {
            $meta = $this->getYamlParser()->parse($rawMetaMatches[3]) ?: array();
            $meta = is_array($meta) ? $meta : array('title' => $meta);
            if (intval($meta['title']) > 1000000000) {
                $meta['title'] = date('d.m.Y', intval($meta['title']));
            }

            foreach ($headers as $name => $key) {
                if (isset($meta[$name])) {
                    // rename field (e.g. remove whitespaces)
                    if ($key != $name) {
                        $meta[$key] = $meta[$name];
                        unset($meta[$name]);
                    }
                } elseif (!isset($meta[$key])) {
                    // guarantee array key existence
                    $meta[$key] = '';
                }
            }

            if (!empty($meta['date']) || !empty($meta['time'])) {
                // workaround for issue #336
                // Symfony YAML interprets ISO-8601 datetime strings and returns timestamps instead of the string
                // this behavior conforms to the YAML standard, i.e. this is no bug of Symfony YAML
                if (is_int($meta['date'])) {
                    $meta['time'] = $meta['date'];
                    $meta['date'] = '';
                }

                if (empty($meta['time'])) {
                    $meta['time'] = strtotime($meta['date']) ?: '';
                } elseif (empty($meta['date'])) {
                    $rawDateFormat = (date('H:i:s', $meta['time']) === '00:00:00') ? 'Y-m-d' : 'Y-m-d H:i:s';
                    $meta['date'] = date($rawDateFormat, $meta['time']);
                }
            } else {
                $meta['date'] = $meta['time'] = '';
            }

            if (empty($meta['date_formatted'])) {
                if ($meta['time']) {
                    $encodingList = mb_detect_order();
                    if ($encodingList === array('ASCII', 'UTF-8')) {
                        $encodingList[] = 'Windows-1252';
                    }

                    $rawFormattedDate = strftime($this->getConfig('date_format'), $meta['time']);
                    $meta['date_formatted'] = mb_convert_encoding($rawFormattedDate, 'UTF-8', $encodingList);
                } else {
                    $meta['date_formatted'] = '';
                }
            }
        } else {
            // guarantee array key existance
            $meta = array_fill_keys($headers, '');
        }

        return $meta;
    }

    public function getConfig($configName = null, $default = null)
    {
        if ($configName !== null) {
            return isset($this->config[$configName]) ? $this->config[$configName] : $default;
        } else {
            return $this->config;
        }
    }

    public function getMetaHeaders()
    {
        if ($this->metaHeaders === null) {
            $this->metaHeaders = array(
                'Title' => 'title',
                'Description' => 'description',
                'Author' => 'author',
                'Date' => 'date',
                'Formatted Date' => 'date_formatted',
                'Time' => 'time',
                'Robots' => 'robots',
                'Template' => 'template',
                'Hidden' => 'hidden'
            );
        }

        return $this->metaHeaders;
    }
}
