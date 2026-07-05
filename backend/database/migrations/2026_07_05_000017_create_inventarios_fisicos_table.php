<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventarios_fisicos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 60)->unique();
            $table->foreignId('almacen_id')->constrained('almacenes')->restrictOnDelete();
            $table->date('fecha');
            $table->foreignId('responsable_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('estado', 20)->default('en_proceso')->comment('en_proceso|cerrado|anulado');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->comment('Conteo fisico del almacen. Diferencias se ajustan con un movimiento tipo ajuste.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventarios_fisicos');
    }
};
