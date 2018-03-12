<?php

namespace App;

abstract class Controller
{
    protected $model;
    function __construct()
    {
        $reflector = new \ReflectionClass(get_called_class());
        $packageName = $reflector->getNamespaceName();
        if(Kernel::includePackageFile($packageName, Kernel::PACKAGE_MODEL)){
            $className = $packageName . "\\" . Kernel::PACKAGE_MODEL;
            $this->model = new $className();
        }
        Kernel::implementComponents($this, 'App\Interfaces\Output');
        Kernel::implementComponents($this, 'App\Interfaces\Request');
    }
}