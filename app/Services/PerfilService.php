<?php

namespace App\Services;

use App\Models\Perfil;
use App\Repositories\Contracts\PerfilRepositoryInterface;

/**
 * Lógica del perfil rápido. Alcanza no tiene registro, así que hay un solo perfil:
 * guardar crea el perfil la primera vez y lo actualiza las siguientes.
 */
class PerfilService
{
    public function __construct(
        private PerfilRepositoryInterface $perfilRepository
    ) {}

    /**
     * Devuelve el perfil actual o null si todavía no existe.
     */
    public function obtener(): ?Perfil
    {
        return $this->perfilRepository->actual();
    }

    /**
     * Crea o actualiza el perfil.
     *
     * @param  array{nombre: string, ahorro_mensual: float}  $datos
     * @return array{0: Perfil, 1: bool} El perfil y si fue creado (true) o actualizado (false).
     */
    public function guardar(array $datos): array
    {
        $perfil = $this->perfilRepository->actual();

        if ($perfil === null) {
            return [$this->perfilRepository->create($datos), true];
        }

        return [$this->perfilRepository->update($perfil, $datos), false];
    }
}
