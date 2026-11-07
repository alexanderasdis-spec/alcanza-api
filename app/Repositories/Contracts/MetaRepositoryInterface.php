<?php

namespace App\Repositories\Contracts;

use App\Models\Meta;
use App\Models\Perfil;
use Illuminate\Database\Eloquent\Collection;

/**
 * Contrato de acceso a datos de las metas.
 */
interface MetaRepositoryInterface
{
    // las metas de un perfil, con el total abonado de cada una
    public function dePerfil(Perfil $perfil): Collection;

    // carga los abonos y el total abonado de una meta
    public function conAbonos(Meta $meta): Meta;

    // crea una nueva meta
    public function create(array $datos): Meta;

    // actualiza una meta existente
    public function update(Meta $meta, array $datos): Meta;

    // elimina una meta (y sus abonos, por la llave foránea en cascada)
    public function delete(Meta $meta): bool;
}
