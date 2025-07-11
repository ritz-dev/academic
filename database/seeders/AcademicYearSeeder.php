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
        $academicYears = [
            [
                'year' => '2023-2024',
                'start_date' => 20230801,
                'end_date' => 20241030,
                'status' => 'Completed'
            ],
            [
                'year' => '2024-2025',
                'start_date' => 20240801,
                'end_date' => 20251030,
                'status' => 'In Progress'
            ],
            [
                'year' => '2025-2026',
                'start_date' => 20250801,
                'end_date' => 20261030,
                'status' => 'Upcoming'
            ]
        ];
    
        foreach ($academicYears as $index => $data) {
            $data['slug'] = generateCustomId($index);

            // You can use $index here, e.g. for logging or custom logic
            AcademicYear::create($data);
        }
    }
}
