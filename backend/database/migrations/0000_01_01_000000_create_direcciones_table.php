<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de direcciones postales. Se crea al inicio para que las entidades
     * (usuarios, almacenes, empresas, proveedores, proyectos) la referencien
     * con una FK real (direccion_id).
     */
    public function up(): void
    {
        Schema::create('direcciones', function (Blueprint $table) {
            $table->id();
            $table->string('ciudad', 80)->nullable();
            $table->string('zona', 100)->nullable();
            $table->string('calle', 150)->nullable();
            $table->string('nro', 20)->nullable();
            $table->string('referencia', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direcciones');
    }
};
