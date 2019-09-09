<?php

return [
    'host'        => env('MAIL_HOST', 'smtp.gmail.com'),
    'port'        => env('MAIL_PORT', 587),
    'username'    => env('MAIL_USERNAME'),
    'password'    => env('MAIL_PASSWORD'),
    'encrypition' => env('MAIL_ENCRYPTION', 'tls'),
];