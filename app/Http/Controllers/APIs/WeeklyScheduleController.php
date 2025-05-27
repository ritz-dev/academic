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

    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'academic_class_section_id' => 'required|exists:academic_class_sections,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'teacher_id' => 'nullable|string',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'required|in:class,break'
        ]);

        $schedule = WeeklySchedule::create($validated);

        return response()->json([
            'message' => 'Weekly schedule created successfully.',
            'data' => $schedule
        ],201);
    }
}
