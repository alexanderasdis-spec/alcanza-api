<?php

namespace App\Services;

use App\Models\Meta;
use App\Models\User;
use App\Repositories\Contracts\MetaRepositoryInterface;
use App\Repositories\Contracts\PerfilRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Lógica de las metas de ahorro. Cada usuario solo ve y maneja las metas de su perfil.
 */
class MetaService
{
    public function __construct(
        private MetaRepositoryInterface $metaRepository,
        private PerfilRepositoryInterface $perfilRepository
    ) {}

    /**
     * Las metas del usuario, con su total ahorrado y su progreso.
     * Si todavía no tiene perfil, no tiene metas.
     */
    public function listar(User $usuario): Collection
    {
        $perfil = $this->perfilRepository->deUsuario($usuario);

        return $perfil ? $this->metaRepository->dePerfil($perfil) : new Collection;
    }

    /**
     * Una meta con sus abonos.
     */
    public function mostrar(Meta $meta): Meta
    {
        return $this->metaRepository->conAbonos($meta);
    }

    /**
     * Crea una meta en el perfil del usuario. Toda meta nueva empieza "en progreso".
     *
     * @param  array<string, mixed>  $datos  Datos ya validados por MetaStoreRequest.
     *
     * @throws ValidationException Si el usuario todavía no tiene perfil (respuesta 422).
     */
    public function crear(User $usuario, array $datos): Meta
    {
        $perfil = $this->perfilRepository->deUsuario($usuario);

        if ($perfil === null) {
            throw ValidationException::withMessages(['perfil' => 'Primero crea tu perfil.']);
        }

        $datos['perfil_id'] = $perfil->id;
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
