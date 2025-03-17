<?php

namespace Database\Seeders;

use App\Models\ExamTeacherAssignment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExamTeacherAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExamTeacherAssignment::create([
            'exam_schedule_id' => '1',
            'teacher_id' => '1',
            'role' => 'Supervisor',
        ]);
    }
}
