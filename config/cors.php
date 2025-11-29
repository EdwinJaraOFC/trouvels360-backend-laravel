<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['https://trouvels360.vercel.app'],
    'allowed_origins_patterns' => [
        'https://trouvels360.vercel.app',       
        'https://trouvels360-.*\.vercel\.app',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,

];
