<?php

namespace App\Services;

use App\Models\Perfil;
use App\Models\User;
use App\Repositories\Contracts\PerfilRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
     * Guarda una foto nueva y borra la anterior para no acumular archivos.
     *
     * @return Perfil|null null si el usuario todavía no tiene perfil.
     */
    public function cambiarFoto(User $usuario, UploadedFile $archivo): ?Perfil
    {
        $perfil = $this->perfilRepository->deUsuario($usuario);

        if ($perfil === null) {
            return null;
        }

        $anterior = $perfil->foto;
        $perfil = $this->perfilRepository->update($perfil, ['foto' => $archivo->store('fotos', 'public')]);
        $this->borrarArchivo($anterior);

        return $perfil;
    }

    /**
     * Quita la foto del perfil y borra el archivo.
     *
     * @return Perfil|null null si el usuario todavía no tiene perfil.
     */
    public function quitarFoto(User $usuario): ?Perfil
    {
        $perfil = $this->perfilRepository->deUsuario($usuario);

        if ($perfil === null) {
            return null;
        }

        $anterior = $perfil->foto;
        $perfil = $this->perfilRepository->update($perfil, ['foto' => null]);
        $this->borrarArchivo($anterior);

        return $perfil;
    }

    /**
     * Borra un archivo del disco "public", si hay alguno.
     */
    private function borrarArchivo(?string $ruta): void
    {
        if ($ruta !== null) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
