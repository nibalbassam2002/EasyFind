<?php

return [
    'public_key' => env('LAHZA_PUBLIC_KEY'),
    'secret_key' => env('LAHZA_SECRET_KEY'),
    'webhook_secret' => env('LAHZA_WEBHOOK_SECRET'),
    'base_uri' => 'https://api.lahza.io', // للـ API
    'payment_page_base_url' => env('LAHZA_PAYMENT_PAGE_BASE_URL', 'https://pay.lahza.io'),
];