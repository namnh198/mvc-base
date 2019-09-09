<?php

namespace System;

class Route
{
    private $app;

    private $routes = [];

    private $current = [];

    private $calls = [];

    private $notFound = '/404';

    public function __construct(App $app)
    {
        $this->app = $app;
    }


    public function getProperRoute()
    {
        foreach ($this->routes as $route) 
        {
            if($this->isMatchingUrl($route['pattern']) && $this->isMatchingMethod($route['method'])) 
            {
                $arguments = $this->getArgumentFrom($route['pattern']);
                $this->current = $route;
                return [$route['action'], $arguments];
            }
        }

        $this->app->redirect->to($this->notFound);
    }

    public function getCurrentUrl()
    {
        return $this->current['url'];
    }

    public function hasCallsFirst()
    {
        return ! empty($this->calls['first']);
    }

    public function callFirstCalls(callable $callable)
    {
        $this->calls['first'][] = $callable;
        return $this;
    }

    public function callFirst()
    {
        foreach ($this->calls['firsts'] as $callable) 
        {
            call_user_func($callable, $this->app);
        }
    }

    public function notFound($notFound)
    {
        $this->notFound = $notFound;
    }

    private function isMatchingUrl($pattern)
    {
        return preg_match($pattern, $this->app->request->url());
    }

    private function isMatchingMethod($method)
    {
        return strpos($method, $this->app->request->method()) !== FALSE;
    }

    private function getArgumentFrom($pattern)
    {
        preg_match($pattern, $this->app->request->url(), $matches);
        array_shift($matches);
        return $matches;
    }

    private function addRoute($method, $url, $action)
    {
        $route = [
            'url'     => $url,
            'pattern' => $this->generatePattern($url),
            'action'  => $this->getAction($action),
            'method'  => strtoupper($method)
        ];

        $this->routes[] = $route;
    }

    public function get($url, $action)
    {
        $this->addRoute('GET', $url, $action);
    }

    public function post($url, $action)
    {
        $this->addRoute('POST', $url, $action);
    }

    public function any($url, $action)
    {
        $this->addRoute('GET|POST', $url, $action);
    }

    private function generatePattern($url)
    {
        $pattern = '#^';
        $pattern .= preg_replace('/\{\w+}/', '([\w-]+)', $url);
        $pattern .= '$#';
        return $pattern;
    }

    private function getAction($action)
    {
        if(is_string($action)) 
        {
            $action = str_replace('/', '\\', $action);
            $action = strpos($action, '@') !== FALSE ? $action : $action . '@index';
            $action = explode('@', $action, 2);
        }
        
        if(is_callable($action) || (is_array($action) && count($action) === 2))
        {
            return $action;
        }

        throw new Exception('Controller wrong format !');
    }
}