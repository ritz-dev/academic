<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StudentEnrollment;
use App\Models\AcademicClassSection;
use Illuminate\Support\Facades\Http;

class StudentEnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $sections = AcademicClassSection::all();
        $studentsApiUrl = config('services.user_management.url') . 'students';
    
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            // 'Authorization' => $request->header('Authorization'),
        ])->post($studentsApiUrl, []);

        if (!$response->ok()) {
            $this->command->error('Failed to fetch students from user management service.');
            return;
        }

        
        $students = $response->json() ?? [];

        logger()->info('Fetched students:', $students);

        foreach($sections as $section) {
            foreach ($students as $student) {
                // Insert student enrollment
                StudentEnrollment::create([
                    'student_id' => $student['slug'],
                    'academic_class_section_id' => $section->id,
                    'roll_number' => rand(1, 100),
                    'admission_date' => now()->subMonths(rand(1, 12)),
                    'enrollment_type' => 'new',
                    'previous_school' => null,
                    'graduation_date' => null,
                    'status' => 'active',
                    'remarks' => 'Auto seeded enrollment.',
                ]);
            }
        }
    }
}
