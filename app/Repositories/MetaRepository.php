<?php

namespace App\Repositories;

use App\Models\Meta;
use App\Repositories\Contracts\MetaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Acceso a la tabla metas con Eloquent.
 */
class MetaRepository implements MetaRepositoryInterface
{
    public function all(): Collection
    {
        // withSum agrega "abonos_sum_monto" en la misma consulta: así no se consulta cada meta por separado.
        return Meta::query()->withSum('abonos', 'monto')->latest('id')->get();
    }

    public function conAbonos(Meta $meta): Meta
    {
        return $meta->load('abonos')->loadSum('abonos', 'monto');
    }

    public function create(array $datos): Meta
    {
        return Meta::create($datos);
    }

    public function update(Meta $meta, array $datos): Meta
    {
        $meta->update($datos);

        return $meta;
    }

    public function delete(Meta $meta): bool
    {
        return $meta->delete();
    }
}
