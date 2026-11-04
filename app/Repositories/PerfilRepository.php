<?php

namespace App\Repositories;

use App\Models\Perfil;
use App\Repositories\Contracts\PerfilRepositoryInterface;

/**
 * Acceso a la tabla perfiles con Eloquent.
 */
class PerfilRepository implements PerfilRepositoryInterface
{
    public function actual(): ?Perfil
    {
        return Perfil::query()->oldest('id')->first();
    }

    public function create(array $datos): Perfil
    {
        return Perfil::create($datos);
    }

    public function update(Perfil $perfil, array $datos): Perfil
    {
        $perfil->update($datos);

        return $perfil;
    }
}
