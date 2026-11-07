<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UsuarioRepositoryInterface;

/**
 * Acceso a la tabla users con Eloquent.
 */
class UsuarioRepository implements UsuarioRepositoryInterface
{
    public function create(array $datos): User
    {
        return User::create($datos);
    }

    public function porEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }
}
