<?php

namespace App\Services;

use App\Models\Meta;
use App\Repositories\Contracts\MetaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Lógica de las metas de ahorro.
 */
class MetaService
{
    public function __construct(
        private MetaRepositoryInterface $metaRepository
    ) {}

    /**
     * Todas las metas, con su total ahorrado y su progreso.
     */
    public function listar(): Collection
    {
        return $this->metaRepository->all();
    }

    /**
     * Una meta con sus abonos.
     */
    public function mostrar(Meta $meta): Meta
    {
        return $this->metaRepository->conAbonos($meta);
    }

    /**
     * Crea una meta. Toda meta nueva empieza "en progreso".
     *
     * @param  array<string, mixed>  $datos  Datos ya validados por MetaStoreRequest.
     */
    public function crear(array $datos): Meta
    {
        $datos['estado'] = Meta::EN_PROGRESO;

        return $this->metaRepository->conAbonos($this->metaRepository->create($datos));
    }

    /**
     * Actualiza una meta y vuelve a revisar si quedó cumplida
     * (por ejemplo, si bajó el precio o subió el descuento).
     *
     * @param  array<string, mixed>  $datos  Datos ya validados por MetaUpdateRequest.
     */
    public function actualizar(Meta $meta, array $datos): Meta
    {
        $meta = $this->metaRepository->update($meta, $datos);
        $this->revisarEstado($meta);

        return $this->metaRepository->conAbonos($meta);
    }

    /**
     * Elimina una meta junto con sus abonos.
     */
    public function eliminar(Meta $meta): bool
    {
        return $this->metaRepository->delete($meta);
    }

    /**
     * Marca la meta como cumplida si lo ahorrado alcanza el precio final, o la devuelve
     * a "en progreso" si ya no lo alcanza.
     */
    public function revisarEstado(Meta $meta): Meta
    {
        $meta->loadSum('abonos', 'monto');
        $estado = $meta->ahorrado >= $meta->precio_final ? Meta::CUMPLIDA : Meta::EN_PROGRESO;

        if ($meta->estado !== $estado) {
            $meta = $this->metaRepository->update($meta, ['estado' => $estado]);
        }

        return $meta;
    }
}
