<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla perfiles: el "perfil rápido sin registro" de Alcanza.
     */
    public function up(): void
    {
        Schema::create('perfiles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 40);
            // Ahorro mensual en pesos colombianos.
            $table->decimal('ahorro_mensual', 14, 2);
            $table->timestamps();
        });
    }

    /**
     * Elimina la tabla perfiles.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfiles');
    }
};
