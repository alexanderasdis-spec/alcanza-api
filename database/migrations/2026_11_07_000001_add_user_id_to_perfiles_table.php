<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Une cada perfil a un usuario: con el inicio de sesión, cada persona tiene su propio perfil.
     *
     * Los perfiles que existían eran datos de prueba sin dueño, así que se borran primero
     * (y con ellos sus metas y abonos, por las llaves foráneas en cascada).
     */
    public function up(): void
    {
        DB::table('perfiles')->delete();

        Schema::table('perfiles', function (Blueprint $table) {
            // unique: un usuario tiene un solo perfil. Si se borra el usuario, se borra su perfil.
            $table->foreignId('user_id')->after('id')->unique()->constrained('users')->cascadeOnDelete();
        });
    }

    /**
     * Quita la relación entre perfiles y usuarios.
     */
    public function down(): void
    {
        Schema::table('perfiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
