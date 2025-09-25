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
        $sections = ['Section A', 'Section B'];

        foreach ($sections as $index => $sectionName) {
            Section::create([
                'slug' => generateCustomId($index),
                'name' => $sectionName,
            ]);
        }
    }
}