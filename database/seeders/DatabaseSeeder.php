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
        $this->call([
            AcademicYearSeeder::class,
            AcademicClassSeeder::class,
            SubjectSeeder::class,
            SectionSeeder::class,
            AcademicClassSectionSeeder::class,
            StudentEnrollmentSeeder::class,
            WeeklyScheduleSeeder::class,
            // AcademicAttendanceSeeder::class,
        ]);
    }
}
