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
        // Límite de intentos de las rutas de cuentas: 10 por minuto, contados por dirección IP
        // y por ruta (los intentos de login no gastan los del registro).
        RateLimiter::for('cuentas', fn (Request $request) => Limit::perMinute(10)->by($request->ip().'|'.$request->path()));

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
