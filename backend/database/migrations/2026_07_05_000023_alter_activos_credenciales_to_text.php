<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corrige activos.credenciales: de json -> text.
 * El cast encrypted:array almacena un string cifrado (no JSON valido), por lo que
 * la columna json rechazaba el insert (SQLSTATE 22032). text es el tipo correcto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activos', function (Blueprint $table) {
            $table->text('credenciales')->nullable()->comment('CIFRADO en la app: {usuario,password,pin,notas}')->change();
        });
    }

    public function down(): void
    {
        Schema::table('activos', function (Blueprint $table) {
            $table->json('credenciales')->nullable()->change();
        });
    }
};
