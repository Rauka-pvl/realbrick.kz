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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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
    
    'bitrix24' => [
        'db_connection' => env('BITRIX24_DB_CONNECTION', 'diller'),
        'verify_ssl' => env('BITRIX24_VERIFY_SSL', true),
        'rest_url' => env('BITRIX24_CATALOG_URL', 'https://realbrick.bitrix24.kz/rest/152/ykk17l6z3bucehjf'),
        'iblock_id' => (int) env('BITRIX24_CATALOG_IBLOCK_ID', 14),
        'product_iblock_id' => (int) env('BITRIX24_PRODUCT_IBLOCK_ID', 14), // если товары в др. инфоблоке — задайте BITRIX24_PRODUCT_IBLOCK_ID=16
        'root_section_id' => (int) env('BITRIX24_ROOT_SECTION_ID', 22),
        // Исключить разделы верхнего уровня (только RealBrick). Точное совпадение имени.
        'excluded_root_section_names' => array_values(array_filter(array_map('trim', explode(',', env('BITRIX24_EXCLUDED_SECTION_NAMES', 'Модная одежда,Одежда,Товары,Галерея Дизайна'))))),
    ],

];
