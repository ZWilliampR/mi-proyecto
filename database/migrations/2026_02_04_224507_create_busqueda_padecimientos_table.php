<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('busqueda_padecimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->string('termino_busqueda', 255);
            $table->json('sintomas_seleccionados')->nullable(); // Array de síntomas
            $table->json('resultados')->nullable(); // Resultados de la API externa
            $table->string('fuente', 100)->nullable(); // ej: Mayo Clinic API
            $table->timestamps();
            $table->index('termino_busqueda');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('busqueda_padecimientos');
    }
};