<?php

namespace VirtualBoot;

class Routes
{
    public static function default($middleware)
    {   
        return [
            ['GET', 'package/install', 'VirtualBoot', 'install', $middleware],
            ['GET', 'package/uninstall', 'VirtualBoot', 'uninstall', $middleware],
            ['GET', 'package/activate', 'VirtualBoot', 'activate', $middleware],
            ['GET', 'package/deactivate', 'VirtualBoot', 'deactivate', $middleware],
        ];
    }
}