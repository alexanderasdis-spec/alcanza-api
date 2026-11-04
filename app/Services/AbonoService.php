<?php

namespace App\Services;

use App\Models\Abono;
use App\Models\Meta;
use App\Repositories\Contracts\AbonoRepositoryInterface;

/**
 * Lógica de los abonos. Al registrar un abono se revisa si la meta llegó al 100 %:
 * en ese caso pasa a "cumplida" y el frontend lanza el confeti.
 */
class AbonoService
{
    public function __construct(
        private AbonoRepositoryInterface $abonoRepository,
        private MetaService $metaService
    ) {}

    /**
     * Registra un abono y actualiza el estado de la meta.
     *
     * @param  array{monto: float, fecha?: string}  $datos  Datos ya validados por AbonoStoreRequest.
     * @return array{abono: Abono, meta: Meta}
     */
    public function registrar(Meta $meta, array $datos): array
    {
        // Si no se envía fecha, el abono queda con la fecha de hoy.
        $datos['fecha'] ??= now()->toDateString();

        $abono = $this->abonoRepository->create($meta, $datos);
        $meta = $this->metaService->revisarEstado($meta);

        return ['abono' => $abono, 'meta' => $this->metaService->mostrar($meta)];
    }
}
