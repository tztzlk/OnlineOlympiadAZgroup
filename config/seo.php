<?php

return [
    'site_name' => env('SEO_SITE_NAME', 'Online Olympiad'),
    'site_url' => rtrim(env('SEO_SITE_URL', env('APP_URL', 'https://onlineolympiadazgroup-3fw5c0hn.on-forge.com')), '/'),
    'default_locale' => env('SEO_LOCALE', 'ru_KZ'),
    'default_lang' => env('SEO_LANG', 'ru-KZ'),
    'default_image' => env('SEO_DEFAULT_IMAGE', '/welcome.png'),
    'support_email' => env('SUPPORT_EMAIL', env('VITE_SUPPORT_EMAIL', 'support@olympiad.kz')),
    'support_phone' => env('SUPPORT_PHONE', env('VITE_SUPPORT_PHONE', '+7 (777) 000-00-00')),
];
