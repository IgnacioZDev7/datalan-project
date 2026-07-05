<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carretes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->string('codigo', 60)->unique();
            $table->unsignedSmallInteger('nro_hilos')->nullable()->comment('1, 2, 4...');
            $table->string('tipo_fibra', 2)->nullable()->comment('MM|SM');
            $table->decimal('metraje_inicial', 10, 2);
            $table->decimal('metraje_disponible', 10, 2)->comment('saldo actual; disminuye con cada salida');
            $table->string('estado', 20)->default('en_almacen')
                ->comment('en_almacen|instalado|en_espera|inexistente');
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->foreignId('carrete_padre_id')->nullable()->constrained('carretes')->nullOnDelete()
                ->comment('bobina de la que se corto este tramo (genealogia)');
            $table->date('fecha_ingreso')->nullable();
            $table->string('observaciones', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carretes');
    }
};
