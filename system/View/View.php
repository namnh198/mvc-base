<?php

namespace System\View;
use System\App;

class View 
{
    private $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function render($viewPath, $data = [])
    {
        return new ViewFactory($viewPath, $data);
    }
}