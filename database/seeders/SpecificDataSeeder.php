<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\Donor;
use App\Models\Donation;
use App\Models\Project;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SpecificDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('en_NG');

        $allowedFaculties = [
            'Faculty of Agriculture',
            'Faculty of Arts',
            'Faculty of Education',
            'Faculty of Engineering',
            'Faculty of Environmental Design',
            'Faculty of Law',
            'Faculty of Physical Sciences',
            'Faculty of Life Sciences',
            'Faculty of Social Sciences',
            'Faculty of Veterinary Medicine',
            'Faculty of Administration',
            'Business School', // or 'ABU Business School' based on previous input
            'Faculty of Basic Medical Sciences',
            'Faculty of Basic Clinical Sciences',
            'Faculty of Allied Health Sciences',
            'Faculty of Clinical Sciences',
            'Faculty of Pharmaceutical Sciences'
        ];

        // 1. Clean up unwanted Faculties
        $deletedCount = Faculty::whereNotIn('current_name', $allowedFaculties)->delete();
        $this->command->info("Deleted $deletedCount unwanted faculties (and likely their related data if cascades are on).");

        // 2. Ensure all allowed faculties exist (just in case)
        // We rely on previous seeders, but this is a safety check or we could re-run FacultySeeder logic here.
        // For now, we assume they exist or we just work with what's there.
        
        $faculties = Faculty::whereIn('current_name', $allowedFaculties)->with('departments')->get();

        /* 
        // 3. Seed Donations per Department
        $this->command->info("Seeding donatons for " . $faculties->count() . " faculties...");

        foreach ($faculties as $faculty) {
            $this->command->info("Processing: " . $faculty->current_name);
            
            foreach ($faculty->departments as $dept) {
                // Create 3-5 donations per department
                $numDonations = rand(3, 5);
                
                for ($i = 0; $i < $numDonations; $i++) {
                    // Create a Donor for this specific Faculty/Dept
                    $donor = Donor::create([
                        'name' => $faker->firstName,
                        'surname' => $faker->lastName,
                        'other_name' => $faker->firstName,
                        'email' => $faker->unique()->safeEmail,
                        'phone' => $faker->phoneNumber,
                        'faculty_id' => $faculty->id,
                        'department_id' => $dept->id,
                        'donor_type' => 'alumni',
                    ]);

                    // Create Donation
                    Donation::create([
                        'donor_id' => $donor->id,
                        'project_id' => null, // Generic endowment or specific project
                        'amount' => $faker->numberBetween(5000, 100000),
                        'status' => $faker->randomElement(['paid', 'success', 'pending']),
                        'payment_reference' => strtoupper(uniqid('REF-')),
                        'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                        'type' => 'once',
                        'frequency' => 'once',
                        'endowment' => $faker->randomElement(['yes', 'no']),
                    ]);
                }
            }
        }
        */

        // 4. Seed Projects and Project Donations
        $this->command->info("Seeding Projects and Project Donations...");
        
        $projects = [
            'Student Hostel Renovation' => 'Renovation of Danfodio and ICSA halls.',
            'Digital Library Fund' => 'Equipping the main library with 500 new computers.',
            'Solar Power Initiative' => 'Providing 24/7 power to the senate building.',
            'Indigent Student Scholarship' => 'Supporting 1000 students with tuition fees.',
        ];

        foreach ($projects as $title => $desc) {
            $project = Project::firstOrCreate(
                ['project_title' => $title],
                [
                    'project_description' => $desc,
                    'target' => 50000000,
                    'raised' => 0,
                    'status' => 'active',
                    'category_id' => 1, // Assuming category 1 exists or is nullable, but safer to rely on ID 1 usually being seeded or generic
                ]
            );

            // Create 5-10 donations for this project
            $numDonations = rand(5, 10);
            
            for ($i = 0; $i < $numDonations; $i++) {
                // Get a random donor (we just seeded many)
                $donor = Donor::inRandomOrder()->first();
                
                if ($donor) {
                    Donation::create([
                        'donor_id' => $donor->id,
                        'project_id' => $project->id,
                        'amount' => $faker->numberBetween(10000, 500000),
                        'status' => 'paid', // Ensure they are counted
                        'payment_reference' => strtoupper(uniqid('REF-PROJ-')),
                        'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                        'type' => 'once',
                        'frequency' => 'once',
                        'endowment' => 'no',
                    ]);
                }
            }
        }
        
        $this->command->info('Specific Data Seeding Completed.');
    }
}
