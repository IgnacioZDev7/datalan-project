<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('tipo_inventario', 20)->comment('consumible|cable|activo|herramienta');
            $table->foreignId('categoria_padre_id')->nullable()
                ->constrained('categorias')->nullOnDelete()->comment('jerarquia opcional');
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
