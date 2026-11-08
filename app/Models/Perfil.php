<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Perfil de un usuario: el nombre con el que la app lo saluda y cuánto ahorra al mes (en COP).
 *
 * @property int $id
 * @property int $user_id
 * @property string $nombre
 * @property float $ahorro_mensual
 * @property string|null $foto Ruta de la foto dentro del disco "public".
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
        'foto',
    ];

    /** La ruta interna no sale en el JSON: la API entrega la dirección completa en foto_url. */
    protected $hidden = ['foto'];

    protected $appends = ['foto_url'];

    /**
     * Dirección pública de la foto, o null si el usuario no ha subido una.
     */
    protected function fotoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->foto ? Storage::disk('public')->url($this->foto) : null);
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
