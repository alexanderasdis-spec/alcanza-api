<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Abono: un aporte de dinero a una meta, en la misma moneda de la meta.
 *
 * @property int $id
 * @property int $meta_id
 * @property float $monto
 */
class Abono extends Model
{
    protected $table = 'abonos';

    protected $fillable = [
        'meta_id',
        'monto',
        'fecha',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monto' => 'float',
            'fecha' => 'date:Y-m-d',
        ];
    }

    /**
     * Meta a la que pertenece el abono.
     */
    public function meta(): BelongsTo
    {
        return $this->belongsTo(Meta::class);
    }
}
