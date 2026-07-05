<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_fisico_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_fisico_id')->constrained('inventarios_fisicos')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->foreignId('activo_id')->nullable()->constrained('activos')->nullOnDelete()
                ->comment('si es un item por serie');
            $table->foreignId('carrete_id')->nullable()->constrained('carretes')->nullOnDelete()
                ->comment('si es un carrete; cantidades en METROS');
            $table->decimal('cantidad_sistema', 12, 2)->default(0);
            $table->decimal('cantidad_fisica', 12, 2)->default(0);
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();
            $table->comment('diferencia = cantidad_fisica - cantidad_sistema (derivable, no se almacena).');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_fisico_detalles');
    }
};
