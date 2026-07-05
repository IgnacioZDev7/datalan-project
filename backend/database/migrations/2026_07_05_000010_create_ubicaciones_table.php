<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('almacen_id')->constrained('almacenes')->cascadeOnDelete();
            $table->string('codigo', 60)->comment('estante / seccion / posicion');
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
            $table->unique(['almacen_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones');
    }
};
