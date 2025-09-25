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

        $sections = AcademicClassSection::with(['academicYear', 'academicClass', 'academicSection'])
                    ->whereHas('academicYear', function($q) {
                        $q->where('status', 'In Progress');
                    })->get();
                    
        $studentsApiUrl = config('services.user_management.url') . 'students';
    
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            // 'Authorization' => $request->header('Authorization'),
        ])->post($studentsApiUrl, []);

        if (!$response->ok()) {
            $this->command->error('Failed to fetch students from user management service.');
            return;
        }
        
        $students = $response->json('data') ?? [];

        $studentsPerSection = 5;
        $sectionCount = $sections->count();

        foreach ($sections as $sectionIndex => $section) {
            for ($i = 0; $i < $studentsPerSection; $i++) {
                $studentIndex = $sectionIndex * $studentsPerSection + $i;
                if (!isset($students[$studentIndex])) {
                    break;
                }
                $student = $students[$studentIndex];
                StudentEnrollment::create([
                    'slug' => generateCustomId($studentIndex),
                    'student_slug' => $student['slug'],
                    'academic_class_section_slug' => $section['slug'],
                    'student_name' => $student['student_name'] ?? null,
                    'roll_number' => rand(1, 100),
                    'admission_date' => now()->subMonths(rand(1, 12)),
                    'enrollment_type' => 'new',
                    'previous_school' => null,
                    'graduation_date' => null,
                    'status' => 'active',
                    'academic_info' => 'Academic Year: ' . $section->academicYear->year . ', Class: ' . $section->academicClass->name . ', Section: ' . $section->academicSection->name,
                    'remarks' => 'Auto seeded enrollment.',
                ]);
            }
        }
    }
}
