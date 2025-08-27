<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */


    'stripe' => [
        'secret' => env('STRIPE_SECRET'),

        // Founding Members
        'founding_novice_yearly' => env('STRIPE_FOUNDING_NOVICE_YEARLY'),
        'founding_trainer_yearly' => env('STRIPE_FOUNDING_TRAINER_YEARLY'),

        // Post Founding Members
        'post_novice_monthly' => env('STRIPE_POST_NOVICE_MONTHLY'),
        'post_novice_yearly' => env('STRIPE_POST_NOVICE_YEARLY'),
        'post_trainer_monthly' => env('STRIPE_POST_TRAINER_MONTHLY'),
        'post_trainer_yearly' => env('STRIPE_POST_TRAINER_YEARLY'),
    ],



    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
