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

        $section = AcademicClassSection::where('slug', $request->slug)->firstOrFail();

        $schedules = DailySchedule::where('academic_class_section_id', $section->id)->get();

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }
}
