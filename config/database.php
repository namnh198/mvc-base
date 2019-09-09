<?php

return [
    'dbconn' => env('DB_CONNECTION', 'mysql'),
    'dbhost' => env('DB_HOST', 'localhost'),
    'dbport' => env('DB_PORT', '3306'),
    'dbuser' => env('DB_USERNAME', 'forge'),
    'dbpass' => env('DB_PASSWORD', ''),
    'dbname' => env('DB_DATABASE', 'forge'),
];