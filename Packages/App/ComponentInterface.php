<?php

namespace App;

class ComponentInterface
{
    protected $components;

    public function __addComponent($name, $instance)
    {
        $this->components[$name] = $instance;
    }

    public function __use($name = false)
    {
        $components = $this->components;
        $component = null;
        if ($name) {
            $component = $components[$name];
        } else {
            $component = reset($components);
        }
        return $component;
    }

    public function __loadPriority($orderArray)
    {
        $this->components = array_replace(array_flip($orderArray), $this->components);
    }

    public function __call($methodName, $arguments)
    {
        foreach($this->components as $name => $instance){
            if(method_exists($instance, $methodName)){
                return call_user_func_array([$instance, $methodName], $arguments);
            }
        }
        throw new \Exception("Method " . $methodName . " doesn't exists");
    }
}