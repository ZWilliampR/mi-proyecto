<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitas_domiciliarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('medico_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->date('fecha_solicitada');
            $table->time('hora_solicitada');
            $table->text('direccion');
            $table->string('ciudad', 100);
            $table->string('estado', 100);
            $table->string('codigo_postal', 10);
            $table->text('motivo_visita');
            $table->enum('tipo_servicio', ['consulta_general', 'urgencia', 'seguimiento']);
            $table->enum('estado_solicitud', ['pendiente', 'confirmada', 'en_camino', 'completada', 'cancelada'])->default('pendiente');
            $table->text('notas_medico')->nullable();
            $table->timestamp('fecha_confirmacion')->nullable();
            $table->timestamp('fecha_completada')->nullable();
            $table->text('motivo_cancelacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitas_domiciliarias');
    }
};