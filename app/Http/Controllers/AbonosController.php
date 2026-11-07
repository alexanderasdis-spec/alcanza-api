<?php

namespace App\Http\Controllers;

use App\Http\Requests\AbonoStoreRequest;
use App\Models\Meta;
use App\Services\AbonoService;
use Illuminate\Support\Facades\Gate;

/**
 * Registro de abonos: POST /api/metas/{meta}/abonos.
 */
class AbonosController extends Controller
{
    public function __construct(
        private AbonoService $abonoService
    ) {}

    /**
     * Registra un abono en una meta - METHOD POST
     * Responde el abono creado y la meta actualizada (con su nuevo progreso y estado),
     * para que el frontend sepa si debe celebrar con confeti.
     */
    public function store(AbonoStoreRequest $request, Meta $meta)
    {
        // Solo el dueño de la meta puede abonarle (MetaPolicy → 403 si no).
        Gate::authorize('update', $meta);
        $resultado = $this->abonoService->registrar($meta, $request->validated());

        return response()->json($resultado, 201);
    }
}
