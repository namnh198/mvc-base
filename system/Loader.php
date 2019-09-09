<?php

namespace System;

class Loader
{
    private $app;

    private $controllers = [];

    private $methods = [];

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function action($controller, $method, $arguments)
    {
        $object = $this->controller($controller);

        if(method_exists($object, $method))
        {
            return call_user_func_array([$object, $method], $arguments);
        }

        throw new Exception("Method {$controller}@{$method} doesn't exists !");
    }

    public function callable(callable $callable, array $arguments = [])
    {
        return call_user_func_array($callable, $arguments);
    }

    public function controller($controller)
    {
        $controller = $this->getControllerName($controller);

        if(! $this->hasController($controller)) 
        {
            $this->addController($controller);
        }

        return $this->getController($controller);
    }

    private function getControllerName($controller)
    {
        return strpos($controller, 'App') === 0 ? $controller : 'App\\Controllers\\' . $controller;
    }

    private function hasController($controller)
    {
        return array_key_exists($controller, $this->controllers);
    }

    private function addController($controller)
    {
        if(! class_exists($controller)) 
        {
            throw new Exception("Controller $controller doesn't exists !");
        }
        
        $object = new $controller($this->app);
        
        $this->controllers[$controller] = $object;
    }

    private function getController($controller)
    {
        return $this->controllers[$controller];
    }
}