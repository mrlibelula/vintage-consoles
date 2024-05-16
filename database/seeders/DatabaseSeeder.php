<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Populate default users

        $libe = \App\Models\User::create([
            'name' => 'Libe',
            'email' => 'luis@libe.dev',
            'password' => Hash::make(env('ADMIN_PW')),
        ]);

        $libe = User::where('email', '=', 'luis@libe.dev')->first();

        // Add roles
        $role_admin = \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        $role_user = \Spatie\Permission\Models\Role::create(['name' => 'user']);

        // Add permissions
        $perm_create = \Spatie\Permission\Models\Permission::create(['name' => 'create']);
        $perm_read = \Spatie\Permission\Models\Permission::create(['name' => 'read']);
        $perm_update = \Spatie\Permission\Models\Permission::create(['name' => 'update']);
        $perm_delete = \Spatie\Permission\Models\Permission::create(['name' => 'delete']);
        $perm_view = \Spatie\Permission\Models\Permission::create(['name' => 'view']);

        // Assign permissions to role
        $role_admin->givePermissionTo([$perm_create, $perm_read, $perm_update, $perm_delete, $perm_view]);
        $role_user->givePermissionTo([$perm_view, $perm_read]);
        // $perm_create->assignRole($role_admin);

        // Assign role to users
        $libe->assignRole(['admin', 'user']);
    }
}
