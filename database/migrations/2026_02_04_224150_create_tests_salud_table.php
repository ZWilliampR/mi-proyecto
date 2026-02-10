<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tests_salud', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->enum('tipo_test', ['estres', 'apnea_sueno', 'depresion', 'podometro']);
            $table->json('respuestas'); // Almacena las respuestas del cuestionario
            $table->integer('puntuacion')->nullable();
            $table->enum('nivel_resultado', ['bajo', 'moderado', 'alto', 'muy_alto'])->nullable();
            $table->text('interpretacion')->nullable();
            $table->text('recomendaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tests_salud');
    }
};