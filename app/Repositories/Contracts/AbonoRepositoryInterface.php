<?php

namespace App\Repositories\Contracts;

use App\Models\Abono;
use App\Models\Meta;

/**
 * Contrato de acceso a datos de los abonos.
 */
interface AbonoRepositoryInterface
{
    // registra un abono en una meta
    public function create(Meta $meta, array $datos): Abono;
}
