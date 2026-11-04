<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla abonos: cada aporte de dinero que el usuario hace a una meta.
     */
    public function up(): void
    {
        Schema::create('abonos', function (Blueprint $table) {
            $table->id();
            // Si se borra la meta, se borran sus abonos.
            $table->foreignId('meta_id')->constrained('metas')->cascadeOnDelete();
            // Monto en la misma moneda de la meta.
            $table->decimal('monto', 14, 2);
            $table->date('fecha');
            $table->timestamps();
        });
    }

    /**
     * Elimina la tabla abonos.
     */
    public function down(): void
    {
        Schema::dropIfExists('abonos');
    }
};
