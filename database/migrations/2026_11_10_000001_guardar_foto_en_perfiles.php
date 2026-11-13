<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La foto de perfil pasa a guardarse dentro de la base de datos, no como archivo.
 *
 * En el servidor gratuito (Render) los archivos se borran en cada reinicio; en la base de datos
 * la foto se conserva. Como el navegador la recorta a 320 × 320, pesa unos 20 KB.
 *
 * - foto_datos: la imagen (MEDIUMBLOB, hasta 16 MB; la validación permite 2 MB).
 * - foto_tipo: su tipo (image/jpeg, image/png o image/webp).
 * - foto_clave: código al azar para la dirección pública; cambia con cada foto nueva,
 *   así nadie adivina las fotos de otros por número y el navegador no muestra una vieja.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perfiles', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
        // Laravel no tiene un tipo para MEDIUMBLOB: se agrega con SQL.
        DB::statement('ALTER TABLE perfiles ADD foto_datos MEDIUMBLOB NULL AFTER ahorro_mensual');
        Schema::table('perfiles', function (Blueprint $table) {
            $table->string('foto_tipo', 30)->nullable()->after('foto_datos');
            $table->string('foto_clave', 36)->nullable()->unique()->after('foto_tipo');
        });
    }

    public function down(): void
    {
        Schema::table('perfiles', function (Blueprint $table) {
            $table->dropUnique(['foto_clave']);
            $table->dropColumn(['foto_datos', 'foto_tipo', 'foto_clave']);
            $table->string('foto')->nullable()->after('ahorro_mensual');
        });
    }
};
