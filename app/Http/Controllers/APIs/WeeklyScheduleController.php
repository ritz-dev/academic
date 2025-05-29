<?php

namespace App\Http\Controllers\APIs;

use Illuminate\Http\Request;
use App\Models\WeeklySchedule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;


class WeeklyScheduleController extends Controller
{
    public function index(Request  $request)
    {
        try {
            $validated = $request->validate([
                'limit' => 'sometimes|integer|min:1|max:100',
                'teacher_name' => 'sometimes|string',
                'academic_class_section_slug' => 'sometimes|string|exists:academic_class_sections,slug',
                'academic_year_slug' => 'sometimes|string|exists:academic_years,slug',
                'academic_class_slug' => 'sometimes|string|exists:academic_classes,slug',
                'day_of_week' => 'sometimes|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'academic_info' => 'sometimes|string',
            ]);

            $limit = $validated['limit'] ?? null;

            $query = WeeklySchedule::query();
            
            if (isset($validated['academic_class_section_slug'])) {
                $query->where('academic_class_section_slug', $validated['academic_class_section_slug']);
            }

            if (isset($validated['academic_year_slug'])) {
                $query->whereHas('academicClassSection.academicYear', function ($q) use ($validated) {
                    $q->where('slug', $validated['academic_year_slug']);
                });
            }

            if (isset($validated['academic_class_slug'])) {
                $query->whereHas('academicClassSection.academicClass', function ($q) use ($validated) {
                    $q->where('slug', $validated['academic_class_slug']);
                });
            }    
    
            if (isset($validated['teacher_name'])) {
                $query->where('teacher_name',  'like', '%' . $validated['teacher_name'] . '%');
            }
    
            if (isset($validated['day_of_week'])) {
                $query->where('day_of_week', $validated['day_of_week']);
            }
    
            if (isset($validated['academic_info'])) {
                $query->where('academic_info', 'like', '%' . $validated['academic_info'] . '%');
            }
    
            $schedules = $limit ? $query->paginate($limit) : $query->get();

            if ($schedules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedules found.',
                    'data' => [],
                ], 404);
            }
    
            return response()->json([
                'status' => 'OK! The request was successful',
                'total' => $limit ? $schedules->total() : $schedules->count(),
                'data' => $limit ? $schedules->items() : $schedules,
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'academic_class_section_slug' => 'required|exists:academic_class_sections,slug',
                'subject_slug' => 'sometimes|exists:subjects,slug',
                'subject_name' => 'sometimes|string',
                'teacher_slug' => 'sometimes|string',
                'teacher_name' => 'sometimes|string',
                'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'type' => 'required|in:class,break',
                'academic_info' => 'required|string',
            ]);

            $overlap = WeeklySchedule::where('academic_class_section_slug', $request->academic_class_section_slug)
                    ->where('day_of_week', $request->day_of_week)
                    ->where(function ($query) use ($request) {
                        $query->where('start_time', '<', $request->end_time)
                            ->where('end_time', '>', $request->start_time);
                    })->exists();

            if ($overlap) {
                return response()->json([
                    'success' => false,
                    'message' => 'This time slot overlaps with another scheduled class.',
                ], 422);
            }
    
            // Step 3: Save the schedule
            $schedule = WeeklySchedule::create([
                'academic_class_section_slug' => $validated['academic_class_section_slug'],
                'subject_slug' => $validated['subject_slug'] ?? null,
                'teacher_slug' => $validated['teacher_slug'] ?? null,
                'teacher_name' => $validated['teacher_name'] ?? null,
                'subject_name' => $validated['subject_name'] ?? null,
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
    
        } catch (\Exception $e) {
            // Catch all other unexpected exceptions
            Log::error('Weekly Schedule Store Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:weekly_schedules,slug',
                'academic_class_section_slug' => 'required|exists:academic_class_sections,slug',
                'subject_slug' => 'sometimes|exists:subjects,slug',
                'teacher_slug' => 'sometimes|string',
                'teacher_name' => 'sometimes|string',
                'subject_name' => 'sometimes|string',
                'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'type' => 'required|in:class,break',
                'academic_info' => 'required|string',
            ]);

            // Find the existing schedule
            $schedule = WeeklySchedule::where('slug', $validated['slug'])->firstOrFail();

            // Update the schedule
            $schedule->update([
                'academic_class_section_slug' => $validated['academic_class_section_slug'],
                'subject_slug' => $validated['subject_slug'] ?? null,
                'teacher_slug' => $validated['teacher_slug'] ?? null,
                'teacher_name' => $validated['teacher_name'] ?? null,
                'subject_name' => $validated['subject_name'] ?? null,
                'day_of_week' => $validated['day_of_week'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'type' => $validated['type'],
                'academic_info' => $validated['academic_info'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Weekly schedule updated successfully.',
                'data' => $schedule
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:weekly_schedules,slug',
            ]);
            // Find the schedule by slug
            $schedule = WeeklySchedule::where('slug', $validated['slug'])->firstOrFail();

            // Delete the schedule
            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Weekly schedule deleted successfully.',
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found.'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Weekly Schedule Delete Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteBySection(Request $request)
    {
        try {
            $validated = $request->validate([
                'academic_class_section_slug' => 'required|string|exists:academic_class_sections,slug',
            ]);

            // Find the schedule by slug
            $schedule = WeeklySchedule::where('academic_class_section_slug', $validated['academic_class_section_slug'])->delete();

            return response()->json([
                'success' => true,
                'message' => 'Weekly schedules deleted successfully.',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Weekly Schedule Delete Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
