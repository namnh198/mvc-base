<?php

namespace System;

class Redirect
{
    private $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function to(string $url)
    {
        if(! is_string($url)) 
        {
            throw new Exception ('URl wrong format');
        }

        $url = rtrim($this->app->config['app_url'], '/') . $url;

        header("Location: $url");
        exit();
    }
}