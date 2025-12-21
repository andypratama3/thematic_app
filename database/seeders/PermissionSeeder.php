<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'view_dashboard', 'guard_name'=> 'web'],

            // Permissions
            ['name' => 'view_permissions', 'guard_name'=> 'web'],
            ['name' => 'create_permission', 'guard_name'=> 'web'],
            ['name' => 'edit_permission', 'guard_name'=> 'web'],
            ['name' => 'delete_permission', 'guard_name'=> 'web'],

            // Roles
            ['name' => 'view_roles', 'guard_name'=> 'web'],
            ['name' => 'create_role', 'guard_name'=> 'web'],
            ['name' => 'edit_role', 'guard_name'=> 'web'],
            ['name' => 'delete_role', 'guard_name'=> 'web'],

            // Users
            ['name' => 'view_users', 'guard_name'=> 'web'],
            ['name' => 'create_user', 'guard_name'=> 'web'],
            ['name' => 'edit_user', 'guard_name'=> 'web'],
            ['name' => 'delete_user', 'guard_name'=> 'web'],

            // Datasets
            ['name' => 'view_datasets', 'guard_name'=> 'web'],
            ['name' => 'create_dataset', 'guard_name'=> 'web'],
            ['name' => 'edit_dataset', 'guard_name'=> 'web'],
            ['name' => 'delete_dataset', 'guard_name'=> 'web'],

            // Maps
            ['name' => 'view_maps', 'guard_name'=> 'web'],
            ['name' => 'edit_maps', 'guard_name'=> 'web'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

    }
}
