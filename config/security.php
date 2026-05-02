<?php

return [
    'auth' => [
        'public_registration' => env('AUTH_PUBLIC_REGISTRATION', false),
        'register_throttle' => env('AUTH_REGISTER_THROTTLE', '3,10'),
        'forgot_password_throttle' => env('AUTH_FORGOT_PASSWORD_THROTTLE', '5,10'),
    ],
];
