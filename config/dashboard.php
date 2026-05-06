<?php

return [
    // Modules that influence whether the dashboard should be visible when explicit dashboard permission is not set
    'relevant_modules' => [
        'reports',
        'reservations',
        'equipment',
        'users',
        'categories',
    ],

    // TTL de caché (minutos) para mapas de permisos por usuario. Se puede configurar desde .env (DASHBOARD_CACHE_TTL).
    'cache_ttl_minutes' => env('DASHBOARD_CACHE_TTL', 60),
];
