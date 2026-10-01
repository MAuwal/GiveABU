<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
        $initialPassword = config('services.security.seed_passwords.admin');
        if (is_string($initialPassword) && strlen($initialPassword) >= 12) {
            User::firstOrCreate(
                ['email' => 'admin@abu.edu.ng'],
                [
                    'name' => 'System Administrator',
                    'password' => Hash::make($initialPassword),
                    'role_id' => $adminRole->id,
                    'email_verified_at' => now(),
                    'verified_at' => now(),
                ]
            );
        }

        // Create Finance User
        $initialPassword = config('services.security.seed_passwords.finance');
        if (is_string($initialPassword) && strlen($initialPassword) >= 12) {
            User::firstOrCreate(
                ['email' => 'finance@abu.edu.ng'],
                [
                    'name' => 'Finance Manager',
                    'password' => Hash::make($initialPassword),
                    'role_id' => $financeRole->id,
                    'email_verified_at' => now(),
                    'verified_at' => now(),
                ]
            );
        }

        // Create Executive User
        $initialPassword = config('services.security.seed_passwords.executive');
        if (is_string($initialPassword) && strlen($initialPassword) >= 12) {
            User::firstOrCreate(
                ['email' => 'executive@abu.edu.ng'],
                [
                    'name' => 'Executive Director',
                    'password' => Hash::make($initialPassword),
                    'role_id' => $executiveRole->id,
                    'email_verified_at' => now(),
                    'verified_at' => now(),
                ]
            );
        }
    }
}
