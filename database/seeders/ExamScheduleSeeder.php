<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Models\ExamSchedule;
use Illuminate\Database\Seeder;

class ExamScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExamSchedule::create([
            'slug' => Str::uuid(),
            'exam_id' => 1,
            'section_id' => 1,
            'subject' => 'M-101',
            'date'=> '2024-10-08',
            'start_time' => '09:00',
            'end_time' => '12:00'
        ]);
    }
}
