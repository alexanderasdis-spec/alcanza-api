<?php

namespace App\Providers;

use App\Repositories\AbonoRepository;
use App\Repositories\Contracts\AbonoRepositoryInterface;
use App\Repositories\Contracts\MetaRepositoryInterface;
use App\Repositories\Contracts\PerfilRepositoryInterface;
use App\Repositories\MetaRepository;
use App\Repositories\PerfilRepository;
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
        $this->app->bind(PerfilRepositoryInterface::class, PerfilRepository::class);
        $this->app->bind(MetaRepositoryInterface::class, MetaRepository::class);
        $this->app->bind(AbonoRepositoryInterface::class, AbonoRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
