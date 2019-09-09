<?php

namespace System;
use \Exception as BaseException;
use \Whoops\Handler\PrettyPageHandler;

class Exception extends BaseException
{

    public function __construct($message, $code = null)
    {
        $debug = filter_var(config('app.debug'), FILTER_VALIDATE_BOOLEAN);

        if($debug)
        {
            $whoops = new \Whoops\Run;
            $whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
            parent::__construct($message, $code);
        }
        else
        {
            $html = file_get_contents(view_path('errors.503'));
            echo $html;
            exit;
        }
        
    }
}