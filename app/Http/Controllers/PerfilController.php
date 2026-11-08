<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerfilFotoRequest;
use App\Http\Requests\PerfilRequest;
use App\Models\Perfil;
use App\Services\PerfilService;
use Illuminate\Http\Request;

/**
 * Endpoints del perfil del usuario que inició sesión: GET y POST /api/perfil,
 * y POST y DELETE /api/perfil/foto.
 */
class PerfilController extends Controller
{
    public function __construct(
        private PerfilService $perfilService
    ) {}

    /**
     * Consulta el perfil - METHOD GET
     * Responde 404 si el usuario todavía no lo ha creado (el frontend lo manda a la bienvenida).
     */
    public function show(Request $request)
    {
        $perfil = $this->perfilService->obtener($request->user());

        if ($perfil === null) {
            return response()->json(['message' => 'Todavía no has creado tu perfil.'], 404);
        }

        return response()->json($perfil, 200);
    }

    /**
     * Crea o actualiza el perfil - METHOD POST
     * 201 si lo creó, 200 si lo actualizó.
     */
    public function store(PerfilRequest $request)
    {
        [$perfil, $creado] = $this->perfilService->guardar($request->user(), $request->validated());

        return response()->json($perfil, $creado ? 201 : 200);
    }

    /**
     * Sube o reemplaza la foto de perfil - METHOD POST (multipart/form-data)
     */
    public function subirFoto(PerfilFotoRequest $request)
    {
        $perfil = $this->perfilService->cambiarFoto($request->user(), $request->file('foto'));

        return $this->respuestaFoto($perfil);
    }

    /**
     * Quita la foto de perfil - METHOD DELETE
     */
    public function eliminarFoto(Request $request)
    {
        return $this->respuestaFoto($this->perfilService->quitarFoto($request->user()));
    }

    /**
     * Responde con el perfil, o 404 si el usuario todavía no lo ha creado.
     */
    private function respuestaFoto(?Perfil $perfil)
    {
        if ($perfil === null) {
            return response()->json(['message' => 'Todavía no has creado tu perfil.'], 404);
        }

        return response()->json($perfil, 200);
    }
}
