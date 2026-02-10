<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('medico_id')->nullable()->constrained('usuarios')->onDelete('set null'); // Usuario con rol hospital/medico
            $table->enum('tipo_chat', ['normal', 'emergencia']);
            $table->enum('estado', ['activo', 'cerrado'])->default('activo');
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};