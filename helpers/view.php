<?php

use \System\QueryBuilder as DB;

if(! function_exists('view'))
{
    function view($viewPath, array $data = []) {
        $app = System\App::getInstance();
        return $app->view->render($viewPath, $data);
    }
}

if(! function_exists('_e'))
{
    function _e($string)
    {
        $string = trim($string);
        $string = stripcslashes($string);
        $string = htmlspecialchars($string);
        return $string;
    }
}

if(! function_exists('url'))
{
    function url($path = '')
    {
        $app = System\App::getInstance();
        return rtrim($app->config['url'], '/') . $path;
    }
}

if(! function_exists('asset'))
{
    function asset($path = '')
    {
        $app = System\App::getInstance();
        return rtrim($app->config['url'], '/') . '/' . $path;
    }
}

if(! function_exists('setting'))
{
    function setting($key)
    {
        return DB::table('settings')->where('`key` = ?', 'title')->fetch()->value;
    }
}