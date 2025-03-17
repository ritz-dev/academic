<?php

namespace Database\Seeders;

use App\Models\Exam;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Exam::create([
            'slug' => Str::uuid(),
            'name' => 'Final',
        ]);
    }
}
