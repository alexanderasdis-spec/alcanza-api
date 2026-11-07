<?php

namespace App\Services;

use App\Models\Perfil;
use App\Models\User;
use App\Repositories\Contracts\PerfilRepositoryInterface;

/**
 * Lógica del perfil. Cada usuario tiene un solo perfil:
 * guardar lo crea la primera vez y lo actualiza las siguientes.
 */
class PerfilService
{
    public function __construct(
        private PerfilRepositoryInterface $perfilRepository
    ) {}

    /**
     * Devuelve el perfil del usuario o null si todavía no existe.
     */
    public function obtener(User $usuario): ?Perfil
    {
        return $this->perfilRepository->deUsuario($usuario);
    }

    /**
     * Crea o actualiza el perfil del usuario.
     *
     * @param  array{nombre: string, ahorro_mensual: float}  $datos
     * @return array{0: Perfil, 1: bool} El perfil y si fue creado (true) o actualizado (false).
     */
    public function guardar(User $usuario, array $datos): array
    {
        $perfil = $this->perfilRepository->deUsuario($usuario);

        if ($perfil === null) {
            return [$this->perfilRepository->create([...$datos, 'user_id' => $usuario->id]), true];
        }

        return [$this->perfilRepository->update($perfil, $datos), false];
    }
}
