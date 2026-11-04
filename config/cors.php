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

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Solo el frontend de Alcanza puede consumir la API desde el navegador.
    // Las direcciones se configuran en FRONTEND_URLS del .env, separadas por comas
    // (5173 es "npm run dev" y 5180 la vista previa).
    'allowed_origins' => explode(',', env('FRONTEND_URLS', 'http://localhost:5173,http://localhost:5180')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
