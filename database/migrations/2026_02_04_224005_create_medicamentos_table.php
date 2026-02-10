<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('nombre_medicamento', 150);
            $table->string('dosis', 50); // ej: 500mg, 1 tableta
            $table->enum('frecuencia', ['cada_4_horas', 'cada_6_horas', 'cada_8_horas', 'cada_12_horas', 'cada_24_horas', 'personalizado']);
            $table->time('hora_inicio');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('notificacion_activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicamentos');
    }
};