<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerfilRequest;
use App\Services\PerfilService;

/**
 * Endpoints del perfil rápido: GET y POST /api/perfil.
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
    public function show()
    {
        $perfil = $this->perfilService->obtener();

        if ($perfil === null) {
            return response()->json(['message' => 'Todavía no hay un perfil creado.'], 404);
        }

        return response()->json($perfil, 200);
    }

    /**
     * Crea o actualiza el perfil - METHOD POST
     * 201 si lo creó, 200 si lo actualizó.
     */
    public function store(PerfilRequest $request)
    {
        [$perfil, $creado] = $this->perfilService->guardar($request->validated());

        return response()->json($perfil, $creado ? 201 : 200);
    }
}
