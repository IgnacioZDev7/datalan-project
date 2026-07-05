<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tecnicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('ci', 30)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('cargo', 80)->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete()
                ->comment('opcional: si el tecnico ademas usa el sistema');
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->comment('Persona a la que se entrega material / se asignan herramientas.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tecnicos');
    }
};
