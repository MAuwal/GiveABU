<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear the table to avoid duplicates
        DB::table('permissions')->truncate();

        $permissions = [
            // Admin permissions
            ['permission_title' => 'manage_users'],
            ['permission_title' => 'manage_projects'],
            ['permission_title' => 'manage_donors'],
            ['permission_title' => 'manage_all_settings'],
            ['permission_title' => 'view_all_reports'],
            
            // Finance permissions
            ['permission_title' => 'manage_finances'],
            ['permission_title' => 'view_financial_reports'],
            ['permission_title' => 'process_donations'],
            ['permission_title' => 'view_donations'],
            
            // Executive permissions
            ['permission_title' => 'view_dashboard_stats'],
            ['permission_title' => 'view_reports'],
            ['permission_title' => 'view_projects'],
            ['permission_title' => 'view_donors'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
} 