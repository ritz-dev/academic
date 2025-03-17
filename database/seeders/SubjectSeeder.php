<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subject::create([
            'slug' => Str::uuid(),
            'name' => 'Math',
            'code' => 'M-101',
            'description' => 'Mathematic of Grade 10',
            'academic_class_id' => 1,
        ]);

        Subject::create([
            'slug' => Str::uuid(),
            'name' => 'English',
            'code' => 'E-101',
            'description' => 'English of Grade 10',
            'academic_class_id' => 1,
        ]);

        Subject::create([
            'slug' => Str::uuid(),
            'name' => 'Myanmar',
            'code' => 'My-101',
            'description' => 'Myanmar of Grade 10',
            'academic_class_id' => 1,
        ]);

        Subject::create([
            'slug' => Str::uuid(),
            'name' => 'Science',
            'code' => 'S-101',
            'description' => 'Science of Grade 10',
            'academic_class_id' => 1,
        ]);

        Subject::create([
            'slug' => Str::uuid(),
            'name' => 'Geography',
            'code' => 'G-101',
            'description' => 'Geography of Grade 10',
            'academic_class_id' => 1,
        ]);

        Subject::create([
            'slug' => Str::uuid(),
            'name' => 'History',
            'code' => 'H-101',
            'description' => 'History of Grade 10',
            'academic_class_id' => 1,
        ]);

        Subject::create([
            'slug' => Str::uuid(),
            'name' => 'Math',
            'code' => 'M-102',
            'description' => 'Mathematic of Grade 10',
            'academic_class_id' => 2,
        ]);

        Subject::create([
            'slug' => Str::uuid(),
            'name' => 'English',
            'code' => 'E-102',
            'description' => 'English of Grade 10',
            'academic_class_id' => 2,
        ]);

        Subject::create([
            'slug' => Str::uuid(),
            'name' => 'Myanmar',
            'code' => 'My-102',
            'description' => 'Myanmar of Grade 10',
            'academic_class_id' => 2,
        ]);

        Subject::create([
            'slug' => Str::uuid(),
            'name' => 'Science',
            'code' => 'S-102',
            'description' => 'Science of Grade 10',
            'academic_class_id' => 2,
        ]);

        Subject::create([
            'slug' => Str::uuid(),
            'name' => 'Geography',
            'code' => 'G-102',
            'description' => 'Geography of Grade 10',
            'academic_class_id' => 2,
        ]);

        Subject::create([
            'slug' => Str::uuid(),
            'name' => 'History',
            'code' => 'H-102',
            'description' => 'History of Grade 10',
            'academic_class_id' => 2,
        ]);
    }
}
