<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\WeeklySchedule;
use Illuminate\Database\Seeder;
use App\Models\AcademicClassSection;
use Illuminate\Support\Facades\Http;

class WeeklyScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $sections = AcademicClassSection::take(25)->get();
        $subjects = Subject::all();
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

        if ($subjects->isEmpty()) {
            $this->command->error('No subjects found.');
            return;
        }

        if (empty($teachers)) {
            $this->command->error('No teachers found.');
            return;
        }

        foreach ($sections as $section) {
            foreach ($daysOfWeek as $day) {
                // Insert break
                WeeklySchedule::create([
                    'academic_class_section_id' => $section->id,
                    'subject_id' => null,
                    'teacher_id' => null,
                    'day_of_week' => $day,
                    'start_time' => '12:00',
                    'end_time' => '13:00',
                    'type' => 'break',
                ]);

                // Class slots
                $classSlots = [
                    ['start' => '09:00', 'end' => '10:30'],
                    ['start' => '10:30', 'end' => '12:00'],
                    ['start' => '13:00', 'end' => '14:30'],
                    ['start' => '14:30', 'end' => '16:00'],
                ];

                foreach ($classSlots as $slot) {
                    $subject = $subjects->random();
                    $randomTeacher = collect($teachers)->random();
                    $teacherId = $randomTeacher['id'] ?? null;

                    WeeklySchedule::create([
                        'academic_class_section_id' => $section->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacherId,
                        'day_of_week' => $day,
                        'start_time' => $slot['start'],
                        'end_time' => $slot['end'],
                        'type' => 'class',
                    ]);
                }
            }
        }
    }
}