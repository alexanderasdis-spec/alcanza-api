<?php

namespace App\Repositories;

use App\Models\Perfil;
use App\Models\User;
use App\Repositories\Contracts\PerfilRepositoryInterface;

/**
 * Acceso a la tabla perfiles con Eloquent.
 */
class PerfilRepository implements PerfilRepositoryInterface
{
    public function deUsuario(User $usuario): ?Perfil
    {
        return Perfil::query()->where('user_id', $usuario->id)->first();
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

    public function porClaveDeFoto(string $clave): ?Perfil
    {
        // Solo las columnas de la foto: no hace falta traer el resto del perfil.
        return Perfil::query()->select(['id', 'foto_datos', 'foto_tipo', 'foto_clave'])->where('foto_clave', $clave)->first();
    }
}
