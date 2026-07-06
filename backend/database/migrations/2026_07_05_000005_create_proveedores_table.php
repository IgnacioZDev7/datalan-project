<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 180);
            $table->string('nit', 30)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('contacto', 120)->nullable();
            $table->foreignId('direccion_id')->nullable()->constrained('direcciones')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->comment('Direccion via tabla direcciones.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
