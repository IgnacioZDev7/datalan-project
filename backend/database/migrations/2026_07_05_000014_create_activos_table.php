<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->string('codigo_interno', 60)->unique()->comment('etiqueta QR / codigo digitalizado');
            $table->string('nro_serie', 120)->nullable()->unique();
            $table->string('mac', 30)->nullable()->unique();
            $table->string('estado', 20)->default('bueno')
                ->comment('condicion FISICA: nuevo|bueno|regular|danado|en_reparacion|baja');
            $table->string('situacion', 20)->default('en_almacen')
                ->comment('ciclo de vida: en_almacen|asignado|instalado|de_baja');
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullOnDelete()
                ->comment('el almacen se obtiene via ubicacion');
            $table->foreignId('tecnico_id')->nullable()->constrained('tecnicos')->nullOnDelete()
                ->comment('custodia actual; obligatorio si situacion=asignado');
            $table->foreignId('activo_padre_id')->nullable()->constrained('activos')->nullOnDelete()
                ->comment('maletin/kit contenedor; prohibir ciclos en la app');
            $table->date('fecha_fabricacion')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->json('credenciales')->nullable()->comment('CIFRADO en la app: {usuario,password,pin,notas}');
            $table->string('observaciones', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('estado');
            $table->index('situacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activos');
    }
};
