<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Paths where CORS should be applied
    'allowed_methods' => ['*'], // Allowed HTTP methods
    'allowed_origins' => ['*'], // Allowed origins (use '*' to allow all domains)
    'allowed_origins_patterns' => [], // Regex patterns for allowed origins
    'allowed_headers' => ['*'], // Allowed headers
    'exposed_headers' => [], // Headers exposed to the client
    'max_age' => 0, // Max age for preflight requests
    'supports_credentials' => false, // Whether to allow credentials (cookies, authorization headers)
];