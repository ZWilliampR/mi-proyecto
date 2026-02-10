<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendario_mujer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->date('fecha_inicio_periodo');
            $table->date('fecha_fin_periodo')->nullable();
            $table->integer('duracion_ciclo')->default(28); // días
            $table->integer('duracion_periodo')->default(5); // días
            $table->date('proxima_fecha_estimada')->nullable();
            $table->date('fecha_ovulacion_estimada')->nullable();
            $table->text('sintomas')->nullable(); // JSON con síntomas
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendario_mujer');
    }
};