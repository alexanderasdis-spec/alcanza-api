<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Meta de ahorro: un antojo que el usuario quiere comprar.
 *
 * Además de sus columnas, la API entrega tres valores calculados:
 * - precio_final: el precio con el descuento aplicado.
 * - ahorrado: la suma de todos los abonos.
 * - progreso: porcentaje de avance (0 a 100).
 *
 * @property int $id
 * @property int $perfil_id
 * @property float $precio
 * @property int $descuento
 * @property string $estado
 */
class Meta extends Model
{
    /** Estado de una meta que todavía no se completa. */
    public const EN_PROGRESO = 'en_progreso';

    /** Estado de una meta cuyos abonos ya cubren el precio final. */
    public const CUMPLIDA = 'cumplida';

    protected $table = 'metas';

    protected $fillable = [
        'perfil_id',
        'producto_id',
        'nombre_producto',
        'imagen',
        'precio',
        'moneda',
        'descuento',
        'fecha_estimada',
        'estado',
    ];

    /** Valores calculados que se agregan siempre a la respuesta JSON. */
    protected $appends = ['precio_final', 'ahorrado', 'progreso'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio' => 'float',
            'descuento' => 'integer',
            'fecha_estimada' => 'date:Y-m-d',
        ];
    }

    /**
     * Perfil dueño de la meta.
     */
    public function perfil(): BelongsTo
    {
        return $this->belongsTo(Perfil::class);
    }

    /**
     * Abonos de la meta, del más reciente al más antiguo.
     */
    public function abonos(): HasMany
    {
        return $this->hasMany(Abono::class)->latest('fecha')->latest('id');
    }

    /**
     * Precio con el descuento aplicado, redondeado a dos decimales.
     */
    protected function precioFinal(): Attribute
    {
        return Attribute::get(fn () => round($this->precio * (1 - $this->descuento / 100), 2));
    }

    /**
     * Total abonado. Usa la suma que trae el repositorio (withSum) si está disponible,
     * para no hacer una consulta extra por cada meta.
     */
    protected function ahorrado(): Attribute
    {
        return Attribute::get(fn () => round((float) ($this->abonos_sum_monto ?? $this->abonos()->sum('monto')), 2));
    }

    /**
     * Porcentaje de avance de la meta, de 0 a 100.
     */
    protected function progreso(): Attribute
    {
        return Attribute::get(function () {
            if ($this->precio_final <= 0) {
                return 100;
            }

            return min(100, round($this->ahorrado / $this->precio_final * 100, 1));
        });
    }
}
