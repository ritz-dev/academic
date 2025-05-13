<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AcademicYear::create([
            'year' => '2023-2024',
            'start_date' => '2023-08-01',
            'end_date' => '2024-06-30',
            'status' => 'Completed'
        ]);

        AcademicYear::create([
            'year' => '2024-2025',
            'start_date' => '2024-08-01',
            'end_date' => '2025-06-30',
            'status' => 'In Progress'
        ]);

        AcademicYear::create([
            'year' => '2025-2026',
            'start_date' => '2025-08-01',
            'end_date' => '2026-06-30',
            'status' => 'Upcoming'
        ]);
    }
}
