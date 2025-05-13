<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Section::create([
            'name' => 'Section A',
        ]);

        Section::create([
            'name' => 'Section B',
        ]);

        Section::create([
            'name' => 'Section C',
        ]);

        Section::create([
            'name' => 'Section D',
        ]);

        Section::create([
            'name' => 'Section E',
        ]);
    }
}
