<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = ['Math', 'English', 'Myanmar', 'Science', 'Geography'];

        foreach ($subjects as $index => $subjectName) {
            Subject::create([
                'name' => $subjectName,
                'slug' => generateCustomId($index), // generate UUID slug for every subject
            ]);
        }
    }
}
