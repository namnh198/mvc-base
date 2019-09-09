<?php

const DS = DIRECTORY_SEPARATOR;

if(! function_exists('pre'))
{
    function pre($variable)
    {
        echo '<pre>';
        print_r($variable);
        echo '</pre>';
    }
}

if(! function_exists('pred'))
{
    function pred($variable)
    {
        pre($variable);
        die;
    }
}

if(! function_exists('array_get'))
{
    function array_get($array, $key, $default = null)
    {
        return $array[$key] ?? $default;
    }
}

if(! function_exists('file_to'))
{
    function file_to($path = '/')
    {
        return dirname(__DIR__) . DS . str_replace(['/', '\\'], DS, $path);
    }
}

if(! function_exists('file_has'))
{
    function file_has($path)
    {
        return file_exists(file_to($path));
    }
}

if(! function_exists('view_path'))
{
    function view_path($path = '')
    {
        $path = (strpos($path, '.')) ? str_replace('.', '/', $path) : $path;
        return file_to('app/Views/' . $path . '.php');
    }
}

if(! function_exists('cache_path'))
{
    function cache_path($path = '')
    {
        return file_to('cache/' . $path);
    }
}

if(! function_exists('file_call'))
{
    function file_call($path)
    {
        $file = file_to($path);
        if(! file_exists($file)) throw new System\Exception("$path Not Exists");
        return require $file;
    }
}

if (!function_exists('config')) {
    function config($variable)
    {
        if(preg_match('/(.*?)\.(.*)/', $variable, $match))
        {
            $url = $match[1];
            $param = $match[2];
        }
        else 
        {
            throw new \System\Exception("The {$variable} doesn't exists !");
        }
        
        $config = file_call('config/' . $url . '.php');
        return $config[$param];
    }
}

if (!function_exists('readDotENV')) {
    function readDotENV()
    {
        $app_url = dirname(dirname(__FILE__));
        $path = $app_url . '/.env';
        $handle = file_get_contents($path);
        $paze = explode("\n", $handle);
        foreach ($paze as $key => $value) {
            $vl[$key] = explode("=", $value);
            if (isset($vl[$key][0]) && isset($vl[$key][1])) {
                $env[$vl[$key][0]] = $vl[$key][1];
            }
        }
        return $env;
    }
}
if (!function_exists('env')) {
    function env($variable, $ndvalue = null)
    {
        $env = readDotENV();
        foreach ($env as $key => $value) {
            if ($variable == $key) {
                $result = preg_replace('/\s+/', '', $value);
                if (!empty($result)) {
                    return $result;
                }
                break;
            }
        }
        return $ndvalue;
    }
}