<?php

namespace App\Http\Controllers\APIs;

use Illuminate\Http\Request;
use App\Models\DailySchedule;
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

        $section = AcademicClassSection::where('slug', $request->slug)->first();

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found'
            ], 404);
        }

        $schedules = DailySchedule::where('academic_class_section_id', $section->id)->get();

        if ($schedules->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No schedules found for this section'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }
}
