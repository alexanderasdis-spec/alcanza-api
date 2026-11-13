<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Perfil de un usuario: el nombre con el que la app lo saluda, cuánto ahorra al mes (en COP)
 * y su foto, guardada dentro de la base de datos.
 *
 * @property int $id
 * @property int $user_id
 * @property string $nombre
 * @property float $ahorro_mensual
 * @property string|null $foto_datos La imagen (bytes).
 * @property string|null $foto_tipo Su tipo, por ejemplo image/jpeg.
 * @property string|null $foto_clave Código al azar de la dirección pública de la foto.
 * @property-read string|null $foto_url
 */
class Perfil extends Model
{
    // Laravel adivinaría "perfils"; se indica el nombre real de la tabla.
    protected $table = 'perfiles';

    protected $fillable = [
        'user_id',
        'nombre',
        'ahorro_mensual',
        'foto_datos',
        'foto_tipo',
        'foto_clave',
    ];

    /** La imagen nunca va dentro del JSON (son bytes): la API entrega su dirección en foto_url. */
    protected $hidden = ['foto_datos', 'foto_tipo', 'foto_clave'];

    protected $appends = ['foto_url'];

    /**
     * Dirección pública de la foto (GET /api/fotos/{clave}), o null si el usuario no ha subido una.
     */
    protected function fotoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->foto_clave ? url('/api/fotos/'.$this->foto_clave) : null);
    }

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
     * Usuario dueño del perfil.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Metas de ahorro del perfil.
     */
    public function metas(): HasMany
    {
        return $this->hasMany(Meta::class);
    }
}
