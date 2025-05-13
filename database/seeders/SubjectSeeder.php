<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subject::create([
            'name' => 'Math',
        ]);

        Subject::create([
            'name' => 'English',
        ]);

        Subject::create([
            'slug' => Str::uuid(),
            'name' => 'Myanmar',
        ]);

        Subject::create([
            'name' => 'Science',
        ]);

        Subject::create([
            'name' => 'Geography',
        ]);

        Subject::create([
            'name' => 'History',
        ]);
    }
}
