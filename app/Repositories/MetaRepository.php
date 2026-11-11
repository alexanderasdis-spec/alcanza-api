<?php

namespace App\Repositories;

use App\Models\Meta;
use App\Models\Perfil;
use App\Repositories\Contracts\MetaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Acceso a la tabla metas con Eloquent.
 */
class MetaRepository implements MetaRepositoryInterface
{
    /**
     * Columnas de los abonos que necesita el frontend (lista y gráfico de ahorro).
     * meta_id hace falta para que Eloquent reparta cada abono en su meta; luego se oculta.
     */
    private const COLUMNAS_ABONO = ['id', 'meta_id', 'monto', 'fecha'];

    public function dePerfil(Perfil $perfil): Collection
    {
        // with('abonos') trae los abonos de todas las metas en una sola consulta extra (para el gráfico
        // de ahorro mes a mes) y withSum agrega "abonos_sum_monto" sin consultar cada meta por separado.
        // Solo se piden las columnas que se usan: la prueba de carga mostró que la lista se volvía lenta
        // cuando había muchos abonos con todas sus columnas.
        $metas = Meta::query()
            ->where('perfil_id', $perfil->id)
            ->with(['abonos' => fn ($consulta) => $consulta->select(self::COLUMNAS_ABONO)])
            ->withSum('abonos', 'monto')
            ->latest('id')
            ->get();

        $metas->each(fn (Meta $meta) => $meta->abonos->makeHidden('meta_id'));

        return $metas;
    }

    public function conAbonos(Meta $meta): Meta
    {
        $meta->load(['abonos' => fn ($consulta) => $consulta->select(self::COLUMNAS_ABONO)])->loadSum('abonos', 'monto');
        $meta->abonos->makeHidden('meta_id');

        return $meta;
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
