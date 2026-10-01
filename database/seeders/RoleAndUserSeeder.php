<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get or create permissions (in case they don't exist)
        $permManageUsers = Permission::firstOrCreate(['permission_title' => 'manage_users']);
        $permManageFinances = Permission::firstOrCreate(['permission_title' => 'manage_finances']);
        $permViewStats = Permission::firstOrCreate(['permission_title' => 'view_dashboard_stats']);

        // Create Admin Role
        $adminRole = Role::updateOrCreate(
            ['role_title' => 'admin'],
            ['permission_id' => $permManageUsers->id]
        );

        // Create Finance Role
        $financeRole = Role::updateOrCreate(
            ['role_title' => 'finance'],
            ['permission_id' => $permManageFinances->id]
        );

        // Create Executive Role
        $executiveRole = Role::updateOrCreate(
            ['role_title' => 'executive'],
            ['permission_id' => $permViewStats->id]
        );

        // Create Admin User
        User::firstOrCreate(
            ['email' => 'admin@abu.edu.ng'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'email_verified_at' => now(),
                'verified_at' => now(),
            ]
        );

        // Create Finance User
        User::firstOrCreate(
            ['email' => 'finance@abu.edu.ng'],
            [
                'name' => 'Finance Manager',
                'password' => Hash::make('finance123'),
                'role_id' => $financeRole->id,
                'email_verified_at' => now(),
                'verified_at' => now(),
            ]
        );

        // Create Executive User
        User::firstOrCreate(
            ['email' => 'executive@abu.edu.ng'],
            [
                'name' => 'Executive Director',
                'password' => Hash::make('executive123'),
                'role_id' => $executiveRole->id,
                'email_verified_at' => now(),
                'verified_at' => now(),
            ]
        );
    }
}

