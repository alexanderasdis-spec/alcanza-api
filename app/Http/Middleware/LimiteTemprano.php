<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Primer filtro de la API: limita las peticiones por minuto de cada IP y de cada token.
 *
 * Corre antes que todo lo demás (rutas, token, controladores), así una petición que se pasa
 * del límite se rechaza con 429 sin consultar la base de datos para saber quién es.
 * La prueba con k6 mostró que, sin este filtro, rechazar costaba casi lo mismo que atender:
 * Laravel revisaba el token en la base de datos antes de llegar al límite.
 *
 * El token se cuenta tal como llega (resumido con un hash), sin validarlo: un atacante con un
 * token real queda frenado por el límite del token, y uno que invente tokens, por el de la IP.
 * Los límites están en config/limites.php.
 */
class LimiteTemprano
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('api/*')) {
            return $next($request);
        }

        $limitador = new RateLimiter(Cache::store(config('limites.almacen')));

        $contadores = ['temprano:ip:'.$request->ip() => config('limites.por_ip')];
        if ($token = $request->bearerToken()) {
            $contadores['temprano:token:'.hash('xxh128', $token)] = config('limites.por_usuario');
        }

        foreach ($contadores as $clave => $maximo) {
            // hit() suma uno y devuelve el total del minuto; si se pasa, se rechaza aquí mismo.
            if ($limitador->hit($clave, 60) > $maximo) {
                return response()->json(
                    ['message' => 'Hiciste demasiadas peticiones. Espera un momento y vuelve a intentarlo.'],
                    429,
                    ['Retry-After' => $limitador->availableIn($clave)],
                );
            }
        }

        return $next($request);
    }
}
