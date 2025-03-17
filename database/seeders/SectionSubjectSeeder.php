<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Section;
use Illuminate\Database\Seeder;
use App\Models\SectionSubject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SectionSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = Section::all();

        $subject_id1 = Subject::where('name','English')->pluck('id')->first();

        $subject_id2 = Subject::where('name','Myanmar')->pluck('id')->first();

        $subject_id3 = Subject::where('name','Math')->pluck('id')->first();
        
        $dataToInsert = [];

        foreach ($sections as $index => $section) {
            $dataToInsert[] = [
                'section_id' => $section->id,
                'subject_id' => $subject_id1,
            ];
            $dataToInsert[] = [
                'section_id' => $section->id,
                'subject_id' => $subject_id2,
            ];
            $dataToInsert[] = [
                'section_id' => $section->id,
                'subject_id' => $subject_id3,
            ];

            
        }

        SectionSubject::insert($dataToInsert);
    }
}
