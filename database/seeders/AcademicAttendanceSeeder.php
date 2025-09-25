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
        $sections = AcademicClassSection::whereHas('academicYear', function($q) {
                        $q->where('status', 'In Progress');
                    })->first();

        $schedule = WeeklySchedule::where('type', 'class')->first();
        
        $lastHash = null;
        
        if (!$sections) {
            $this->command->error('No sections found. Please run the AcademicClassSectionSeeder first.');
            return;
        }

        $students = StudentEnrollment::where('academic_class_section_slug', $sections->slug)->get();
        
        if ($students->isEmpty()) {
            $this->command->error('No students found for the section. Please run the StudentEnrollmentSeeder first.');
            return;
        }

        $teacherApiUrl = config('services.user_management.url') . 'teachers';

        // Fetch teacher info based on the section ID
        $responset = Http::withHeaders([
            'Accept' => 'application/json',
            // 'Authorization' => $request->header('Authorization'),
        ])->post($teacherApiUrl, ['limit' => 10]);

        if (!$responset->ok()) {
            $this->command->error('Failed to fetch teachers from user management service.');
            return;
        }

        $teachersArray = collect($responset->json('data') ?? []);

        $teachers = collect($teachersArray);

        if ($teachers->isEmpty()) {
            $this->command->error('No teachers found for the section. Please run the TeacherSeeder first.');
            return;
        }

        $dateInt = now()->format('Ymd'); // Returns '20250609' as a string
        $dateInt = (int) $dateInt;    

        if($schedule->type !== 'break') {

            $weeklySchedule = WeeklySchedule::where('slug', $schedule->slug)->first();

            foreach ($students as $index => $student) {
                $customId = generateCustomId($index + 1);

                AcademicAttendance::create([
                    'slug' => $customId,
                    'previous_hash' => $lastHash,
                    'hash' => 'studenthash' . $student['slug'],
                    'weekly_schedule_slug' => $schedule->slug,
                    'subject' => $schedule->subject_name,
                    'academic_class_section_slug' => $sections->slug,
                    'academic_info' => $schedule->academic_info,
                    'attendee_slug' => $student['slug'],
                    'attendee_name' => $student['student_name'],
                    'attendee_type' => 'student',
                    'status' => 'present',
                    'attendance_type' => 'class',
                    'approved_slug' => $weeklySchedule->teacher_slug,
                    'date' => $dateInt,
                    'remark' => null,
                ]);
                $lastHash = 'studenthash' . $student['slug'];
            }

            
        }
    
        $this->command->info('Academic attendance seeded successfully.');
    }
}
