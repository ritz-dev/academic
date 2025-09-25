<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\AcademicClass;
use Illuminate\Database\Seeder;
use App\Models\AcademicClassSection;

class AcademicClassSectionSeeder extends Seeder
{
    public function run(): void
    {
        $academicYears = AcademicYear::all();
        $sections = Section::all();
        $academicClasses = AcademicClass::all();

        $indexx = 0;  // global counter

        foreach ($academicYears as $year) {
            foreach ($academicClasses as $class) {
                foreach ($sections as $section) {
                    $customId = generateCustomId($indexx++);
                    AcademicClassSection::create([
                        'slug' => $customId,
                        'academic_year_slug' => $year->slug,
                        'class_slug' => $class->slug,
                        'section_slug' => $section->slug,
                    ]);
                }
            }
        }
    }
}
