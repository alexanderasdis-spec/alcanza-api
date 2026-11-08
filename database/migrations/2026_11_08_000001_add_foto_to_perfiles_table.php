<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega a perfiles la ruta de la foto de perfil dentro del disco "public".
 * Es nula mientras el usuario no suba una.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perfiles', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('ahorro_mensual');
        });
    }

    public function down(): void
    {
        Schema::table('perfiles', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
