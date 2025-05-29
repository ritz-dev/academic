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
        $validated = $request->validate([
            'limit' => 'sometimes|integer|min:1|max:100',
            'academic_class_section_slug' => 'sometimes|string|exists:academic_class_sections,slug',
            'academic_year_slug' => 'sometimes|string|exists:academic_years,slug',
            'academic_class_slug' => 'sometimes|string|exists:academic_classes,slug',
            'day_of_week' => 'sometimes|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'academic_info' => 'sometimes|string',
        ]);

        try {
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
    
            if (isset($validated['teacher_slug'])) {
                $query->where('teacher_slug', $validated['teacher_slug']);
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
        $validated = $request->validate([
            'academic_class_section_slug' => 'required|exists:academic_class_sections,slug',
            'subject_slug' => 'nullable|exists:subjects,slug',
            'teacher_slug' => 'nullable|string',
            'subject_name' => 'nullable|string',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'required|in:class,break',
            'academic_info' => 'required|string',
        ]);

        try {
            if (!empty($validated['teacher_slug'])) {
                $teacherApiUrl = rtrim(config('services.user_management.url'), '/') . '/teachers/show';
    
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    // 'Authorization' => $request->header('Authorization'),
                ])->post($teacherApiUrl, [
                    'slug' => $validated['teacher_slug']
                ]);
    
                if ($response->failed() || !$response->json('data')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid teacher_slug.',
                        'errors' => [
                            'teacher_slug' => ['Teacher not found in the external system.']
                        ]
                    ], 422);
                }
            }
    
            // Step 3: Save the schedule
            $schedule = WeeklySchedule::create([
                'academic_class_section_slug' => $validated['academic_class_section_slug'],
                'subject_slug' => $validated['subject_slug'],
                'teacher_slug' => $validated['teacher_slug'] ?? null,
                'teacher_name' => $validated['teacher_slug'] ? $response->json('data.name') : null,
                'subject_name' => $validated['subject_name'],
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
    
        } catch (ValidationException $e) {
            // Catch Laravel validation exceptions
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
    
        } catch (\Exception $e) {
            // Catch all other unexpected exceptions
            Log::error('Weekly Schedule Store Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while creating the schedule.'
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|exists:weekly_schedules,slug',
            'academic_class_section_slug' => 'required|exists:academic_class_sections,slug',
            'subject_slug' => 'nullable|exists:subjects,slug',
            'teacher_slug' => 'nullable|string',
            'subject_name' => 'nullable|string',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'required|in:class,break',
            'academic_info' => 'required|string',
        ]);

        try {
            // Find the existing schedule
            $schedule = WeeklySchedule::where('slug', $validated['slug'])->firstOrFail();

            // If teacher_slug is present, verify it using external API
            if (!empty($validated['teacher_slug'])) {
                $teacherApiUrl = rtrim(config('services.user_management.url'), '/') . '/teachers/show';

                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    // 'Authorization' => $request->header('Authorization'),
                ])->post($teacherApiUrl, [
                    'slug' => $validated['teacher_slug']
                ]);

                if ($response->failed() || !$response->json('data')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid teacher_slug.',
                        'errors' => [
                            'teacher_slug' => ['Teacher not found in the external system.']
                        ]
                    ], 422);
                }
            }

            // Update the schedule
            $schedule->update([
                'academic_class_section_slug' => $validated['academic_class_section_slug'],
                'subject_slug' => $validated['subject_slug'],
                'teacher_slug' => $validated['teacher_slug'] ?? null,
                'teacher_name' => $validated['teacher_slug'] ? $response->json('data.name') : null,
                'subject_name' => $validated['subject_name'],
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
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found.'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Weekly Schedule Update Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating the schedule.'
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|exists:weekly_schedules,slug',
        ]);

        try {
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
                'message' => 'An unexpected error occurred while deleting the schedule.'
            ], 500);
        }
    }
}
