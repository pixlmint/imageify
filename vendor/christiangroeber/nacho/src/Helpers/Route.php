<?php

namespace Nacho\Helpers;

class Route
{
    private string $path;
    private string $controller;
    private string $minRole;
    private string $function;
    private array $variables;
    private array $arrRoute;

    public function __construct(array $route)
    {
        $this->arrRoute = $route;
        $this->path = $route['route'];
        $this->controller = $route['controller'];
        $this->function = $route['function'];
        if (!isset($route['variables'])) {
            $route['variables'] = [];
        }
        $this->variables = $route['variables'];
        if (!isset($route['minRole'])) {
            $route['minRole'] = 'Guest';
        }
        $this->minRole = $route['minRole'];
        if ($this->path !== '/') {
            $this->path = substr($this->path, 1, strlen($this->path));
        }
    }

    public function getVariables()
    {
        return $this->variables;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getMinRole()
    {
        return $this->minRole;
    }

    public function getFunction()
    {
        return $this->function;
    }

    public function match(string $path)
    {
        if ($path === $this->path) {
            return true;
        }
        $re = '/{[a-zA-Z0-9]*}/m';
        $matches = [];
        preg_match_all($re, $this->path, $matches, PREG_SET_ORDER, 0);
        if (empty($matches)) {
            return false;
        }
        $parts = explode('/', $this->path);
        $pathParts = explode('/', $path);
        if (count($parts) !== count($pathParts)) {
            return false;
        }
        $variables = $this->variables;
        foreach ($matches as $var) {
            $var = $var[0];
            $index = array_search($var, $parts);
            $value = $pathParts[$index];
            $re2 = '/[a-zA-Z0-9]*/m';
            $par = [];
            preg_match_all($re2, $var, $par);
            $par = $par[0][1];
            $variable = [];
            foreach ($variables as $tmp) {
                if ($tmp['variable'] === $par && isset($tmp['options'])) {
                    $variable = $tmp;
                    break;
                }
            }
            if (!$variable) {
                $this->$par = $value;
            } else {
                $options = $variable['options'];
                if (is_bool(array_search($value, $options))) {
                    return false;
                }
                $this->$par = $value;
            }
        }

        return true;
    }

    private function setVariable(string $key, string $value)
    {
        $this->$key = $value;

        return $this;
    }

    private function getPathVariables(string $path)
    {
    }
}
