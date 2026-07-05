<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimiento_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movimiento_id')->constrained('movimientos')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->foreignId('activo_id')->nullable()->constrained('activos')->nullOnDelete()
                ->comment('si el producto es tipo activo/herramienta');
            $table->foreignId('carrete_id')->nullable()->constrained('carretes')->nullOnDelete()
                ->comment('si el producto es cable');
            $table->decimal('cantidad', 12, 2)->nullable()->comment('consumibles / unidades por serie');
            $table->decimal('metraje', 10, 2)->nullable()->comment('metros consumidos del carrete');
            $table->string('estado', 20)->nullable()->comment('estado del item en este movimiento');
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();
            $table->comment('Exactamente uno de {activo_id, carrete_id} segun tipo del producto (regla de app).');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento_detalles');
    }
};
