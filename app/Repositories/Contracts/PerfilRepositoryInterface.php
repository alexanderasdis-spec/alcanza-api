<?php

namespace App\Repositories\Contracts;

use App\Models\Perfil;

/**
 * Contrato de acceso a datos del perfil.
 * Alcanza no tiene registro: se trabaja con un único perfil, el primero de la tabla.
 */
interface PerfilRepositoryInterface
{
    // devuelve el perfil actual, o null si todavía no se ha creado
    public function actual(): ?Perfil;

    // crea un perfil nuevo
    public function create(array $datos): Perfil;

    // actualiza un perfil existente
    public function update(Perfil $perfil, array $datos): Perfil;
}
