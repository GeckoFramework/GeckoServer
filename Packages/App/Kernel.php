<?php
namespace App;

use App\PDOException;

class Kernel
{
    const PACKAGE_CONTROLLER = 'Controller';
    const PACKAGE_MODEL = 'Model';
    const PACKAGE_CONFIG = 'Config';
    const PACKAGE_ROUTES = 'Routes';
    const PACKAGE_MIDDLEWARE = 'Middleware';

    const ROUTES_METHOD = 0;
    const ROUTES_ROUTE = 1;
    const ROUTES_CONTROLLER = 2;
    const ROUTES_ACTION = 3;
    const ROUTES_MIDDLEWARE = 4;

    private static $components = [];
    private static $componentsInstances = [];
    private static $packages = [];
    private static $configuration = [];
    private static $routes;
    private static $componentsPriority = [];

    function __construct()
    {
        spl_autoload_register(function ($className) {
            $path = explode('\\', $className);
            $package = $path[0];
            unset($path[0]);
            $file = implode('\\', $path);
            if (!self::includePackageFile($package, $file)) {
                throw new \Exception("Class '" . $className . "' doesn't exists");
            }
        });
        self::initConfig();
        self::initPackages([
            'App',
            'JSON',
            'PDO'
        ]);
        self::initComponents();
        self::implementComponents($this, 'App\Interfaces\Kernel');
    }

    public static function initPackages($packages)
    {
        self::$packages = array_merge(self::$packages, $packages);
    }

    public static function getPackageComponents($package)
    {
        $path = 'Packages/' . $package . '/Components';
        if ($scanDir = @scandir($path)) {
            if ($scanDirFiltered = array_diff($scanDir, array('.', '..'))) {
                return array_map(function ($value) {
                    return pathinfo($value, \PATHINFO_FILENAME);
                }, $scanDirFiltered);
            }
        }
        return [];
    }

    public static function initComponents()
    {
        foreach (self::getPackages(true) as $package) {
            foreach (self::getPackageComponents($package) as $component) {
                if (self::includePackageFile($package, "Components/" . $component)) {
                    $class = $package . '\\Components\\' . $component;
                    $types = class_implements($class);
                    foreach ($types as $componentType) {
                        if (!array_key_exists($componentType, self::$components)) {
                            self::$components[$componentType] = [];
                        }
                        self::$components[$componentType][$package] = $class;
                    }
                }
            }
        }
    }

    public static function setPriority($componentType, $order)
    {
        self::$componentsPriority[$componentType] = $order;
    }

    public static function implementComponents(&$instance, $componentType, $package = false, $constructor = false)
    {
        if (array_key_exists($componentType, self::$components)) {
            $interfaceName = explode('\\', $componentType);
            $componentIdentifier = end($interfaceName);
            $instance->$componentIdentifier = new ComponentInterface();
            foreach (self::$components[$componentType] as $componentName => $componentClass) {
                if ($package && $package !== $componentName) {
                    continue;
                }
                if ($constructor) {
                    try {
                        $instance->$componentIdentifier->__addComponent($componentName, new $componentClass($constructor));
                    } catch (\Exception $e) {
                        die(500);
                    }
                } else {
                    if (!key_exists($componentClass, static::$componentsInstances)) {
                        try {
                            static::$componentsInstances[$componentClass] = new $componentClass();
                        } catch (\Exception $e) {
                            die(500);
                        }

                    }
                    $instance->$componentIdentifier->__addComponent($componentName, static::$componentsInstances[$componentClass]);
                }
            }
            $priorityOrder = null;
            if (array_key_exists($componentType, self::$componentsPriority)) {
                $priorityOrder = self::$componentsPriority[$componentType];
            } else {
                $priorityOrder = array_keys(self::$components[$componentType]);
            }
            $instance->$componentIdentifier->__loadPriority($priorityOrder);
        }
        return true;
    }

    public static function initConfig()
    {
        $config = [];
        foreach (self::getPackages() as $package) {
            if (self::includePackageFile($package, self::PACKAGE_CONFIG)) {
                self::$configuration = array_merge($config, self::$configuration);
            }
        }
        include self::PACKAGE_CONFIG . '.php';
        self::$configuration = array_merge($config, self::$configuration);
    }

    public static function getConfig($config)
    {
        return self::$configuration[$config];
    }

    public static function getPackages($excludeApp = false)
    {
        $packages = self::$packages;
        if ($excludeApp) {
            $packages = array_diff(self::$packages, ['App']);
        }
        return $packages;
    }

    public static function packageExists($package)
    {
        $path = 'Packages/' . $package . '/';
        return is_dir($path);
    }

    public static function includePackageFile($package, $file, $alsoIfNotInitialized = false)
    {
        $path = 'Packages/' . $package . '/' . str_replace("\\", "/", $file) . '.php';
        if ((in_array($package, self::getPackages()) || $alsoIfNotInitialized) && file_exists($path)) {
            require_once $path;
            return $path;
        }
        return false;
    }

    public static function addRoute($method, $route, $controller, $action, $middleware = false)
    {
        $middlewareBefore = false;
        $middlewareAfter = false;
        if (is_string($middleware) && self::includePackageFile($middleware, self::PACKAGE_MIDDLEWARE)) {
            $class = $middleware . '\\' . self::PACKAGE_MIDDLEWARE;
            if (method_exists($class, "before")) {
                $middlewareBefore = $class::before();
            }
            if (method_exists($class, "after")) {
                $middlewareAfter = $class::after();
            }
        }
        self::$routes[$method][$route] = [$controller, $action, $middlewareBefore, $middlewareAfter];
    }

    public static function loadPackageRoutes($package, $middleware = false, $group = 'default')
    {
        if (self::includePackageFile($package, self::PACKAGE_ROUTES)) {
            $class = $package . '\\' . self::PACKAGE_ROUTES;
            if (method_exists($class, $group)) {
                $routesGroup = $class::$group($middleware);
                if (is_array($routesGroup)) {
                    foreach ($routesGroup as $route) {
                        self::addRoute($route[self::ROUTES_METHOD], $route[self::ROUTES_ROUTE], $route[self::ROUTES_CONTROLLER], $route[self::ROUTES_ACTION], @$route[self::ROUTES_MIDDLEWARE]);
                    }
                }
            }
        }
    }

    public function serve()
    {
        if (array_key_exists($_SERVER['REQUEST_METHOD'], self::$routes)) {
            if (array_key_exists(@$_REQUEST['route'], self::$routes[$_SERVER['REQUEST_METHOD']])) {
                list($package, $action, $middlewareBefore, $middlewareAfter) = self::$routes[$_SERVER['REQUEST_METHOD']][$_REQUEST['route']];
                $class = $package . '\\' . self::PACKAGE_CONTROLLER;
                if (method_exists($class, $action)) {
                    $controllerInstance = new $class();
                    $request = new Request($_REQUEST);
                    $next = true;
                    if (is_callable($middlewareBefore)) {
                        $next = $middlewareBefore($request);
                    }
                    if ($next) {
                        $next = $controllerInstance->$action($request);
                        if (is_callable($middlewareAfter)) {
                            $middlewareAfter($request, $next);
                        }
                        die();
                    } else {
                        return false;
                    }
                }
            }
        }
        die(404);
    }
}