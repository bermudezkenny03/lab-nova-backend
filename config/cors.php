<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración CORS
    |--------------------------------------------------------------------------
    | Ajustes para permitir solicitudes desde el frontend (evita bloqueos
    | en el navegador durante desarrollo/producción según convenga).
    | Modifica "allowed_origins" en producción para restringir orígenes.
    */

    // Rutas en las que se aplicará CORS (incluye API y cookie CSRF de Sanctum)
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Métodos permitidos (usar ['*'] para permitir todos)
    'allowed_methods' => ['*'],

    // Orígenes permitidos. En desarrollo se puede usar '*', en producción encuéntralo
    // a los dominios de frontend (ej. 'https://app.midominio.com')
    'allowed_origins' => ['*'],

    // Patrones para orígenes (opcional)
    'allowed_origins_patterns' => [],

    // Cabeceras que el frontend puede enviar
    'allowed_headers' => ['*'],

    // Cabeceras que se expondrán al frontend
    'exposed_headers' => [],

    // Tiempo en segundos para cachear preflight
    'max_age' => 0,

    // Si se permiten cookies/sesiones entre dominios (true si usas cookies con frontend en otro dominio)
    'supports_credentials' => false,
];
