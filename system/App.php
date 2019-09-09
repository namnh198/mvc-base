<?php

namespace System;
use \Closure;

class App
{
    private static $instance;

    private $container = [];

    private function __construct($config = null)
    {  
        $this->share('config', $config);
        $this->loadHelpers();
    }

    public static function getInstance($config = null)
    {
        if(is_null(static::$instance))
        {
            static::$instance = new static($config);
        }
        
        return static::$instance;
    }

    public function run()
    {
        $this->session->start();
        $this->request->prepareUrl();

        file_call('app/index.php');
        
        list($action, $arguments) = $this->route->getProperRoute();

        if($this->route->hasCallsFirst()) 
        {
            $this->route->callFirstCalls();
        }

        if(is_array($action))
        {
            $output = (string) $this->load->action($action[0], $action[1], $arguments);
        }
        elseif(is_callable($action))
        {
            $output = (string) $this->load->callable($action, $arguments) ;
        }

        $this->response->setOutput($output);
        $this->response->send();
    }

    public function share($key, $value)
    {
        if($value instanceof Closure)
        {
            $value = call_user_func($value, $this);
        }
        
        $this->container[$key] = $value;
    }

    private function isSharing($key)
    {
        return isset($this->container[$key]);
    }

    private function isCoreAlias($alias)
    {
        $coreClasses = $this->coreClasses();
        return isset($coreClasses[$alias]);
    }

    private function createNewCoreObject($alias)
    {
        $coreClasses = $this->coreClasses();
        $object = $coreClasses[$alias];

        if(class_exists($object))
        {
            return new $object($this);
        }

        throw new Exception('Class ' . ucfirst($coreClasses[$alias]) . ' not exists');
    }

    public function get($key)
    {
        if(! $this->isSharing($key)) 
        {
            if(! $this->isCoreAlias($key)) 
            {
                throw new Exception('System\\' . ucfirst($key) . ' not found in application');
            }

            $this->share($key, $this->createNewCoreObject($key));
        }

        return $this->container[$key];
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    private function loadHelpers()
    {
        $helpers = array_diff(scandir(file_to('helpers')), ['.', '..', 'common.php']);

        foreach ($helpers as $helper) 
        {
            file_call('helpers/' . $helper);
        }
    }

    private function loadRoutes()
    {
        $routes = array_diff(scandir(file_to('routes')), ['.', '..']);

        foreach ($routes as $route) 
        {
            file_call('routes/' . $route);
        }
    }

    private function coreClasses()
    {
        return [
            'request'  => 'System\\Http\\Request',
            'response' => 'System\\Http\\Response',
            'session'  => 'System\\Session',
            'cookie'   => 'System\\Cookie',
            'route'    => 'System\\Route',
            'load'     => 'System\\Loader',
            'redirect' => 'System\\Redirect',
            'html'     => 'System\\Html',
            'validate' => 'System\\Validation',
            'paginate' => 'System\\Pagination',
            'view'     => 'System\\View\\View',
        ];
    }
}