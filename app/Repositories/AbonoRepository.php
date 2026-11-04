<?php

namespace App\Repositories;

use App\Models\Abono;
use App\Models\Meta;
use App\Repositories\Contracts\AbonoRepositoryInterface;

/**
 * Acceso a la tabla abonos con Eloquent.
 */
class AbonoRepository implements AbonoRepositoryInterface
{
    public function create(Meta $meta, array $datos): Abono
    {
        // Crear desde la relación llena meta_id automáticamente.
        return $meta->abonos()->create($datos);
    }
}
