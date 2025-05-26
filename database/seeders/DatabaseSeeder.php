<?php

namespace Database\Seeders;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\DailyScheduleSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AcademicYearSeeder::class,
            AcademicClassSeeder::class,
            GradeSeeder::class,
            SubjectSeeder::class,
            SectionSeeder::class,
            ExamSeeder::class,
            ExamScheduleSeeder::class,
            ExamTeacherAssignmentSeeder::class,
            ExamStudentAssignmentSeeder::class,
            HolidaySeeder::class,
            AcademicClassSectionSeeder::class,
            StudentEnrollmentSeeder::class,
            WeeklyScheduleSeeder::class,
            DailyScheduleSeeder::class,
            AcademicAttendanceSeeder::class,
        ]);
    }
}
