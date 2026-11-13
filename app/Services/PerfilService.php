<?php

namespace App\Services;

use App\Models\Perfil;
use App\Models\User;
use App\Repositories\Contracts\PerfilRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

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

    /**
     * Guarda una foto nueva dentro de la base de datos, reemplazando la anterior.
     * La clave nueva cambia la dirección, así el navegador no muestra la foto vieja de su caché.
     *
     * @return Perfil|null null si el usuario todavía no tiene perfil.
     */
    public function cambiarFoto(User $usuario, UploadedFile $archivo): ?Perfil
    {
        $perfil = $this->perfilRepository->deUsuario($usuario);

        if ($perfil === null) {
            return null;
        }

        return $this->perfilRepository->update($perfil, [
            'foto_datos' => $archivo->get(),
            'foto_tipo' => $archivo->getMimeType(),
            'foto_clave' => (string) Str::uuid(),
        ]);
    }

    /**
     * Quita la foto del perfil.
     *
     * @return Perfil|null null si el usuario todavía no tiene perfil.
     */
    public function quitarFoto(User $usuario): ?Perfil
    {
        $perfil = $this->perfilRepository->deUsuario($usuario);

        if ($perfil === null) {
            return null;
        }

        return $this->perfilRepository->update($perfil, ['foto_datos' => null, 'foto_tipo' => null, 'foto_clave' => null]);
    }

    /**
     * Busca la foto por su clave pública, para mostrarla.
     *
     * @return Perfil|null null si no hay ninguna foto con esa clave.
     */
    public function fotoPorClave(string $clave): ?Perfil
    {
        return $this->perfilRepository->porClaveDeFoto($clave);
    }
}
