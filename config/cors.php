<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */


    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login', 'logout', 'register', // if you use these web routes
        'user','test',                        // hit by many apps after login
        'forgot-password', 'reset-password', // if you use Breeze/Fortify
    ],

    'allowed_methods' => ['*'],

    // Development and Production origins
    'allowed_origins' => [
        'https://prime.ajurnie.com',
        'http://localhost:3000', // React dev serve
        'http://localhost:5173', // Vite dev server

    ],

    // OPTION B (alternative): allow any HTTPS subdomain of geniusretired.com
    // Comment OPTION A above and use this if you truly need multiple subdomains.
    // 'allowed_origins' => [],
    // 'allowed_origins_patterns' => ['#^https://([a-z0-9-]+\.)*geniusretired\.com$#i'],

    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'Accept',
        'Origin',
        'Cache-Control',
        'X-Requested-With',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
