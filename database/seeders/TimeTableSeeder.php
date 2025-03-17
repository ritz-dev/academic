<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Subject;
use App\Models\TimeTable;
use Illuminate\Support\Str;
use App\Models\AcademicClass;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TimeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startDate = Carbon::parse('2025-02-01'); // Start of the month
        $endDate = Carbon::parse('2025-02-28'); // End of the month

        $subjects = [1, 2, 3, 4]; // Example subject IDs
        $teachers = [1, 2, 3, 4]; // Example teacher IDs
        $rooms = ['Room One', 'Room Two', 'Room Three'];

        // Define time slots
        $time_slots = [
            ['start' => '9:00', 'end' => '10:30'],
            ['start' => '10:30', 'end' => '12:00'],
            ['start' => '12:00', 'end' => '13:00'], // Break-Time
            ['start' => '13:00', 'end' => '14:30']
        ];

        // Define holidays
        $holidays = [
            '2025-02-12',
        ];

        $exam_dates = ['2025-02-15', '2025-02-20', '2025-02-25'];

        while ($startDate->lte($endDate)) {
            if ($startDate->isWeekend() || in_array($startDate->toDateString(), $holidays)) {
                // Insert holiday/weekend record
                TimeTable::create([
                    'slug' => Str::uuid(),
                    'title' => 'Holiday',
                    'academic_class_id' => null,
                    'section_id' => 1,
                    'subject_id' => null,
                    'teacher_id' => null,
                    'room' => null,
                    'date' => $startDate->toDateString(),
                    'start_time' => null,
                    'end_time' => null,
                    'type' => 'Holiday'
                ]);
            } elseif (in_array($startDate->toDateString(), $exam_dates)) {
                // Insert exam record
                $subjectId = $subjects[array_rand($subjects)];
                $subjectName = Subject::find($subjectId)?->name ?? 'Exam';

                TimeTable::create([
                    'slug' => Str::uuid(),
                    'title' => "Exam - $subjectName",
                    'academic_class_id' => 1,
                    'section_id' => 1,
                    'subject_id' => $subjectId,
                    'teacher_id' => '915c85ef-7781-4358-88fd-b65494980a2a',
                    'room' => 'Exam Hall',
                    'date' => $startDate->toDateString(),
                    'start_time' => '9:00',
                    'end_time' => '12:00',
                    'type' => 'Exam'
                ]);
            } else {
                // Insert 4 sessions per day
                foreach ($time_slots as $slot) {
                    if ($slot['start'] == '12:00') {
                        // Break-Time record
                        TimeTable::create([
                            'slug' => Str::uuid(),
                            'title' => 'Break-Time',
                            'academic_class_id' => 1,
                            'section_id' => 1,
                            'subject_id' => null,
                            'teacher_id' => null,
                            'room' => 'Cafeteria',
                            'date' => $startDate->toDateString(),
                            'start_time' => $slot['start'],
                            'end_time' => $slot['end'],
                            'type' => 'Break-Time'
                        ]);
                    } else {
                        // Lecture session
                        $subjectId = $subjects[array_rand($subjects)];
                        $subjectName = Subject::find($subjectId)?->name ?? 'Lecture';

                        TimeTable::create([
                            'slug' => Str::uuid(),
                            'title' => $subjectName,
                            'academic_class_id' => 1,
                            'section_id' => 1,
                            'subject_id' => $subjectId,
                            'teacher_id' => '915c85ef-7781-4358-88fd-b65494980a2a',
                            'room' => $rooms[array_rand($rooms)],
                            'date' => $startDate->toDateString(),
                            'start_time' => $slot['start'],
                            'end_time' => $slot['end'],
                            'type' => 'Lecture'
                        ]);
                    }
                }
            }
            // Move to the next day
            $startDate->addDay();
        }
    }
}
