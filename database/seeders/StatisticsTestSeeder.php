<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Donor;
use App\Models\Donation;
use App\Models\Department;
use App\Models\Faculty;

class StatisticsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some test departments if they don't exist
        $departments = [
            ['current_name' => 'Computer Engineering', 'faculty_id' => 2], // Engineering
            ['current_name' => 'Electrical Engineering', 'faculty_id' => 2], // Engineering
            ['current_name' => 'Civil Engineering', 'faculty_id' => 2], // Engineering
            ['current_name' => 'Chemical Engineering', 'faculty_id' => 2], // Engineering
            ['current_name' => 'Architecture', 'faculty_id' => 2], // Engineering
            ['current_name' => 'Computer Science', 'faculty_id' => 1], // Applied Science
            ['current_name' => 'Mathematics', 'faculty_id' => 1], // Applied Science
            ['current_name' => 'Physics', 'faculty_id' => 1], // Applied Science
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['current_name' => $dept['current_name']], 
                $dept
            );
        }

        $departmentIds = Department::pluck('id')->toArray();

        // Create test donors
        $donors = [
            [
                'name' => 'John',
                'surname' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone' => '08012345678',
                'donor_type' => 'addressable_alumni',
                'state' => 'Kaduna',
                'lga' => 'Zaria',
                'department_id' => $departmentIds[0] ?? null,
                'reg_number' => 'U14CE1001'
            ],
            [
                'name' => 'Jane',
                'surname' => 'Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '08012345679',
                'donor_type' => 'addressable_alumni',
                'state' => 'Lagos',
                'lga' => 'Ikeja',
                'department_id' => $departmentIds[1] ?? null,
                'reg_number' => 'U15EE1002'
            ],
            [
                'name' => 'Aliyu',
                'surname' => 'Musa',
                'email' => 'aliyu.musa@example.com',
                'phone' => '08012345680',
                'donor_type' => 'addressable_alumni',
                'state' => 'Kano',
                'lga' => 'Fagge',
                'department_id' => $departmentIds[2] ?? null,
                'reg_number' => 'U16CE1003'
            ],
            [
                'name' => 'Fatima',
                'surname' => 'Abubakar',
                'email' => 'fatima.abubakar@example.com',
                'phone' => '08012345681',
                'donor_type' => 'staff',
                'state' => 'Abuja',
                'lga' => 'Wuse',
                'department_id' => null
            ],
            [
                'name' => 'Ahmed',
                'surname' => 'Ibrahim',
                'email' => 'ahmed.ibrahim@example.com',
                'phone' => '08012345682',
                'donor_type' => 'addressable_alumni',
                'state' => 'Kaduna',
                'lga' => 'Sabon Gari',
                'department_id' => $departmentIds[3] ?? null,
                'reg_number' => 'U17CHE1004'
            ],
            [
                'name' => 'Amina',
                'surname' => 'Hassan',
                'email' => 'amina.hassan@example.com',
                'phone' => '08012345683',
                'donor_type' => 'anonymous',
                'state' => 'Lagos',
                'lga' => 'Victoria Island',
                'department_id' => null
            ]
        ];

        foreach ($donors as $donorData) {
            $donor = Donor::updateOrCreate(
                ['email' => $donorData['email']],
                $donorData
            );
            
            // Create some donations for each donor
            $donationAmounts = [5000, 3000, 2000, 1500, 1000, 800];
            $randomAmount = $donationAmounts[array_rand($donationAmounts)];
            
            Donation::updateOrCreate(
                ['donor_id' => $donor->id],
                [
                    'donor_id' => $donor->id,
                    'amount' => $randomAmount,
                    'type' => 'donation',
                    'frequency' => 'onetime',
                    'status' => 'success',
                    'payment_reference' => 'TEST_' . time() . '_' . $donor->id,
                    'endowment' => 'yes',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->command->info('Test data for statistics created successfully!');
        $this->command->info('Created ' . Donor::count() . ' donors and ' . Donation::count() . ' donations.');
    }
}
