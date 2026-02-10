<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'titular',
                'descripcion' => 'Titular de la membresía con acceso completo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'miembro_familiar',
                'descripcion' => 'Miembro familiar con acceso limitado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'administrador',
                'descripcion' => 'Administrador del sistema',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'hospital',
                'descripcion' => 'Personal del hospital Costamed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);
    }
}