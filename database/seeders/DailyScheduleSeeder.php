<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\AcademicYear;
use App\Models\DailySchedule;
use App\Models\WeeklySchedule;
use Illuminate\Database\Seeder;
use App\Models\AcademicClassSection;

class DailyScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYear = AcademicYear::where('status', 'Completed')->first();

        if (!$academicYear) {
            $this->command->error('No academic year with "In Progress" status found.');
            return;
        }

        $weeklyHolidays = ['Saturday', 'Sunday'];

        $startDate = Carbon::parse($academicYear->start_date);
        $endDate = Carbon::parse($academicYear->end_date);

        $sections = AcademicClassSection::where('academic_year_slug', $academicYear->slug)->take(5)->get();

        foreach ($sections as $section) {
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                $dayName = $currentDate->format('l'); // e.g., "Monday"
                // $isPublicHoliday = in_array($currentDate->toDateString(), $publicHolidays);
                $isWeeklyHoliday = in_array($dayName, $weeklyHolidays);

                $weeklySlots = WeeklySchedule::where('academic_class_section_slug', $section->slug)
                        ->where('day_of_week', $dayName)
                        ->get();

                if ($isWeeklyHoliday) {
                    DailySchedule::create([
                        'date' => $currentDate->toDateString(),
                        'academic_class_section_slug' => $section->slug,
                        'subject_slug' => null,
                        'teacher_slug' => null,
                        'teacher_name' => null,
                        'start_time' => null,
                        'end_time' => null,
                        'type' => 'break',
                        'is_holiday' => true,
                        'holiday_type' => 'weekly',
                        'note' => 'Weekly Holiday',
                        'academic_info' => "Academic Year: {$academicYear->year}, Class: {$section->academicClass->name}, Section: {$section->academicSection->name}",
                    ]);
                } else {
                    foreach ($weeklySlots as $slot) {
                        DailySchedule::create([
                            'date' => $currentDate->toDateString(),
                            'academic_class_section_slug' => $section->slug,
                            'subject_slug' => $slot->subject_slug,
                            'teacher_slug' => $slot->teacher_slug,
                            'teacher_name' => $slot->teacher_name,
                            'start_time' => $slot->start_time,
                            'end_time' => $slot->end_time,
                            'type' => $slot->type,
                            'is_holiday' => false,
                            'holiday_type' => 'none',
                            'note' => null,
                            'academic_info' => "Academic Year: {$academicYear->year}, Class: {$section->academicClass->name}, Section: {$section->academicSection->name}",
                        ]);
                    }
                }
                $currentDate->addDay();
            }

            $this->command->info('Daily schedule seeded for academic year: ' . $academicYear->year);
        }
    }
}
