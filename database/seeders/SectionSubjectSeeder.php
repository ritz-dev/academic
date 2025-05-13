<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\AcademicClassSection;


class SectionSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = AcademicClassSection::all();
        $subjects = Subject::all();

        foreach ($sections as $section) {
            // Randomly assign 3 subjects per section
            $assignedSubjects = $subjects->random(3);

            foreach ($assignedSubjects as $subject) {
                $section->subjects()->attach($subject->id, [
                    'slug' => Str::uuid(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
