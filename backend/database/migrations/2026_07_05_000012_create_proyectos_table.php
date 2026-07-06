<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 60)->unique();
            $table->string('nombre', 180);
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('tecnico_id')->nullable()->constrained('tecnicos')->nullOnDelete()
                ->comment('responsable principal');
            $table->foreignId('direccion_id')->nullable()->constrained('direcciones')->nullOnDelete()
                ->comment('direccion de la instalacion');
            $table->string('estado', 20)->default('planificado')
                ->comment('planificado|en_progreso|finalizado|cancelado');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->comment('La direccion de la instalacion vive en la tabla direcciones.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
