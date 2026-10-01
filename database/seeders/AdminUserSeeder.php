<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $perm_manage_users = Permission::firstOrCreate(['permission_title' => 'manage_users']);
        $perm_manage_projects = Permission::firstOrCreate(['permission_title' => 'manage_projects']);
        $perm_view_stats = Permission::firstOrCreate(['permission_title' => 'view_stats']);

        // Create admin role
        $admin_role = Role::firstOrCreate(
            ['role_title' => 'admin'],
            ['permission_id' => $perm_manage_users->id]
        );
        
        // You might want to associate multiple permissions with a role.
        // The current schema (permission_id) is a one-to-one relationship.
        // This is a simplification based on the initial schema.
        // For a many-to-many relationship, you would use a pivot table.

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@abu.edu.ng'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role_id' => $admin_role->id,
                'email_verified_at' => now(),
                'verified_at' => now(),
            ]
        );
    }
}
