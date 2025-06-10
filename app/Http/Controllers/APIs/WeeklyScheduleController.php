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
                'academic_class_section_slug' => ['nullable', 'string', 'exists:academic_class_sections,slug'],
                'day_of_week' => ['nullable', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
                'type' => ['nullable', 'in:class,break'],
                'teacher_slug' => ['nullable', 'string'], // optionally validate existence if needed
                'limit' => ['nullable', 'integer', 'min:1'],
                'skip' => ['nullable', 'integer', 'min:0'],
                'search' => ['nullable', 'string', 'max:255'],
            ]);
    
            $query = WeeklySchedule::query()
                ->when(!empty($validated['academic_class_section_slug']), fn($q) =>
                    $q->where('academic_class_section_slug', $validated['academic_class_section_slug']))
                ->when(!empty($validated['day_of_week']), fn($q) =>
                    $q->where('day_of_week', $validated['day_of_week']))
                ->when(!empty($validated['type']), fn($q) =>
                    $q->where('type', $validated['type']))
                ->when(!empty($validated['teacher_slug']), fn($q) =>
                    $q->where('teacher_slug', $validated['teacher_slug']))
                ->when(!empty($validated['search']), fn($q) =>
                    $q->where(function($query) use ($validated) {
                        $query->where('subject_name', 'like', '%'.$validated['search'].'%')
                              ->orWhere('teacher_name', 'like', '%'.$validated['search'].'%');
                    }));
    
            $total = (clone $query)->count();
    
            if (!empty($validated['skip'])) {
                $query->skip($validated['skip']);
            }
    
            if (!empty($validated['limit'])) {
                $query->take($validated['limit']);
            }
    
            $results = $query->get();

            // Extract unique student slugs
            $slugs = $results->pluck('teacher_slug')->filter()->unique()->values();

            $teacherData = collect();

            if ($slugs->isNotEmpty()) {
                $baseUrl = config('services.user_management.url');

                $type = 'student'; // or dynamically set this if needed
                $endpoint = "{$baseUrl}teachers";

                if ($endpoint) {
                    $response = Http::withHeaders([
                        'Accept' => 'application/json',
                        // You can include auth header if needed
                        // 'Authorization' => $request->header('Authorization'),
                    ])->post($endpoint, [
                        'slugs' => $slugs
                    ]);

                    if ($response->successful()) {
                        $teacherData = collect($response->json('data'))->keyBy('slug');
                    }
                }
            }

            $weeklyWithTeacher = $results->map(function ($result) use ($teacherData) {
                return array_merge($result->toArray(), [
                    'teacher' => $teacherData[$result->teacher_slug] ?? null,
                ]);
            });
    
            return response()->json([
                'status' => 'OK! The request was successful',
                'total' => $total,
                'data' => $weeklyWithTeacher,
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch weekly schedules.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'academic_class_section_slug' => 'required|exists:academic_class_sections,slug',
                'subject_slug' => 'nullable|string',
                'subject_name' => 'nullable|string',
                'teacher_slug' => 'nullable|string',
                'teacher_name' => 'nullable|string',
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
                'subject_slug' => $validated['subject_slug'] === '' ? null : $validated['subject_slug'],
                'teacher_slug' => $validated['teacher_slug'] === '' ? null : $validated['teacher_slug'],
                'teacher_name' => $validated['teacher_name'] === '' ? null : $validated['teacher_name'],
                'subject_name' => $validated['subject_name'] === '' ? null : $validated['subject_name'],
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
            ]);
    
        } catch (\Exception $e) {
    
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
                'subject_slug' => 'nullable|string',
                'teacher_slug' => 'nullable|string',
                'teacher_name' => 'nullable|string',
                'subject_name' => 'nullable|string',
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
                'subject_slug' => $validated['subject_slug'] === '' ? null : $validated['subject_slug'],
                'teacher_slug' => $validated['teacher_slug'] === '' ? null : $validated['teacher_slug'],
                'teacher_name' => $validated['teacher_name'] === '' ? null : $validated['teacher_name'],
                'subject_name' => $validated['subject_name'] === '' ? null : $validated['subject_name'],
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
            ]);

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
