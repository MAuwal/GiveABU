<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;

class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faculties = [
            // Main Campus – Samaru
            'Faculty of Engineering',
            'Faculty of Physical Sciences',
            'Faculty of Life Sciences',
            'Faculty of Arts',
            'Faculty of Education',
            'Faculty of Social Sciences',
            'Faculty of Environmental Design',
            'Faculty of Veterinary Medicine',
            'ABU Business School', // Adjusted naming convention for clarity if needed, or stick to user input "Business School"
            'Faculty of Agriculture',
            'Faculty of Pharmaceutical Sciences',
            'Faculty of Basic Medical Sciences',
            'Faculty of Allied Health Sciences',
            'Faculty of Clinical Sciences',
            
            // Kongo Campus
            'Faculty of Law',
            'Faculty of Administration',
            
            // Additional/Medical
            'Faculty of Basic Clinical Sciences',
            // 'Faculty of Allied Health Sciences' is already listed above, avoiding duplicate in logic
        ];
        
        // Handling the "Business School" vs "Faculty of..." consistency if desired. 
        // User wrote "Business School". I'll use exactly what they gave, but "ABU Business School" is more formal. 
        // Let's stick strictly to the user's list but ensure unique names.
        
        $facultiesList = [
            'Faculty of Engineering',
            'Faculty of Physical Sciences',
            'Faculty of Life Sciences',
            'Faculty of Arts',
            'Faculty of Education',
            'Faculty of Social Sciences',
            'Faculty of Environmental Design',
            'Faculty of Veterinary Medicine',
            'Business School',
            'Faculty of Agriculture',
            'Faculty of Pharmaceutical Sciences',
            'Faculty of Basic Medical Sciences',
            'Faculty of Allied Health Sciences',
            'Faculty of Clinical Sciences',
            'Faculty of Law',
            'Faculty of Administration',
            'Faculty of Basic Clinical Sciences',
        ];

        foreach ($facultiesList as $name) {
            Faculty::firstOrCreate(['current_name' => $name]);
        }
        
        $this->command->info('ABU Faculties seeded successfully.');
    }
}
