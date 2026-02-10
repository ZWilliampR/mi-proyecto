<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imc_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->decimal('peso', 5, 2); // kg (ej: 75.50)
            $table->decimal('altura', 5, 2); // metros (ej: 1.75)
            $table->decimal('imc_calculado', 5, 2); // resultado
            $table->enum('clasificacion', ['bajo_peso', 'peso_normal', 'sobrepeso', 'obesidad_1', 'obesidad_2', 'obesidad_3']);
            $table->text('recomendaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imc_registros');
    }
};