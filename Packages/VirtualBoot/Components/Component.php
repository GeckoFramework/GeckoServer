<?php
namespace VirtualBoot\Components;

use App;

class Component implements App\Interfaces\Kernel
{
    public function __construct()
    {
        App\Kernel::implementComponents($this, 'App\Interfaces\Database');
        $this->model = new \VirtualBoot\Model();
        $this->boot();
    }

    public function boot()
    {
        $packages = $this->model->getActivePackages();
        App\Kernel::initPackages($packages);
        App\Kernel::initComponents();
        $this->model->loadPriorities();
        $routes = $this->model->getRoutes($packages);
        if ($routes) {
            foreach ($routes as $route) {
                App\Kernel::addRoute($route['method'], $route['route'], $route['controller'], $route['action'], $route['middleware']);
            }
        }
    }
}