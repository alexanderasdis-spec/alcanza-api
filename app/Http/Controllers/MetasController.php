<?php

namespace App\Http\Controllers;

use App\Http\Requests\MetaStoreRequest;
use App\Http\Requests\MetaUpdateRequest;
use App\Models\Meta;
use App\Services\MetaService;

/**
 * CRUD de metas: /api/metas.
 * Se registra con Route::apiResource, que crea solo las rutas de una API
 * (sin los formularios create y edit del modelo vista controlador).
 */
class MetasController extends Controller
{
    public function __construct(
        private MetaService $metaService
    ) {}

    /**
     * Lista las metas - METHOD GET
     */
    public function index()
    {
        return response()->json($this->metaService->listar(), 200);
    }

    /**
     * Crea una meta - METHOD POST
     */
    public function store(MetaStoreRequest $request)
    {
        $meta = $this->metaService->crear($request->validated());

        return response()->json($meta, 201);
    }

    /**
     * Muestra una meta con sus abonos - METHOD GET
     * Si el id no existe, Laravel responde 404 automáticamente (route model binding).
     */
    public function show(Meta $meta)
    {
        return response()->json($this->metaService->mostrar($meta), 200);
    }

    /**
     * Actualiza una meta - METHOD PUT/PATCH
     */
    public function update(MetaUpdateRequest $request, Meta $meta)
    {
        $meta = $this->metaService->actualizar($meta, $request->validated());

        return response()->json($meta, 200);
    }

    /**
     * Elimina una meta y sus abonos - METHOD DELETE
     */
    public function destroy(Meta $meta)
    {
        $this->metaService->eliminar($meta);

        return response()->json(null, 204);
    }
}
