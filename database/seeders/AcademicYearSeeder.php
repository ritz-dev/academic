<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AcademicYear::create([
            'slug' => Str::uuid(),
            'year' => '2023-2024',
            'start_date' => '2024-08-01',
            'end_date' => '2025-06-30',
            'status' => 'Completed'
        ]);

        AcademicYear::create([
            'slug' => Str::uuid(),
            'year' => '2024-2025',
            'start_date' => '2024-08-01',
            'end_date' => '2025-06-30',
            'status' => 'In Progress'
        ]);

        AcademicYear::create([
            'slug' => Str::uuid(),
            'year' => '2025-2026',
            'start_date' => '2025-08-01',
            'end_date' => '2026-06-30',
            'status' => 'Upcoming'
        ]);
    }
}
