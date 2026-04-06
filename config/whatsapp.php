<?php

return [
    'base_url' => env('WHATSAPP_BASE_URL', 'https://graph.facebook.com'),
    'graph_version' => env('WHATSAPP_GRAPH_VERSION', 'v24.0'),
    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
    'app_secret' => env('WHATSAPP_APP_SECRET'),
    'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
    'route_prefix' => env('WHATSAPP_ROUTE_PREFIX', 'whatsapp'),
    'middleware' => ['api'],
];
