<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('existencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('almacen_id')->constrained('almacenes')->cascadeOnDelete();
            $table->decimal('cantidad_actual', 12, 2)->default(0)
                ->comment('CACHE derivado de movimientos, no editable a mano');
            $table->decimal('cantidad_minima', 12, 2)->default(0)->comment('punto de reorden por almacen');
            $table->decimal('cantidad_maxima', 12, 2)->nullable()->comment('tope por almacen');
            $table->timestamps();
            $table->unique(['producto_id', 'almacen_id']);
            $table->comment('Solo productos consumibles. cantidad_actual se recalcula por observer.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('existencias');
    }
};
