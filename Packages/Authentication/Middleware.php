<?php

namespace Authentication;

class Middleware
{
    function __construct()
    {
        \App\Kernel::implementComponents($this, "App\Interfaces\Request");
    }
    public function before(){
        return function(){
            return $this->request->checkToken();
        };
    }
}