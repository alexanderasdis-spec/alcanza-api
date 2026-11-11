<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Comprueba con Cloudflare Turnstile que quien envía el formulario es una persona.
 *
 * El navegador resuelve la verificación y manda un código de un solo uso. Aquí se le pregunta
 * a Cloudflare si ese código es válido usando la clave secreta, que nunca sale del servidor.
 * Así un bot no se la salta llamando directo a la API.
 *
 * La clave se configura en TURNSTILE_SECRET_KEY. En desarrollo se usa la clave de prueba de
 * Cloudflare que siempre aprueba; al desplegar se cambia por la real.
 */
class Turnstile implements ValidationRule
{
    /** Dirección donde Cloudflare verifica los códigos. */
    private const URL_VERIFICACION = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail('Confirma que no eres un robot.');

            return;
        }

        try {
            $respuesta = Http::asForm()->timeout(5)->post(self::URL_VERIFICACION, [
                'secret' => config('services.turnstile.secret'),
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);
        } catch (ConnectionException) {
            // Si Cloudflare no responde, se rechaza: es más seguro que dejar pasar sin verificar.
            $fail('No pudimos comprobar que no eres un robot. Inténtalo de nuevo.');

            return;
        }

        if ($respuesta->json('success') !== true) {
            $fail('La verificación venció o no es válida. Vuelve a hacerla.');
        }
    }
}
