<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->enum('tipo', ['medicamento', 'calendario_mujer', 'chat', 'sistema', 'emergencia']);
            $table->string('titulo', 200);
            $table->text('mensaje');
            $table->json('datos_extra')->nullable(); // Información adicional en JSON
            $table->boolean('leida')->default(false);
            $table->timestamp('fecha_leida')->nullable();
            $table->boolean('enviada')->default(false);
            $table->timestamp('fecha_envio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};