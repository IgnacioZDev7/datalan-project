<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 60)->unique()->comment('codigo interno / QR / codigo de barras');
            $table->string('nombre', 180);
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->foreignId('modelo_id')->nullable()->constrained('modelos')->nullOnDelete()
                ->comment('la marca se obtiene via modelo');
            $table->foreignId('unidad_id')->constrained('unidades_medida')->restrictOnDelete();
            $table->boolean('retornable')->default(false);
            $table->json('especificaciones')->nullable()->comment('atributos variables por categoria');
            $table->string('imagen', 255)->nullable()->comment('ruta en storage');
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
