<?php

namespace App\Repositories\Contracts;

use App\Models\Perfil;
use App\Models\User;

/**
 * Contrato de acceso a datos del perfil. Cada usuario tiene a lo sumo un perfil.
 */
interface PerfilRepositoryInterface
{
    // devuelve el perfil del usuario, o null si todavía no lo ha creado
    public function deUsuario(User $usuario): ?Perfil;

    // crea un perfil nuevo
    public function create(array $datos): Perfil;

    // actualiza un perfil existente
    public function update(Perfil $perfil, array $datos): Perfil;
}
