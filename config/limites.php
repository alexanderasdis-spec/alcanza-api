<?php

/*
 * Límites de peticiones por minuto (los usa AppServiceProvider).
 *
 * Evitan que una sola IP o un solo usuario acapare el servidor: al pasarse,
 * la API responde 429 sin hacer el trabajo de la ruta.
 * Se pueden cambiar desde el .env sin tocar el código.
 */
return [

    // Toda la API, contada por dirección IP (middleware LimiteTemprano, antes de revisar el token).
    // Ojo: varias personas detrás del mismo internet (un colegio, una oficina) comparten IP.
    'por_ip' => (int) env('LIMITE_POR_IP', 300),

    // Por token (LimiteTemprano) y por usuario (throttle:por_usuario, después del token):
    // frena a una cuenta que abuse aunque cambie de IP.
    'por_usuario' => (int) env('LIMITE_POR_USUARIO', 120),

    // Rutas que escriben en la base de datos (crear, cambiar, borrar, abonar), por usuario.
    'escrituras' => (int) env('LIMITE_ESCRITURAS', 30),

    // Registro, login, contraseñas y foto, por IP y por ruta: frena a quien intente adivinar claves.
    'cuentas' => (int) env('LIMITE_CUENTAS', 10),

    // Dónde se guardan los contadores del filtro temprano (un almacén de config/cache.php).
    // "file" rechaza el doble de rápido que "database" porque no consulta MySQL (medido con k6).
    // Con varios servidores se usaría "redis", para que todos compartan los contadores.
    'almacen' => env('LIMITE_ALMACEN', 'file'),

    // En internet: encabezado donde el hosting deja la IP real del visitante (por ejemplo
    // CF-Connecting-IP detrás de Cloudflare). Vacío en desarrollo, donde no hay intermediario.
    'cabecera_ip' => env('LIMITE_CABECERA_IP'),

];
