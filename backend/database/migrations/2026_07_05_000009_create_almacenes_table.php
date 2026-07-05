<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('almacenes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->foreignId('responsable_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->comment('La direccion vive en la tabla direcciones. DATALAN tiene 2 ubicaciones.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('almacenes');
    }
};
