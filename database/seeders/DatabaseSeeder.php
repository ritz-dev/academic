<?php

namespace Database\Seeders;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
       $this->call([
            AcademicYearSeeder::class,
            AcademicClassSeeder::class,
            GradeSeeder::class,
            SubjectSeeder::class,
            SectionSeeder::class,
            TimeTableSeeder::class,
            ExamSeeder::class,
            ExamScheduleSeeder::class,
            ExamTeacherAssignmentSeeder::class,
            ExamStudentAssignmentSeeder::class,
            SectionSubjectSeeder::class,
            HolidaySeeder::class,
       ]);
    }
}
