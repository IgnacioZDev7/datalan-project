<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 60)->comment('stock_minimo|mantenimiento|devolucion_pendiente|password_generica');
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->foreignId('activo_id')->nullable()->constrained('activos')->nullOnDelete();
            $table->string('mensaje', 255);
            $table->string('nivel', 20)->default('advertencia')->comment('info|advertencia|critico');
            $table->boolean('leida')->default(false);
            $table->timestamps();
            $table->index('leida');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
