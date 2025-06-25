<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\WeeklySchedule;
use Illuminate\Database\Seeder;
use App\Models\AcademicClassSection;
use Illuminate\Support\Facades\Http;

class WeeklyScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $academicYear = AcademicYear::where('status', 'In Progress')->first();

        if (!$academicYear) {
            $this->command->error('No academic year found.');
            return;
        }

        $sections = AcademicClassSection::where('academic_year_slug', $academicYear->slug)
            ->with(['academicYear', 'academicClass', 'academicSection'])
            ->get();

        $subjects = Subject::all();
        $teacherApiUrl = config('services.user_management.url') . 'teachers';

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post($teacherApiUrl, []);

        if (!$response->ok()) {
            $this->command->error('Failed to fetch teachers from user management service.');
            return;
        }

        $teachers = $response->json('data') ?? [];

        if ($subjects->isEmpty()) {
            $this->command->error('No subjects found.');
            return;
        }

        if (empty($teachers)) {
            $this->command->error('No teachers found.');
            return;
        }

        $indexx = 0;
        $teacherSchedule = []; // [teacher_slug][day_of_week][] = ['start' => ..., 'end' => ...]

        foreach ($sections as $section) {
            foreach ($daysOfWeek as $day) {
                // Break slot
                $customId = generateCustomId($indexx++);
                WeeklySchedule::create([
                    'slug' => $customId,
                    'academic_class_section_slug' => $section->slug,
                    'subject_slug' => null,
                    'teacher_slug' => null,
                    'subject_name' => null,
                    'teacher_name' => null,
                    'day_of_week' => $day,
                    'start_time' => '12:00',
                    'end_time' => '13:00',
                    'type' => 'break',
                    'academic_info' => "Academic Year: {$academicYear->year}, Class: {$section->academicClass->name}, Section: {$section->academicSection->name}",
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
                    $assignedTeacher = null;

                    foreach (collect($teachers)->shuffle() as $teacher) {
                        $slug = $teacher['slug'];
                        $conflicts = false;

                        $assignedSlots = $teacherSchedule[$slug][$day] ?? [];

                        foreach ($assignedSlots as $assigned) {
                            if (
                                ($slot['start'] < $assigned['end']) &&
                                ($assigned['start'] < $slot['end'])
                            ) {
                                $conflicts = true;
                                break;
                            }
                        }

                        if (!$conflicts) {
                            $assignedTeacher = $teacher;

                            // Track assigned slot
                            $teacherSchedule[$slug][$day][] = [
                                'start' => $slot['start'],
                                'end' => $slot['end']
                            ];
                            break;
                        }
                    }

                    if (!$assignedTeacher) {
                        $this->command->warn("No available teacher found for {$section->slug} on {$day} at {$slot['start']}");
                        continue;
                    }

                    $customId = generateCustomId($indexx++);

                    WeeklySchedule::create([
                        'slug' => $customId,
                        'academic_class_section_slug' => $section->slug,
                        'subject_slug' => $subject->slug,
                        'teacher_slug' => $assignedTeacher['slug'],
                        'subject_name' => $subject->name,
                        'teacher_name' => $assignedTeacher['teacher_name'],
                        'day_of_week' => $day,
                        'start_time' => $slot['start'],
                        'end_time' => $slot['end'],
                        'type' => 'class',
                        'academic_info' => "Academic Year: {$academicYear->year}, Class: {$section->academicClass->name}, Section: {$section->academicSection->name}",
                    ]);
                }
            }
        }

        $this->command->info('Weekly schedules seeded successfully without teacher time conflicts.');
    }
}