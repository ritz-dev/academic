<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\AcademicClass;
use Illuminate\Database\Seeder;
use App\Models\AcademicClassSection;

class AcademicClassSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYears = AcademicYear::all();
        $sections = Section::all();
        $academicClasses = AcademicClass::all();

        foreach ($academicYears as $year) {
            foreach ($academicClasses as $class) {
                foreach ($sections as $section) {
                    AcademicClassSection::create([
                        'academic_year_id' => $year->id,
                        'class_id' => $class->id,
                        'section_id' => $section->id,
                    ]);
                }
            }
        }
    
    }
}
