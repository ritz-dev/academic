<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StudentEnrollment;
use App\Models\AcademicAttendance;
use App\Models\AcademicClassSection;
use Illuminate\Support\Facades\Http;

class AcademicAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = AcademicClassSection::first();

        $scheduleId = 1;
        $lastHash = null;
        
        if (!$sections) {
            $this->command->error('No sections found. Please run the AcademicClassSectionSeeder first.');
            return;
        }

        $students = StudentEnrollment::where('academic_class_section_id', $sections->id)->get();
        
        if ($students->isEmpty()) {
            $this->command->error('No students found for the section. Please run the StudentEnrollmentSeeder first.');
            return;
        }

        $teacherApiUrl = config('services.user_management.url') . 'teachers';

        // Fetch teacher info based on the section ID
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            // 'Authorization' => $request->header('Authorization'),
        ])->post($teacherApiUrl, []);

        if (!$response->ok()) {
            $this->command->error('Failed to fetch teachers from user management service.');
            return;
        }

        $teachers = $response->json() ?? [];


        foreach ($students as $student) {
            AcademicAttendance::create([
                'previous_hash' => $lastHash,
                'hash' => 'studenthash' . $student->id,  // Replace with real hash logic
                'attendee_type' => 'student',
                'attendee_id' => $student->id,
                'schedule_id' => $scheduleId,
                'status' => 'present', // or some logic
                'date' => now(),
                'remark' => null,
            ]);
            $lastHash = 'studenthash' . $student->id;
        }

        AcademicAttendance::create([
            'previous_hash' => $lastHash,
            'hash' => 'teacherhash' . $teachers[0]['id'],  // Replace with real hash logic
            'attendee_type' => 'teacher',
            'attendee_id' => $teachers[0]['id'],
            'schedule_id' => $scheduleId,
            'status' => 'present', // or some logic
            'date' => now(),
            'remark' => null,
        ]);

        $lastHash = 'teacherhash' . $teachers[0]['id'];
    
        $this->command->info('Academic attendance seeded successfully.');
    }
}
