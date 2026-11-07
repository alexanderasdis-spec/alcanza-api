<?php

namespace App\Policies;

use App\Models\Meta;
use App\Models\User;

/**
 * Reglas de permisos de las metas: cada usuario solo puede ver, cambiar,
 * abonar o eliminar las metas de su propio perfil.
 *
 * Laravel la encuentra sola por el nombre (Meta → MetaPolicy) y los controladores
 * la usan con Gate::authorize(). Si la regla no se cumple, la API responde 403.
 */
class MetaPolicy
{
    /**
     * Indica si la meta pertenece al usuario.
     */
    private function esDueno(User $usuario, Meta $meta): bool
    {
        return $meta->perfil()->value('user_id') === $usuario->id;
    }

    /**
     * Ver una meta con sus abonos.
     */
    public function view(User $usuario, Meta $meta): bool
    {
        return $this->esDueno($usuario, $meta);
    }

    /**
     * Cambiar una meta o registrarle abonos.
     */
    public function update(User $usuario, Meta $meta): bool
    {
        return $this->esDueno($usuario, $meta);
    }

    /**
     * Eliminar una meta.
     */
    public function delete(User $usuario, Meta $meta): bool
    {
        return $this->esDueno($usuario, $meta);
    }
}
