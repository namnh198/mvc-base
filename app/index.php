<?php

$app = System\App::getInstance();

$app->route->get('/', function(){
    return view('welcome');
});

// NotFound
$app->route->get('/404', function() {
    new \System\Http\HttpResponseCode(404);
    return view('errors.404');
});
