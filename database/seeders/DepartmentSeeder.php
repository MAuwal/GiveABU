<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Faculty of Agriculture' => [
                'Agricultural Economics & Rural Development',
                'Agronomy',
                'Animal Science',
                'Crop Protection',
                'Plant Science',
                'Soil Science'
            ],
            'Faculty of Arts' => [
                'African Languages & Cultures',
                'Arabic',
                'Archaeology & History',
                'English & Literary Studies',
                'French',
                'History',
                'Philosophy',
                'Theatre & Performing Arts'
            ],
            'Faculty of Education' => [
                'Educational Psychology & Counselling',
                'Arts & Social Science Education',
                'Library & Information Science',
                'Physical & Health Education',
                'Science Education',
                'Vocational & Technical Education',
                'Educational Foundations & Curriculum Studies'
            ],
            'Faculty of Engineering' => [
                'Agricultural & Bio-Resources Engineering',
                'Chemical & Petroleum Engineering',
                'Civil Engineering',
                'Computer Engineering',
                'Electrical & Computer Engineering',
                'Electronics & Telecommunication Engineering',
                'Mechanical Engineering',
                'Metallurgical & Materials Engineering',
                'Polymer & Textile Engineering',
                'Water Resources & Environmental Engineering'
            ],
            'Faculty of Environmental Design' => [
                'Architecture',
                'Building',
                'Fine Arts',
                'Geomatics (Land Surveying)',
                'Glass & Silicate Technology',
                'Industrial Design',
                'Urban & Regional Planning',
                'Quantity Surveying'
            ],
            'Faculty of Law' => [
                'Public Law',
                'Private Law',
                'Commercial Law',
                'Islamic Law'
            ],
            'Faculty of Physical Sciences' => [
                'Chemistry',
                'Computer Science',
                'Geography',
                'Geology',
                'Mathematics',
                'Physics',
                'Statistics',
                'Textile Science & Technology'
            ],
            'Faculty of Life Sciences' => [
                'Biochemistry',
                'Biology',
                'Botany',
                'Microbiology',
                'Zoology'
            ],
            'Faculty of Social Sciences' => [
                'Mass Communication',
                'Political Science & International Studies',
                'Sociology'
            ],
            'Faculty of Veterinary Medicine' => [
                'Veterinary Anatomy',
                'Veterinary Medicine',
                'Veterinary Microbiology',
                'Veterinary Physiology',
                'Veterinary Pathology',
                'Veterinary Parasitology & Entomology',
                'Veterinary Pharmacology & Toxicology',
                'Veterinary Public Health & Preventive Medicine',
                'Veterinary Surgery & Radiology',
                'Veterinary Theriogenology & Production'
            ],
            'Faculty of Administration' => [
                'Accounting',
                'Business Administration',
                'Local Government & Development Studies',
                'Public Administration'
            ],
            'Business School' => [
                'Accounting',
                'Banking & Finance',
                'Business Management',
                'Economics',
                'Insurance & Actuarial Sciences',
                'Marketing'
            ],
            'Faculty of Basic Medical Sciences' => [
                'Anatomy',
                'Physiology',
                'Medical Biochemistry'
            ],
            'Faculty of Basic Clinical Sciences' => [
                'Chemical Pathology',
                'Clinical Pharmacology & Therapeutics',
                'Haematology & Blood Transfusion',
                'Medical Microbiology',
                'Pathology (Morbid Anatomy)'
            ],
            'Faculty of Allied Health Sciences' => [
                'Nursing Sciences',
                'Medical Laboratory Sciences',
                'Medical Radiography'
            ],
            'Faculty of Clinical Sciences' => [
                'Anaesthesia',
                'Community Medicine',
                'Dental Surgery',
                'Medicine',
                'Obstetrics & Gynaecology',
                'Ophthalmology',
                'Paediatrics',
                'Psychiatry',
                'Surgery'
            ],
            'Faculty of Pharmaceutical Sciences' => [
                'Clinical Pharmacy & Pharmacy Practice',
                'Pharmaceutics & Pharmaceutical Microbiology',
                'Pharm. & Medicinal Chemistry',
                'Pharmacognosy & Drug Development',
                'Pharmacology & Therapeutics'
            ]
        ];

        foreach ($data as $facultyName => $departments) {
            $faculty = Faculty::where('current_name', $facultyName)->first();

            if ($faculty) {
                foreach ($departments as $deptName) {
                    Department::firstOrCreate(
                        [
                            'current_name' => $deptName,
                            'faculty_id' => $faculty->id
                        ]
                    );
                }
            } else {
                $this->command->warn("Faculty not found: $facultyName. Skipping departments...");
            }
        }
        
        $this->command->info('ABU Departments seeded successfully.');
    }
}
