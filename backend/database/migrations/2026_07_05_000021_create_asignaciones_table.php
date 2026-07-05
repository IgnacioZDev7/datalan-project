<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained('activos')->cascadeOnDelete();
            $table->foreignId('tecnico_id')->constrained('tecnicos')->restrictOnDelete();
            $table->foreignId('proyecto_id')->nullable()->constrained('proyectos')->nullOnDelete();
            $table->dateTime('fecha_asignacion');
            $table->dateTime('fecha_devolucion')->nullable();
            $table->string('estado', 20)->default('asignado')->comment('asignado|devuelto|perdido|danado');
            $table->string('observaciones', 255)->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
            $table->comment('Historial de entrega/devolucion de herramientas a un tecnico.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};
