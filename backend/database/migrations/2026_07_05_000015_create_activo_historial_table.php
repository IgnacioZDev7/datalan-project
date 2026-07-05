<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activo_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained('activos')->cascadeOnDelete();
            $table->string('estado_anterior', 20)->nullable();
            $table->string('estado_nuevo', 20)->nullable();
            $table->string('situacion_anterior', 20)->nullable();
            $table->string('situacion_nueva', 20)->nullable();
            $table->string('motivo', 255)->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete()
                ->comment('quien realizo el cambio');
            $table->timestamp('created_at')->nullable()->useCurrent()->comment('fecha del cambio');
            $table->comment('Linea de tiempo funcional del activo (complementa al Activity Log).');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activo_historial');
    }
};
