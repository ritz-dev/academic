<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Models\AcademicClass;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AcademicClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AcademicClass::create([
            'slug' => Str::uuid(),
            'name' => 'Primary 1',
            'limit' => 100,
            'academic_year_id' => 1,
        ]);

        AcademicClass::create([
            'slug' => Str::uuid(),
            'name' => 'Primary 2',
            'limit' => 100,
            'academic_year_id' => 1,
        ]);

        AcademicClass::create([
            'slug' => Str::uuid(),
            'name' => 'Primary 3',
            'limit' => 100,
            'academic_year_id' => 1,
        ]);

        AcademicClass::create([
            'slug' => Str::uuid(),
            'name' => 'Primary 4',
            'limit' => 100,
            'academic_year_id' => 1,
        ]);



        AcademicClass::create([
            'slug' => Str::uuid(),
            'name' => 'Primary 5',
            'limit' => 100,
            'academic_year_id' => 1,
        ]);

        AcademicClass::create([
            'slug' => Str::uuid(),
            'name' => 'Primary 6',
            'limit' => 100,
            'academic_year_id' => 1,
        ]);



        AcademicClass::create([
            'slug' => Str::uuid(),
            'name' => 'Primary 7',
            'limit' => 100,
            'academic_year_id' => 1,
        ]);

        AcademicClass::create([
            'slug' => Str::uuid(),
            'name' => 'Primary 8',
            'limit' => 100,
            'academic_year_id' => 1,
        ]);


        AcademicClass::create([
            'slug' => Str::uuid(),
            'name' => 'Primary 9',
            'limit' => 100,
            'academic_year_id' => 1,
        ]);


        AcademicClass::create([
            'slug' => Str::uuid(),
            'name' => 'Primary 10',
            'limit' => 100,
            'academic_year_id' => 1,
        ]);

    }
}
