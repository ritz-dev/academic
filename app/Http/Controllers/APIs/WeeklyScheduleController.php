<?php

namespace App\Http\Controllers\APIs;

use Illuminate\Http\Request;
use App\Models\WeeklySchedule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;


class WeeklyScheduleController extends Controller
{
    public function bySection(Request $request)
    {
        try {
            // Validate the request input
            $validated = $request->validate([
                'academic_class_section_slug' => 'required|string|exists:academic_class_sections,slug',
            ]);
    
            // Retrieve the schedules for the given section
            $schedules = WeeklySchedule::where('academic_class_section_slug', $validated['academic_class_section_slug'])->get();
    
            // Check if any schedules were found
            if ($schedules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedules found for the given section.',
                    'data' => [],
                ], 404);
            }
    
            // Return the schedules
            return response()->json([
                'success' => true,
                'message' => 'Schedules retrieved successfully.',
                'data' => $schedules,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation error response
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Catch all other exceptions
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(), // Consider removing this in production to avoid exposing sensitive info
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Step 1: Validate basic fields first
        $validated = $request->validate([
            'academic_class_section_slug' => 'required|exists:academic_class_sections,slug',
            'subject_slug' => 'nullable|exists:subjects,slug',
            'teacher_slug' => 'nullable|string',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'required|in:class,break',
            'academic_info' => 'required|string',
        ]);

        // Step 2: Validate teacher_slug via external API if present
        if (!empty($validated['teacher_slug'])) {
            $teacherApiUrl = config('services.user_management.url') . 'teachers/show';

            // Fetch teacher info based on the section ID
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                // 'Authorization' => $request->header('Authorization'),
            ])->post($teacherApiUrl, ['slug' => $validated['teacher_slug']]);

            if ($response->failed() || !$response->json('data')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid teacher_slug.',
                    'errors' => ['teacher_slug' => ['Teacher not found.']]
                ], 422);
            }
        }

        // Step 3: Store the schedule
        $schedule = WeeklySchedule::create([
            'academic_class_section_slug' => $validated['academic_class_section_slug'],
            'subject_slug' => $validated['subject_slug'],
            'teacher_slug' => $validated['teacher_slug'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'type' => $validated['type'],
            'academic_info' => $validated['academic_info'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Weekly schedule created successfully.',
            'data' => $schedule
        ], 201);
    }
}
