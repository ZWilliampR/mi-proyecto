<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('telefono', 20)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('genero', ['masculino', 'femenino', 'otro'])->nullable();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->string('id_socio_costamed', 50)->nullable()->unique(); // Solo para titulares
            $table->foreignId('titular_id')->nullable()->constrained('usuarios')->onDelete('cascade'); // Para miembros familiares
            $table->boolean('membresia_activa')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // Para eliminación lógica
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};