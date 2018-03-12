<?php

namespace Request\Components;

class Request implements \App\Interfaces\Request
{
    protected static $headers;

    function __construct($request)
    {
        self::$headers = getallheaders();
    }

    public function header($header)
    {
        if(array_key_exists($header, self::$headers)){
            return self::$headers[$header];
        }
        return false;
    }

    public function get($parameter)
    {
        return @$_REQUEST[$parameter];
    }
}