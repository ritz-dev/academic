<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Illuminate\Http\Request;
use App\Models\DailySchedule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\AcademicClassSection;

class DailyScheduleController extends Controller
{
    public function bySection(Request $request)
    {
        // Validate the request if needed
        $request->validate([
            'slug'  => 'required'
        ]);

        try{

        $section = AcademicClassSection::where('slug', $request->slug)->first();

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found'
            ], 404);
        }

        logger($section);

        $schedules = DailySchedule::where('academic_class_section_id', $section->id)->take(10)->get();

        if ($schedules->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No schedules found for this section'
            ], 404);
        }
        
        logger($schedules);

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function byTeacherAcademicYear(Request $request)
    {
        // Validate the request if needed
        $request->validate([
            // 'teacher_id'  => 'required|string|max:255',
            // 'academic_year_id'  => 'required|academic_years.id'
        ]);

        try{

            $sectionIds = AcademicClassSection::where('academic_year_id', $request->academic_year_id)->pluck('id');

            if ($sectionIds->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sections found for the specified academic year.'
                ], 404);
            }


            $schedules = DailySchedule::with([
                'academicClassSection.academicYear',
                'academicClassSection.class',
                'academicClassSection.section',
                'subject'
            ])->whereIn('academic_class_section_id', $sectionIds)
                        ->where('teacher_id', $request->teacher_id)
                        ->orderBy('date', 'desc')
                        ->take(10)
                        ->get();

            if ($schedules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedules found for this teacher in the specified academic year.'
                ], 404);
            }

            Log::info('Schedules retrieved', ['schedules' => $schedules]);

            return response()->json([
                'success' => true,
                'data' => $schedules
            ]);
        
        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
