<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direcciones', function (Blueprint $table) {
            $table->id();
            $table->morphs('direccionable'); // direccionable_type + direccionable_id + indice
            $table->string('ciudad', 80)->nullable();
            $table->string('zona', 100)->nullable();
            $table->string('calle', 150)->nullable();
            $table->string('nro', 20)->nullable();
            $table->string('referencia', 255)->nullable();
            $table->timestamps();
            $table->comment('Relacion polimorfica: Usuario, Almacen, Empresa, Proveedor, Proyecto.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direcciones');
    }
};
