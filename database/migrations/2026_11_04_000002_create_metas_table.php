<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla metas: cada antojo que el usuario decidió convertir en meta de ahorro.
     */
    public function up(): void
    {
        Schema::create('metas', function (Blueprint $table) {
            $table->id();
            // Si se borra el perfil, se borran sus metas.
            $table->foreignId('perfil_id')->constrained('perfiles')->cascadeOnDelete();
            // Id del producto en el catálogo del frontend (por ejemplo "78" o "ent-2").
            $table->string('producto_id', 40);
            $table->string('nombre_producto');
            $table->string('imagen')->nullable();
            // Precio antes del descuento, en la moneda indicada.
            $table->decimal('precio', 14, 2);
            $table->char('moneda', 3)->default('COP');
            // Porcentaje de descuento (0 a 100).
            $table->unsignedTinyInteger('descuento')->default(0);
            $table->date('fecha_estimada')->nullable();
            $table->enum('estado', ['en_progreso', 'cumplida'])->default('en_progreso');
            $table->timestamps();
        });
    }

    /**
     * Elimina la tabla metas.
     */
    public function down(): void
    {
        Schema::dropIfExists('metas');
    }
};
