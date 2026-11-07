<?php

namespace App\Repositories\Contracts;

use App\Models\User;

/**
 * Contrato de acceso a datos de los usuarios.
 */
interface UsuarioRepositoryInterface
{
    // crea un usuario nuevo (la contraseña se cifra sola por el cast "hashed" del modelo)
    public function create(array $datos): User;

    // busca un usuario por su correo, o null si no existe
    public function porEmail(string $email): ?User;
}
