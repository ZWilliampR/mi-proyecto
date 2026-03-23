<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Deshabilitar foreign keys y crear rol manualmente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('roles')->insert([
            'id' => 1,
            'nombre' => 'titular',
            'descripcion' => 'Titular de la membresía',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    // UT-01: Registro Exitoso de Usuario
    public function test_registro_exitoso_de_usuario(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nombre' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'López',
            'email' => 'juan@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'id_socio_costamed' => 'COST-001',
        ]);
        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'user', 'token']);
    }

    // UT-02: Login con Credenciales Válidas
    public function test_login_con_credenciales_validas(): void
    {
        Usuario::create([
            'nombre' => 'María',
            'apellido_paterno' => 'García',
            'email' => 'maria@test.com',
            'password' => Hash::make('password123'),
            'id_socio_costamed' => 'COST-002',
            'role_id' => 1,
            'membresia_activa' => true,
        ]);
        $response = $this->postJson('/api/auth/login', [
            'email' => 'maria@test.com',
            'password' => 'password123',
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'user', 'token']);
    }

    // UT-03: Login con Contraseña Incorrecta
    public function test_login_con_password_incorrecto(): void
    {
        Usuario::create([
            'nombre' => 'Carlos',
            'apellido_paterno' => 'Ruiz',
            'email' => 'carlos@test.com',
            'password' => Hash::make('password123'),
            'id_socio_costamed' => 'COST-003',
            'role_id' => 1,
            'membresia_activa' => true,
        ]);
        $response = $this->postJson('/api/auth/login', [
            'email' => 'carlos@test.com',
            'password' => 'wrongpassword',
        ]);
        $response->assertStatus(401)
            ->assertJson(['message' => 'Credenciales incorrectas']);
    }

    // UT-04: Registro con Email Duplicado
    public function test_registro_con_email_duplicado(): void
    {
        $datos = [
            'nombre' => 'Ana',
            'apellido_paterno' => 'Torres',
            'email' => 'ana@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'id_socio_costamed' => 'COST-004',
        ];
        $this->postJson('/api/auth/register', $datos);
        $response = $this->postJson('/api/auth/register', $datos);
        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    // UT-05: Login con Membresía Inactiva
    public function test_login_con_membresia_inactiva(): void
    {
        Usuario::create([
            'nombre' => 'Pedro',
            'apellido_paterno' => 'Mora',
            'email' => 'pedro@test.com',
            'password' => Hash::make('password123'),
            'id_socio_costamed' => 'COST-005',
            'role_id' => 1,
            'membresia_activa' => false,
        ]);
        $response = $this->postJson('/api/auth/login', [
            'email' => 'pedro@test.com',
            'password' => 'password123',
        ]);
        $response->assertStatus(403)
            ->assertJson(['message' => 'Membresía inactiva']);
    }
}
