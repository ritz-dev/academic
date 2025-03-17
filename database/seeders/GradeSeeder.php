<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Grade::create([
            'slug' => Str::uuid(),
            'name' => 'Grade 9'
        ]);

        Grade::create([
            'slug' => Str::uuid(),
            'name' => 'Grade 10'
        ]);
    }
}
