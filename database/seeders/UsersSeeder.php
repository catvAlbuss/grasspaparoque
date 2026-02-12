<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Root User',
                'email' => 'root@pcgrass.com',
                'personal' => 'Direccion General',
                'password' => 'password',
                'role' => 'root',
            ],
            [
                'name' => 'Gerente User',
                'email' => 'gerente@pcgrass.com',
                'personal' => 'Gerencia',
                'password' => 'password',
                'role' => 'gerente',
            ],
            [
                'name' => 'Administrador User',
                'email' => 'admin@pcgrass.com',
                'personal' => 'Administracion',
                'password' => 'password',
                'role' => 'administrador',
            ],
            [
                'name' => 'Asistente User',
                'email' => 'asistente@pcgrass.com',
                'personal' => 'Asistencia',
                'password' => 'password',
                'role' => 'asistente',
            ],
            [
                'name' => 'Cliente User',
                'email' => 'cliente@pcgrass.com',
                'personal' => 'Cliente',
                'password' => 'password',
                'role' => 'cliente',
            ],
        ];

        foreach ($users as $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'personal' => $data['personal'],
                    'password' => $data['password'],
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
