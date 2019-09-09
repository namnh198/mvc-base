<?php

if(file_exists(dirname(__DIR__) . '/vendor/autoload.php'))
{
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}
else 
{
    die('<h1>Composer does not install</h1>');
}

$config = file_call('config/app.php');

$debug = filter_var($config['debug'], FILTER_VALIDATE_BOOLEAN);

if($debug)
{
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
else
{
    ini_set('display_errors', 0);
    error_reporting(0);
}

date_default_timezone_set($config['timezone']);

$app = System\App::getInstance($config);
$app->run();