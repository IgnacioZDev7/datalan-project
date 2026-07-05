<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 60)->unique();
            $table->string('tipo', 20)->comment('entrada|salida|devolucion|baja|traslado|ajuste');
            $table->dateTime('fecha');
            $table->foreignId('almacen_origen_id')->nullable()->constrained('almacenes')->nullOnDelete()
                ->comment('salidas, traslados, bajas');
            $table->foreignId('almacen_destino_id')->nullable()->constrained('almacenes')->nullOnDelete()
                ->comment('entradas, traslados');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete()
                ->comment('solo entradas');
            $table->foreignId('proyecto_id')->nullable()->constrained('proyectos')->nullOnDelete()
                ->comment('salidas hacia instalacion');
            $table->foreignId('tecnico_id')->nullable()->constrained('tecnicos')->nullOnDelete()
                ->comment('responsable que recibe/entrega');
            $table->foreignId('inventario_fisico_id')->nullable()->constrained('inventarios_fisicos')->nullOnDelete()
                ->comment('si es un ajuste nacido de un conteo');
            $table->foreignId('registrado_por')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('autorizado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('documento_referencia', 120)->nullable()->comment('factura, nota, cuaderno...');
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('tipo');
            $table->index('fecha');
            $table->comment('Fuente de verdad del inventario. deleted_at = anulacion recuperable.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
