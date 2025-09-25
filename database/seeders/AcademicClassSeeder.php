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
        $classNames = range(1, 2); // [1, 2, 3, ..., 10]

        foreach ($classNames as $index => $number) {
            $customId = generateCustomId($index);

            AcademicClass::create([
                'slug' => $customId,
                'name' => 'Primary ' . $number,
            ]);
        }
    }
}
