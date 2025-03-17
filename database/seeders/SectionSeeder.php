<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Support\Str;
use App\Models\AcademicClass;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academic_classes = AcademicClass::get();

        $classes = [];

        foreach($academic_classes as $index => $academic_class){
            $classes[] = [
                'slug' => $index === 0 ? '915c85ef-7781-4358-88fd-b65494980a2a' : Str::uuid(),
                'name' => 'Section A',
                'limit' => 100,
                'academic_class_id' => $academic_class->id,
                'teacher_id' => 1
            ];
            $classes[] = [
                'slug' => Str::uuid(),
                'name' => 'Section B',
                'limit' => 100,
                'academic_class_id' => $academic_class->id,
                'teacher_id' => 1
            ];
            $classes[] = [
                'slug' => Str::uuid(),
                'name' => 'Section C',
                'limit' => 100,
                'academic_class_id' => $academic_class->id,
                'teacher_id' => 1
            ];
            $classes[] = [
                'slug' => Str::uuid(),
                'name' => 'Section D',
                'limit' => 100,
                'academic_class_id' => $academic_class->id,
                'teacher_id' => 1
            ];
            $classes[] = [
                'slug' => Str::uuid(),
                'name' => 'Section E',
                'limit' => 100,
                'academic_class_id' => $academic_class->id,
                'teacher_id' => 1
            ];
        }

        Section::insert($classes);
    }
}
