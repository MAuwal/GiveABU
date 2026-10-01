<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Project;
use App\Models\Faculty;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ReportsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('en_NG');

        // Ensure we have some Faculties and Departments
        if (Faculty::count() == 0) {
            $faculties = [
                'Faculty of Sciences',
                'Faculty of Engineering',
                'Faculty of Arts',
                'Faculty of Medicine',
            ];
            foreach ($faculties as $fac) {
                Faculty::create(['name' => $fac]);
            }
        }

        if (Department::count() == 0) {
            $faculties = Faculty::all();
            foreach ($faculties as $faculty) {
                Department::create([
                    'name' => 'Department of ' . $faker->word,
                    'faculty_id' => $faculty->id
                ]);
            }
        }

        // Ensure we have some Donors
        if (Donor::count() < 10) {
            $departments = Department::all();
            for ($i = 0; $i < 20; $i++) {
                $dept = $departments->random();
                Donor::create([
                    'name' => $faker->firstName,
                    'surname' => $faker->lastName,
                    'other_name' => $faker->firstName, // Add other_name if required
                    'email' => $faker->unique()->safeEmail,
                    'phone' => $faker->phoneNumber,
                    'faculty_id' => $dept->faculty_id,
                    'department_id' => $dept->id,
                    'donor_type' => 'alumni', // Assuming 'alumni' is a valid type
                ]);
            }
        }

        // Ensure we have some Projects
        if (Project::count() < 5) {
             for ($i = 0; $i < 5; $i++) {
                Project::create([
                    'project_title' => $faker->sentence(3),
                    'project_description' => $faker->paragraph,
                    'target' => $faker->numberBetween(100000, 10000000),
                    'raised' => 0,
                    'status' => 'active',
                ]);
            }
        }


        // Seed Donations
        $donors = Donor::all();
        $projects = Project::all();

        $this->command->info('Seeding 35 test donations...');

        for ($i = 0; $i < 35; $i++) {
            $donor = $donors->random();
            $project = $faker->boolean(70) ? $projects->random() : null; // 70% chance of project donation

            Donation::create([
                'donor_id' => $donor->id,
                'project_id' => $project ? $project->id : null,
                'amount' => $faker->numberBetween(1000, 500000),
                'status' => $faker->randomElement(['paid', 'pending', 'success']),
                'payment_reference' => strtoupper(uniqid('REF-')),
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'type' => 'once',
                'frequency' => 'once',
                'endowment' => $faker->randomElement(['yes', 'no']), // Fixed missing field
            ]);
        }
        
        $this->command->info('ReportsTestSeeder completed successfully.');
    }
}
