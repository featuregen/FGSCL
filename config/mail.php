<?php
/**
 * Mail Configuration
 */

return [
    'host'       => env('MAIL_HOST', 'smtp.yourdomain.com'),
    'port'       => (int)env('MAIL_PORT', 587),
    'username'   => env('MAIL_USERNAME', ''),
    'password'   => env('MAIL_PASSWORD', ''),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@yourdomain.com'),
        'name'    => env('MAIL_FROM_NAME', 'EduGen'),
    ],
];
