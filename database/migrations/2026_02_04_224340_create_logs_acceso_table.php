<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs_acceso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->string('email', 150)->nullable();
            $table->string('ip_address', 45); // IPv4 o IPv6
            $table->string('dispositivo', 255)->nullable(); // User agent
            $table->enum('accion', ['login', 'logout', 'intento_fallido', 'acceso_endpoint']);
            $table->string('endpoint', 255)->nullable(); // URL accedida
            $table->enum('resultado', ['exitoso', 'fallido'])->default('exitoso');
            $table->text('detalles')->nullable();
            $table->timestamp('fecha_hora')->useCurrent();
            $table->index(['usuario_id', 'fecha_hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs_acceso');
    }
};