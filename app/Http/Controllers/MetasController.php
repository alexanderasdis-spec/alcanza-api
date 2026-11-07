<?php

namespace App\Http\Controllers;

use App\Http\Requests\MetaStoreRequest;
use App\Http\Requests\MetaUpdateRequest;
use App\Models\Meta;
use App\Services\MetaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * CRUD de metas del usuario que inició sesión: /api/metas.
 * Se registra con Route::apiResource, que crea solo las rutas de una API
 * (sin los formularios create y edit del modelo vista controlador).
 */
class MetasController extends Controller
{
    public function __construct(
        private MetaService $metaService
    ) {}

    /**
     * Lista las metas del usuario - METHOD GET
     */
    public function index(Request $request)
    {
        return response()->json($this->metaService->listar($request->user()), 200);
    }

    /**
     * Crea una meta en el perfil del usuario - METHOD POST
     */
    public function store(MetaStoreRequest $request)
    {
        $meta = $this->metaService->crear($request->user(), $request->validated());

        return response()->json($meta, 201);
    }

    /**
     * Muestra una meta con sus abonos - METHOD GET
     * Si el id no existe, Laravel responde 404 (route model binding);
     * si existe pero es de otro usuario, MetaPolicy responde 403.
     */
    public function show(Meta $meta)
    {
        Gate::authorize('view', $meta);

        return response()->json($this->metaService->mostrar($meta), 200);
    }

    /**
     * Actualiza una meta - METHOD PUT/PATCH
     */
    public function update(MetaUpdateRequest $request, Meta $meta)
    {
        Gate::authorize('update', $meta);
        $meta = $this->metaService->actualizar($meta, $request->validated());

        return response()->json($meta, 200);
    }

    /**
     * Elimina una meta y sus abonos - METHOD DELETE
     */
    public function destroy(Meta $meta)
    {
        Gate::authorize('delete', $meta);
        $this->metaService->eliminar($meta);

        return response()->json(null, 204);
    }
}
