<?php

namespace Nacho\Helpers;

class Request implements RequestInterface
{
    public $requestMethod;
    protected Route $route;

    function __construct(Route $route)
    {
        $this->bootstrapSelf();
        $this->route = $route;
    }

    private function bootstrapSelf()
    {
        foreach ($_SERVER as $key => $value) {
            $this->{$this->toCamelCase($key)} = $value;
        }
    }

    private function toCamelCase($string)
    {
        $result = strtolower($string);

        preg_match_all('/_[a-z]/', $result, $matches);

        foreach ($matches[0] as $match) {
            $c = str_replace('_', '', strtoupper($match));
            $result = str_replace($match, $c, $result);
        }

        return $result;
    }

    public function getBody()
    {
        if (strtolower($this->requestMethod) === "get") {
            return [];
        }


        if (strtolower($this->requestMethod) === "post") {
            $body = array();
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }

            return $body;
        }

        return [];
    }

    public function getRoute()
    {
        return $this->route;
    }
}
