<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Perfil rápido del usuario: su nombre y cuánto ahorra al mes (en COP).
 *
 * @property int $id
 * @property string $nombre
 * @property float $ahorro_mensual
 */
class Perfil extends Model
{
    // Laravel adivinaría "perfils"; se indica el nombre real de la tabla.
    protected $table = 'perfiles';

    protected $fillable = [
        'nombre',
        'ahorro_mensual',
    ];

    /**
     * Convierte los montos a número para que la API los entregue como número y no como texto.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ahorro_mensual' => 'float',
        ];
    }

    /**
     * Metas de ahorro del perfil.
     */
    public function metas(): HasMany
    {
        return $this->hasMany(Meta::class);
    }
}
