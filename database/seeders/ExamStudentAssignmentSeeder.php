<?php

namespace Database\Seeders;

use App\Models\ExamStudentAssignment;
use Illuminate\Database\Seeder;

class ExamStudentAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExamStudentAssignment::create([
            'exam_schedule_id' => '1',
            'student_id' => '1',
        ]);

        ExamStudentAssignment::create([
            'exam_schedule_id' => '1',
            'student_id' => '2',
        ]);
    }
}
