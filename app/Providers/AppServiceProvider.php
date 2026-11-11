<?php

namespace App\Providers;

use App\Models\User;
use App\Repositories\AbonoRepository;
use App\Repositories\Contracts\AbonoRepositoryInterface;
use App\Repositories\Contracts\MetaRepositoryInterface;
use App\Repositories\Contracts\PerfilRepositoryInterface;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use App\Repositories\MetaRepository;
use App\Repositories\PerfilRepository;
use App\Repositories\UsuarioRepository;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Le dice a Laravel qué clase usar cuando un servicio pide una interfaz de repositorio.
     * Si mañana cambia la forma de guardar los datos, solo se cambia la clase aquí.
     */
    public function register(): void
    {
        $this->app->bind(UsuarioRepositoryInterface::class, UsuarioRepository::class);
        $this->app->bind(PerfilRepositoryInterface::class, PerfilRepository::class);
        $this->app->bind(MetaRepositoryInterface::class, MetaRepository::class);
        $this->app->bind(AbonoRepositoryInterface::class, AbonoRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->definirLimites();

        // El enlace de "Olvidé mi contraseña" lleva a la página de React, no a una vista de Laravel.
        ResetPassword::createUrlUsing(fn (User $usuario, string $token) => $this->enlaceRestablecer($usuario, $token));

        // Correo de recuperación en español y con el nombre del usuario.
        ResetPassword::toMailUsing(fn (User $usuario, string $token) => (new MailMessage)
            ->subject('Cambia tu contraseña de Alcanza')
            ->greeting('¡Hola, '.$usuario->name.'!')
            ->line('Recibimos una solicitud para cambiar la contraseña de tu cuenta de Alcanza.')
            ->action('Cambiar mi contraseña', $this->enlaceRestablecer($usuario, $token))
            ->line('El enlace vence en '.config('auth.passwords.users.expire').' minutos.')
            ->line('Si no pediste este cambio, puedes ignorar este correo: tu contraseña sigue igual.')
            ->salutation('El equipo de Alcanza'));
    }

    /**
     * Límites de peticiones por minuto de las rutas. Los valores están en config/limites.php.
     * El límite por IP y por token va antes, en el middleware LimiteTemprano.
     *
     * - por_usuario: rutas con sesión, por usuario (cubre varios tokens de la misma cuenta).
     * - escrituras: crear, cambiar, borrar y abonar, por usuario.
     * - cuentas: registro, login y contraseñas, por IP y por ruta.
     *
     * Al pasarse, la API responde 429 con un mensaje en español y el encabezado Retry-After
     * (cuántos segundos esperar).
     */
    private function definirLimites(): void
    {
        $demasiadas = fn (Request $request, array $encabezados) => response()->json(
            ['message' => 'Hiciste demasiadas peticiones. Espera un momento y vuelve a intentarlo.'],
            429,
            $encabezados,
        );
        // Con sesión se cuenta por usuario; sin sesión, por IP.
        $identificador = fn (Request $request) => $request->user()?->id ? 'usuario:'.$request->user()->id : 'ip:'.$request->ip();

        RateLimiter::for('por_usuario', fn (Request $request) => Limit::perMinute(config('limites.por_usuario'))
            ->by($identificador($request))->response($demasiadas));

        RateLimiter::for('escrituras', fn (Request $request) => Limit::perMinute(config('limites.escrituras'))
            ->by($identificador($request))->response($demasiadas));

        // Los intentos de login no gastan los del registro: se cuenta por IP y por ruta.
        RateLimiter::for('cuentas', fn (Request $request) => Limit::perMinute(config('limites.cuentas'))
            ->by($request->ip().'|'.$request->path())->response($demasiadas));
    }

    /**
     * Arma el enlace a la página "Restablecer contraseña" del frontend.
     */
    private function enlaceRestablecer(User $usuario, string $token): string
    {
        return config('app.frontend_url').'/restablecer?'.http_build_query([
            'token' => $token,
            'email' => $usuario->email,
        ]);
    }
}
