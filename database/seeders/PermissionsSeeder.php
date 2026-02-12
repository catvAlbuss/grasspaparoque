<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        Role::findByName('root')->syncPermissions($permissions);
        Role::findByName('gerente')->syncPermissions([
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
        ]);
        Role::findByName('administrador')->syncPermissions([
            'users.view',
            'users.create',
            'users.update',
        ]);
        Role::findByName('asistente')->syncPermissions([
            'users.view',
            'users.create',
        ]);
        Role::findByName('cliente')->syncPermissions([
            'users.view',
        ]);
    }
}
