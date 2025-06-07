<?php

namespace Database\Seeders;

use App\Models\WeeklySchedule;
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

        $schedule = WeeklySchedule::where('type', 'class')->first();
        
        $lastHash = null;
        
        if (!$sections) {
            $this->command->error('No sections found. Please run the AcademicClassSectionSeeder first.');
            return;
        }

        // $students = StudentEnrollment::where('academic_class_section_slug', $sections->slug)->get();
        
        // if ($students->isEmpty()) {
        //     $this->command->error('No students found for the section. Please run the StudentEnrollmentSeeder first.');
        //     return;
        // }

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

        $teachers = $response->json('data') ?? [];

        if($schedule->type !== 'break') {

            foreach ($students as $student) {
                AcademicAttendance::create([
                    'previous_hash' => $lastHash,
                    'hash' => 'studenthash' . $student->slug,
                    'weekly_schedule_slug' => $schedule->slug,
                    'subject' => $schedule->subject_name,
                    'academic_class_section_slug' => $sections->slug,
                    'academic_info' => $schedule->academic_info,
                    'attendee_slug' => $student->slug,
                    'attendee_name' => $student->student_name,
                    'attendee_type' => 'student',
                    'status' => 'present',
                    'attendance_type' => 'class',
                    'date' => now(),
                    'remark' => null,
                ]);
                $lastHash = 'studenthash' . $student->slug;
            }

            AcademicAttendance::create([
                'previous_hash' => $lastHash,
                'hash' => 'teacherhash' . $teachers[0]['slug'],
                'weekly_schedule_slug' => $schedule->slug,
                'subject' => $schedule->subject_name,
                'academic_class_section_slug' => $sections->slug,
                'academic_info' => $schedule->academic_info,
                'attendee_slug' => $teachers[0]['slug'],
                'attendee_name' => $teachers[0]['name'],
                'attendee_type' => 'teacher',
                'status' => 'present',
                'attendance_type' => 'class',
                'date' => now(),
                'remark' => null,
            ]);

        $lastHash = 'teacherhash' . $teachers[0]['slug'];

        }
    
        $this->command->info('Academic attendance seeded successfully.');
    }
}
