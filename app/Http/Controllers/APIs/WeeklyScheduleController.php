<?php

namespace App\Http\Controllers\APIs;

use App\Models\WeeklySchedule;
use App\Http\Controllers\Controller;
use App\Models\AcademicClassSection;
use Illuminate\Http\Request;


class WeeklyScheduleController extends Controller
{
    public function bySection(Request $request)
    {
        // Validate the request if needed
        $request->validate([
            'slug'  => 'required'
        ]);

        $section = AcademicClassSection::where('slug', $request->slug)->first();

        $schedules = WeeklySchedule::where('academic_class_section_id', $section->id)->get();

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }
}
