<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use Illuminate\Database\Seeder;

class AcademicClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AcademicClass::create([
            'name' => 'Primary 1',
        ]);

        AcademicClass::create([
            'name' => 'Primary 2',
        ]);

        AcademicClass::create([
            'name' => 'Primary 3',
        ]);

        AcademicClass::create([
            'name' => 'Primary 4',
        ]);

        AcademicClass::create([
            'name' => 'Primary 5',
        ]);

        AcademicClass::create([
            'name' => 'Primary 6',
        ]);

        AcademicClass::create([
            'name' => 'Primary 7',
        ]);

        AcademicClass::create([
            'name' => 'Primary 8',
        ]);

        AcademicClass::create([
            'name' => 'Primary 9',
        ]);

        AcademicClass::create([
            'name' => 'Primary 10',
        ]);

    }
}
