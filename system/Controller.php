<?php

namespace System;
use Jenssegers\Blade\Blade;

abstract class Controller
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }


    public function __get($key)
    {
        return $this->app->get($key);
    }
}